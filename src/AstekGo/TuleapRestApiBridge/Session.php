<?php
/**
 * This file is part of the project TuleapRestApiBridge
 *
 * Last edited by jpvantelon@astek.fr
 *
 * Copyright ASTEK (c) 2015.
 */

namespace AstekGo\TuleapRestApiBridge;

use Symfony\Component\HttpFoundation\Session\Session as SymfonySession;
use Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorage;


class Session
{
    /** @var string */
    protected $namespace = 'AstekGo/TuleapRestApiBridge/Session/';
    /** @var SymfonySession */
    protected $session;

    /**
     * Session constructor.
     *
     * @param string|null $namespace
     */
    public function __construct($namespace = null)
    {
        @session_start();
        $this->session = new SymfonySession(new PhpBridgeSessionStorage());
        if (!$this->session->isStarted()) {
            $this->session->start();
        }
        $this->setSessionValues($namespace);
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param string $namespace
     */
    public function setNamespace($namespace)
    {
        $namespace = trim($namespace);
        if ('' != $namespace) {
            $namespace = trim(str_replace('\\', '/', $namespace), '/').'/';
            $this->namespace = $namespace;
        }
    }

    /**
     * @return array
     */
    public function getSessionValues()
    {
        $sessionArray = $this->session->all();
        $sessionValues = array();
        foreach ($sessionArray as $key => $value) {
            if (preg_match('@^'.$this->namespace.'@', $key)) {
                $shortKey = preg_replace('@^'.$this->namespace.'@', '', $key);
                $sessionValues[$shortKey] = $value;
            }
        }

        return $sessionValues;
    }

    /**
     * @param array $sessionValues
     */
    public function setSessionValues($sessionValues)
    {
        if (is_array($sessionValues)) {
            foreach ($sessionValues as $key => $value) {
                $longKey = $this->namespace.$key;
                $this->session->set($longKey, $value);
            }
            $this->session->save();
        }
    }
}
