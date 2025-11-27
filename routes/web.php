<?php

// ゲスト
$router->get(PATH . '/', 'User/GuestController', 'index');
$router->get(PATH . '/category/{parent}', 'User/GuestController', 'index');
$router->get(PATH . '/category/{parent}/{sub}', 'User/GuestController', 'index');
$router->get(PATH . '/category/{parent}/{sub}/{item}', 'User/GuestController', 'index');
$router->get(PATH . '/items/{itemId}', 'User/GuestController', 'show');

// エラー
$router->get(PATH . '/error', 'ErrorController', 'show');
// ユーザー仮登録
$router->get(PATH . '/temporary', 'User/RegisterController', 'temporary');
$router->post(PATH . '/temporary', 'User/RegisterController', 'temporarySend');
$router->get(PATH . '/guide', 'User/RegisterController', 'showGuide');
$router->post(PATH . '/guide', 'User/RegisterController', 'checkCode');
// ユーザー本登録
$router->get(PATH . '/userRegister', 'User/RegisterController', 'showForm');
$router->post(PATH . '/userRegister', 'User/RegisterController', 'sendForm');
$router->get(PATH . '/confirmInput', 'User/RegisterController', 'confirmInput');
$router->post(PATH . '/confirmInput', 'User/RegisterController', 'sendInput');
$router->get(PATH . '/completeRegister', 'User/RegisterController', 'complete');

// ユーザーログイン・ログアウト処理
$router->get(PATH . '/login', 'User/AuthController', 'loginForm');
$router->post(PATH . '/login', 'User/AuthController', 'login');
$router->post(PATH . '/logout', 'User/AuthController', 'logout');

// カート
$router->get(PATH . '/cart', 'User/CartController', 'showCart');
$router->post(PATH . '/cart', 'User/CartController', 'addInCart');
$router->post(PATH . '/cart/item/{id}/delete', 'User/CartController', 'removeFromCart');
$router->post(PATH . '/cart/update', 'User/CartController', 'updateCart');

// お気に入り
$router->get(PATH . '/favorite', 'User/FavoriteController', 'showFavorite');
$router->post(PATH . '/favorite/toggle', 'User/FavoriteController', 'toggle');
$router->post(PATH . '/favorite/delete', 'User/FavoriteController', 'delete');
$router->post(PATH . '/favorite', 'User/FavoriteController', 'moveToCart');
$router->post(PATH . '/favorite/move', 'User/FavoriteController', 'moveFromCart');

// 注文処理
$router->get(PATH . '/checkout/show', 'User/CheckoutController', 'showOrder');
$router->post(PATH . '/checkout/confirm', 'User/CheckoutController', 'confirm');
$router->post(PATH . '/checkout/payment', 'User/CheckoutController', 'payment');
$router->get(PATH . '/checkout/success', 'User/CheckoutController', 'success');
$router->get(PATH . '/checkout/cancel', 'User/CheckoutController', 'cancel');

$router->post(PATH . '/webhook/stripe', 'User/WebhookController', 'handleStripe');

// 管理者
$router->get(PATH . '/admin/login', 'Admin/AuthController', 'loginForm');
$router->post(PATH . '/admin/login', 'Admin/AuthController', 'login');
$router->post(PATH . '/admin/logout', 'Admin/AuthController', 'logout');
$router->get(PATH . '/admin/home', 'Admin/HomeController', 'showHome');
$router->get(PATH . '/admin/showOwner', 'Admin/HomeController', 'showOwner');
$router->get(PATH . '/admin/registerOwner', 'Admin/HomeController', 'showForm');
$router->post(PATH . '/admin/registerOwner', 'Admin/HomeController', 'sendForm');
$router->get(PATH . '/admin/owner/{id}/edit', 'Admin/HomeController', 'edit');
$router->post(PATH . '/admin/owner/{id}/edit', 'Admin/HomeController', 'update');
$router->post(PATH . '/admin/owner/{id}/delete', 'Admin/HomeController', 'delete');

