<?php
namespace App\Services\Mail;

use App\Exceptions\ErrorHandler;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailerService
{
  public static function sendUserMail(string $to, string $subject, string $body): bool
  {
    // 本番環境では送信しない
    if(isProduction()) {
      ErrorHandler::log("[メール未送信（本番）] to: {$to}, subject: {$subject}");
      return true;
    }

    return self::send($to, $subject, $body);
  }

  public static function sendAdminMail(string $subject, string $body)
  {
    $to = ADMIN_MAIL ?? 'admin@example.com';
    $name = ADMIN_NAME ?? '管理者';

    return self::send($to, $subject, $body, $name);
  }

  private static function send(string $to, string $subject, string $body, ?string $name = null): bool
  {
    $mailer = new PHPMailer();

    try {
      $mailer->isSMTP();
      $mailer->Host = MAIL_HOST;
      $mailer->SMTPAuth = true;
      $mailer->Username = MAIL_USERNAME;
      $mailer->Password = MAIL_PASSWORD;
      $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      $mailer->Port = MAIL_PORT;

      $mailer->CharSet = 'UTF-8';
      $mailer->Encoding = 'base64';

      $mailer->setFrom(MAIL_FROM, MAIL_FROM_NAME);
      $mailer->addAddress($to, $name);
      $mailer->isHTML(true);
      $mailer->Subject = $subject;
      $mailer->Body = $body;

      if(! $mailer->send()) {
        throw new \RuntimeException("PHPMailer::send() が false を返しました（to: {$to}）");
      }
      
      return true;

    } catch(Exception $e) {
      ErrorHandler::log("メール送信失敗（to: {$to}）: " . $mailer->ErrorInfo);
      throw new \RuntimeException("メール送信失敗: " . $mailer->ErrorInfo);
    }
  }
}