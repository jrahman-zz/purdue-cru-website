<?php

require_once "CRU_Facebook_Object.php";


/**
 * Class to hold non-object structures for the Facebook Graph API
 *
 * 
 * @author Jason P Rahman (rahmanj@purdue.edu, jprahman93@gmail.com)
 *
 */
class CRU_Facebook_Struct {

    
    public function __construct($fields, $client) {
        $this->_facebookClient = $client;
        $this->_objectFields = $fields;

    }


    /**
     * Get the value of a field by name
     *
     * @param string $name the name of the field to retrieve
     * @return mixed The value of the field named $name
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
    }

    /**
     * Check if a given field name is set
     *
     * @param string $name the name of the field to check
     * @return boolean TRUE is the field with $name is set, FALSE otherwise
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
     * Facebook client associated with the objects
     *
     * @var object
     */
    private $_facebookClient;


    /**
     * Array of expanded fields and objects
     *
     * @var array
     */
    private $_expandedFieldValues;

    /**
     * Array of field values
     *
     * @var array
     */
    private $_objectFields;


    /**
     * Flag to denote if the struct has been expanded
     *
     * @var boolean
     */
    private $_hasBeenExpanded;
}
?>
