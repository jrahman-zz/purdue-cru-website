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
class CRU_Contacts {


    /**
     * Handler for editing contacts
     */
    public function edit_contact() {

        /*
         * INPUT: HTTP Request
         * (integer) POST[request_nouce] - nonce to authenticate the request
         * (integer) POST[contact_id] - the ID of the contact to update
         * (integer) POST[provider_id] - the ID of the contact's cell provider
         * (string) POST[phone_number] - the updated phone number, or empty
         * 
         * OUTPUT: JSON
         * (integer) next_nonce - the next nonce for AJAX calls
         * (boolean) success - TRUE if the contact was updated, FALSE otherwise
         * (string) message - a message with the action status
         * (integer) contact_id - 
         */

        global $wpdb;

        $result = array();

        $contact_id = CRU_Utils::get_request_param('contact_id');
        if ($contact_id !== FALSE) {
            $result['contact_id'] = $contact_id;
        }

        // Build phone number from multiple fields
        $phone_number = CRU_Utils::get_request_param('area_code')
                      . CRU_Utils::get_request_param('exchange_code') 
                      . CRU_Utils::get_request_param('number');

        // Define the query to fetch providers from the table
        $provider_id = CRU_Utils::get_request_param('provider_id');
        $provider_query = $wpdb->prepare("SELECT * FROM " . $wpdb->prefix . CRU_Utils::_provider_table . " WHERE provider_id = %d", $provider_id);

        // Perform the security check first
        if (!current_user_can('edit_cru_contacts') || wp_verify_nonce(CRU_Utils::get_request_param("_cru_edit_contact_nonce"), "cru_edit_contact-$contact_id") !== 1) {
            $result['success'] = FALSE;
            $result['message'] = "Unauthorized request";
        } else {

            // Input validation
            if ($contact_id !== FALSE && preg_match("/^\d+$/", $contact_id) === 1) {

                // Check for a plausably valid ID and that such a provider exists
                if ($provider_id !== FALSE && preg_match("/^\d+$/", $provider_id) === 1 && $wpdb->query($provider_query) === 1) {

                    // Fetch previous meta then update with the new value
                    $prev_value = get_user_meta($contact_id, "wp_cru_provider", true);
                    $success = update_user_meta($contact_id, "wp_cru_provider", $provider_id, $prev_value);
                    if ($success !== FALSE) {

                        // Check that the phone number is valid
                        if (preg_match("/^([2-9][0-9]{2})([2-9][0-9]{2})([0-9]{4})$/", $phone_number) === 1) {

                            // Fetch the previous meta then update with the new value
                            $prev_value = get_user_meta($contact_id, "wp_cru_phone_number", true);
                            $success = update_user_meta($contact_id, "wp_cru_phone_number", $phone_number, $prev_value);
                            if ($success !== FALSE) {
                                $result['success'] = TRUE;
                                $result['message'] = "Contact updated";
                            } else {
                                $result['success'] = FALSE;
                                $result['message'] = "Failed to update the phone number";
                            }
                        } else {
                            $result['success'] = FALSE;
                            $result['message'] = "Invalid or missing phone number";
                        }
                    } else {
                        $result['success'] = FALSE;
                        $result['message'] = "Failed to update the provider";
                    }
                } else {
                    $result['success'] = FALSE;
                    $result['message'] = "Invalid or missing provider ID";
                }
            } else {
                $result['success'] = FALSE;
                $result['message'] = "Invalid or missing contact ID";
            }
        }

        $result['next_nonce'] = wp_create_nonce("cru_edit_contact-$contact_id");
        return $result;
    } // public function edit_contact()


