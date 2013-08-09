<?php

// Check if this function exists to see if we are being invoked as a plugin
// in wordpress context, otherwise we need to bailout and not output anything
if (!function_exists('add_action')) {
    exit(1);
}
 
@include_once("Mail.php");
require_once("CRU_Utils.php");

/**
 * Primary class for sending message to contacts
 *
 * author: Jason P. Rahman (rahmanj@purdue.edu, jprahman93@gmail.com)
 *
 */
class CRU_Messaging {

    /**
     *
     * Send test text message to user
     *
     */
    public static function send_test_text() {
        /*
         * INPUT: HTTP Request
         * (integer) POST[contact_id] - The ID of the contact to be sent a test message
         *
         * OUTPUT: JSON or an associative array
         * (integer) next_nonce - The next nonce to use for AJAX calls
         * (boolean) success - True if the call succeeded, false otherwise
         * (string) message - String describing the result of the action
         * (integer) contact_id - ID of the contact the text was sent to
         */
        
        $contact_id = CRU_Utils::get_request_param('contact_id');    
        $result = array();

        if ($contact_id !== FALSE) {
            $result['contact_id'] = $contact_id;
        }

        $nonce = CRU_Utils::get_request_param("_cru_send_test_text_nonce");
        if (!current_user_can("edit_cru_contacts") || wp_verify_nonce($nonce, "cru_send_test_text-$contact_id") !== 1) {

            // Security check failed
            $result['message'] = "Unauthorized request";
            $result['success'] = FALSE;
        } else {
            $contact = CRU_Contacts::get_contact_information($contact_id);
            if ($contact === NULL || !is_array($contact)) {
                $result['message'] = "Invalid or missing contact ID";
                $result['success'] = FALSE;
            } else {
                $message = CRU_Messaging::_create_message("Test");

                // TODO Improve the robustness here
                $address = $contact['phone_number'] . '@' . $contact['provider_gateway'];
                $to = array($address => $contact['display_name']);
                $ret = CRU_Messaging::_send_message("Purdue CRU Test",
                                                    get_option('cru-email-address', ''),
                                                    $message, $to);

                if (!Pear::isError($ret)) {
                    $result['message'] = "Test message sent, please wait for it to arrive";
                    $result['success'] = TRUE;
                } else {
                    $result['message'] = $ret->getMessage();   
                    $result['success'] = FALSE;
                }
            }
        }
        
        $result['next_nonce'] = wp_create_nonce("cru_send_test_text-$contact_id");
        return $result;
    }


	/**
	 * Send a message to all members of a target area
	 *
     * @param string $subject The message subject
     * @param string $from The email address of the sender
     * @param string $message The message to send
	 * @param integer $area_id the id of the area in the database to send the message to
 	 *
     * @return mixed On failure returns PEAR_Error, on success returns the number of contacts to whom the messages were sent
	 */
	public static function send_target_area_message($subject, $from, $message, $area_id = -1) {
		global $wpdb;

		$providers_table = $wpdb->prefix . _provider_table;
		$affiliations_table = $wpdb->prefix . _area_contacts_table;

        // Improve this
        $addr = get_option('cru-email-default-address1','');
        if ($addr !== '') {
            $_default_addresses[$addr] = '';
        }
        $addr = get_option('cru-email-default-address2','');
        if ($addr !== '') {
            $_default_addresses[$addr] = '';
        }
        $addr = get_option('cru-email-default-address3','');
        if ($addr !== '') {
            $_default_addresses[$addr] = '';
        }

		$results = array();
		$addresses = array_merge($_default_addresses);
        
        // Check if a target area parameter was given, and if so query 
        if ($area_id >= 0) {
            $temp = CRU_Messaging::_get_target_area_addresses($area_id);
            $addresses = array_merge($addresses, $temp);
        }      

		$ret = CRU_Messaging::_send_message($subject, $from, $message, $addresses);
        if ($ret === TRUE) {
            return count($addresses);
        } else {
            return $ret;
        }
	}

