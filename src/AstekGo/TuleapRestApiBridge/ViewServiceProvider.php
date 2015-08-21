<?php
/**
 * This file is part of the project TuleapRestApiBridge
 *
 * Last edited by jpvantelon@astek.fr
 *
 * Copyright ASTEK (c) 2015.
 */

namespace AstekGo\TuleapRestApiBridge;

use Klein\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * ViewServiceProvider constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Return the view service provider
     *
     * @param string $view View file path
     * @param array  $data
     */
    public function render($view, array $data = array())
    {
        @parent::render($view, $data);
    }
}
