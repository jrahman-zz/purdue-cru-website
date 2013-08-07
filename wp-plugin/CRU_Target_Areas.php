<?php

// Check if this function exists to see if we are being invoked as a plugin
// in wordpress context, otherwise we need to bailout and not output anything
if (!function_exists('add_action')) {
    exit(1);
}

require_once("CRU_Utils.php");

/**
 * Primary class for managing target areas
 *
 *
 * author: Jason P. Rahman (rahmanj@purdue.edu, jprahman93@gmail.com)
 */
class CRU_Target_Areas {


    /**
     * Handler to add a new target area
     *
     *
     */
    public function add_target_area() {

        /*
         * INPUT: HTTP Request
         * (integer) POST[_cru_add_target_area_nonce] - the nouce to authenticate the request
         * (string) POST[target_area_name] - the name of the target area
         * (integer) POST[primary_contact] - the ID of the primary contact
         * (string) POST[male] - true if the target area is a mens area, false or not set otherwise
         * (string) POST[female] - true if the target area is a womens area, false or not set otherwise
         * 
         * OUTPUT: JSON
         * (integer) next_nonce - the next nouce to use for AJAX calls
         * (boolean) success - TRUE if operation succeeded, FALSE otherwise
         * (string) message - if SUCCESS == FALSE, then a message for the failure, empty otherwise
         * (integer) area_id - the ID of the target area if it was successfully created
         *
         */
    
        global $wpdb;
        $table_prefix = $wpdb->prefix;
        $result = array();
        
        if (!current_user_can('add_target_areas') || wp_verify_nonce(CRU_Utils::get_request_param('_cru_add_target_area_nonce'), 'cru_add_target_area') !== 1) {
            // Security check failed
            $result['success'] = FALSE;
            $result['message'] = "Unauthorized request";
        } else {
            if (CRU_Utils::get_request_param('area_name') === FALSE
                || CRU_Utils::get_request_param('area_name') === "") {
                $result['success'] = FALSE;
                $result['message'] = "Invalid or missing area name";            
            } else if (CRU_Utils::get_request_param('primary_contact') === FALSE
                || preg_match("/^\d+$/", CRU_Utils::get_request_param('primary_contact')) !== 1) {
                $result['success'] = FALSE;
                $result['message'] = "Invalid or missing primary contact";
                /*&& (CRU_Utils::get_request_param('men') !== FALSE || CRU_Utils::get_request_param('women') !== FALSE)*/              
            } else {
                
                $name = CRU_Utils::get_request_param('area_name');
                $primary_contact = CRU_Utils::get_request_param('primary_contact');
                //$men = (CRU_Utils::get_request_param('men') !== FALSE && CRU_Utils::get_request_param('men') == "true") ? 1 : 0;
                //$women = (CRU_Utils::get_request_param('women') !== FALSE && CRU_Utils::get_request_param('women') == "true") ? 1 : 0;

                // Perform the queries in a transaction in case the addition of the affiliation fails
                //
                $wpdb->query("START TRANSACTION");
            
                $affiliations_table = $wpdb->prefix . CRU_Utils::_area_contacts_table;

                if ($wpdb->insert($wpdb->prefix . CRU_Utils::_target_areas_table, array("area_name" => $name)) === FALSE) {
                    $result['success'] = FALSE;
                    $result['message'] = "Failed to add the target area to the database";
                } else {
                    $area_id = $wpdb->insert_id;
                    if ($wpdb->insert($affiliations_table,
                                        array("contact_id" => $primary_contact, 
                                              "area_id" => $area_id,
                                              "affiliation_type" => CRU_Utils::_staff_area_leader)) === FALSE
                    ) {
                        $result['success'] = FALSE;
                        $result['message'] = "Failed to add affiliation for the primary contact";
                        $wpdb->query("ROLLBACK");
                    } else {
                        $wpdb->query("COMMIT"); 
                        $result['success'] = TRUE;
                        $result['message'] = "Added target area";
                        $result['area_id'] = $area_id;
                    }
                }
            }
        }

        $result['next_nonce'] = wp_create_nonce('cru_add_target_area');
        return $result; 
    }


