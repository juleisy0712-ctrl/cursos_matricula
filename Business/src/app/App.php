<?php

use Slim\Factory\AppFactory;
use DI\Container;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

require __DIR__ . '/../../vendor/autoload.php';


$container = new Container();

$dotenv = Dotenv\Dotenv::createImmutable('/var/www/html');
$dotenv->load();

AppFactory::setContainer($container);

$app = AppFactory::create();

$app->addRoutingMiddleware();
$app->addBodyParsingMiddleware();

require_once "routes.php";
require_once "config.php";

$errorMiddleware = $app->addErrorMiddleware(true,true,true);

$app->add(function (ServerRequestInterface $request, RequestHandlerInterface $handler) use ($app): ResponseInterface {
	$origin = $request->getHeaderLine('Origin');
	$allowedOrigin = $_ENV['CORS_ALLOWED_ORIGIN'] ?? 'http://localhost:4200';

	if ($request->getMethod() === 'OPTIONS' && $origin === $allowedOrigin) {
		$response = $app->getResponseFactory()->createResponse(204);
	} else {
		$response = $handler->handle($request);
	}

	if ($origin !== $allowedOrigin) {
		return $response;
	}

	return $response
		->withHeader('Access-Control-Allow-Origin', $allowedOrigin)
		->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
		->withHeader('Access-Control-Allow-Headers', 'Accept, Authorization, Content-Type, Origin, X-Requested-With')
		->withHeader('Vary', 'Origin');
});

$app->run();