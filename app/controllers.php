<?php

$container['HomeController'] = function($container) {
	return new \App\Controllers\HomeController($container);
};

$container['AuthController'] = function($container) {
	return new \App\Controllers\AuthController($container);
};

$container['JwtController'] = function($container) {
	return new \App\Controllers\JwtController($container);
};

$container['BrandController'] = function($container) {
	return new \App\Controllers\BrandController($container);
};

$container['CategoryController'] = function($container) {
	return new \App\Controllers\CategoryController($container);
};

$container['BackendProductController'] = function($container) {
	return new \App\Controllers\BackendProductController($container);
};

$container['FrontendProductController'] = function($container) {
	return new \App\Controllers\FrontendProductController($container);
};

$container['WishlistController'] = function($container) {
	return new \App\Controllers\WishlistController($container);
};

$container['CheckoutController'] = function($container) {
	return new \App\Controllers\CheckoutController($container);
};

$container['BackendInvoiceController'] = function($container) {
	return new \App\Controllers\BackendInvoiceController($container);
};

$container['FrontendInvoiceController'] = function($container) {
	return new \App\Controllers\FrontendInvoiceController($container);
};

$container['JwtApi'] = function($container) {
	return new \App\Controllers\Api\JwtApi($container);
};

$container['ProductApi'] = function($container) {
	return new \App\Controllers\Api\ProductApi($container);
};

$container['WishlistApi'] = function($container) {
	return new \App\Controllers\Api\WishlistApi($container);
};

$container['CheckoutApi'] = function($container) {
	return new \App\Controllers\Api\CheckoutApi($container);
};

$container['InvoiceApi'] = function($container) {
	return new \App\Controllers\Api\InvoiceApi($container);
};

$container['InformationApi'] = function($container) {
	return new \App\Controllers\Api\InformationApi($container);
};
