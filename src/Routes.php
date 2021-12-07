<?php declare(strict_types=1);

namespace basteyy\Website;

use League\Plates\Engine;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;

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


    public function registerRoutes(array $routes) {
        foreach($routes as $route) {
            if(is_string($route[2])) {
                $template = $route[2];
                $route[2] = function(ServerRequestInterface $request, ResponseInterface $response) use ($template)  {
                    $response->getBody()->write(
                        $this->templateEnding->render($template)
                    );
                    return $response;
                };
            }

            $this->addRoute(strtoupper($route[0]), $route[1], $route[2]);
        }
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