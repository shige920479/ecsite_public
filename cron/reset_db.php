<?php
require __DIR__ . '/../bootstrap/app.php';

use App\Services\Reset\ResetService;
use App\Services\Reset\SlackNotifier;

$resetService = new ResetService();
$notifier = new SlackNotifier();

$result = $resetService->run();

$notifier->send($result);

if ($result->hasErrors()) {
    echo "RESET FAILED\n";
    echo implode("\n", $result->getMessages());

} else {
    echo "RESET SUCCESS\n";
}


