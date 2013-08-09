<?php

require_once "CRU_Defines.php";
require_once CRU_Defines::facebookApiDir . "facebook.php";


/**
 *
 * Class to represent a client connection to Facebook used
 * to retrieve objects from Facebook on behalf of the website
 *
 * @author Jason P Rahman (jprahman93@gmail.com, rahmanj@purdue.edu)
 *
 */
class CRU_Facebook_Client {

    /**
     * Create a new instance of a CRU_Facebook_Client
     *
     * @param string $appId the Facebook application ID
     * @param string $appSecret the Facebook application secret 
     */
    public function __construct($appId, $appSecret) {
        $facebookConfig = array();
        $facebookConfig['appId'] = $appId;
        $facebookConfig['secret'] = $appSecret;

        try {
            $this->_facebookClient = new Facebook($facebookConfig);
            //$this->_facebookClient->setAccessToken($this->_getAccessToken($appId, $appSecret));
            //$this->_facebookClient->setAccessToken($appId . "|" . $appSecret);
            //echo $appId . "|" . $appSecret;
            //echo $this->_facebookClient->getAccessToken();
        } catch (Exception $e) {
            throw new CRU_Facebook_InitializationException($e->getMessage());
        }
    }

    /**
     * Get an object through the Graph API via it's object ID
     *
     * @param string $objectId the ID of the object to fetch from Facebook
	 * @param string $fields an array of strings denoting the field to fetch
     * @return array an associative array of fields for the given object
     */
    public function getObject($objectId, $fields) {
        try {
			if (is_array($fields) && count($fields)) {
				$query_string = "?fields=";
				$query_string .= implode(",", $fields);
			} else {
				$query_string = "";
			}
            $array = $this->_makeGetRequest($objectId . $query_string);
        } catch (FacebookApiException $e) {
            $this->_throwException($e);
        } catch (Exception $e) {

        }
        return $array;
    }


    /**
     * Fetch the list of objects in the given connection
     *
     * @param string $objectId the ID of the object at the source of the connection
     * @param string $connectionName the name of the kind of connection to get
     * @return array an array of associative arrays containing the partial fields of
     *               the objects in the connection
     */
    public function getConnection($objectId, $connectionName, $limit = "") {
        try {
			if ($limit != "") {
				$limit = "?limit=$limit";
			}
            $array = $this->_makeGetRequest($objectId . "/" . $connectionName . $limit);
            if (isset($array['data'])) {
                $array = $array['data'];
            }
        } catch (FacebookApiException $e) {
            $this->_throwException($e);
        } catch (Exception $e) {
            throw $e;
        }
        return $array;
    }

       
    /**
     * Make a GET reqeust to the Graph API 
     *
     * @param string $queryString the string to pass to the Graph API
     * @return array associative array parsed from the JSON response data
     * @throws 
     */
    private function _makeGetRequest($queryString) {
        return $this->_facebookClient->api($queryString, 'GET');
    }


    /**
     * Make a POST reqeust to the Graph API 
     *
     * @param string $id the ID of the object to make the post request to
     * @param 
     * @return array associative array parsed from the JSON response data
     * @throws FacebookApiException
     */
    private function _makePostRequest($id, $postContent) {
        // TODO Finish this
        $this->_facebookClient->api($queryString, 'POST');
    }


    /**
     * Process a FacebookApiException a throw a new exception based on the details
     *
     * @param object $exception
     */
    private function _throwException($exception) {
        // TODO Finish this
        // We are to parse the exception and throw our own type based on it

        throw $exception;
    }


    /**
     * Get a public access token for the site
     *
     * @param string $appId the AppID to create the access token with
     * @param string $appSecret the App Secret to create the access token with
     * @return string an access token suitable for accessing public content on Facebook
     */
    private function _getAccessToken($appId, $appSecret) {
        $appTokenUrl = "https://graph.facebook.com/oauth/access_token?"
        . "client_id=" . $appId
        . "&client_secret=" . $appSecret 
        . "&grant_type=client_credentials";

        $response = file_get_contents($appTokenUrl);
        $params = null;
        parse_str($response, $params);
        return $params['access_token'];
    }

    const OAUTH_ERROR_CODE = "298";
    const INVALID_CONNECTION_CODE = "2500";


    /**
     * The Facebook API client we are using    
     *
     * @var object
     */
    private $_facebookClient;    
}


/**
 * Class to represent an exception raised when the client
 * cannot be initialized correctlty
 *
 * @author Jason P Rahman (jprahman93@gmail.com, rahmanj@purdue.edu)
 *
 */
class CRU_Facebook_InitializationException extends Exception {
    
    /**
     * Create a new instance of the exception
     *
     * @param string $message message to include in the exception
     */
    public function __construct($message) {
        parent::__construct($message);
    }
}

/**
 * Class to represent an exception raised when an object doesn't exist
 *
 *
 * @author Jason P Rahman (jprahman93@gmail.com, rahmanj@purdue.edu)
 *
 */
class CRU_Facebook_NoSuchObjectException extends Exception {


    /**
     * Construct 
     *
     * @param string $id the ID of the object we attempted to fetch
     */
    public function __construct($id) {
        parent::__construct("No object with ID " . $id . " could be found");
    }
}

/**
 * Class to represent an exception raised when a
 * connection doesn't exist for a given object ID
 *
 * @author Jason P Rahman (jprahman93@gmail.com, rahmanj@purdue.edu)
 * 
 */
class CRU_Facebook_NoSuchConnectionException extends Exception {


    /**
     * Construct an instance of the exception
     *
     * @param string $name the name of the connection that couldn't be found
     */
    public function __construct($name) {
        parent::__construct("No connection with name " . $name . " could be found");
    }
}


/**
 * Class to represent an exception raised when an attempt to
 * access an object for which access permissions do not exist
 *
 * @author Jason P Rahman (jprahman93@gmail.com. rahmanj@purdue.edu)
 *
 */
class CRU_Facebook_OAuthAccessException extends Exception {
    

    /**
     * Construct an instance of the exception
     *
     */
    public function __construct() {
        parent::__construct("Access not allowed for this object");
    }
}


/**
 * Class to represent an exception raised when an unknown error occurs
 *
 * @author Jason P Rahman (jprahman93@gmail.com, rahmanj@purdue.edu)
 *
 */
class CRU_Facebook_UnknownException extends Exception {


    /**
     * Construct an instance of the exception
     *
     * @param string $message the message describing the (unknown) exception
     */
    public function __construct($message) {
        parent::__construct($message);
    }
}

?>
