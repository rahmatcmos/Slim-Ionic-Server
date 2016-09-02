<?php

use App\Middleware\AdminMiddleware;
use App\Middleware\AdminxStaffMiddleware;
use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use App\Middleware\AjaxMiddleware;
use App\Middleware\JwtMiddleware;

/*-----------------------------------------------------------*/

/**
* Route path for REST API
* Access limited to XHR request only
* Require JWT token
*/
$app->group('',function () {
	$this->get('/api/v1/wishlist','WishlistApi:manageWishlist');
	$this->delete('/api/v1/wishlist/delete','WishlistApi:deleteWishlist');
	$this->post('/api/v1/wishlist/add','WishlistApi:addWishlist');

	$this->get('/api/v1/checkout','CheckoutApi:getCheckout');
	$this->delete('/api/v1/checkout/delete','CheckoutApi:deleteCheckout');
	$this->post('/api/v1/checkout/add','CheckoutApi:addCheckout');

	$this->get('/api/v1/invoice','InvoiceApi:getInvoice');
	$this->get('/api/v1/invoice/view','InvoiceApi:viewInvoice');
	$this->post('/api/v1/invoice/generate','InvoiceApi:generateInvoice');
	$this->delete('/api/v1/invoice/delete','InvoiceApi:deleteInvoice');
})->add(new AjaxMiddleware($container))->add(new JwtMiddleware($container));

/*-----------------------------------------------------------*/

/**
* Route path for REST API
* Access limited to XHR request only
* Not require JWT token
*/
$app->group('',function () {
	$this->post('/api/v1/jwt/signup','JwtApi:signUp');
	$this->post('/api/v1/jwt/signin','JwtApi:signIn');
	$this->get('/api/v1/jwt/signout','JwtApi:signOut');

	$this->get('/api/v1/product','ProductApi:listProduct')->setName('api.list.product');
	$this->get('/api/v1/product/search','ProductApi:searchProduct');
	$this->get('/api/v1/product/view','ProductApi:viewProduct');

	$this->get('/api/v1/information','InformationApi:getCompanyInfo');
})->add(new AjaxMiddleware($container));

/*-----------------------------------------------------------*/

/**
* Public route path for frontend
* Does not require any access permission
*/
$app->get('/','HomeController:index')->setName('home');
$app->get('/product','FrontendProductController:listProduct')->setName('frontend.list.product');
$app->get('/product/search','FrontendProductController:searchProduct')->setName('frontend.search.product');
$app->get('/product/view','FrontendProductController:viewProduct')->setName('frontend.view.product');

/*-----------------------------------------------------------*/

/**
* Non logged-in user route path for frontend
* Access limited to guest and user not for logged-in user
*/
$app->group('',function () {
	$this->get('/auth/signup','AuthController:getSignUp')->setName('auth.signup');
	$this->post('/auth/signup','AuthController:postSignUp');

	$this->get('/auth/signin','AuthController:getSignIn')->setName('auth.signin');
	$this->post('/auth/signin','AuthController:postSignIn');

	$this->get('/auth/reset','AuthController:getResetPassword')->setName('auth.reset');
	$this->post('/auth/reset','AuthController:postResetPassword');

	$this->get('/auth/recover','AuthController:getRecoverAccount')->setName('auth.recover');
	$this->post('/auth/recover','AuthController:postRecoverAccount');
})->add(new GuestMiddleware($container));

/*-----------------------------------------------------------*/

