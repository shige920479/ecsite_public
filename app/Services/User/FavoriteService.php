<?php
namespace App\Services\User;

use App\Database\DbConnect;
use App\Exceptions\ErrorHandler;
use App\Repositories\CartRepository;
use App\Repositories\FavoriteRepository;
use App\Services\Core\SessionService;
use Exception;

class FavoriteService
{
  private FavoriteRepository $favoriteRepo;
  private CartRepository $cartRepo;

  public function __construct(FavoriteRepository $favoriteRepo, CartRepository $cartRepo)
  {
    $this->favoriteRepo = $favoriteRepo;
    $this->cartRepo = $cartRepo;
  }

  public function addInCartAndRemoveFavorite(array $favorite): void
  {
    try {
      DbConnect::beginTransaction();
      
      $this->cartRepo->insert($favorite['user_id'], $favorite['item_id'], 1); //1:初期値(quantity)
      $this->favoriteRepo->deleteById($favorite['id']);

      DbConnect::commitTransaction();

    } catch(Exception $e) {
      DbConnect::rollbackTransaction();
      throw $e;
    }
  }

  public function addInFavoriteAndRemoveCart(array $cartItem)
  {
    try {
      DbConnect::beginTransaction();

      $this->favoriteRepo->add($cartItem['user_id'], $cartItem['item_id']);
      $this->cartRepo->deleteCartItemById($cartItem['id']);

      DbConnect::commitTransaction();
    
    } catch(Exception $e) {
      DbConnect::rollbackTransaction();
      throw $e;
    }
  }
}