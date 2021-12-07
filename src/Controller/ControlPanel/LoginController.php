<?php declare(strict_types=1);

namespace basteyy\Website\Controller\ControlPanel;

use basteyy\Website\Controller\Controller;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class LoginController extends Controller
{
    public function __invoke(RequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        if($this->isPost()) {

        }



        return $this->render($response, 'CP::login');
    }
}