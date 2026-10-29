<?php

namespace App\controllers;

use Slim\Routing\RouteCollectorProxy;

$app->group('/api', function (RouteCollectorProxy $api) {
    $api->group('/cursos', function (RouteCollectorProxy $endpoint) {
        $endpoint->get('', CursosMatricula::class . ':read');
        $endpoint->get('/{id}', CursosMatricula::class . ':read');
        $endpoint->post('', CursosMatricula::class . ':create');
        $endpoint->put('/{id}', CursosMatricula::class . ':update');
        $endpoint->delete('/{id}', CursosMatricula::class . ':delete');
    });
});
