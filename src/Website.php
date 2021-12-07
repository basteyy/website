<?php declare(strict_types=1);

namespace basteyy\Website;

use League\Plates\Engine;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\App;
use Slim\Handlers\Strategies\RequestHandler;

class Website
{
    /** @var string Template Path */
    private string $templatePath;

    /** @var bool State of debugging */
    private bool $debugMode = false;

    /** @var bool State of the control panel */
    private bool $controlPanel = false;

    private string $controlPanelNamespace;

    /** @var string Root Path of the project */
    private string $root;

    public function __construct()
    {
        $this->root = __DIR__;
    }

    /**
     * Enable the control panel tool
     * @see https://github.com/basteyy/website/wiki/Control-Panel
     */
    public function enableControlPanel(string $controlPanelUrl = '/control-panel') : void {
        $this->controlPanel = true;
        $this->controlPanelNamespace = $controlPanelUrl;
    }

    /**
     * Change the debug mode
     * @param bool $debugeState
     */
    public function setDebugMode(bool $debugeState) : void {
        $this->debugMode = $debugeState;
    }

    /**
     * Define the current webroot
     * @param string $webroot
     */
    public function setWebroot(string $webroot) : void {
        $this->webroot = $webroot;
    }

    /**
     * Add the template path
     * @param string $path
     */
    public function addTemplateFolder(string $path) {

        if(!is_dir($path)) {
            throw new \Exception(sprintf('Website cannot use template path %s, because its not a valid folder', $path));
        }

        if (!is_readable($path)) {
            throw new \Exception(sprintf('Cannot access %s', $path));
        }

        $this->templatePath = $path;
    }


    /**
     * @throws \Exception
     */
    public function run() {
        $slim = $this->getSlim();

        try {

            // COntrol Panel?
            if($this->controlPanel) {
                if(!file_exists($this->root . '/Routes/ControlPanelRoutes.php')) {
                    throw new \Exception(sprintf('Control Panel Routes file not exists/not be found at %s', $this->root . '/Routes/ControlPanelRoutes.php'));
                }
                $this->getRouter()->registerGroupRoutes($this->controlPanelNamespace, require $this->root . '/Routes/ControlPanelRoutes.php');
            }

            // Add the slash middleware
            $slim->add(function (RequestInterface $request, RequestHandlerInterface $handler) {
                $uri = $request->getUri();
                $path = $uri->getPath();

                if ($path != '/' && substr($path, -1) == '/') {
                    // recursively remove slashes when its more than 1 slash
                    $path = rtrim($path, '/');

                    // permanently redirect paths with a trailing slash
                    // to their non-trailing counterpart
                    $uri = $uri->withPath($path) . ((strlen($uri->getQuery()) > 1) ? '?' . $uri->getQuery() : '');

                    if ($request->getMethod() == 'GET') {
                        ob_clean();
                        http_response_code(301);
                        header("Location: " . $path);
                        header("Connection: close");
                        exit();
                    } else {
                        $request = $request->withUri($uri);
                    }
                }

                return $handler->handle($request);
            });


            $slim->run();
        }catch (\Exception $exception) {
            if($this->debugMode) {
                $whoops = new \Whoops\Run;
                $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
                $whoops->handleException($exception);
            } else {
                ob_clean();
                http_response_code(200);
                if(file_exists($this->root . '/Resources/Templates/Errors/FatalStaticError.php')) {
                    echo file_get_contents($this->root . '/Resources/Templates/Errors/FatalStaticError.php');
                } else {
                    echo 'Gleich wieder da!';
                }
                exit();
            }
        }
    }

    /**
     * Return the route class
     * @return Routes
     * @throws \Exception
     */
    public function getRouter(): Routes
    {
        if(!isset($this->routeHandler)) {
            $this->routeHandler = new Routes($this->getSlim());
        }

        return $this->routeHandler;
    }

    /***
     * Return the Slim App
     * @return App
     * @throws \Exception
     */
    private function getSlim() : App
    {
        if(!isset($this->slim)) {
            $this->slim = new SlimBridge();
            $this->slim->getContainer()->set(Engine::class, new Engine($this->templatePath));
        }

        return $this->slim->get();
    }
}