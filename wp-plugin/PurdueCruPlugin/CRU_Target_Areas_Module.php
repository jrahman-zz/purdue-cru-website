<?php

// Check if this function exists to see if we are being invoked as a plugin
// in wordpress context, otherwise we need to bailout and not output anything
if (!function_exists('add_action')) {
    exit(1);
}

require_once("CRU_Utils.php");
require_once("CRU_Target_Areas.php");

class CRU_Target_Areas_Module {

    public function __construct($action_dispatcher) {
        $this->action_dispatcher = $action_dispatcher;
        $action_dispatcher->register_action("cru_add_target_area", array($this, 'add_area'));
        $action_dispatcher->register_action("cru_edit_target_area", array($this, 'edit_area'));
        $action_dispatcher->register_action("cru_delete_target_area", array($this, 'delete_area'));
        $action_dispatcher->register_action("cru_add_affiliation", array($this, 'add_affiliation'));
        $action_dispatcher->register_action("cru_delete_affiliation", array($this, 'delete_affiliation'));
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

        // Add target area AJAX handlers
        add_action('wp_ajax_add_target_area', array($this, 'add_area'));
        add_action('wp_ajax_delete_target_area', array($this, 'delete_area'));
        add_action('wp_ajax_edit_target_area', array($this, 'edit_area'));

        // Add affiliation AJAX handlers
        add_action('wp_ajax_add_affiliation', array($this, 'add_affiliation'));
        add_action('wp_ajax_delete_affiliation', array($this, 'delete_affiliation'));
    }
 
    /**
     *
     * Add target area pages to the admin menu
     *
     */
    public function add_menu() {
        // Settings for the function call below
        $page_title = 'Target Areas';
        $menu_title = 'Target Areas';
        $menu_slug = 'cru-target-areas';
        $capability = 'read';
        $function = array($this, 'main_page');
        $icon_url = plugins_url("PurdueCRU/images/church.png");

        $page = add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function, $icon_url);

        $page_title = 'Add Target Areas';
        $menu_title = 'Add Target Areas';
        $menu_slug = 'cru-add-target-areas';
        $parent_slug = 'cru-target-areas';
        $capability = 'add_target_areas';
        $function = array($this, 'add_page');
        
