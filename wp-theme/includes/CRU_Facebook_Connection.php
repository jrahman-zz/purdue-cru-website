<?php

require_once "CRU_Facebook_Client.php";
require_once "CRU_Facebook_Object.php";
require_once "CRU_Facebook_Struct.php";


/**
* Represents a connection from a CRU_Facebook_Object
*
* @author Jason P Rahman (jprahman93@gmail.com, rahmanj@purdue.edu)
*
*/
class CRU_Facebook_Connection implements Iterator, ArrayAccess {

    /**
     * Construct an instance of a Facebook connection
     *
     * @param string $objectId the ID of the object this connection leads from
     * @param string $name the name of this particular connection
     * @param string $facebookClient 
     */
    public function __construct($objectId, $name, $facebookClient) {

        $this->_objectId = $objectId;
        $this->_connectionName = $name;
        $this->_facebookClient = $facebookClient;

        $this->_objects = $this->_loadConnection($this->_connectionName);

        if (!is_array($this->_objects)) {
            $this->_objects = array($this->_objects);
        }
    }


    /**
     * Check for the existance of the offset 
     *
     * @param mixed $offset the offset into the array to check for
     * @return boolean true if the offset exists, false otherwise
     */
    public function offsetExists($offset) {
        return isset($this->objects);
    }
       

    /**
     * Get the value at a certain offset
     *
     * @param mixed $offset the offset of the value to retrieve
     * @return mixed if the offset exists, the value at the offset, null otherwise
     */
    public function offsetGet($offset) {
        return offsetExists($offset) ? $this->objects[$offset] : null;
    }


    /**
     * Set the value at a certain offset
     *
     * @param mixed $offset the offset at which to set a value
     * @param mixed $value the value to set
     */
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->objects[] = $value;
        } else {
            $this->objects[$offset] = $value;
        }
    }

    
    /**
     * Unset the objects at a given offset
     *
     * @param mixed $offset the offset at which to unset the elements
     */
    public function offsetUnset($offset) {
        unset($this->objects[$offset]);
    }
 

    /**
     * Reset the position of the iterator
     *
     */
    public function rewind() {
        $this->_currentPosition = 0;
    }


    /**
     * Check if the current iterator element is still valid 
     *
     * @return TRUE if the current element is still valid, FALSE otherwise
     */
    public function valid() {
        if ($this->_currentPosition < count($this->_objects)) {
            return TRUE;
        } else {
            return FALSE;
        }   
    }   


    /**
     * Increment the position of the iterator by one
     *
     */
    public function next() {
        $this->_currentPosition++;
    } 

    
    /**
     * Get the key at the current position of the iterator
     *
     * @return string the key of the current object  
     */
    public function key() {     
        strval($this->_objects[$this->_currentPosition]->fields->id);
    }  

    
    /**
     * Get the value at the current position of the iterator
     *
     * @return mixed the value at the current position
     */     
    public function current() {
        return $this->_objects[$this->_currentPosition];
    }


    /**
     * Load the objects for the connection with the given name
     *
     * @param string $connectionName the name of the connection to load
     * @return mixed FALSE if the connection couldn't be found,
     *                     an array of CRU_Facebook_Objects otherwise
     */
    private function _loadConnection($connectionName) {
        // Attempt to fetch the graph connection with the given name
        try {
            $connection = $this->_getConnection($connectionName);
        } catch (Exception $e) {
            return FALSE;
        }
                
        // Now that we have our connection array, expand it
        return $this->_expandConnection($connection);
    }

    
    /**
     * Get the connection array for the current object with the given connection name
     *
     * @param string $connectionName the name of the graph connection to fetch
     * @return array an array of associative arrays representing
     *               the fields of each object in the connection
     */
    private function _getConnection($connectionName) {
        $result = $this->_facebookClient->getConnection($this->_objectId, $connectionName, "5");
        return $result;
    }   


    /**
     * Expand the array representing the connection into an array
     * of objects representing the objects in the connection
     *
     * @param array $connectionArray an array of associative arrays holding partial information
     *                               for each object that is part of the connection
     * @return array an array of CRU_Facebook_Objects representing the connection
     */
    private function _expandConnection($connectionArray) {
        if (is_array($connectionArray) && isset($connectionArray[0]) && is_array($connectionArray[0])) {
            $result = array();
            foreach ($connectionArray as $connection) {
                if (isset($connection['id'])) {
                    array_push($result, new CRU_Facebook_Object($connection, $this->_facebookClient));
                } else {
                    array_push($result, new CRU_Facebook_Struct($connection, $this->_facebookClient));
                }
            }
        } else {
            if (isset($connectionArray['id'])) {
                $result = new CRU_Facebook_Object($connectionArray, $this->_facebookClient);
            } else {
                $result = new CRU_Facebook_Struct($connectionArray, $this->_facebookClient);
            }
        }
        return $result;
    }


    /**
     * The CRU_Facebook_Objects in the connection
     *
     * @var object
     */
    private $_objects;

    
    /**
     * The current position of the iterator
     *
     */
    private $_currentPosition;


    /**
     * The name of the connection
     *
     * @var string
     */ 
    private $_connectionName;


    /**
     * Client used to retrieve this object and other objects
     * that need to be fetched
     *
     * @var object
     */
    private $_facebookClient;


    /**
     * The ID of the object this connection leads from
     *
     * @var string     
     */    
    private $_objectId;
}
?>