/**
* Logged-in user route path for frontend
* Access limited to logged-in user only
*/
$app->group('',function () {
	$this->get('/jwt','JwtController:getJwt')->setName('jwt.index');
	$this->post('/jwt/delete','JwtController:deleteJwt')->setName('jwt.delete');

	$this->get('/auth/signout','AuthController:getSignOut')->setName('auth.signout');
	$this->get('/auth/password/change','AuthController:getChangePassword')->setName('auth.password.change');
	$this->post('/auth/password/change','AuthController:postChangePassword');

	$this->get('/wishlist','WishlistController:getWishlist')->setName('wishlist.index');
	$this->post('/wishlist/delete','WishlistController:deleteWishlist')->setName('wishlist.delete');
	$this->post('/wishlist/add','WishlistController:addWishlist')->setName('wishlist.add');

	$this->get('/checkout','CheckoutController:getCheckout')->setName('checkout.index');
	$this->post('/checkout/delete','CheckoutController:deleteCheckout')->setName('checkout.delete');
	$this->post('/checkout/add','CheckoutController:addCheckout')->setName('checkout.add');

	$this->get('/invoice','FrontendInvoiceController:getInvoice')->setName('frontend.invoice.index');
	$this->get('/invoice/view','FrontendInvoiceController:viewInvoice')->setName('frontend.invoice.view');
	$this->post('/invoice/generate','FrontendInvoiceController:generateInvoice')->setName('frontend.invoice.generate');
	$this->post('/invoice/delete','FrontendInvoiceController:deleteInvoice')->setName('frontend.invoice.delete');
})->add(new AuthMiddleware($container));
/*-----------------------------------------------------------*/

/**
* Admin route path for frontend
* Access limited to ADMIN only
*/
$app->group('',function () {

	$this->get('/dashboard/member','AuthController:getUser')->setName('dashboard.manage.user');
	$this->post('/dashboard/member/delete','AuthController:deleteUser')->setName('dashboard.delete.user');
	$this->post('/dashboard/member/update','AuthController:updateUser')->setName('dashboard.update.user');
})->add(new AdminMiddleware($container));

/*-----------------------------------------------------------*/

/**
* Admin route path for frontend
* Access limited to ADMIN only
*/
$app->group('/dashboard',function () {

	$this->get('/category','CategoryController:getCategory')->setName('dashboard.manage.category');
	$this->post('/category/add','CategoryController:addCategory')->setName('dashboard.add.category');
	$this->post('/category/delete','CategoryController:deleteCategory')->setName('dashboard.delete.category');
	$this->post('/category/update','CategoryController:updateCategory')->setName('dashboard.update.category');

	$this->get('/brand','BrandController:getBrand')->setName('dashboard.manage.brand');
	$this->post('/brand/add','BrandController:addBrand')->setName('dashboard.add.brand');
	$this->post('/brand/delete','BrandController:deleteBrand')->setName('dashboard.delete.brand');
	$this->post('/brand/update','BrandController:updateBrand')->setName('dashboard.update.brand');

	$this->get('/product','BackendProductController:getProduct')->setName('dashboard.manage.product');
	$this->get('/product/search','BackendProductController:searchProduct')->setName('dashboard.search.product');
	$this->get('/product/add','BackendProductController:getAddProduct')->setName('dashboard.add.product');
	$this->post('/product/add','BackendProductController:postAddProduct');
	$this->get('/product/update','BackendProductController:getUpdateProduct')->setName('dashboard.update.product');
	$this->post('/product/update','BackendProductController:postUpdateProduct');
	$this->post('/product/delete','BackendProductController:deleteProduct')->setName('dashboard.delete.product');

	$this->get('/invoice','BackendInvoiceController:getInvoice')->setName('dashboard.manage.invoice');
	$this->get('/invoice/search','BackendInvoiceController:searchInvoice')->setName('dashboard.search.invoice');
	$this->get('/invoice/view','BackendInvoiceController:viewInvoice')->setName('dashboard.view.invoice');
	$this->post('/invoice/delete','BackendInvoiceController:deleteInvoice')->setName('dashboard.delete.invoice');
	$this->post('/invoice/update','BackendInvoiceController:updateInvoice')->setName('dashboard.update.invoice');
})->add(new AdminxStaffMiddleware($container));


/*-----------------------------------------------------------*/