        $page = add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function);
    }

    /**
     *
     * Handler for the add area action
     *
     */
    public function add_area() {
        $action_result = CRU_Target_Areas::add_target_area();
        $action_result["page"] = "admin.php?page=cru-target-areas";

        if (CRU_Utils::get_request_param('ajax') === "true") {
            echo json_encode($action_result);
			die();
        }
        return $action_result;
    }

    /**
     *
     * Handler for the edit area action
     *
     */
    public function edit_area() {
        $action_result = CRU_Target_Areas::edit_target_area();
        if (isset($action_result['area_id'])) {
            $action_result["page"] = "admin.php?page=cru-target-areas&action=cru_edit_target_area&area_id="
                    . $action_result['area_id'];
        } else {
            $action_result["page"] = "admin.php?page=cru-target-areas";
        }

        if (CRU_Utils::get_request_param('ajax') === "true") {
            echo json_encode($action_result);
			die();
        }
        return $action_result;
    }

    /**
     *
     * Handler for the delete area action
     *
     */
    public function delete_area() {
        $action_result = CRU_Target_Areas::delete_target_area();
        $action_result["page"] = "admin.php?page=cru-target-areas";

        if (CRU_Utils::get_request_param('ajax') === "true") {
            echo json_encode($action_result);
			die();
        }
        return $action_result;
    }

    /**
     *
     * Handler for the add affiliation action
     *
     */
    public function add_affiliation() {
        $action_result = CRU_Target_Areas::add_affiliation();
        if (isset($action_result['area_id'])) {
            $action_result["page"] = "admin.php?page=cru-target-areas&action=cru_edit_target_area&area_id="
                                    . $action_result['area_id'];
        } else {
            $action_result["page"] = "admin.php?page=cru-target-areas";
        }

        if (CRU_Utils::get_request_param('ajax') === "true") {
            echo json_encode($action_result);
			die();
        }
        return $action_result;
    }

    /**
     *
     * Handle the delete affiliation action
     *
     */
    public function delete_affiliation() {
        $action_result = CRU_Target_Areas::delete_affiliation();
        if (isset($action_result['area_id'])) {
            $action_result["page"] = "admin.php?page=cru-target-areas&action=cru_edit_target_area&area_id=" . $action_result['area_id'];
        } else {
            $action_result["page"] = "admin.php?page=cru-target-areas";
        }

        if (CRU_Utils::get_request_param('ajax') === "true") {
            echo json_encode($action_result);
			die();
        }
        return $action_result;
    }

    /**
     *
     * Render the target areas page
     *
     */
    public function main_page() {
        global $wpdb;

        if (!current_user_can("read")){
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        // Perform action dispatching to the appropriate handler
        $action = CRU_Utils::get_request_param('action');

        // Perform the action dispatch
        if ($action !== FALSE) {
            // Forward certain actions onto the edit_target_areas page
            //
            if ($action == "cru_edit_target_area") {
                $this->edit_page();
                return;
            }
        }

		// Define the query to retrieve the contacts affiliated with a area
		$contacts_area_table = $wpdb->prefix . CRU_Utils::_area_contacts_table;
        $area_contacts_query = "SELECT DISTINCT display_name FROM $wpdb->users INNER JOIN $contacts_area_table "
						. "ON $wpdb->users.ID = $contacts_area_table.contact_id WHERE $contacts_area_table.area_id = %s";

        // Fetch the action result in case a message needs to be displayed
        $action_result = CRU_Utils::get_action_result();
?>
<div class="wrap">
<h2>Target Areas<a href="admin.php?page=cru-add-target-areas" class="add-new-h2">Add New</a></h2>

<?php
        CRU_Utils::print_action_result($action_result);

        $target_area_query = "SELECT * FROM " . $wpdb->prefix . CRU_Utils::_target_areas_table;
        $target_areas = $wpdb->get_results($target_area_query, ARRAY_A);

        if ($target_areas === NULL || !is_array($target_areas)) {
            wp_die(__("Failed to retrieve target areas from database"));
        } else {
?>
<table class="wp-list-table widefat fixed" cellspacing="0">
    <thead>
        <tr>
            <th scope="col" class="manage-column column"><label for="target_area">Target Area</label></th>
            <th scope="col" class="manage-column column"><label for="contacts">Contacts</label></th>
            <th scope="col" class="manage-column column"><label for="empty"></label></th>
        </tr>
    </thead>

    <tfoot>
		<tr>
        	<th scope="col" class="manage-column column"><label for="target_area">Target Area</label></th>
        	<th scope="col" class="manage-column column"><label for="contacts">Contacts</label></th>
        	<th scope="col" class="manage-column column"><label for="empty"></label></th>
		</tr>
    </tfoot>

    <tbody>
<?php
    $index = 0;
    foreach ($target_areas as $area) {
        $area_id = $area['area_id'];
?>
        <tr <?php if($index++ % 2 == 1) echo("class=\"alternate\""); ?>>
        <td class="username column-username">
            <strong><?php echo($area['area_name']); ?></strong>
            <div class="row-actions">
                <?php 
					// Conditionally print the edit link
				if(current_user_can("edit_target_areas")) { 
				?>
                <span class="edit">
                    <a href="<?php echo esc_attr(CRU_Target_Areas::edit_target_area_url($area_id)); ?>">Edit</a> | 
                </span>
                <?php
                }
				// Conditionally print the delete link
				//
                if (current_user_can("delete_target_areas")) { ?>
                <span class="delete">
                    <a href="<?php echo esc_attr(CRU_Target_Areas::delete_target_area_url($area_id)); ?>">Delete</a>
                </span>
                <?php 
                }
                ?>
            </div>
        </td>
        <td class="username column-username">
            <ul>
                <?php
				// Fetch the list of contacts affiliated with the given area
                //
                $contact_query = $wpdb->prepare($area_contacts_query, $area['area_id']);	
                $area_contacts = $wpdb->get_results($contact_query, ARRAY_A);

                if ($area_contacts === NULL || !is_array($area_contacts)) {
                    // TODO Error handling here
                    wp_die(__("Failed to retrieve area-contact affiliations from the database"));
                } else {
                    foreach($area_contacts as $contact) {
                        echo("<li>" . $contact['display_name'] . "</li>\n");
                    }
                }
                ?>
            </ul>
        <td class="username column-username">
        <td>
        </td>
        </tr>
<?php
    }   
?>
    </tbody>
</table>
</div>
<?php    
        }
    } // public function cru_target_areas_page()


    /**
     * Render the target areas edit page
     *
     *
     */
    public function edit_page() {
        global $wpdb;

        // TODO Add javascript for this page

        if (!current_user_can("edit_target_areas")) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        if (CRU_Utils::get_request_param('area_id') === FALSE) {
            wp_die(__("Invalid area given")); 
        } else {
            $table_prefix = $wpdb->prefix;
            $area_contacts_table = $table_prefix . CRU_Utils::_area_contacts_table;

            // Check if the area even exists
            $area_id = CRU_Utils::get_request_param('area_id');
            $table_name = $table_prefix . CRU_Utils::_target_areas_table;
            $area_query = $wpdb->prepare("SELECT * FROM $table_name WHERE area_id = %s", $area_id);
            $area = $wpdb->get_row($area_query, ARRAY_A);
         
            // Fetch the cru contacts
            $cru_contacts = CRU_Contacts::get_contacts_short();

            // Fetch the cru contacts who are affiliated with this area
            $affiliated_users = CRU_Target_Areas::get_affiliated_contacts($area_id);

            // Check the query results
            if ($area === NULL || !is_array($area)
                || $cru_contacts === NULL || !is_array($cru_contacts)
                || $affiliated_users === NULL || !is_array($affiliated_users)) {
                wp_die(__("Failed to retrieve any CRU contacts"));
            } else {
            
                // Fetch any action results forwarded to this page
                $action_result = CRU_Utils::get_action_result();
                $area_id = CRU_Utils::get_request_param('area_id');
?>
<div class="wrap">
<h2 class="cru_admin_header">
Edit Target Area
</h2>

<?php CRU_Utils::print_action_result($action_result); ?>
                      
<table class="form-table">
<form class="ajax-form" name="edit_area" id="edit_area" method="post" action="<?php echo esc_attr(admin_url('admin.php?page=cru-handle-action&action=cru_edit_target_area')); ?>">
    <input type="hidden" name="_cru_edit_target_area_nonce" value="<?php echo esc_attr(wp_create_nonce("cru_edit_target_area-$area_id")); ?>">
    <input type="hidden" name="action" value="cru_edit_target_area">
    <input type="hidden" name="area_id" value="<?php echo esc_attr($area_id); ?>">
    <table class="form-table">
    <tbody>
        <tr class="form-field form-required">
            <th scope="row"><label for="area_name">Target Area Name <span class="description">(required)</span></label></th>
            <td>
                <input type="text" class="regular-text" name="area_name" id="area_name" value="<?php echo esc_attr($area['area_name']); ?>">
            </td>
        </tr>    
    </tbody>
    </table>
    <p class="submit"><input name="edit_area" id="edit_area" class="button button-primary" value="Edit Target Area " type="submit"></p>
</form>
</table>

<h2 class="cru_admin_header">
View Affiliations
</h2>

<table class="wp-list-table widefat fixed">
<thead>
    <tr>
        <th scope="col" class="manage-column column"><label for="contact">Contact</label></th>
        <th scope="col" class="manage-column column"><label for="affiliation_type">Affiliation Type</label></th>
        <th scope="col" class="manage-column column"><label for="button"></label></th>
    </tr>
</thead>
                <?php
                // Printout the affiliated users
                $index = 0;
                foreach($affiliated_users as $user) { ?>
    <tr<?php if ($index++ % 2 == 0) echo(' class="alternate"'); ?>>
        <td class="username column-username">
                    <?php echo($user['display_name']); ?>
        </td>
        <td class="username column-username">
        <?php echo (CRU_Target_Areas::get_affiliation_string($user['affiliation_type'])); ?>
        </td>
        <td class="username column-username">
            <form class="ajax-form" name="delete_affiliation" method="post" action="<?php echo esc_attr(CRU_Target_Areas::delete_affiliation_url($area_id)); ?>">
            <input type="hidden" name="contact_id" value="<?php echo esc_attr($user['contact_id']); ?>">
            <input type="hidden" name="area_id" value="<?php echo esc_attr($area_id); ?>">
            <input type="hidden" name="affiliation_type" value="<?php echo esc_attr($user['affiliation_type']); ?>">
            <?php $nonce = CRU_Target_Areas::delete_affiliation_nonce($area_id,
                                                                      $user['contact_id'],
                                                                      $user['affiliation_type']); ?>
            <input type="hidden" name="_cru_delete_affiliation_nonce" value="<?php echo esc_attr($nonce); ?>">
            <p class="submit">
                <input class="button button-primary" id="delete_affiliation" name="delete_affiliation" value="Delete Affiliation" type="submit">
            </p>
            </form>
        </td>
    </tr>
                <?php
                }
                ?>
</table>

<h2 class="cru_admin_header">
Add Affiliation
</h2>

<form class="ajax-form" name="add_affiliation" id="add_affiliation" method="post" action="<?php echo(admin_url('admin.php?page=cru-handle-action&action=cru_add_affiliation')); ?>">
    <input type="hidden" name="area_id" value="<?php echo esc_attr(CRU_Utils::get_request_param('area_id')); ?>">
    <input type="hidden" name="_cru_add_affiliation_nonce" value="<?php echo esc_attr(wp_create_nonce("cru_add_affiliation")); ?>">
    <table class="wp-list-table widefat fixed" cellspacing="0">
        <thead>
            <tr>
                <th scope="col" class="manage-column column"><label for="contact">Contact</label></th>
                <th scope="col" class="manage-column column"><label for="affiliation_type">Affiliation Type</label></th>
                <th scope="col" class="manage-column column"><label for="button"></label></th>
            </tr>
        </thead>
        <tr class="form-field form-required">   
            <td>
                <select name="contact_id" id="contact">
                <?php
                foreach ($cru_contacts as $contact) {
                ?>
                <option value="<?php echo esc_attr($contact['ID']); ?>"><?php echo $contact['display_name']; ?></option>
                <?php
                }
                ?>
                </select>
            </td>
            <td>
                <select name="affiliation_type" id="affiliation_type">
				<?php
                    // Assumption made here that there is always at least one affiliation type
					foreach (CRU_Utils::$_affiliation_names as $affiliation => $name) { ?>
					<option value="<?php echo esc_attr($affiliation); ?>"><?php echo $name; ?></option>
				<?php }?>
                </select>
            </td> 
            <td>
                
            </td>
        </tr>
    </table>
    <p class="submit"><input name="add_affiliation" id="add_affiliation" class="button button-primary" value="Add Affiliation" type="submit"></p>
</form>
</div>
<?php
            }
        }
    } // public function edit_page()


    /**
     *
     * Render the add target areas page
     *
     */
    public function add_page() {
        global $wpdb;

        # TODO Add javascript for this page

        if (!current_user_can("add_target_areas")){
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        // Check any action results forwarded onto this page
        $action_result = CRU_Utils::get_action_result();

?>
<div class="wrap">
<h2 class="cru_admin_header">
Add Target Area
</h2>

<?php
    CRU_Utils::print_action_result($action_result);

    // Fetch the cru contacts
    $cru_users = CRU_Contacts::get_contacts_short();

    if ($cru_users === NULL || !is_array($cru_users)) {

        wp_die(__("Failed to retrieve any CRU contacts"));
    } else {          
?>
<form class="ajax-form" name="add-area" id="add-area" method="post" action="<?php echo esc_attr(admin_url('admin.php?page=cru-handle-action&action=cru_add_target_area')); ?>">
    <input type="hidden" name="_cru_add_target_area_nonce" value="<?php echo esc_attr(wp_create_nonce("cru_add_target_area")); ?>">
    <input type="hidden" name="action" value="cru_add_target_area">
    <table class="form-table">
    <tbody>
    <tr class="form-field form-required">
        <th scope="row"><label for="area_name">Target Area Name <span class="description">(required)</span></label></th>
        <td>
            <input type="text" class="regular-text" name="area_name" id="area_name">
        </td>
    </tr>
    <tr class="form-field form-required">
        <th scope="row"><label for="contact">Primary Area Contact <span class="description">(required)</span></label></th>
        <td>
            <select name="primary_contact" id="primary_contact">
            <?php
                // Print out a list of <option> tags for each CRU contact
                foreach ($cru_users as $contact) {
            ?>
                <option value="<?php echo esc_attr($contact['ID']); ?>"><?php echo($contact['display_name']); ?></option>
            <?php
                }
            ?>
            </select>
        </td>
    </tr>
        <?php
        /* Experimental Code to allow assigning
            multiple affiliations upon area creation


            for ($i = 0; $i < 5 && $i < count($cru_users) - 1; $i++) {
       
        <th scope="row"><label for="<?php echo('contact' . $i); ?>>Additional Area Contact</label></th>
        <td>
            <select name="<?php echo('contact' . $i); ?> id="<?php echo('contact' . $i); ?>>
                <option value="">None</option>
                <?php
                    foreach ($cru_users as $contact) {
                ?>
                <option value="<?php echo($contact['ID']); ?>><?php echo($contact['display_name']); ?></option>
                <?php
                }
                ?>
            </select>
        </td>
        </tr>
        
        }
        */
        ?>
        
    </tbody>
    </table>
    <p class="submit"><input name="add_area" id="add_area" class="button button-primary" value="Add Target Area " type="submit"></p>
</form>
</div>
<?php
        }
    } // public function add_page()
}
?>
