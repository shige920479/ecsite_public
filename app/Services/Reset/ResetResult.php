<?php
namespace App\Services\Reset;

class ResetResult
{
  public int $errorCount = 0;
  public array $messages = [];

  public function addError(string $message): void
  {
    $this->errorCount++;
    $this->messages[] = $message;
  }

  public function hasErrors(): bool
  {
    return $this->errorCount > 0;
  }

  public function getMessages(): array
  {
    return $this->messages;
  }

  public function getErrorCount(): int
  {
    return $this->errorCount;
  }

  public function getErrorSummary(): string
  {
    if(empty($this->messages)) {
      return "No errors.";
    }

    return implode("\n", $this->messages);
  }
}