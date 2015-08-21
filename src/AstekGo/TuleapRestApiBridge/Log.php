<?php
/**
 * This file is part of the project TuleapRestApiBridge
 *
 * Last edited by jpvantelon@astek.fr
 *
 * Copyright ASTEK (c) 2015.
 */

namespace AstekGo\TuleapRestApiBridge;

class Log
{
    /**
     * @param $message
     *
     * @throws \Exception
     */
    static public function write($message)
    {
        $config = self::getConfig();
        $logFilePath = null;
        if (is_array($config) && array_key_exists('logFilePath', $config)) {
            $logFilePath = $config['logFilePath'];
            if('' != $logFilePath) {
                $logFilePath = realpath(dirname($logFilePath)).DIRECTORY_SEPARATOR.basename($logFilePath);
            }
        }
        if (null != $logFilePath) {
            $logLine = date('Y-m-d H:i:s').' - '.$message."\r\n";

            if (false === file_put_contents($logFilePath, $logLine, FILE_APPEND | LOCK_EX)) {
                throw new \Exception(__NAMESPACE__.'::'.__CLASS__.' : Error while attempting to write to log file "'.$logFilePath.'"');
            }
        }
    }

    /**
     * @return array|null
     */
    static protected function getConfig()
    {
        $config = null;
        if (file_exists(__DIR__.'/Ressources/Config/log.conf.php')) {
            $config = require __DIR__.'/Ressources/Config/log.conf.php';
        }

        return $config;
    }
}