    /**
     * Handler to edit a target area
     *
     *
     */
    public function edit_target_area() {

        /*
         * INPUT: HTTP Request
         * (integer) POST[_cru_edit_target_area_nonce] - the nonce to authenticate the request
         * (integer) POST[area_id] - the ID of the area to edit
         * (string) POST[target_area_name] - the name of the target area
         * (boolean) POST[men] - TRUE if the target area is a mens area, FALSE otherwise
         * (boolean) POST[women] - TRUE if the target area is a womens area, FALSE otherwise
         *
         * OUTPUT: JSON
         * (integer) next_nonce - the next nonce to use for AJAX calls
         * (integer) area_id - the ID of the edited area
         * (boolean) success - TRUE if the area was modified successfully, FALSE otherwise
         * (string) message - if success == FALSE, then a string describing the failure
         *
         */
        
        global $wpdb;
        $table_prefix = $wpdb->prefix;
        $result = array();

        // Forward the area id onward
        $area_id = CRU_Utils::get_request_param('area_id');
        if (CRU_Utils::get_request_param('area_id') !== FALSE) {
            $result['area_id'] = $area_id;
        }

        if (!current_user_can('edit_target_areas') || wp_verify_nonce(CRU_Utils::get_request_param('_cru_edit_target_area_nonce'), 'cru_edit_target_area-' . $area_id) !== 1) {
            // Security check failed
            $result['success'] = FALSE;
            $result['message'] = "Unauthorized request";
        } else {
            // Parameter validation
            //
            if (CRU_Utils::get_request_param('area_name') === FALSE
                || CRU_Utils::get_request_param('area_name') === "") {
                $result['success'] = FALSE;
                $result['message'] = "Invalid or missing area name";            
            } else {
                
                $name = CRU_Utils::get_request_param('area_name');
                //$men = (CRU_Utils::get_request_param('men') !== FALSE && CRU_Utils::get_request_param('men') == "true") ? 1 : 0;
                //$women = (CRU_Utils::get_request_param('women') !== FALSE && CRU_Utils::get_request_param('women') == "true") ? 1 : 0;


                if ($wpdb->update($wpdb->prefix . CRU_Utils::_target_areas_table, array('area_name' => $name), array('area_id' => $area_id)) === FALSE) {
                    $result['success'] = FALSE;
                    $result['message'] = "Failed to edit the target area";
                } else {     
                    $result['success'] = TRUE;
                    $result['message'] = "Target area edited";   
                }
            }
        }

        $result['next_nonce'] = wp_create_nonce('cru_edit_target_area-' . $area_id);
        return $result; 
    }


    /**
     * Handler for removing target areas
     *
     *
     */
    public static function delete_target_area() {
        /*
         * INPUT: HTTP Request
         * (integer) POST[_cru_delete_target_area_nonce] - the nonce to authenticate the request
         * (integer) POST[area_id] - the ID of the area to delete
         * 
         * OUTPUT: JSON
         * (integer) next_nonce - the next nonce to use for AJAX calls
         * (boolean) success - TRUE if the area was deleted, FALSE otherwise
         * (string) message - if success == FALSE, then a message for the failure, empty otherwise
         * (integer) area_id - the id of the area being removed
         */
        
        global $wpdb;
        $table_prefix = $wpdb->prefix;
        $result = array();

        $area_id = CRU_Utils::get_request_param('area_id');
        if ($area_id !== FALSE) {
            $result['area_id'] = $area_id;
        }
        
        if (!current_user_can('delete_users') || wp_verify_nonce(CRU_Utils::get_request_param('_cru_delete_target_area_nonce'), "cru_delete_target_area-$area_id") !== 1) {
            // Security check failed
            $result['success'] = FALSE;
            $result['message'] = "Unauthorized request";
        } else {
            if ($area_id !== FALSE
                && preg_match("/^\d+$/", $area_id) === 1
            ) {
                
                $area_table = $wpdb->prefix . CRU_Utils::_target_areas_table;
                $area_contacts_table = $wpdb->prefix . CRU_Utils::_area_contacts_table;
                $small_groups_table = $wpdb->prefix . CRU_Utils::_small_groups_table;

                $small_groups_query = $wpdb->prepare("DELETE FROM $small_groups_table WHERE area_id = %s", $area_id);
                $rows = $wpdb->query($small_groups_query);

                $area_contacts_query = $wpdb->prepare("DELETE FROM $area_contacts_table WHERE area_id = %s", $area_id); 
                $rows = $wpdb->query($area_contacts_query);

                $areas_query = $wpdb->prepare("DELETE FROM $area_table WHERE area_id = %s", $area_id);
                $rows = $wpdb->query($areas_query);

                if ($rows !== 1) {
                    $result['success'] = FALSE;
                    $result['message'] = "Failed to delete the area";
                } else {
                    $result['success'] = TRUE;
                    $result['message'] = "Deleted the area";
                }
            } else {
                $result['success'] = FALSE;
                $result['message'] = "Invalid or missing area";
            }
        }

        $result['next_nonce'] = wp_create_nonce("cru_delete_target_area-$area_id");
        return $result;   
    } // public function delete_area()



