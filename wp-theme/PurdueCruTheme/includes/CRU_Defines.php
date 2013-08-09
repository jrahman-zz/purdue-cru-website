<?php
    
/**
 * Class to hold constants for the application
 * 
 * @author Jason P Rahman (jprahman93@gmail.com, rahmanj@purdue.edu)
 *
 */
class CRU_Defines {

    // Empty private constructor to disallow instantiation
    private function __construct() {}

    /**
     * The (relative) path to the facebook API files
     *
     * @var string
     * @access public
     */
    const facebookApiDir = "facebook_api/";

    /**
     * The (relative) path to the config file
     *
     * @var string
     */
    const configPath = "config.ini";

    /**
     * The level of verbosity to use when logging messages
     *
     * Legal values are (in order of increasing verbosity): 'VERBOSE', 'DEBUG', 'INFO', 'WARNING', 'ERROR' 
     *
     * @var string
     */
    const logLevel = "VERBOSE";
}
?>
