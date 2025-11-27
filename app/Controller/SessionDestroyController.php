<?php
namespace App\Controller;

use App\Services\Core\SessionService;
use App\Services\Helper\ImageHandler;

class SessionDestroyController extends BaseController
{
  public function clearRedirect(): void
  {
    if(isset($_SESSION['tmp_image_path'])) clearTmpImageSessionAndFile();
    SessionService::clear(['item_preview', 'old', 'error']);

    $redirect = $_POST['redirect'] ?? (PATH . '/owner/home');

    redirect($redirect);
  }
}