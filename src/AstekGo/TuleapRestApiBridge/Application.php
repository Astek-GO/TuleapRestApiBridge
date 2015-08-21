<?php
/**
 * This file is part of the project TuleapRestApiBridge
 *
 * Last edited by jpvantelon@astek.fr
 *
 * Copyright ASTEK (c) 2015.
 */

namespace AstekGo\TuleapRestApiBridge;

class Application
{
    /** @var array */
    protected $applicationConfig;
    /** @var array */
    protected $bridgeConfig;
    /** @var bool */
    protected $debug;
    /** @var Bridge */
    protected $bridge;
    /** @var Router */
    protected $router;
    /** @var Controller */
    protected $controller;

    /**
     * Application constructor.
     *
     * @param array|null $config Configuration
     *
     * @throws \Exception
     */
    public function __construct($config = null)
    {
        // Configuration du gestionnaire d'erreurs
        set_error_handler(array(__NAMESPACE__.'\\ErrorHandler', 'errorHandler'), E_ALL & ~E_NOTICE);
        try {
            $this->loadConfig($config);
        } catch (\Exception $e) {
            $errorMessage = 'Exception occurred while loading configuration : '.$e;
            Log::write($errorMessage);
            echo '<pre>'.htmlspecialchars($errorMessage).'<pre>';
        }
        Log::write('****** '.__NAMESPACE__.'\\'.__CLASS__.' : Application loading ******');
        Log::write('Loading configuration : done');
    }

    /**
     * Run the application
     */
    public function run()
    {
        if ($this->debug) {
            Log::write('Trying to run the application...');
        }
        try {
            $this->configure();
        } catch (\Exception $e) {
            $errorMessage = 'Exception occurred while configuring application : '.$e;
            Log::write($errorMessage);
            if ($this->debug) {
                echo '<pre>'.htmlspecialchars($errorMessage).'<pre>';
            }
        }
        Log::write('Application is running...');
        try {
            $this->router->listen();
        } catch (\Exception $e) {
            $errorMessage = 'Exception occurred while running application : '.$e;
            Log::write($errorMessage);
            if ($this->debug) {
                echo '<pre>'.htmlspecialchars($errorMessage).'<pre>';
            }
        }
    }

