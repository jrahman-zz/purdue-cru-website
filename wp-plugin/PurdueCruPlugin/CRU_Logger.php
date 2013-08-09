<?php

require_once "CRU_Defines.php";

/**
 * Class for logging messages during script execution
 *
 * @author Jason P Rahman (jprahman93@gmail.com, rahmanj@purdue.edu)
 *
 */
class CRU_Logger {


    /**
     * Fetch a given logger
     *
     * @param string $name the name of the logger object ot retrieve
     * @return object a logger object to write log messages to
     */
    public static function getLogger($name) {
        if (isset(CRU_Logger::$_loggers[$name])) {
            return CRU_Logger::$_loggers[$name];
        } else {
            CRU_Logger::$_loggers[$name] = new CRU_Logger($name, CRU_Defines::logLevel);
            return CRU_Logger::$_loggers[$name];
        }
    }

    /**
     * Global array used to hold logger instances
     *
     * @var array
     */
    public static $_loggers = array();


    public function __construct($name) {

        ;
    }

    /**
     * Log a message (duh)
     *
     * @param string $level the level at which to log, values are 'VERBOSE', 'DEBUG', 'INFO', 'WARNING', 'ERROR'
     * @param string $file the filename in which the call occured
     * @param string $line the source code line where the log call is being made from
     * @param string $message the message to be logged
     */
    public function log($level, $file, $line, $message) {
        ;
    }
    
    /**
     * Set the lowest level message which will be logged
     *
     * @param string $level the minimum logging level
     */
    public function setLevel($level) {
        ;
    }

    /**
     * The current minimum logging level
     *
     * @var string
     */    
    private $_lvl;

}
?>
