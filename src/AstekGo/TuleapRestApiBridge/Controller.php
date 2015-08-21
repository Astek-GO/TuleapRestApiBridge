<?php
/**
 * This file is part of the project TuleapRestApiBridge
 *
 * Last edited by jpvantelon@astek.fr
 *
 * Copyright ASTEK (c) 2015.
 */

namespace AstekGo\TuleapRestApiBridge;

class Controller
{
    /** @var Bridge */
    protected $bridge;
    /** @var Router */
    protected $router;

    /**
     * Controller constructor.
     *
     * @param Bridge $bridge
     * @param Router $router
     */
    public function __construct(Bridge $bridge, Router $router)
    {
        $this->bridge = $bridge;
        $this->router = $router;
    }

    /**
     * Display the IDE configuration
     */
    public function getIDEConfiguration()
    {
        $datasBridge = $this->bridge->getIDEConfiguration();
        $datasRouter = $this->router->getIDEConfiguration();
        $datas = array(
            'tuleapRestApiBridgeRootUrl' => $datasBridge['tuleapRestApiBridgeRootUrl'],
            'tuleapRestApiBridgeBaseUrl' => $datasBridge['tuleapRestApiBridgeBaseUrl'],
            'tuleapRestApiBridgeHasRedirectBase' => $datasBridge['tuleapRestApiBridgeHasRedirectBase'],
            'tuleapRestApiBridgeHasRedirectBaseManualConfig' => $datasBridge['tuleapRestApiBridgeHasRedirectBaseManualConfig'],
            'getUserTokenRoute' => $datasRouter['getUserToken'],
            'getArtifactsListRoute' => $datasRouter['getArtifactsList'],
            'getArtifactRoute' => $datasRouter['getArtifact'],
            'getIDEConfigurationRoute' => $datasRouter['getIDEConfiguration'],
        );
        $viewServiceProvider = new ViewServiceProvider();
        $viewServiceProvider->render(
            __DIR__.'/Ressources/Views/config-ide.phtml',
            $datas
        );
    }

    /**
     * Generate user authentification token
     *
     * @param $username
     * @param $password
     */
    public function getUserToken($username, $password)
    {
        echo $this->bridge->getUserToken($username, $password);
    }

    /**
     * Display all tracker's artifacts values in XML
     *
     * @param $trackerId
     * @param $trackerFields
     */
    public function getArtifactsList($trackerId, $trackerFields)
    {
        $xml = $this->bridge->getArtifactsList($trackerId, $trackerFields);

        echo $xml;
    }

    /**
     * Display an artifact values in XML
     *
     * @param $artefactId
     * @param $trackerFields
     */
    public function getArtifact($artefactId, $trackerFields)
    {
        $xml = $this->bridge->getArtifact($artefactId, $trackerFields);

        echo $xml;
    }
}
