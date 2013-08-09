<?php
require_once "CRU_Defines.php";
require_once "CRU_Facebook_Client.php";
require_once "CRU_Facebook_Fields.php";
require_once "CRU_Facebook_Connections.php";


/**
 * Class to represent an object in the Facebook object graph
 *
 * @author Jason P Rahman (jprahman93@gmail.com, rahmanj@purdue.edu)
 *
 */
class CRU_Facebook_Object {


    /**
     * Construct an instance of a Facebook graph object
     *
     * @param mixed $objectId the ID of the object to create or an associative array which includes the ID
	 * @param array $fields a list of fields to fetch for the object
     * @param object $client the Facebook client object to
     *                       be used to create the object
     * @throws
     */
    public function __construct($objectId, $client, $fields = array()) {
        // Fetch the results from Facebook and save everything we need
        if (is_array($objectId)) {
            if (isset($objectId['id'])) {
                $fields = $objectId;
                $this->_objectId = $objectId['id'];
            } else {
                throw new CRU_Facebook_NoSuchFieldException();
            }
        } else {
            $fields = $client->getObject($objectId, $fields);
            $this->_objectId = $objectId;
        }

        
        $this->_fields = new CRU_Facebook_Fields($fields, $client);
        $this->_connections = new CRU_Facebook_Connections($objectId, $client);

        $this->_facebookClient = $client;       
    }


    /**
     * Get a property with the specified name
     *
     * @param string 
     * @return object a CRU_Facebook_Fields or CRU_Facebook_Connections object
     * @throws Exception
     */
    public function __get($name) {
        if ($name === "connections") {
            return $this->_connections;
        } else {
            return $this->_fields->__get($name);
        }
    }

    /**
     *
     *
     *
     *
     */
    public function __isset($name) {
        try {
            $this->__get($name);
            return TRUE;
        } catch (Exception $e) {
            return FALSE;
        }

    }


    /**
     * Performs a given action on the Facebook graph API object
     *
     * @param string $name the name of the action to perform
     * @param array $args the arguments to pass to the function
     *
     * @return mixed
     */
    public function __call($name, $args) {
        
        switch ($name) {
            case "like":

                break;
            case "unlike":

                break;
        }
    }


    /**
     * Facebook graph objectId for the current object
     *
     * @var string
     */
    private $_objectId;


    /**
     * Client used to retrieve this object and other objects
     * that need to be fetched
     *
     * @var object
     */
    private $_facebookClient;   
}


/**
 * Represents an exception raised when an attempt is made
 * to retrieve a object field that doesn't exist
 *
 * @author Jason P Rahman (jprahman93@gmail.com, rahmanj@purdue.edu)
 *
 */
class CRU_Facebook_NoSuchFieldException extends Exception {
    
    public function __construct($name) {
        parent::__construct("No field named " . $name . " found");
    }
}
?>