$router->get(PATH . '/admin/category', 'Admin/CategoryController', 'createCategory');
$router->post(PATH . '/admin/category', 'Admin/CategoryController', 'storeCategory');
$router->get(PATH . '/admin/category/{id}/edit', 'Admin/CategoryController', 'editCategory');
$router->post(PATH . '/admin/category/{id}/update', 'Admin/CategoryController', 'updateCategory');
$router->get(PATH . '/admin/subCategory', 'Admin/CategoryController', 'createSubCategory');
$router->post(PATH . '/admin/subCategory', 'Admin/CategoryController', 'storeSubCategory');
$router->get(PATH . '/admin/subCategory/{id}/edit', 'Admin/CategoryController', 'editSubCategory');
$router->post(PATH . '/admin/subCategory/{id}/update', 'Admin/CategoryController', 'updateSubCategory');
$router->get(PATH . '/admin/itemCategoryList', 'Admin/CategoryController', 'index');
$router->get(PATH . '/admin/itemCategory', 'Admin/CategoryController', 'createItemCategory');
$router->post(PATH . '/admin/itemCategory', 'Admin/CategoryController', 'storeItemCategory');
$router->get(PATH . '/admin/itemCategory/{id}/edit', 'Admin/CategoryController', 'editItemCategory');
$router->post(PATH . '/admin/itemCategory/{id}/update', 'Admin/CategoryController', 'updateItemCategory');

// オーナー
$router->get(PATH . '/owner/login', 'Owner/AuthController', 'loginForm');
$router->post(PATH . '/owner/login', 'Owner/AuthController', 'login');
$router->post(PATH . '/owner/logout', 'Owner/AuthController', 'logout');
$router->get(PATH . '/owner/home', 'Owner/ShopController', 'showHome');
$router->get(PATH . '/owner/registerShop', 'Owner/ShopController', 'showForm');
$router->post(PATH . '/owner/registerShop', 'Owner/ShopController', 'registerShop');
$router->get(PATH . '/owner/shop/{id}/edit', 'Owner/ShopController', 'editShop');
$router->post(PATH . '/owner/shop/{id}/edit', 'Owner/ShopController', 'updateShop');
$router->post(PATH . '/owner/shop/deleteTempImage', 'Owner/ShopController', 'deleteTemp');

//item 新規登録
$router->get(PATH . '/owner/items', 'Owner/ItemController', 'index');
$router->get(PATH . '/owner/item/create', 'Owner/ItemController', 'create');
$router->post(PATH . '/owner/item/create', 'Owner/ItemController', 'confirm');
$router->get(PATH . '/owner/item/confirm', 'Owner/ItemController', 'confirmView');
$router->post(PATH . '/owner/item/confirm', 'Owner/ItemController', 'store');
//image
$router->get(PATH . '/owner/item/{itemId}/image', 'Owner/ImageController', 'create');
$router->post(PATH . '/owner/item/{itemId}/image', 'Owner/ImageController', 'store');
$router->get(PATH . '/owner/item/{itemId}/image/edit', 'Owner/ImageController', 'edit');
$router->post(PATH . '/owner/item/{itemId}/image/update', 'Owner/ImageController', 'update');
$router->post(PATH . '/owner/item/image/delete', 'Owner/ImageController', 'delete');
//item 編集
$router->get(PATH . '/owner/item/{itemId}/edit', 'Owner/ItemController', 'edit');
$router->post(PATH . '/owner/item/{itemId}/edit', 'Owner/ItemController', 'confirm');
$router->post(PATH . '/owner/item/{itemId}/update', 'Owner/ItemController', 'update');
$router->post(PATH . '/owner/item/{itemId}/delete', 'Owner/ItemController', 'delete');

//stock
$router->get(PATH . '/owner/item/{itemId}/stock', 'Owner/StockController', 'create');
$router->post(PATH . '/owner/item/{itemId}/stock', 'Owner/StockController', 'store');

// セッション破棄
$router->post(PATH . '/session/clearRedirect', 'SessionDestroyController', 'clearRedirect');