    /**
     * Handler for adding affiliations
     *
     *
     */
    public function add_affiliation() {

        /*
         * INPUT: HTTP Request
         * (integer) POST[_cru_add_affiliation_nonce] - nonce to authenticate the request
         * (integer) POST[contact_id] - contact ID of the affiliation
         * (integer) POST[area_id] - area ID of the affiliation
         * (string) POST[affiliation_type] - the type of affiliation (STAFF, INTERN, and STUDENT are only allowed values)
         *
         * OUTPUT: JSON
         * (integer) next_nonce - next nonce for AJAX calls
         * (boolean) success - TRUE if the affiliation was added, FALSE otherwise
         * (string) message - if success == FALSE, then a message when the failure occured, empty otherwise
         *
         */

        # TODO Include information in the response indicating the added affiliation
        global $wpdb;
        $table_prefix = $wpdb->prefix;
        $result = array();

        // Forward the area_id
        if (CRU_Utils::get_request_param('area_id') !== FALSE) {
            $result['area_id'] = CRU_Utils::get_request_param('area_id');
        }

        if (!current_user_can('edit_target_areas') || wp_verify_nonce(CRU_Utils::get_request_param('_cru_add_affiliation_nonce'), 'cru_add_affiliation') !== 1) {
            // Security check failed
            $result['success'] = FALSE;
            $result['message'] = "Unauthorized request";
        } else {
            // Validate input

            // Check for valid affiliation type
            $affiliation_type = CRU_Utils::get_request_param('affiliation_type');
            if (CRU_Utils::get_request_param('affiliation_type') === FALSE
                || ($affiliation_type != CRU_Utils::_student_group_leader
                    && $affiliation_type != CRU_Utils::_student_area_leader
                    && $affiliation_type != CRU_Utils::_staff_area_leader)) {
                    $result['success'] = FALSE;
                    $result['message'] = "Invalid or missing affiliation type";
            // Check for valid contact ID
            } else if (CRU_Utils::get_request_param('contact_id') === FALSE
                     || preg_match("/^\d+$/", CRU_Utils::get_request_param('contact_id')) !== 1) {
                $result['success'] = FALSE;
                $result['message'] = "Invalid or missing contact";
            // Check for valid area ID
            } else if (CRU_Utils::get_request_param('area_id') === FALSE 
                     || preg_match("/^\d+$/", CRU_Utils::get_request_param('area_id')) !== 1) {
                $result['success'] = FALSE;
                $result['message'] = "Invalid or missing area";
            } else {
                       
                // Query the DB to see if the user even exists
                $group_leader_query = $wpdb->prepare("SELECT * FROM $wpdb->users INNER JOIN $wpdb->usermeta ON "
                . "$wpdb->users.ID = $wpdb->usermeta.user_id WHERE $wpdb->usermeta.meta_key = 'wp_capabilities'"
                . " AND $wpdb->usermeta.meta_value LIKE '%s' AND $wpdb->usermeta.user_id = %s", "%cru%", CRU_Utils::get_request_param('contact_id')); 
              
                $contacts_found = $wpdb->query($group_leader_query);

                // Query the DB to see if the area even exists
                $area_table = $table_prefix . CRU_Utils::_target_areas_table;
                $area_query = $wpdb->prepare("SELECT * FROM $area_table WHERE area_id = %s", CRU_Utils::get_request_param('area_id'));
                $areas_found = $wpdb->query($area_query);
        
                // Check that the area and contact exist, then perform the insertion
                if ($contacts_found !== 1 || $areas_found !== 1) {
                    $result['success'] = FALSE;
                    $result['message'] = "Specified contact and/or area doesn't exist";
                } else {
                    $table_name = $table_prefix . CRU_Utils::_area_contacts_table;

                    $val = $wpdb->insert($table_name, array('contact_id' => CRU_Utils::get_request_param('contact_id'),
                                                            'area_id' => CRU_Utils::get_request_param('area_id'),
                                                            'affiliation_type' => CRU_Utils::get_request_param('affiliation_type')));
                    if ($val === FALSE) {
                        $result['success'] = FALSE;
                        $result['message'] = "Affiliation could not be added";
                    } else {                
                        $result['success'] = TRUE;  
                        $result['message'] = "Affiliation added";
                    }
                }                
            }
        }        

        $result['next_nonce'] = wp_create_nonce('cru_add_affiliation');
        return $result; 
    }

    
    /**
     * Handler for removing affiliations
     *
     */
    public function delete_affiliation() {

        /*
         * INPUT: HTTP Request
         * (integer) POST[_cru_delete_affiliation_nonce] - nonce to authenticate the request
         * (integer) POST[contact_id] - contact ID of the affiliation
         * (integer) POST[area_id] - area ID of the affiliation
         * (string) POST[affiliation_type] - the type of affiliation (STAFF, INTERN, and STUDENT are only allowed values)
         *
         * OUTPUT: JSON
         * (integer) next_nonce - next nonce for AJAX calls
         * (boolean) success - TRUE if the affiliation was deleted, FALSE otherwise
         * (string) message - if success == FALSE, then a message when the failure occured, empty otherwise
         * (string) objectId - if success == TRUE, then the id of the affiliation in the form "TYPE contact_id area_id"
         *
         */
        global $wpdb;
        $table_prefix = $wpdb->prefix;
        $result = array();

        // Forward the area_id
        $area_id = CRU_Utils::get_request_param('area_id');
        if (CRU_Utils::get_request_param('area_id') !== FALSE) {
            $result['area_id'] = $area_id;
        }

        $contact_id = CRU_Utils::get_request_param('contact_id');
        $affiliation_type = CRU_Utils::get_request_param('affiliation_type');
        
        // Perform security check first
        if (!current_user_can('edit_target_areas') 
            || wp_verify_nonce(CRU_Utils::get_request_param('_cru_delete_affiliation_nonce'),
                                "cru_delete_affiliation-$area_id-$contact_id-$affiliation_type") !== 1) {
            // Security check failed
            $result['success'] = FALSE;
            $result['message'] = "Unauthorized request";
        } else {

            // Validate input
            if (CRU_Utils::get_request_param('affiliation_type') === FALSE 
                || (CRU_Utils::get_request_param('affiliation_type') != CRU_Utils::_student_group_leader
                    && CRU_Utils::get_request_param('affiliation_type') != CRU_Utils::_staff_area_leader)) {
                $result['success'] = FALSE;
                $result['message'] = "Invalid or missing affiliation type";
            } else if (CRU_Utils::get_request_param('contact_id') === FALSE
                    || preg_match("/^\d+$/", CRU_Utils::get_request_param('contact_id')) !== 1) {
                $result['success'] = FALSE;
                $result['message'] = "Invalid or missing contact";
            } else if (CRU_Utils::get_request_param('area_id') === FALSE 
                    || preg_match("/^\d+$/", CRU_Utils::get_request_param('area_id')) !== 1) {
                $result['success'] = FALSE;
                $result['message'] = "Invalid or missing area";
            } else {
                 
                $table_name = $table_prefix . CRU_Utils::_area_contacts_table;  
             
                // Perform the query against the area_contacts table
                $query = $wpdb->prepare("DELETE FROM " . $table_name . " WHERE contact_id = %s AND area_id = %s AND affiliation_type = %s",
                                        CRU_Utils::get_request_param('contact_id'), CRU_Utils::get_request_param('area_id'), CRU_Utils::get_request_param('affiliation_type'));
                $val = $wpdb->query($query);
                if ($val > 0) {
                    $result['success'] = TRUE;
                    $result['message'] = "Affiliation deleted";
                } else {
                    $result['success'] = FALSE;
                    $result['message'] = "Affiliation could not be deleted";
                }              
            }
        }        

        return $result; 
    } // public function cru_delete_affiliation()


