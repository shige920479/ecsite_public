<?php
namespace App\Services\Helper;

use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripeSessionClient
{
  public function __construct()
  {
    Stripe::setApiKey(STRIPE_SECRET_KEY);
  }

  public function createCheckoutSession(array $lineItems, string $baseUrl, int $orderId): string
  {
    $checkout_session = Session::create([
      'payment_method_types' => ['card'],
      'line_items' => $lineItems,
      'mode' => 'payment',
      'success_url' => $baseUrl . 'ecsite/checkout/success?session_id={CHECKOUT_SESSION_ID}',
      'cancel_url' => $baseUrl . 'ecsite/checkout/cancel?session_id={CHECKOUT_SESSION_ID}',
      'metadata' => [
        'order_id' => $orderId,
      ]
    ]);

    return $checkout_session->url;
  }

  public function retrieve(string $sessionId)
  {
    return Session::retrieve($sessionId);
  }

}