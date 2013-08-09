<?php

require_once "CRU_Defines.php";
require_once "CRU_Facebook_Client.php";
require_once "CRU_Facebook_Connection.php";

/**
 * Represents a connection from a Facebook graph object
 *
 * @author Jason P Rahman (jprahman93@gmail.com, rahmanj@purdue.edu)
 *
 */
class CRU_Facebook_Connections {

    /**
     * Construct a set of connections from the given object
     *
     * @param string $objectId the ID of the object whose connections this object represents
     * @param object $facebookClient the Facebook client these connections will be associated with
     */
    public function __construct($objectId, $facebookClient) {
        if (is_array($objectId)) {
            $this->_objectId = $objectId['id'];
        } else {
            $this->_objectId = $objectId;
        }       
        $this->_facebookClient = $facebookClient;
        $this->_graphConnections = array();
    }


    // TODO Add iterator methods

    /**
     * Retrieve the object, or array of Facebook graph objects
     * representing the connection from the facebook graph object
     *
     * @param string $name the name of the connection to fetch
     * @return mixed Returns either an array of CRU_Facebook_Objects or a string field value
     * @throw CRU_Facebook_NoSuchFieldException, CRU_Facebook_NoSuchConnectionException
     */
    public function __get($name) {
        if (isset($this->_graphConnections[$name])) {
            if ($this->_graphConnections[$name] !== FALSE) {
                return $this->_graphConnections[$name];
            } else {
                throw new CRU_Facebook_NoSuchFieldException($name);
            }
        } else {
            // Attempt to load the connection since we haven't already tried
            $this->_graphConnections[$name] = $this->_loadConnection($name);
            if ($this->_graphConnections[$name] !== FALSE) {
                return $this->_graphConnections[$name];
            } else {
                throw new CRU_Facebook_NoSuchFieldException($name);  
            }
        }
    }


    /**
     * Check if a connection exists
     *
     * @param string $name the name of the connection to retrive
     * @return bool TRUE if the connection does exist, FALSE otherwise
     */
    public function __isset($name) {
        if (isset($this->_graphConnections[$name])
                        && $this->_graphConnections[$name] !== FALSE) {
            return TRUE;
        } else {
            // Try to search for a connection with the given name that we haven't tried to load
            $connnection = $this->_loadConnection($name);
            $this->_graphConnections[$name] = $connection;
            if ($connection !== FALSE) {
                return TRUE;
            }
            return FALSE;
        }
    }


    /**
     * Loads the connection with the given name
     *
     * @param string $name the name of the connection to retrieve
     * @return mixed a CRU_Facebook_Connection object if it exists, FALSE otherwise
     */
    private function _loadConnection($name) {
        try {
            $result = new CRU_Facebook_Connection($this->_objectId, $name, $this->_facebookClient);
        } catch (Exception $e) {
            // The connection couldn't be created, probably because 
            // it didn't exist or we didn't have access rights
            return FALSE;
        }
        return $result;
    }


    /**
     * ID for the object these connections are associated with
     *
     * @var string 
     */
    private $_objectId;


    /**
     * Cache of Facebook graph connections, filled lazily
     * Equal to FALSE if the connection with a given key doesn't exist
     *
     * @var array
     */
    private $_graphConnections;


    /**
     * Client used to retrieve this object and other objects
     * that need to be fetched
     *
     * @var object
     */
    private $_facebookClient;
}
?>
