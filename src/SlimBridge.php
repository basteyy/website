<?php declare(strict_types=1);

namespace basteyy\Website;

use Slim\App;

class SlimBridge
{
    /**
     * @var \DI\ContainerBuilder
     */
    private $builder;

    /**
     * SlimBridge constructor.
     */
    public function __construct()
    {
        $this->builder = new \DI\ContainerBuilder();
    }

    /**
     * @return \DI\ContainerBuilder
     */
    public function getBuilder(): \DI\ContainerBuilder
    {
        return $this->builder;
    }

    /**
     * @throws \Exception
     */
    public function getContainer(): \DI\Container
    {

        if(!isset($this->container)) {
            $this->container = $this->builder->build();
        }

        return $this->container;
    }

    /**
     * @throws \Exception
     */
    protected function boot() : void {
        if(!isset($this->app)) {
            $this->app = \DI\Bridge\Slim\Bridge::create($this->getContainer());
        }
    }

    /**
     * @throws \Exception
     */
    public function get() : App {
        if(!isset($this->app)) {
            $this->boot();
        }

        return $this->app;
    }
}