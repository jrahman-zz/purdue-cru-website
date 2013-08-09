<?php

require_once "CRU_Defines.php";
require_once "CRU_Facebook_Client.php";

/**
 * Represents the fields of a Facebook graph object
 *
 * @author Jason P Rahman (jprahman93@gmail.com, rahmanj@purdue.edu)
 *
 */
class CRU_Facebook_Fields implements Iterator {

    /**
     * Construct an instance of a set of fields
     *
     *
     * @param array $fields an array of associative arrays holding the partial subobject information
     * @param object $facebookClient the Facebook client associated with these fields
     */
    public function __construct($fields, $facebookClient) {
        // TODO Add validation
        $this->_hasBeenExpanded = FALSE;
        $this->_objectFields = $fields;
        $this->_expandedObjectFields = array();
        $this->_facebookClient = $facebookClient;
    }


    /**
     * Reset the position of the iterator
     *
     */
    public function rewind() {
        $this->_currentPosition = 0;
        $this->_keys = array_merge(array_keys($this->_expandedObjectFields), array_keys($this->_objectFields));
    }

    /**
     * Check if the current iterator element is still valid 
     *
     * @return TRUE if the current element is still valid, FALSE otherwise
     */
    public function valid() {
        if ($this->_currentPosition < count($this->_keys)) {
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
        strval($this->_keys[$this->_currentPosition]);
    }  

    
    /**
     * Get the value at the current position of the iterator
     *
     * @return mixed the value at the current position
     */     
    public function current() {
        return $this->__get($this->_keys[$this->_currentPosition]);
    }


    /**
     * Retrieve the object, or array of Facebook graph objects
     * representing the fields of the facebook graph object
     *
     * @param string $name the name of the field to fetch
     * @return mixed Returns either an array of CRU_Facebook_Objects or a string field value
     * @throw CRU_Facebook_NoSuchFieldException
     */
    public function __get($name) {
        if (isset($this->_expandedObjectFields[$name])) {

            // Return the already expanded object field
            return $this->_expandedObjectFields[$name];
        }

        if (isset($this->_objectFields[$name])) {

            // If this is an array, we need to expand it
            if (is_array($this->_objectFields[$name])) {
                // Expand the unexpanded version of the field
                $this->_expandedObjectFields[$name] = $this->_expandField($this->_objectFields[$name]);
                // Unset the unexpanded version
                unset($this->_objectFields[$name]);
                return $this->_expandedObjectFields[$name];
            } else {
                // Return the field itself if it is not an array
                return $this->_objectFields[$name];
            }
        }
        
        // We couldn't find the field either because we haven't expanded the object fully, or it doesn't exist
        if ($this->_hasBeenExpanded == FALSE) {
            // Attempt to expand the object whose fields we hold
            $newFields = $this->_facebookClient->getObject($this->_objectFields['id']);
            $this->_objectFields = $newFields;
            $this->_hasBeenExpanded = TRUE;
            return $this->__get($name);
        } else {
            throw new CRU_Facebook_NoSuchFieldException($name);
        }
    }


    /**
     * Check if a field exists
     *
     * @param string $name the name of the field to retrive
     * @return bool TRUE if the field does exist, FALSE otherwise
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
     * Take a array of associative arrays representing a field and expand them into objects
     *
     * @param array $fieldArray an array of associative arrays holding partial
     *                          object information for each object in the field
     * @return array an array containing CRU_Facebook_Objects for each 
     *               object in the field
     */
    private function _expandField($fieldArray) {
        
        if (is_array($fieldArray) && isset($fieldArray[0]) && is_array($fieldArray[0])) {
            $result = array();
            foreach ($fieldArray as $field) {
                if (isset($field['id'])) {
                    array_push($result, new CRU_Facebook_Object($field, $this->_facebookClient));
                } else {
                    array_push($result, new CRU_Facebook_Struct($field, $this->_facebookClient));
                }
            }
        } else {
            if (isset($fieldArray['id'])) {
                $result = new CRU_Facebook_Object($fieldArray, $this->_facebookClient);
            } else {
                $result = new CRU_Facebook_Struct($fieldArray, $this->_facebookClient);
            }
        }
        return $result;
    }


    /**
     * Holds the fields of the object
     *
     * @var array
     */
    private $_objectFields;


    /**
     * Holds the expanded versions of the fields in the
     * event that a field is itself is an (array of) object(s) 
     * that need to replaced with actual objects
     *
     * @var array
     */
    private $_expandedObjectFields;


    /**
     * Client used to retrieve this object and other objects
     * that need to be fetched
     *
     * @var object
     */
    private $_facebookClient;


    /**
     * The current index of the iterator
     *
     * @var integer
     */     
    private $_currentIndex;


    /**
     * Array of keys for each field
     *
     * @var array
     */
    private $_keys;
}
?>
