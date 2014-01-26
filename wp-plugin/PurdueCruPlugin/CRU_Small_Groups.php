<?php

// Check if this function exists to see if we are being invoked as a plugin
// in wordpress context, otherwise we need to bailout and not output anything
if (!function_exists('add_action')) {
    exit(1);
}


require_once("CRU_Utils.php");

/**
 * Primary class for managing small groups
 *
 * author: Jason P. Rahman (rahmanj@purdue.edu, jprahman93@gmail.com)
 */
class CRU_Small_Groups {
    

    /**
     * Handler for removing small groups
     */
    public static function delete_small_group() {
        
        /*
         * INPUT: HTTP Request
         * (integer) POST[_cru_delete_small_group_nonce] - nonce to authenticate the request
         * (integer) POST[group_id] - ID of the small group to delete
         *
         * OUTPUT: JSON
         * (integer) next_nonce - the next nonce for AJAX calls
         * (boolean) success - TRUE if the group was deleted, FALSE otherwise
         * (string) message - if success == FALSE, then a message for the failure, emtpy otherwise
         * (integer) group_id - the ID of the deleted small group
         */

        global $wpdb;
        $table_prefix = $wpdb->prefix;
        $result = array();
        
        // Forward the group id onward
        //
        $group_id = CRU_Utils::get_request_param('group_id');
        if ($group_id !== FALSE) {
            $result['group_id'] = $group_id;
        }
        
        // Perform security check first
        if (!current_user_can('delete_small_groups') || wp_verify_nonce(CRU_Utils::get_request_param('_cru_delete_small_group_nonce'), 'cru_delete_small_group-' . $group_id) !== 1) {
            $result['success'] = FALSE;
            $result['message'] = "Unauthorized request";
        } else if ($group_id === FALSE || preg_match("/^\d+$/", $group_id) !== 1) {
            $result['success'] = FALSE;
            $result['message'] = "Missing or invalid group";
        } else {

            // Perform the deletion and check that it was successful
            $group_table = $wpdb->prefix . CRU_Utils::_small_groups_table;
            $delete_query = $wpdb->prepare("DELETE FROM $group_table WHERE group_id = %s", $group_id);
            $rows_affected = $wpdb->query($delete_query);

            if ($rows_affected > 0) {
                $result['success'] = TRUE;  
                $result['message'] = "Deleted group";         
            } else {
                $result['success'] = FALSE;
                $result['message'] = "Could not delete the group";
            }
        }

        $result['next_nonce'] = wp_create_nonce('cru_delete_small_group');
        return $result;
    }


