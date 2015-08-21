<?php
/**
 * This file is part of the project TuleapRestApiBridge
 *
 * Last edited by jpvantelon@astek.fr
 *
 * Copyright ASTEK (c) 2015.
 */

namespace AstekGo\TuleapRestApiBridge;

use Guzzle\Http\Client as HttpClient;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Guzzle\Http\Message\RequestInterface as HttpRequestInterface;
use Guzzle\Http\Message\Response as HttpResponse;
use SimpleXMLElement;

class Bridge
{
    /** @var string */
    protected $tuleapServerBaseUrl = 'https://localhost';
    /** @var string */
    protected $tuleapServerApiUrl = 'https://localhost/api';
    /** @var string */
    protected $tuleapRestApiBridgeBaseUrl = 'https://localhost';
    /** @var string */
    protected $tuleapRestApiBridgeRootUrl = 'https://localhost';
    /** @var boolean */
    protected $tuleapRestApiBridgeHasRedirectBaseManualConfig = false;
    /** @var string */
    protected $username;
    /** @var string */
    protected $password;
    /** @var string */
    protected $token;
    /** @var integer */
    protected $userId;
    /** @var HttpClient */
    protected $httpClient;

    /**
     * Bridge constructor.
     */
    public function __construct()
    {
        $this->loadConfig();
        $this->loadSession();
    }

    /**
     * Retrieve the IDE configuration datas
     *
     * @return array
     */
    public function getIDEConfiguration()
    {
        return array(
            'tuleapServerBaseUrl' => $this->tuleapServerBaseUrl,
            'tuleapRestApiBridgeRootUrl' => $this->tuleapRestApiBridgeRootUrl,
            'tuleapRestApiBridgeBaseUrl' => $this->tuleapRestApiBridgeBaseUrl,
            'tuleapRestApiBridgeHasRedirectBase' => ($this->tuleapRestApiBridgeRootUrl != $this->tuleapRestApiBridgeBaseUrl),
            'tuleapRestApiBridgeHasRedirectBaseManualConfig' => $this->tuleapRestApiBridgeHasRedirectBaseManualConfig,
        );
    }

    /**
     * Generate user authentification token
     *
     * @param string $username
     * @param string $password
     *
     * @return string
     */
    public function getUserToken($username, $password)
    {
        if ((null === $this->token) || ($username != $this->username)) {
            $this->clearSession();
            $request = $this->getHttpClient()->post(
                $this->tuleapServerApiUrl.'/tokens',
                '',
                json_encode(
                    array(
                        "username" => $username,
                        "password" => $password,
                    )
                )
            );
            $response = $request->send();
            $response = $response->json();
            if (array_key_exists('token', $response) && (null != $response['token'])) {
                $this->username = $username;
                $this->password = $password;
                $this->token = $response['token'];
                $this->userId = $response['user_id'];
                $this->saveSession();
            }
        }

        return $this->token;
    }

    /**
     * Get all tracker's artifacts values
     *
     * @param $trackerId
     * @param $trackerFields
     *
     * @return string
     */
    public function getArtifactsList($trackerId, $trackerFields)
    {
        $request = $this->getHttpClient()->get($this->tuleapServerApiUrl.'/trackers/'.$trackerId.'/artifacts?values=all');
        $response = $this->send($request);
        $response = $response->json();

        $artifacts = array();
        foreach ($response as $artifact) {
            $artifacts[] = $this->filterArtifactValues($artifact, $trackerFields);
        }

        $xml = $this->arrayToXml($artifacts, '<artifacts/>');

        return $xml;
    }

    /**
     * Get an artifact values
     *
     * @param $artefactId
     * @param $trackerFields
     *
     * @return string
     */
    public function getArtifact($artefactId, $trackerFields)
    {
        $request = $this->getHttpClient()->get($this->tuleapServerApiUrl.'/artifacts/'.$artefactId.'?values_format=collection');
        $response = $this->send($request);
        $response = $response->json();

        $artifact = $this->filterArtifactValues($response, $trackerFields);

        $xml = $this->arrayToXml($artifact, '<artifact/>');

        return $xml;
    }

    /**
     * Filter raw values from Tuleap API to formatted values for use in IDE
     *
     * @param array $artifact
     * @param array $trackerFields
     *
     * @return array
     */
    protected function filterArtifactValues($artifact, $trackerFields)
    {
        $id = $artifact['id'];
        $summary = '';
        $description = '';
        $created = '';
        $updated = '';
        $closed = '';
        $values = $artifact['values'];
        foreach ($values as $value) {
            switch ($value['field_id']) {
                case $trackerFields['summaryFieldId']:
                    $summary = $value['value'];
                    break;
                case $trackerFields['descriptionFieldId']:
                    $description = $value['value'];
                    break;
                case $trackerFields['createdFieldId']:
                    $created = $value['value'];
                    break;
                case $trackerFields['updatedFieldId']:
                    $updated = $value['value'];
                    break;
                case $trackerFields['closedFieldId']:
                    $closed = $value['value'];
                    break;
            }
        }
        $issueUrl = $this->tuleapServerBaseUrl.$artifact['html_url'];

        return array(
            'id' => $id,
            'summary' => $summary,
            'description' => $description,
            'updated' => $updated,
            'created' => $created,
            'closed' => ('' == $closed) ? false : true,
            'issueUrl' => $issueUrl,
        );
    }

