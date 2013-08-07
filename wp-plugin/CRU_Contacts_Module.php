<?php

// Check if this function exists to see if we are being invoked as a plugin
// in wordpress context, otherwise we need to bailout and not output anything
if (!function_exists('add_action')) {
    exit(1);
}

require_once("CRU_Utils.php");
require_once("CRU_Contacts.php");
require_once("CRU_Messaging.php");

class CRU_Contacts_Module {

    public function __construct($action_dispatcher) {
        $this->action_dispatcher = $action_dispatcher;
        $action_dispatcher->register_action("cru_send_test_text", array($this, 'send_text'));
        $action_dispatcher->register_action("cru_edit_contact", array($this, 'edit_contact'));
    }

    /**
     *
     * Action dispatcher used for this modules action handling
     * 
     */
    public $action_dispatcher;

    /**
     *
     * Register the module with Wordpress
     *
     */
    public function register_module() {
        add_action('admin_menu', array($this, 'add_menu'));

        // Add contact AJAX handlers
        add_action('wp_ajax_edit_contact', array($this, 'cru_edit_contact'));
    }

    /**
     *
     * Add menu to Wordpress
     *
     */
    public function add_menu() {
        // Settings for the function call below
        $page_title = 'CRU Contacts';
        $menu_title = 'CRU Contacts';
        $menu_slug = 'cru-contacts';
        $capability = 'edit_cru_contacts';
        $function = array($this, 'edit_page' );
        $icon_url = plugins_url("PurdueCRU/images/contact.png");

        $page = add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function, $icon_url);
    }

    /**
     *
     * Handler for send test text action
     *
     */
    public function send_text() {
        $action_result = CRU_Messaging::send_test_text();
        $action_result["page"] = "admin.php?page=cru-contacts";

        if (CRU_Utils::get_request_param('ajax') === "true") {
            echo json_encode($action_result);
			die();
        }
        return $action_result;
    }
    
    /**
     *
     * Handler for the edit contact action
     *
     */
    public function edit_contact() {
        $action_result = CRU_Contacts::edit_contact();
        $action_result["page"] = "admin.php?page=cru-contacts";
        if (CRU_Utils::get_request_param('ajax') === "true") {
            $echo json_encode($action_result);
			die();
        }
        return $action_result;
    }

    /**
     *
     * Render the edit contacts page
     *
     */
    public function edit_page() {
        global $wpdb;

        // Check for proper access rights
        if (!current_user_can('edit_cru_contacts')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        $action_result = CRU_Utils::get_action_result();

    ?>
<div class="wrap">
<h2 class="cru_admin_header">
CRU Contacts
</h2>

    <?php
        CRU_Utils::print_action_result($action_result);

        // Get all the contacts and providers
        $providers_table = $wpdb->prefix . CRU_Utils::_provider_table;
        $contacts = CRU_Contacts::get_contacts_full();

        $provider_query = "SELECT * FROM $providers_table";
        $providers = $wpdb->get_results($provider_query, ARRAY_A);

        $target_areas_table = $wpdb->prefix . CRU_Utils::_target_areas_table;
        $affiliations_table = $wpdb->prefix . CRU_Utils::_area_contacts_table;

		// Define query to fetch the area affiliations of a user, pass this to wpdb->prepare with the contact_id 
        $affiliations_query = "SELECT DISTINCT $target_areas_table.area_name FROM $affiliations_table "
                            . "INNER JOIN $target_areas_table ON $affiliations_table.area_id = $target_areas_table.area_id "
                            . "WHERE $affiliations_table.contact_id = '%s' ORDER BY area_name ASC";

        if ($contacts === NULL || !is_array($contacts)) {
            wp_die(__("Failed to retrieve contacts from database"));
        } else {
?>

<table class="wp-list-table widefat fixed">
<thead>
    <tr>
        <th><label for="contact_name">Name</label></th>
        <th><label for="contact_email">Email</label></th>
        <th><label for="areas">Areas</labels></th>
        <th><label for="contact_provider">Provider</th>
        <th><label for="phone_number">Phone Number</label></th>
        <th><label for="test"></label></th>
        <th><label for="update"></label>
    </tr>
</thead>
<tbody>
            <?php
        $index = 0;
            foreach ($contacts as $contact) {
                $contact_id = $contact['ID'];
            ?>
    <tr <?php if($index++ % 2 == 1) echo(" class=\"alternate\""); ?>>
        <form class="ajax-form" method="post" action=<?php echo(admin_url("admin.php?page=cru-handle-action")); ?>>
        <input type="hidden" name="contact_id" value=<?php echo($contact['ID']); ?>>
        <input type="hidden" name="_cru_edit_contact_nonce" value=<?php echo(wp_create_nonce("cru_edit_contact-$contact_id")); ?>>
        <input type="hidden" name="action" value="cru_edit_contact">
        <td>
            <?php echo($contact['display_name']); ?>
        </td>
        <td>
            <?php echo($contact['user_email']); ?>
        </td>      
        <td>
            <ul class="contact_area">
            <?php
                // Fetch the list of areas this contact is affiliated with         
                $query = $wpdb->prepare($affiliations_query, $contact_id);
                $areas = $wpdb->get_results($query, ARRAY_A);

                // Print the areas
                if ($areas !== NULL && is_array($areas)) {
                    foreach ($areas as $area) {
                ?>
                <li>
                    <?php echo($area['area_name']); ?>
                </li>
                <?php
                    }
                }
            ?>
            </ul>
        </td>
        <td>
            <select name="provider_id">
                <?php
                // Print a list of <option> tags for each provider
                foreach ($providers as $provider) {
                ?>
                <option value="<?php echo($provider['provider_id']); ?>" <?php if ($contact['provider_id'] == $provider['provider_id']) echo('selected="true"'); ?>>
                <?php echo($provider['provider_name']); ?>
                </option>
                <?php             
                }
                ?>
            </select>
        </td>
        <td>
            <?php
                $matches = array();
                $ac = $ec = $num = "";

                // Check that the phone number is plausably correct and split it
                if (preg_match("/^([2-9][0-9]{2})([2-9][0-9]{2})([0-9]{4})$/", $contact['phone_number'], $matches)) {
                    $ac = $matches[1];
                    $ec = $matches[2];
                    $num = $matches[3];
               } 
            ?>
            <input type="text" size="3" name="area_code" value="<?php echo($ac); ?>">-<input type="text" size="3" name="exchange_code" value="<?php echo($ec); ?>">-<input type="text" size="4" name="number" value="<?php echo($num); ?>">
        </td>
        <td>
            <p class="submit">
            
                <a class="cru_button_link" href="<?php 
                $url = "/admin.php?page=cru-handle-action&action=cru_send_test_text&contact_id="
                      . urlencode($contact_id) . "&_cru_send_test_text_nonce="
                      . urlencode(wp_create_nonce("cru_send_test_text-$contact_id")); 
                echo(admin_url($url)); ?>">
                    <input type="button" id="test_button" class="button-primary" value="Test">
                </a>
            </p>
        </td>
        <td>
            <p class="submit">
                <input class="button button-primary" id="update_contact" name="update_contact" value="Update Contact" type="submit">
            </p>
        </td>
        </form>
    </tr>
            <?php
            }
            ?>
</tbody>
</table>
</div>
<?php        
        } // if ($contacts === NULL || !is_array($contacts)) else
    } // public function edit_page()

}
?>
