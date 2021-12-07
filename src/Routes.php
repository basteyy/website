<?php declare(strict_types=1);

namespace basteyy\Website;

use League\Plates\Engine;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

final class Routes {

    const METHOD_POST = 'POST';
    const METHOD_GET = 'GET';

    private App $app;
    private Engine $templateEnding;

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->templateEnding = $app->getContainer()->get(Engine::class);
    }

    /**
     * Register a set of routes. Each route contains at first the method (post, get etc), the pattern and the callable.
     * @param array $routes
     * @throws \Exception
     * @see https://github.com/basteyy/website/wiki/Routes
     */
    public function registerRoutes(array $routes) {
        foreach($routes as $route) {
            $this->addRoute(strtoupper($route[0]), $route[1], $this->getRouteCallable($route[2]));
        }
    }

    public function registerGroupRoutes(string $groupAppendix, array $routes) : void {
        $this->app->group($groupAppendix, function (RouteCollectorProxy $routeCollectorProxy) use($routes) {
           foreach($routes as $route) {

               if(is_string($route[0])) {
                   $route[0] = explode('|', strtoupper($route[0]));
               }

               $routeCollectorProxy->map($route[0], $route[1], $this->getRouteCallable($route[2]));
           }
        });
    }

    private function getRouteCallable (mixed $callable) : mixed {
        if(is_string($callable) && !str_contains($callable, '\\')) {
            $template = $callable;
            return function(ServerRequestInterface $request, ResponseInterface $response) use ($template)  {
                $response->getBody()->write(
                    $this->templateEnding->render($template)
                );
                return $response;
            };
        }

        return $callable;
    }

    /**
     * Add a get route
     * @param string $pattern
     * @param $callback
     */
    public function get(string $pattern, $callback) : void {
        $this->addRoute(self::METHOD_GET, $pattern, $callback);
    }


    protected function addRoute(string $method, string $pattern, $callback) : void {

        switch($method) {
            case self::METHOD_GET:
                $this->app->get($pattern, $callback);
                break;

            case self::METHOD_POST:
                $this->app->post($pattern, $callback);
                break;

            default:
                throw new \Exception(sprintf('Invalid method "%s" for using a route.', $method));
        }


    }
}