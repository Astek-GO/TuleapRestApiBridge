<?php
/**
 * This file is part of the project TuleapRestApiBridge
 *
 * Last edited by jpvantelon@astek.fr
 *
 * Copyright ASTEK (c) 2015.
 */

namespace AstekGo\TuleapRestApiBridge;

use Klein\DataCollection\RouteCollection;
use Klein\Klein;
use Klein\Request;
use Klein\Response;
use Klein\Route;

class Router
{
    /** @var Klein */
    protected $router;
    /** @var bool */
    protected $debug;

    /**
     * Router constructor.
     */
    public function __construct()
    {
        $this->router = new Klein();
        $this->loadConfig();
    }

    /**
     * Set the routes
     *
     * @param array $routes Array of routes configurations
     *
     * @throws \Exception
     */
    public function configureRoutes($routes)
    {
        $routeCollection = new RouteCollection();
        foreach ($routes as $route) {
            if ($this->debug) {
                Log::write('Configuring route "'.$route['path'].'"');
            }
            $routeObject = $this->router->respond($route['httpMethod'], $route['path'], $route['callback']);
            $routeObject->setName($route['name']);
            $routeCollection->set($route['name'], $routeObject);
        }
        $this->router = new Klein($this->router->service(), $this->router->app(), $routeCollection);

        if ($this->debug) {
            // Add a catchall debugging route
            Log::write('Configuring catchall route');
            $this->router->respond(
                '*',
                function (Request $request, Response $response) {
                    Log::write(' ==> URI called : "'.$request->uri().'" / User Agent : "'.$request->userAgent().'"');
                    Log::write(' <== Response code : '.$response->code());
                }
            );
        }
    }

    /**
     * Retrieve the IDE configuration datas
     *
     * @return array
     */
    public function getIDEConfiguration()
    {
        $datas = array();
        $routes = $this->router->routes();
        /** @var Route $route */
        foreach ($routes as $route) {
            $name = $route->getName();
            $path = $route->getPath();
            $simplifiedPath = $this->simplifiedRegExpRoutePath($path);

            $datas[$name] = $simplifiedPath;
        }

        return $datas;
    }

    /**
     * Listen to the defined routes
     */
    public function listen()
    {
        $this->router->dispatch();
    }

    /**
     * @param $routePath
     *
     * @return mixed
     */
    protected function simplifiedRegExpRoutePath($routePath)
    {
        $filteredPath = $routePath;
        $filteredPath = preg_replace('`^@\^(.*)\$$`', '$1', $filteredPath);
        $filteredPath = preg_replace('`\(\?:\(\?P<([^>]+)>[^)]*\)[^)]*\)`', '{$1}', $filteredPath);
        $filteredPath = preg_replace('`\?`', '', $filteredPath);

        return $filteredPath;
    }

    /**
     * @return array|null
     */
    protected function getConfig()
    {
        $config = null;
        if (file_exists(__DIR__.'/Ressources/Config/router.conf.php')) {
            $config = require __DIR__.'/Ressources/Config/router.conf.php';
        }

        return $config;
    }

    /**
     * @return array|null
     */
    protected function loadConfig()
    {
        $config = $this->getConfig();
        $this->debug = $config['debug'];
    }
}