    /**
     * Handler to add small groups
     */
    public static function add_small_group() {

        /*
         * INPUT: HTTP Request
         * (integer) POST[_cru_add_small_group_nonce] - nonce to authenticate the request
         * (string) POST[time] - starting time of the small group
         * (string) POST[day] - the day of the week the small group occurs
         * (string) POST[location] - the location where the small group meets
         * (integer) POST[area_id] - the ID of the area the small group belongs to
         * (integer) POST[leader] - ID of the user who leads the group
         *
         * OUTPUT: JSON
         * (integer) next_nonce - the next nonce for AJAX calls
         * (boolean) success - TRUE if the group was created, FALSE otherwise
         * (string) message - if success == FALSE, then a message for the failure, empty otherwise
         * (integer) group_id - the ID of the newly created group
         *
         */

        global $wpdb;
        $table_prefix = $wpdb->prefix;
        $result = array();

        // Perform security check
        $nonce = CRU_Utils::get_request_param('_cru_add_small_group_nonce');
        if (!current_user_can('add_small_groups') || wp_verify_nonce($nonce, 'cru_add_small_group') !== 1) {

            // Security check failed
            $result['success'] = FALSE;
            $result['message'] = "Unauthorized request";
        } else {

            // Fetch the request parameters
            $time = CRU_Utils::get_request_param('time');
            $location = CRU_Utils::get_request_param('location');
            $area_id = CRU_Utils::get_request_param('area_id');
            $leader = CRU_Utils::get_request_param('leader');
            $day = CRU_Utils::get_request_param('day');

            // TODO Check that the group leader is valid

            // Validate the request parameters
            if ($time === FALSE || $time === "") {

                $result['success'] = FALSE;
                $result['message'] = "Invalid or missing time";
            } else if ($day === FALSE || $day === "") {

                $result['success'] = FALSE;
                $result['message'] = "Invalid or missing day";
            } else if ($area_id === FALSE || preg_match("/^\d+$/", $area_id) !== 1) {

                $result['success'] = FALSE;
                $result['message'] = "Invalid or missing area";
            } else if ($location === FALSE || $location === "") {

                $result['success'] = FALSE;
                $result['message'] = "Invalid or missing location";
            } else if ($leader === FALSE || preg_match("/^\d+$/", $leader) !== 1) {

                $result['success'] = FALSE;
                $result['message'] = "Invalid or missing group leader";
            } else {

                // Check that the area exists
                $area_table = $wpdb->prefix . CRU_Utils::_target_areas_table;
                $area_query = $wpdb->prepare("SELECT * FROM $area_table WHERE area_id = %s", CRU_Utils::get_request_param('area_id'));
                $rows = $wpdb->query($area_query);
                    
                if ($rows !== 1) {
                    $result['success'] = FALSE;
                    $result['message'] = "Cannot add small group because the area doesn't exist";
                } else {
                            
                    // Add the group to the table now that we have verified the input
                    //
                    $small_groups_table = $wpdb->prefix . CRU_Utils::_small_groups_table;
                    $val = $wpdb->insert($small_groups_table,
                                         array('time' => $time,
                                               'day' => $day,
                                               'area_id' => $area_id,
                                               'contact_id' => $leader,
                                               'location' => $location));
                    if ($val === FALSE) {
                        $result['success'] = FALSE;
                        $result['message'] = "Failed to add the small group";
                    } else {
                        // Return the ID of the inserted group along with the result
                        //
                        $result['success'] = TRUE;
                        $result['message'] = "Small group added";
                        $result['group_id'] = $wpdb->insert_id;
                    }
                }
            }
        }

        $result['next_nonce'] = wp_create_nonce('cru_add_small_group');
        return $result;
    }


    /**
     * Handler for editing a small group
     */
    public static function edit_small_group() {
         
        /*
         * INPUT: HTTP Request
         * (integer) POST[_cru_edit_small_group_nonce] - nonce to authenticate the request
         * (integer) POST[group_id] - the id of the group being edited
         * (string) POST[time] - starting time of the small group
         * (string) POST[day] - the day of the week the small group occurs
         * (string) POST[location] - the location where the small group meets
         * (integer) POST[area_id] - the ID of the area the small group belongs to
         * (integer) POST[leader] - ID of the user who leads the group
         *
         * OUTPUT: JSON
         * (integer) next_nonce - the next nonce for AJAX calls
         * (boolean) success - TRUE if the group was created, FALSE otherwise
         * (string) message - if success == FALSE, then a message for the failure
         * (integer) group_id - the ID of the edited group
         */

        global $wpdb;
        $table_prefix = $wpdb->prefix;
        $result = array();

        $group_id = CRU_Utils::get_request_param('group_id');
        if ($group_id !== FALSE) {
            $result['group_id'] = $group_id;
        }   

        // Retrive the various parameters
        $time = CRU_Utils::get_request_param('time');
        $location = CRU_Utils::get_request_param('location');
        $area_id = CRU_Utils::get_request_param('area_id');
        $leader = CRU_Utils::get_request_param('leader');
        $day = CRU_Utils::get_request_param('day');

        // Perform security check
        $nonce = CRU_Utils::get_request_param('_cru_edit_small_group_nonce');
        if (!current_user_can('edit_small_groups') || wp_verify_nonce($nonce, "cru_edit_small_group-$group_id") !== 1) {
            $result['success'] = FALSE;
            $result['message'] = "Unauthorized request";
        } else {

            // Validate the request parameters
            if ($time === FALSE || $time === "") {

                $result['success'] = FALSE;
                $result['message'] = "Invalid or missing time";
            } else if ($day === FALSE || $day === "") {

                $result['success'] = FALSE;
                $result['message'] = "Invalid or missing day";
            } else if ($area_id === FALSE || preg_match("/^\d+$/", $area_id) !== 1) {

                $result['success'] = FALSE;
                $result['message'] = "Invalid or missing area";
            } else if ($location === FALSE || $location === "") {

                $result['success'] = FALSE;
                $result['message'] = "Invalid or missing location";
            } else if ($leader === FALSE || preg_match("/^\d+$/", $leader) !== 1) {

                $result['success'] = FALSE;
                $result['message'] = "Invalid or missing group leader";
            } else {

                // Query to check if the area exists
                $area_table = $wpdb->prefix . CRU_Utils::_target_areas_table;
                $area_query = $wpdb->prepare("SELECT * FROM $area_table WHERE area_id = %s", $area_id);
                $rows = $wpdb->query($area_query);
            
                if ($rows !== 1) {
                    $result['success'] = FALSE;
                    $result['message'] = "Cannot edit small group because the area doesn't exist";
                } else {

                    // Perform update query on datebase with validated data
                    $small_groups_table = $wpdb->prefix . CRU_Utils::_small_groups_table;
                    $val = $wpdb->update($small_groups_table,
                                         array('time' => $time,
                                               'day' => $day,
                                               'location' => $location,
                                               'area_id' => $area_id,
                                               'contact_id' => $leader),
                                         array('group_id' => $group_id));
                    if ($val === FALSE) {
                        $result['success'] = FALSE;
                        $result['message'] = "Failed to edit the small group";
                    } else {
                        $result['success'] = TRUE;
                        $result['message'] = "Small group edited";
                    }
                }
            }
        }

        $result['next_nonce'] = wp_create_nonce("cru_edit_small_group-$group_id");
        return $result;
    }

