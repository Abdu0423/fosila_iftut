<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Локаль устанавливается в SetLocale middleware.
        $this->app->booted(function () {
            $this->cloneWebRoutesUnderApi();
        });
    }

    /**
     * Те же веб-маршруты доступны как /api/... и отдают JSON для Vue.
     */
    protected function cloneWebRoutesUnderApi(): void
    {
        $router = $this->app['router'];
        $snapshot = [];

        foreach ($router->getRoutes() as $route) {
            $snapshot[] = $route;
        }

        foreach ($snapshot as $route) {
            $name = $route->getName();
            if ($name === 'spa.shell') {
                continue;
            }

            $uri = trim($route->uri(), '/');

            if ($uri === 'api' || str_starts_with($uri, 'api/')
                || str_starts_with($uri, 'sanctum')
                || str_starts_with($uri, '_ignition')
                || str_starts_with($uri, 'livewire')
                || str_contains($uri, '{any}')) {
                continue;
            }

            $target = $uri === '' ? 'api' : 'api/'.$uri;

            $action = $route->getAction();
            unset($action['as'], $action['prefix']);

            $methods = array_values(array_diff($route->methods(), ['HEAD']));
            if ($methods === []) {
                continue;
            }

            $clone = $router->addRoute($methods, $target, $action);

            foreach ($route->wheres as $parameter => $expression) {
                $clone->where($parameter, $expression);
            }
        }
    }
}