    /**
     * Get a list of (distinct) names affiliated with a given target area
     *   
     * @param string $area_id
     *
     */
    public static function get_affiliated_contacts($area_id) {
        global $wpdb;
        $area_contacts_table = $wpdb->prefix . CRU_Utils::_area_contacts_table;

        $affiliated_users_query = $wpdb->prepare("SELECT display_name, contact_id, affiliation_type FROM (SELECT * FROM $wpdb->users INNER JOIN $wpdb->usermeta ON "
                            . "$wpdb->usermeta.user_id = $wpdb->users.ID WHERE "
                            . "$wpdb->usermeta.meta_key = 'wp_capabilities' AND $wpdb->usermeta"
                            . ".meta_value LIKE '%s') AS cru_contacts INNER JOIN $area_contacts_table ON cru_contacts.user_id = "
                            . "$area_contacts_table.contact_id WHERE $area_contacts_table.area_id = '%s'", '%cru%', $area_id);
        $affiliated_users = $wpdb->get_results($affiliated_users_query, ARRAY_A);
        return $affiliated_users;
    }


    /**
     * Get all the target areas in the database
     *
     *
     */
    public static function get_target_areas() {
        global $wpdb;
        $target_areas_table = $wpdb->prefix . CRU_Utils::_target_areas_table;
        
        $query = "SELECT * FROM $target_areas_table";
        $target_areas = $wpdb->get_results($query, ARRAY_A);

        return $target_areas;
    }
    
