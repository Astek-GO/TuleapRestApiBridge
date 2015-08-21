<?php
/**
 * This file is part of the project TuleapRestApiBridge
 *
 * Last edited by jpvantelon@astek.fr
 *
 * Copyright ASTEK (c) 2015.
 */

namespace AstekGo\TuleapRestApiBridge;

class ErrorHandler
{
    /**
     * @param int    $errNo
     * @param string $errStr
     * @param string $errFile
     * @param int    $errLine
     * @param array  $errContext
     *
     * @return bool
     */
    static public function errorHandler($errNo, $errStr, $errFile, $errLine, $errContext)
    {
        $errorMessage = '!!! PHP Error : '.$errNo.' - '.$errStr.' at line '.$errLine.' in file '.$errFile.PHP_EOL.'Context :'.PHP_EOL.var_export($errContext, true).PHP_EOL.'!!!';
        Log::write($errorMessage);

        return false;
    }
}
