<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Helpers/URI_helper.php';
require __DIR__ . '/../app/Helpers/JWT_helper.php';

define('BASEPATH', realpath(dirname(__DIR__, 1)));

use Respect\Validation\Validator as v;
v::with('App\\Validation\\Rules\\');

session_save_path(BASEPATH.'/sessions');
session_start();

$settings = require __DIR__ . '/../app/settings.php';
$app = new \Slim\App($settings);
$container = $app->getContainer();

$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container['settings']['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$container['db'] = function ($container) use ($capsule) {
	return $capsule;
};

$container['auth'] = function($container) {
	return new \App\Services\Auth;
};

$container['wishlist'] = function($container) {
	return new \App\Services\Wishlist;
};

$container['jwtauth'] = function($container) {
	return new \App\Services\JwtAuth;
};

$container['flash'] = function($container) {
	return new \Slim\Flash\Messages;
};

$container['view'] = function ($container) {
	$view = new \Slim\Views\Twig(__DIR__ . '/../resources/views/', [
		'cache' => false,
		'debug' => true,
	]);

	$view->addExtension(new Twig_Extension_Debug());
	$view->addExtension(new \Slim\Views\TwigExtension(
		$container->router,
		$container->request->getUri()
	));

	$view->getEnvironment()->addGlobal('auth',[
		'check' => $container->auth->check(),
		'user' => $container->auth->user(),
		'totalWishlist'=> $container->wishlist->totalWishlist(),
	]);

	$view->getEnvironment()->addGlobal('company_info',$container->get('settings')['shipping']);

	$view->getEnvironment()->addGlobal('config',$container->get('settings')['config']);

	$view->getEnvironment()->addGlobal('flash',$container->flash);

	$view->getEnvironment()->addGlobal('_get',$_GET);

	return $view;
};

$container['logger'] = function ($container) {
	$settings = $container->get('settings')['logger'];
	$logger = new Monolog\Logger($settings['name']);
	$logger->pushProcessor(new Monolog\Processor\UidProcessor());
	$logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
	return $logger;
};

$container['validator'] = function ($container) {
	return new \App\Validation\Validator;
};

$container['csrf'] = function($container) {
	return new \App\Middleware\CsrfGuard;
};

$app->add(new \App\Middleware\ValidationErrorsMiddleware($container));
$app->add(new \App\Middleware\OldInputMiddleware($container));
$app->add(new \App\Middleware\CsrfViewMiddleware($container));

$app->add($container->csrf);

require __DIR__ . '/../app/controllers.php';
require __DIR__ . '/../app/routes.php';
