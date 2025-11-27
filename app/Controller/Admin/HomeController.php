<?php
namespace App\Controller\Admin;

use App\Controller\BaseController;
use App\Exceptions\ErrorHandler;
use App\Middleware\AuthMiddleware;
use App\Models\Owner;
use App\Repositories\OwnerRepository;
use App\Services\Admin\AdminService;
use App\Services\Auth\OwnerRegisterService;
use App\Services\Core\SessionService;
use App\Services\Validation\OwnerRegisterValidation;
use App\Services\Validation\OwnerUpdateValidation;
use Exception;

class HomeController extends BaseController
{
  private AdminService $adminService;

  public function __construct()
  {
    parent::__construct();
    $this->adminService = new AdminService();
    AuthMiddleware::checkAuth('admin');
  }

  public function showHome()
  {
    $this->render(APP_PATH . '/Views/admin/home.php');
  }

  public function showOwner()
  {
    $owners = $this->adminService->getAllOwners();
    $this->render(APP_PATH . '/Views/admin/owner-list.php', ['owners' => $owners]);
  }
  public function showForm()
  {
    $this->render(APP_PATH . '/Views/admin/owner-register.php');
  }

  public function sendForm()
  {
    $validator = new OwnerRegisterValidation();
    if(! $validator->validate($this->request)) {
      SessionService::set('errors', $validator->getErrors());
      SessionService::set('old', $validator->getOld());
      redirect('/admin/registerOwner');
    }
    $register = new OwnerRegisterService();
    if(! $register->registerOwner($this->request)) {
      SessionService::set('errors', '入力されたオーナーは登録済みです');
      SessionService::set('old', $this->request['email']);
      redirect('/admin/registerOwner');
    }
    SessionService::set('success', '新規オーナーを登録しました');
    redirect('/admin/showOwner');
  }

  public function edit(int $id)
  {
    if($owner = $this->adminService->getOwner($id)) {
      $this->render(APP_PATH . '/Views/admin/owner-edit.php', ['owner' => $owner]); 
    } else {
      ErrorHandler::log('存在しないidが入力されました');
      ErrorHandler::redirectWithCode(400);
    }
  }

  public function update(int $id)
  {
    $validator = new OwnerUpdateValidation();
    if(! $validator->validate($this->request)) {
      SessionService::set('errors', $validator->getErrors());
      SessionService::set('old', $validator->getOld());
      redirect("/admin/owner/{$id}/edit");
    }

    $result = $this->adminService->updateOwner($id, $this->request);
    if(! $result['success']) {
      switch ($result['error']) {
        case 'no_changes':
          SessionService::set('errors.general', '登録内容に変更がありません');
          break;
        case 'email_exists':
          SessionService::set('errors.email', '既に登録済のメールアドレスです');
          break;
        case 'not_found':
          ErrorHandler::redirectWithCode(500);
          break;
      }
      SessionService::set('old', ['name' => $this->request['name'], 'email' => $this->request['email']]);
      redirect("/admin/owner/{$id}/edit");
    }

    SessionService::set('success', 'オーナー情報を変更しました');
    redirect('/admin/showOwner');
  }

  public function delete(int $id): void
  {
    try {
      $repository = new OwnerRepository();
      $owner = $repository->findById($id);
      if($owner === null) {
        ErrorHandler::redirectWithCode(404);
      }
      $repository->delete($id);
      SessionService::set('success', 'オーナー情報を削除しました');
      redirect('/admin/showOwner');
    } catch(Exception $e) {
      ErrorHandler::redirectWithCode(500);
    }
  }

}