    /**
     * Get a url to delete a given affiliation
     *
     * @param integer $id The id of the affiliation to delete
     *
     * @return The url to request to perform a deletion
     */
    public static function delete_affiliation_url($id) {
        $id = urlencode($id);
        return admin_url("/admin.php?page=cru-handle-action&action=cru_delete_affiliation&area_id=$id");
    }


    /**
     * 
     *
     */
    public static function delete_affiliation_nonce($area_id, $contact_id, $affiliation_type) {
       return wp_create_nonce("cru_delete_affiliation-$area_id-$contact_id-$affiliation_type");
    }

    
    /**
     *
     *
     */
    public static function edit_target_area_url($area_id) {
        $area_id = urlencode($area_id);
        return admin_url("admin.php?page=cru-target-areas&action=cru_edit_target_area&area_id=$area_id");
    }


    /**
     * Create a url to delete the target area
     *  
     *
     */
    public static function delete_target_area_url($area_id) {
        $area_id =  urlencode($area_id);
        return admin_url("admin.php?page=cru-handle-action&action=cru_delete_target_area&area_id=$area_id&_cru_delete_target_area_nonce=" . wp_create_nonce("cru_delete_target_area-$area_id"));
    }


    /**
     * Get the string naming an affiliation based on the type
     *
     */
    public static function get_affiliation_string($type) {
        if (isset(CRU_Utils::$_affiliation_names[$type])) {
			return CRU_Utils::$_affiliation_names[$type];
		} else {
			return "";
		}
    } // public function get_affiliation_string()

}
?>
