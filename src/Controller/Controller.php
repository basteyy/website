<?php declare(strict_types=1);

namespace basteyy\Website\Controller;

use League\Plates\Engine;
use Psr\Http\Message\ResponseInterface;

class Controller
{
    private Engine $engine;

    public function __construct(Engine $engine)
    {
        $this->engine = $engine;

        $this->engine->addFolder('CP', dirname(__DIR__) . '/Static/Templates/ControlPanel/');
    }

    protected function isPost() : bool {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function isGet() : bool {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    protected function render(ResponseInterface $response, string $templateName, array $data = [] ) : ResponseInterface {

        $response->getBody()->write($this->engine->render($templateName, $data));

        return $response;
    }
}