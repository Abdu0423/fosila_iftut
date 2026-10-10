<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$routes = [];

foreach (app('router')->getRoutes() as $route) {
    $name = $route->getName();
    $uri = $route->uri();

    if (! $name || str_starts_with($uri, 'api/') || $uri === 'api' || $name === 'spa.shell') {
        continue;
    }

    $routes[$name] = ['uri' => $uri];
}

$path = __DIR__.'/../resources/js/router/laravelRoutes.json';
file_put_contents($path, json_encode($routes, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
echo 'routes: '.count($routes).PHP_EOL;
