<?php
namespace App\Controller\User;

use App\Controller\BaseController;
use App\Exceptions\ErrorHandler;
use App\Repositories\CartClearErrorsRepository;
use App\Repositories\CartRepository;
use App\Repositories\OrderRepository;
use App\Repositories\WebhookEventsRepository;
use App\Services\Mail\MailerService;
use App\Services\User\StripeWebhookService;
use Exception;
use Stripe\Webhook;


class WebhookController extends BaseController
{
  private StripeWebhookService $stripeWebhookService;
  private CartClearErrorsRepository $cartClearErrorRepo;
  private OrderRepository $orderRepo;
  private WebhookEventsRepository $webhookRepo;

  public function __construct()
  {
    $this->cartClearErrorRepo = new CartClearErrorsRepository();
    $this->orderRepo = new OrderRepository();
    $this->stripeWebhookService = new StripeWebhookService($this->orderRepo, new CartRepository());
    $this->webhookRepo =new WebhookEventsRepository();
  }

  /**
   * stipe-webhook通知受領、ordersテーブル更新、在庫変更/エラー時の処理
   */
  public function handleStripe()
  {
    $endpointSecret = STRIPE_WEBHOOK_SECRET;

    $payload = file_get_contents('php://input');
    $sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

    try {
      $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);

      $eventId = $event->id;
      if($this->webhookRepo->isProcessed($eventId)) { //再送防止（冪等性制御）同じイベントを複数回送ることがある
        http_response_code(200);
        exit;
      }
      $this->webhookRepo->markAsProcessed($eventId); // すでに処理済

    } catch (\UnexpectedValueException $e) {
      http_response_code(400);
      exit('Invalid payload');
    } catch(\Stripe\Exception\SignatureVerificationException $e) {
      http_response_code(400);
      exit('Invalid signature');
    } catch(Exception) {
      http_response_code(500);
      exit('Internal Server Error');
    }

    if($event->type === 'checkout.session.completed') {
      $session = $event->data->object;
      $sessionId = $session->id;
      $orderId = $session->metadata->order_id ?? null;
      $userEmail = $session->customer_details->email ?? null;
      $userName = $session->customer_details->name ?? 'お客様';
      $order = $this->orderRepo->findById($orderId);

      if($orderId) {
        try {
          $this->stripeWebhookService->registerStripeSessionId($orderId, $sessionId);

          if(! $userEmail) {
            ErrorHandler::log("メールアドレスが取得できませんでした（session_id: {$sessionId}");
            MailerService::sendAdminMail(
              '【ECサイト】新しい注文通知（メール送信エラー）',
              "オーダーNO：{$orderId} 注文がありました。メール送信エラーが発生しているので確認下さい（session_id: {$sessionId}"
            );
          } else {
            MailerService::sendUserMail(
              $userEmail,
              'ご注文ありがとうございました',
              $this->buildEmailBody($userName, $order)
            );

            MailerService::sendAdminMail(
              '【ECサイト】新しい注文通知',
              "オーダーNO：{$orderId} 注文がありました。管理画面をご確認ください "
            );
          }

        } catch (Exception $e) {
          ErrorHandler::log($e->getMessage());
          http_response_code(500);
          exit;
        }
      }
    }

    http_response_code(200);

    try {
      $this->stripeWebhookService->clearOrderedItemsFromCart($orderId);
    } catch(Exception $e) {
      $userId = $order['user_id'];
      $this->cartClearErrorRepo->insert($userId, $orderId, $e->getMessage());
    }
  }

  private function buildEmailBody(string $userName, array $order): string
  {
    $orderNumber = h($order['id']);
    $date = date('Y年m月d日 H:i');

    return <<<HTML
<p>{$userName} 様</p>
<p>この度はご注文いただき誠にありがとうございます。</p>
<p>ご注文番号：<strong>{$orderNumber}</strong></p>
<p>注文日時：{$date}</p>
<p>ご注文内容の詳細はマイページよりご確認いただけます。</p>
<p>今後ともよろしくお願いいたします。</p>
HTML;
  }

}