    /**
     * Get full information about all CRU contacts
     *
     */
    public static function get_contacts_full() {
        global $wpdb;

        // This is a terrible SQL query, it grabs all the CRU contacts with their phone numbers and provider IDs
		// Note that some contacts will not have a provider or number, so we use a left outer join
        $providers_table = $wpdb->prefix . CRU_Utils::_provider_table;
        $contact_query = "SELECT $wpdb->users.ID, $wpdb->users.display_name, $wpdb->users.user_email, meta2.meta_value AS provider_id, "
                        . "meta3.meta_value AS phone_number, meta4.meta_value AS first_name, providers.provider_gateway AS provider_gateway "
                        . "FROM $wpdb->users "
                        . "INNER JOIN $wpdb->usermeta AS meta "
                        . "ON $wpdb->users.ID = meta.user_id AND meta.meta_key = 'wp_capabilities' AND meta.meta_value LIKE '%CRU%' "
                        . "LEFT OUTER JOIN $wpdb->usermeta AS meta2 "
                        . "ON $wpdb->users.ID = meta2.user_id AND meta2.meta_key = 'wp_cru_provider' "
                        . "LEFT OUTER JOIN $wpdb->usermeta AS meta3 "
                        . "ON $wpdb->users.ID = meta3.user_id AND meta3.meta_key = 'wp_cru_phone_number' "
                        . "LEFT OUTER JOIN $wpdb->usermeta AS meta4 "
                        . "ON $wpdb->users.ID = meta4.user_id AND meta3.meta_key = 'first_name' "
                        . "LEFT OUTER JOIN $providers_table AS providers "
                        . "ON meta2.meta_value = providers.provider_id "
                        . "ORDER BY $wpdb->users.display_name ASC";

        $contacts = $wpdb->get_results($contact_query, ARRAY_A);
        return $contacts;
    } // public static function


    /**
     * Get short basic information for all CRU contacts
     *
     */
    public static function get_contacts_short() {
        global $wpdb;

        $cru_contacts_query = "SELECT * FROM $wpdb->users INNER JOIN $wpdb->usermeta  ON "
                            . "$wpdb->usermeta.user_id = $wpdb->users.ID WHERE "
                            . "$wpdb->usermeta.meta_key = 'wp_capabilities' AND $wpdb->usermeta"
                            . ".meta_value LIKE '%cru%' ORDER BY display_name ASC";
        $cru_contacts = $wpdb->get_results($cru_contacts_query, ARRAY_A);
        return $cru_contacts;
    }

    
    /**
     * Get a contact's full information by ID
     *
     * @return array associative array of information about the contact, or NULL on invalid input
     */
    public static function get_contact_information($contact_id) {
        global $wpdb;

        // Quick sanity check so we can bailout early if needed
        if ($contact_id === "" or $contact_id < 0) {
            return NULL;
        }

        // Grabs all the CRU contacts with their phone numbers and provider IDs
		// Note that some contacts will not have a provider or number, so we use a left outer join
        $providers_table = $wpdb->prefix . CRU_Utils::_provider_table;
        $contact_query = $wpdb->prepare(
                        "SELECT $wpdb->users.ID, $wpdb->users.display_name, $wpdb->users.user_email, meta2.meta_value AS provider_id, "
                        . "meta3.meta_value AS phone_number, meta4.meta_value AS first_name, providers.provider_gateway AS provider_gateway "
                        . "FROM $wpdb->users "
                        . "INNER JOIN $wpdb->usermeta AS meta "
                        . "ON $wpdb->users.ID = meta.user_id AND meta.meta_key = 'wp_capabilities' AND meta.meta_value LIKE '%s' "
                        . "LEFT OUTER JOIN $wpdb->usermeta AS meta2 "
                        . "ON $wpdb->users.ID = meta2.user_id AND meta2.meta_key = 'wp_cru_provider' "
                        . "LEFT OUTER JOIN $wpdb->usermeta As meta3 "
                        . "ON $wpdb->users.ID = meta3.user_id AND meta3.meta_key = 'wp_cru_phone_number' "
                        . "LEFT OUTER JOIN $wpdb->usermeta AS meta4 "
                        . "ON $wpdb->users.ID = meta4.user_id AND meta3.meta_key = 'first_name' "
                        . "LEFT OUTER JOIN $providers_table AS providers "
                        . "ON meta2.meta_value = providers.provider_id "
                        . "WHERE $wpdb->users.ID = %s", '%CRU%', $contact_id);
        $contact = $wpdb->get_row($contact_query, ARRAY_A);
        return $contact; 
    }
}
?>
