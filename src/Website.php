<?php declare(strict_types=1);

namespace basteyy\Website;

use League\Plates\Engine;
use Slim\App;

class Website
{
    private string $templatePath;

    private bool $debugMode = false;

    /** @var string Root Path of the project */
    private string $root;

    public function __construct()
    {
        $this->root = __DIR__;
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
            $slim->run();
        }catch (\Exception $exception) {
            if($this->debugMode) {
                $whoops = new \Whoops\Run;
                $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
                $whoops->handleException($exception);
            } else {
                http_response_code(200);
                if(file_exists($this->root . '/Resources/Templates/Errors/FatalStaticError.php')) {
                    echo file_get_contents($this->root . '/Resources/Templates/Errors/FatalStaticError.php');
                } else {
                    echo 'Gleich wieder da!';
                }
            }
        }
    }

    public function registerRoutes(array $routes) : void {
        if(!isset($this->routeHandler)) {
            $this->routeHandler = new Routes($this->getSlim());
        }

        $this->routeHandler->registerRoutes($routes);
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