    /**
     * Check and load the configuration
     *
     * @throws \Exception
     */
    protected function loadConfig()
    {
        // Load configuration from
        $config = null;
        if (file_exists(__DIR__.'/Ressources/Config/bridge.conf.php')) {
            $config = require __DIR__.'/Ressources/Config/bridge.conf.php';
        }

        if (is_array($config)
            && array_key_exists('tuleapServerBaseUrl', $config)
            && array_key_exists('tuleapRestApiBridgeRootUrl', $config)
            && array_key_exists('tuleapRestApiBridgeBaseUrl', $config)
            && array_key_exists('tuleapRestApiBridgeHasRedirectBaseManualConfig', $config)
        ) {
            $this->tuleapServerBaseUrl = rtrim(trim($config['tuleapServerBaseUrl']), '/');
            $this->tuleapServerApiUrl = $this->tuleapServerBaseUrl.'/api';
            $this->tuleapRestApiBridgeRootUrl = rtrim(trim($config['tuleapRestApiBridgeRootUrl']), '/');
            $this->tuleapRestApiBridgeBaseUrl = rtrim(trim($config['tuleapRestApiBridgeBaseUrl']), '/');
            $this->tuleapRestApiBridgeHasRedirectBaseManualConfig = $config['tuleapRestApiBridgeHasRedirectBaseManualConfig'];
        } else {
            throw new \Exception(__NAMESPACE__.'::'.__CLASS__.' : Invalid configuration');
        }
    }

    /**
     * Convert a PHP array to XML
     *
     * @param array            $array       Array to be converted
     * @param string           $rootElement If specified, will be taken as root element, otherwise defaults to <root>
     * @param SimpleXMLElement $xml         If specified, content will be appended, used for recursion
     *
     * @return string XML version of $array
     */
    protected function arrayToXml($array, $rootElement = null, $xml = null)
    {
        $_xml = $xml;

        if ($_xml === null) {
            $_xml = new SimpleXMLElement($rootElement !== null ? $rootElement : '<root/>');
        }

        foreach ($array as $k => $v) {
            if (is_array($v)) {
                // Nested array
                if (preg_match('/\d+/', $k)) {
                    $xmlChild = $_xml->addChild('node');
                    $xmlChild->addAttribute('id', $k);
                    $this->arrayToXml($v, $k, $xmlChild);
                } else {
                    $this->arrayToXml($v, $k, $_xml->addChild($k));
                }
            } else {
                // SimpleXML escaping bug
                if (is_bool($v)) {
                    $v = var_export($v, true);
                }
                $value = str_replace('&', '&amp;', $v);
                if (preg_match('/\d+/', $k)) {
                    $xmlChild = $_xml->addChild('node', $value);
                    $xmlChild->addAttribute('id', $k);
                } else {
                    $_xml->addChild($k, $value);
                }
            }
        }

        return $_xml->asXML();
    }

    /**
     * Load the session
     */
    protected function loadSession()
    {
        $session = new Session(__NAMESPACE__.'\\'.__CLASS__);
        foreach ($session->getSessionValues() as $key => $value) {
            switch ($key) {
                case 'username':
                    $this->username = $value;
                    break;
                case 'password':
                    $this->password = $value;
                    break;
                case 'token':
                    $this->token = $value;
                    break;
                case 'userId':
                    $this->userId = $value;
                    break;
            }
        }
    }

    /**
     * Save the session
     */
    protected function saveSession()
    {
        $sessionValues = array(
            'username' => $this->username,
            'password' => $this->password,
            'token' => $this->token,
            'userId' => $this->userId,
        );
        $session = new Session(__NAMESPACE__.'\\'.__CLASS__);
        $session->setSessionValues($sessionValues);
        $this->loadSession();
    }

    /**
     * Clear the session
     */
    protected function clearSession()
    {
        $sessionValues = array(
            'username' => null,
            'password' => null,
            'token' => null,
            'userId' => null,
        );
        $session = new Session(__NAMESPACE__.'\\'.__CLASS__);
        $session->setSessionValues($sessionValues);
    }

    /**
     * Retrieve the HTTP client object
     *
     * @return HttpClient
     */
    protected function getHttpClient()
    {
        if (null === $this->httpClient) {
            $this->httpClient = new HttpClient(
                $this->tuleapServerApiUrl,
                array(
                    'ssl.certificate_authority' => false,
                )
            );
        }

        return $this->httpClient;
    }

    /**
     * Send datas trough HTTP client
     *
     * @param HttpRequestInterface $request
     *
     * @param bool                 $stopOnException
     *
     * @return HttpResponse
     * @throws \Exception
     */
    protected function send(HttpRequestInterface $request, $stopOnException = false)
    {
        $request->setHeader('Content-Type', 'application/json')
            ->setHeader('X-Auth-Token', array($this->token))
            ->setHeader('X-Auth-UserId', array($this->userId));

        try {
            $response = $request->send();
        } catch (ClientErrorResponseException $e) {
            if (!$stopOnException && (401 == $e->getResponse()->getStatusCode()) && (null != $this->username)) {
                // If the HTTP error is 401 Unauthorized, the session is cleared
                $username = $this->username;
                $password = $this->password;
                $this->clearSession();
                // Then we try a new authentication
                $this->getUserToken($username, $password);
                // Then we resend the request once
                $response = $this->send($request, true);
            } else {
                throw $e;
            }
        }

        if (!$response) {
            throw new \Exception(__NAMESPACE__.'\\'.__CLASS__.' : Bad response while sending the request');
        }

        return $response;
    }
}