    /**
     * Get a listing of all the contacts from a target area who should be messaged
     *
     * @param int $area_id
     *
     * @return mixed
     */
    private static function _get_target_area_addresses($area_id) {
        global $wpdb;
        $addresses = array();

        $providers_table = $wpdb->prefix . _provider_table;
		$affiliations_table = $wpdb->prefix . _area_contacts_table;

        $query = $wpdb->prepare("SELECT affiliations.affiliation_type AS affiliation_type, "
            . "wp_users.user_email AS user_email, wp_users.display_name AS display_name, "
            . "meta1.meta_value AS first_name, meta2.meta_value AS last_name, "
            . "meta3.meta_value AS phone_number, providers.provider_gateway AS provider_gateway "
            . "FROM $affiliations_table AS affiliations "
            . "LEFT OUTER JOIN wp_usermeta AS meta1 "
            . "ON meta1.user_id = affiliations.contact_id "
            . "AND meta1.meta_key = 'first_name' "
            . "LEFT OUTER JOIN wp_usermeta AS meta2 "
            . "ON meta2.user_id = affiliations.contact_id "
            . "AND meta2.meta_key = 'last_name' "
            . "LEFT OUTER JOIN wp_usermeta AS meta3 "
            . "ON meta3.user_id = affiliations.contact_id "
            . "AND meta3.meta_key = 'wp_cru_phone_number' "
            . "INNER JOIN wp_usermeta AS meta4 "
            . "ON meta4.user_id = affiliations.contact_id "
            . "AND meta4.meta_key = 'wp_capabilities' "
            . "AND meta4.meta_value LIKE '%s' "
            . "INNER JOIN wp_users "
            . "ON wp_users.ID = affiliations.contact_id "
            . "LEFT OUTER JOIN wp_usermeta AS meta5 "
            . "ON meta5.user_id = affiliations.contact_id "
            . "AND meta5.meta_key = 'wp_cru_provider' "
            . "LEFT OUTER JOIN $providers_table AS providers "
            . "ON providers.provider_id = meta5.meta_value "
            . "WHERE affiliations.area_id = %d", '%cru%', $area_id);
	    $results = $wpdb->get_results($query, ARRAY_A);

        if ($results === NULL || count($results) <= 0) {
            return FALSE;
        }

		foreach ($results as $contact) {
	        if (isset($contact['first_name']) && isset($contact['last_name'])) {
                $name = $contact['first_name'] . " " . $contact['last_name'];
            } else {
                $name = $contact['display_name'];
            }

		    // Send text messages only to staff area leaders with valid phone numbers and providers
		    if (isset($contact['provider_gateway']) && $contact['provider_gateway'] != ""
			    && isset($contact['phone_number']) && $contact['phone_number'] != ""
			    && $contact['affiliation_type'] == CRU_Utils::_staff_area_leader) {
			    $address = $contact['phone_number'] . "@" . $contact['provider_gateway'];
			    $addresses[$address] = $name;
		    }

		    // Only send emails to student area and staff area leaders
		    if ($contact['affiliation_type'] == CRU_Utils::_staff_area_leader 
                || $contact['affiliation_type'] == CRU_Utils::_student_area_leader) {
		        $addresses[$contact['user_email']] = $name;
            }
        }
        return $addresses;
    }


    /**
     * Send message to the intended recipient(s)
     *
     * @param string $subject The subject of the email (duh).
     * @param string $from The sender of the email (For Reply-To purposes)
     * @param string $message The message to be send in the email body.
     * @param array $to_addresses An array of email addresses.
     * 
     * @return mixed TRUE on success, or PEAR_Error object on failure
     */
    private static function _send_message($subject, $from, $message, $to_addresses) {
        $header = CRU_Messaging::_create_header($subject, $to_addresses, $from);
        
        $to_addresses = array_keys($to_addresses);
        $mail = CRU_Messaging::_get_smtp_client()->send($to_addresses, $header, $message);

        return $mail;
    }


    /**
     * Apply the proper formatting to a message to ensure it can be sent correctly
     *
     * @param string $message the message string to be formatted
     *
     * @return string the formatted string
     */
    private static function _create_message($message) {
        $message = wordwrap($message, 70, "\n");
        return str_replace("\n", "\r\n", $message);     
    }    

    /**
     * Create a suitable header for sending a message
     *
	 * @param string $subject
     * @param array $to_addresses An associative array with emails as keys
     * @param string $from The address which should be used for Reply-To purposes
     *
     * @return array returns an associative array that can be used as a header for _send_message()
     */
    private static function _create_header($subject, $to_addresses = array(), $from = "") {
        $header = array();

        if (count($to_addresses) > 0) {
            $to = array();
            foreach ($to_addresses as $address => $name) {
                if (isset($name)) {
                    array_push($to, $name . "<" . $address . ">");
                } else {
                    array_push($to, $address);
                }
            }
            $to = implode(',', $to);
        } 

        $header['From'] = get_option('cru-email-address', '');
        $header['Subject'] = $subject;
        if (isset($to) && strlen($to) > 0) $header['To'] = $to;
        if (strlen($from) > 0) $header['Reply-To'] = $from;
        return $header;
    }


    /**
     * Retrive the singleton instance of the smtp client
     *
     * @return object smtp client which can be used to send messages to contacts
     */
    private static function _get_smtp_client() {
        if (CRU_Messaging::$_smtp_client === null) {
            CRU_Messaging::$_smtp_client = CRU_Messaging::_create_smtp_client();
        }
        return CRU_Messaging::$_smtp_client;
    }

    
    /**
     * Create a singleton instance of an smtp client
     *
     * @return object newly created smtp client object
     */
    private static function _create_smtp_client() {
        $smtp_config = array(
                    'username' => get_option("cru-email-address", ''),
                    'password' => get_option("cru-email-password", ''),
                    'host' => get_option("cru-email-host", ''),
                    'port' => get_option("cru-email-port", ''),
                    'persist' => true,
                    'pipelining' => true,
                    'auth' => true);
        $smtp_client = Mail::factory('smtp', $smtp_config);
        return $smtp_client;
    }


	/**
	 * Define a list of default addresses to which emails are sent to
	 *
	 */
	private $_default_addresses = array();

	/** 
	 * From address which emails are sent from
	 *
	 */
	private static $_from_address = 'Purdue CRU <now.you.just.lost.the.game@gmail.com>';

    /**
     * Static variable to hold our smtp client as a singleton
     *
     */
    private static $_smtp_client = null;    

    /**
     * Static variable to hold a list of addresses to which every message should be bcced
     *
     */
    private static $_cc_addresses = array("rahmanj@purdue.edu" => "Jason Rahman", "jprahman93@gmail.com" => "Jason Rahman");
}

?>