    /**
     * Check and load configuration
     *
     * @param array $config
     *
     * @throws \Exception
     */
    protected function loadConfig($config)
    {
        $isConfigOk = false;
        if (null !== $config) {
            if (is_array($config)) {
                // Get application configuration
                if (array_key_exists('application', $config) && is_array($config['application'])) {
                    $isConfigOk = true;
                    $this->applicationConfig = $config['application'];
                    $this->debug = (array_key_exists('debug', $this->applicationConfig) && (true === $this->applicationConfig['debug']));
                    // Get bridge configuration
                    if (array_key_exists('bridge', $config) && is_array($config['bridge'])) {
                        $this->bridgeConfig = $config['bridge'];
                        $this->bridgeConfig['debug'] = $this->debug;
                        $this->bridgeConfig['tuleapRestApiBridgeHasRedirectBaseManualConfig'] = true;
                        if (
                            !array_key_exists('tuleapRestApiBridgeRootUrl', $this->bridgeConfig)
                            || (null == $this->bridgeConfig['tuleapRestApiBridgeRootUrl'])
                        ) {
                            $this->bridgeConfig['tuleapRestApiBridgeRootUrl'] = $this->getCurrentServerBaseUrl(false);
                        }
                        if (
                            !array_key_exists('tuleapRestApiBridgeRedirectBase', $this->bridgeConfig)
                            || (null == $this->bridgeConfig['tuleapRestApiBridgeRedirectBase'])
                        ) {
                            $this->bridgeConfig['tuleapRestApiBridgeRedirectBase'] = $this->getCurrentServerRedirectBase();
                            $this->bridgeConfig['tuleapRestApiBridgeHasRedirectBaseManualConfig'] = false;
                        }
                        $this->bridgeConfig['tuleapRestApiBridgeBaseUrl'] = trim($this->bridgeConfig['tuleapRestApiBridgeRootUrl'].'/'.$this->bridgeConfig['tuleapRestApiBridgeRedirectBase'], '/');
                    } else {
                        $isConfigOk = false;
                    }
                }
            }
        }

        if (!$isConfigOk) {
            throw new \Exception(__NAMESPACE__.'::'.__CLASS__.' : Invalid configuration');
        }
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function getRoutesConfig()
    {
        // $controller is used in routes.conf.php configuration file
        /** @noinspection PhpUnusedLocalVariableInspection */
        $controller = $this->controller;
        // $redirectBase is used in routes.conf.php configuration file
        /** @noinspection PhpUnusedLocalVariableInspection */
        $redirectBase = $this->bridgeConfig['tuleapRestApiBridgeRedirectBase'];
        $routes = array();
        if (file_exists(__DIR__.'/Ressources/Config/routes.conf.php')) {
            /** @var array $routes */
            $routes = include __DIR__.'/Ressources/Config/routes.conf.php';
            if (!is_array($routes)) {
                throw new \Exception(__NAMESPACE__.'::'.__CLASS__.' : Invalid routes configuration');
            }
        }

        return $routes;
    }

    /**
     * Configure the application
     */
    protected function configure()
    {
        // Log configuration
        $this->writeLogConfigurationToFile();

        // Bridge configuration
        $this->writeBridgeConfigurationToFile();
        $this->bridge = new Bridge();

        // Router configuration
        $this->writeRouterConfigurationToFile();
        $this->router = new Router();

        // Controller configuration
        $this->controller = new Controller($this->bridge, $this->router);

        // Routes configuration
        $routes = $this->getRoutesConfig();
        $this->router->configureRoutes($routes);
    }

    /**
     * @param bool $withRedirectBase
     *
     * @return string
     */
    protected function getCurrentServerBaseUrl($withRedirectBase = true)
    {
        $currentServerBaseUrl = $this->getCurrentServerInfos(($withRedirectBase) ? 'baseUrlFull' : 'baseUrlStrict');

        return $currentServerBaseUrl;
    }

    /**
     * @return string
     */
    protected function getCurrentServerRedirectBase()
    {
        $currentServerRedirectBase = $this->getCurrentServerInfos('redirectBase');

        return $currentServerRedirectBase;
    }

    /**
     * @param string|array|null $param Infos list to retrieve
     *
     * @return array
     */
    protected function getCurrentServerInfos($param = null)
    {
        // Protocol
        $protocol = substr(strtolower($_SERVER['SERVER_PROTOCOL']), 0, strpos($_SERVER['SERVER_PROTOCOL'], '/'));
        // HTTP or HTTPS ?
        $isHttp = ($protocol == 'HTTP');
        $isHttps = ($isHttp && (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on'));
        // Server name
        $serverName = $_SERVER['SERVER_NAME'];
        // Non-standard port
        $port = '';
        if (
            ($isHttps && ($_SERVER['SERVER_PORT'] != '443'))
            || ($isHttp && ($_SERVER['SERVER_PORT'] != '80'))
        ) {
            $port = $_SERVER['SERVER_PORT'];
        }
        // Redirect Base
        $redirectBase = (array_key_exists('REDIRECT_BASE', $_SERVER)) ? trim(($_SERVER['REDIRECT_BASE']), '/') : null;
        // Base Url Strict (without redirect base)
        $baseUrlStrict = $protocol.(($isHttps) ? 's' : '').'://'.$serverName.(('' == $port) ? '' : ':'.$port);
        // Base Url Full (with redirect base)
        $baseUrlFull = $baseUrlStrict.'/'.$redirectBase;

        // Parameters handling
        if (null === $param) {
            $params = array(
                'protocol',
                'isHttp',
                'isHttps',
                'serverName',
                'port',
                'redirectBase',
                'baseUrlStrict',
                'baseUrlFull',
            );
        } else {
            $params = (!is_array($param)) ? array($param) : $param;
        }

        $infosToReturn = array();
        foreach ($params as $info) {
            switch (strtolower($info)) {
                case 'protocol':
                    $infosToReturn[$info] = $protocol;
                    break;
                case 'ishttp':
                    $infosToReturn[$info] = $isHttp;
                    break;
                case 'ishttps':
                    $infosToReturn[$info] = $isHttps;
                    break;
                case 'servername':
                    $infosToReturn[$info] = $serverName;
                    break;
                case 'port':
                    $infosToReturn[$info] = $port;
                    break;
                case 'redirectbase':
                    $infosToReturn[$info] = $redirectBase;
                    break;
                case 'baseurlstrict':
                    $infosToReturn[$info] = $baseUrlStrict;
                    break;
                case 'baseurlfull':
                    $infosToReturn[$info] = $baseUrlFull;
                    break;
            }
        }

        if (is_string($param)) {
            $infosToReturn = $infosToReturn[$param];
        }

        return $infosToReturn;
    }

    /**
     * Write log configuration to Log class configuration file
     */
    protected function writeLogConfigurationToFile()
    {
        if (array_key_exists('logFilePath', $this->applicationConfig)) {
            $logFilePath = $this->applicationConfig['logFilePath'];
            $logConfig
                = <<<HEREDOC
<?php
/**
 * !!! Do not edit this file !!!
 * It will be overwritten by the application.
 * Use the application configuration variable \"logFilePath\" instead.
 */
return array(
    'logFilePath' => '$logFilePath'
);
HEREDOC;
            file_put_contents(__DIR__.'/Ressources/Config/log.conf.php', $logConfig);
        }
    }

    /**
     * Write bridge configuration to Bridge class configuration file
     */
    protected function writeBridgeConfigurationToFile()
    {
        if (is_array($this->bridgeConfig)) {
            $bridgeConfig
                = <<<HEREDOC
<?php
/**
 * !!! Do not edit this file !!!
 * It will be overwritten by the application.
 * Use the application configuration array \"bridge\" variables instead.
 */
return array(
HEREDOC;
            foreach ($this->bridgeConfig as $key => $value) {
                $value = var_export($value, true);
                $bridgeConfig
                    .= <<<HEREDOC

    '$key' => $value,
HEREDOC;
            }
            $bridgeConfig
                .= <<<HEREDOC

);
HEREDOC;
            file_put_contents(__DIR__.'/Ressources/Config/bridge.conf.php', $bridgeConfig);
        }
    }

    /**
     * Write router configuration to Router class configuration file
     */
    protected function writeRouterConfigurationToFile()
    {
        $debug = var_export($this->debug, true);
        $routerConfig
            = <<<HEREDOC
<?php
/**
 * !!! Do not edit this file !!!
 * It will be overwritten by the application.
 * Use the application configuration array \"router\" variables instead.
 */
return array(
    'debug' => $debug
);
HEREDOC;
        file_put_contents(__DIR__.'/Ressources/Config/router.conf.php', $routerConfig);
    }

}
