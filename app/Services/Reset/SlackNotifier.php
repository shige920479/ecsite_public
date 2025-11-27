<?php 
namespace App\Services\Reset;

class SlackNotifier
{
  private string $webhook;

  public function __construct()
  {
    $this->webhook = SLACK_WEBHOOK_URL ?? '';
  }

  public function send(ResetResult $result): void
  {
    if(! $this->webhook) {
      return;
    }

    $status = ! $result->hasErrors() ? '✅ SUCCESS' : '⚠️ WARNINGS / ERRORS';
    $text = $result->getErrorSummary();

    $payload = json_encode([
      'text' => "*ECサイト Reset Report*\n" .
                "Status: {$status}\n" .
                "Error Count: {$result->getErrorCount()}\n" .
                "```{$text}```"
    ]);

    $ch = curl_init($this->webhook);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_CRLF, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      'Content-Type: application/json'
    ]);

    curl_exec($ch);
    curl_close($ch);
  }
}