    /**
     * Get information for all small groups
     *
     *
     */
    public static function get_small_groups() {
        global $wpdb;
        $groups_table = $wpdb->prefix . CRU_Utils::_small_groups_table;
        $areas_table = $wpdb->prefix . CRU_Utils::_target_areas_table;

        $small_groups_query = "SELECT * FROM $groups_table INNER JOIN " 
        . "$wpdb->users ON $groups_table.contact_id = $wpdb->users.ID INNER JOIN "
        . "$areas_table ON $groups_table.area_id = $areas_table.area_id";
        $small_groups = $wpdb->get_results($small_groups_query, ARRAY_A);
        return $small_groups;
    }



    /**
     * Get full information for all the small groups in a given target area, including full contact information
     *
     * @param integer $area_id
     *
     * @return an array of associative arrays containing group information
     */
    public static function get_small_groups_full($area_id) {
        global $wpdb;

        if (preg_match(CRU_Utils::_id_regex, $area_id) !== 1) {
            return FALSE;
        }

        $small_groups_table = $wpdb->prefix . CRU_Utils::_small_groups_table;

        $query = $wpdb->prepare("SELECT group_id, area_id, contact_id, day, time, location, meta1.meta_value AS first_name, "
                . "meta2.meta_value AS last_name, meta3.meta_key AS phone_number FROM $small_groups_table "
                . "LEFT OUTER JOIN $wpdb->usermeta AS meta1 ON meta1.user_id = contact_id "
                . "LEFT OUTER JOIN $wpdb->usermeta AS meta2 ON meta2.user_id = contact_id "
                . "LEFT OUTER JOIN $wpdb->usermeta AS meta3 ON meta3.user_id = contact_id "
                . "INNER JOIN $wpdb->usermeta AS meta4 ON meta4.user_id = contact_id "
                . "WHERE meta1.meta_key = 'first_name' AND meta2.meta_key = 'last_name' "
                . "AND meta3.meta_key = 'phone_number' AND meta4.meta_key = 'wp_capabilities' AND meta4.meta_value LIKE '%s''", 'CRU', $area_id);

        $small_groups = $wpdb->get_results($query, ARRAY_A);
        return $small_groups;
    }


    /**
     * Get a url to the page where the given group can be edited
     *
     *
     */
    public static function delete_group_url($id) {
        $id = urlencode($id);
        $nonce = wp_create_nonce("cru_delete_small_group-$id");
        return admin_url("admin.php?page=cru-handle-action&action=cru_delete_small_group&group_id=$id&_cru_delete_small_group_nonce=$nonce");
    }


    /**
     * Get a url to delete the group with the given ID
     *
     *
     */
    public static function edit_group_url($id) {
        $id = urlencode($id);
        return admin_url("admin.php?page=cru-small-groups&action=cru_edit_small_group&group_id=$id");
    }
}
?>
