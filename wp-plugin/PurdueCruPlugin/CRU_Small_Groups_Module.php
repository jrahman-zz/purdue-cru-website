<?php

// Check if this function exists to see if we are being invoked as a plugin
// in wordpress context, otherwise we need to bailout and not output anything
if (!function_exists('add_action')) {
    exit(1);
}

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
require_once("CRU_Small_Groups.php");

class CRU_Small_Groups_Module {

    public function __construct($action_dispatcher) {
        $this->action_dispatcher = $action_dispatcher;
        $action_dispatcher->register_action("cru_add_small_group", array($this, 'add_group'));
        $action_dispatcher->register_action("cru_edit_small_group", array($this, 'edit_group'));
        $action_dispatcher->register_action("cru_delete_small_group", array($this, 'delete_group'));
    }

    /**
     * Action dispatcher used for this modules action handling
     */
    public $action_dispatcher;

    /**
     * Register the module with Wordpress
     */
    public function register_module() {
        add_action('admin_menu', array($this, 'add_menu'));

        // Add small group AJAX handlers
        add_action('wp_ajax_add_small_group', array($this, 'cru_add_small_group'));
        add_action('wp_ajax_delete_small_group', array($this, 'cru_delete_small_group'));
        add_action('wp_ajax_edit_small_group', array($this, 'cru_edit_small_group'));
    }

    /**
     * Install the module by updating the database
     */
    public function install_module() {
        global $wpdb;
        global $cru_db_version;
        $small_groups_table = $wpdb->prefix . CRU_Utils::_small_groups_table;
        $sql = "CREATE TABLE $small_groups_table (
        group_id mediumint(9) NOT NULL AUTO_INCREMENT,
        area_id mediumint(9) NOT NULL,
        contact_id bigint(20) unsigned NOT NULL,
        day char(10) NOT NULL,
        time char(10) NOT NULL,
        location varchar(512) NOT NULL,
        men tinyint(2) NOT NULL,
        women tinyint(2) NOT NULL,
        PRIMARY KEY  (group_id)," .
        /*FOREIGN KEY  (area_id) REFERENCES $target_areas_table(area_id),
        FOREIGN KEY  (contact_id) REFERENCES $wpdb->users(ID)*/
        ");";

        dbDelta($sql);
    }

    /**
     * Add the small groups page to the admin menu
     */
    public function add_menu() {
        $page_title = 'Small Groups';
        $menu_title = 'Small Groups';
        $menu_slug = 'cru-small-groups';
        $capability = 'read';
        $function = array($this, 'main_page');
        $icon_url = plugins_url(PURDUE_CRU_PLUGIN_NAME . "/images/bible.png");

        $page = add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function, $icon_url);

        $page_title = 'Add Small Groups';
        $menu_title = 'Add Small Groups';
        $menu_slug = 'cru-add-small-groups';
        $parent_slug = 'cru-small-groups';
        $capability = 'add_small_groups';
        $function = array($this, 'add_page');
        
        $page = add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function);
    }

    /**
     *
     * Handler for the add group action
     *
     */
    public function add_group() {
        $action_result = CRU_Small_Groups::add_small_group();
        if (isset($action_result['success']) && $action_result['success'] === FALSE) {
            $action_result["page"] = "admin.php?page=cru-add-small-groups";
        } else {
            $action_result["page"] = "admin.php?page=cru-small-groups";
        }

        if (CRU_Utils::get_request_param('ajax') === "true") {
            echo json_encode($action_result);
			die();
        }
        return $action_result;
    }
    
    /**
     *
     * Handler for the edit group action
     *
     */
    public function edit_group() {
        $action_result = CRU_Small_Groups::edit_small_group();
        if (isset($action_result['group_id'])) {
            $action_result["page"] = "admin.php?page=cru-small-groups&action=cru_edit_small_group&group_id="
                                    . $action_result['group_id'];
        } else {
            $action_result["page"] = "admin.php?page=cru-small-groups";
        }

        if (CRU_Utils::get_request_param('ajax') === "true") {
            echo json_encode($action_result);
			die();
        }
        return $action_result;
    }

    /**
     *
     * Handler for the delete group action
     *
     */
    public function delete_group() {
        $action_result = CRU_Small_Groups::delete_small_group();
        $action_result["page"] = "admin.php?page=cru-small-groups";

        if (CRU_Utils::get_request_param('ajax') === "true") {
            echo json_encode($action_result);
			die();
        }
        return $action_result;
    }

    /**
     *
     * Render the small groups page
     *
     */  
    public function main_page() {
        global $wpdb;

        if (!current_user_can('read')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        // Get any results of actions that were forwarded onto this page
        $action_result = CRU_Utils::get_action_result();

        $action = CRU_Utils::get_request_param('action');
        if ($action !== FALSE) {
            if ($action == "cru_edit_small_group") {
                $this->edit_page();
                return;
            }
        }
        
?>
<div class="wrap">
<h2>Small Groups<a href="admin.php?page=cru-add-small-groups" class="add-new-h2">Add New</a></h2>

<div id="messsage" <?php if($action_result !== FALSE) if($action_result['success']) echo('class="updated"'); else echo('class="error"'); ?>>
<p>
<?php
    if($action_result !== FALSE) echo(urldecode($action_result['message']));
?>
</p>
</div>
<?php

        // Run queries to gather the groups
        $small_groups = CRU_Small_Groups::get_small_groups();

        if ($small_groups === NULL || !is_array($small_groups)) {
            wp_die(__('Failed to get small groups from database'));
        } else {
?>
<table class="wp-list-table widefat fixed" cellspacing="0">
    <thead>
        <tr>
            <th scope="col" class="manage-column column"><span>Group Leader</span></th>
            <th scope="col" class="manage-column column"><span>Time</span></th>
            <th scope="col" class="manage-column column"><span>Day</span></th>
            <th scope="col" class="manage-column column"><span>Location</span></th>
            <th scope="col" class="manage-column column"><span>Men</span></th>
            <th scope="col" class="manage-column column"><span>Women</span></th>
            <th scope="col" class="manage-column column"><span>Area</span></th>
        </tr>
    </thead>

    <tfoot>
		<tr>
		    <th scope="col" class="manage-column column"><span>Group Leader</span></th>
		    <th scope="col" class="manage-column column"><span>Time</span></th>
		    <th scope="col" class="manage-column column"><span>Day</span></th>
		    <th scope="col" class="manage-column column"><span>Location</span></th>
		    <th scope="col" class="manage-column column"><span>Men</span></th>
		    <th scope="col" class="manage-column column"><span>Women</span></th>
		    <th scope="col" class="manage-column column"><span>Area</span></th>
		</tr>
    </tfoot>

    <tbody>
            <?php
            $index = 0;
            // Print out each group with alternating styles
            foreach ($small_groups as $group) { ?>
        <tr<?php if($index++ % 2 == 1) echo(" class=\"alternate\""); ?>>
        <td class="username column-username">
        <strong><?php echo($group['display_name']); ?></strong>
            <div class="row-actions">
                <?php if(current_user_can("edit_small_groups")) { ?>
            <span class="edit">
                <a href="<?php echo esc_attr(CRU_Small_Groups::edit_group_url($group['group_id'])); ?>">Edit</a> | </span>
                <?php } ?>
                <?php if (current_user_can("delete_small_groups")) { ?>
            <span class="delete">
                <a href="<?php echo esc_attr(CRU_Small_Groups::delete_group_url($group['group_id'])); ?>">Delete</a>
            </span>
                <?php } ?>
            </div>
        </td>
        <td><strong><?php echo($group['time']); ?></strong></td>
        <td><strong><?php echo($group['day']); ?></strong></td>
        <td><strong><?php echo($group['location']); ?></strong></td>
        <td><strong><?php echo($group['men'] == 1 ? "Yes" : "No"); ?></strong>
        </td>
        <td><strong><?php echo($group['women'] == 1 ? "Yes" : "No"); ?></strong>
        </td>
        <td><strong><?php echo($group['area_name']); ?></strong>
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
    } // public function main_page()

    
    /**
     *
     * Render the add small groups page
     *
     */
    public function add_page() {
        global $wpdb;

        if (!current_user_can('add_small_groups')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        // Fetch any action result forwarded to us
        $action_result = CRU_Utils::get_action_result();      
?>
<div class="wrap">
<h2 class="cru_admin_header">
Add Small Group
</h2>

<?php
        CRU_Utils::print_action_result($action_result);

        // Retrieve the target areas
        $target_area_query = "SELECT * FROM " . $wpdb->prefix . CRU_Utils::_target_areas_table . " ORDER BY area_name ASC";
        $target_areas = $wpdb->get_results($target_area_query, ARRAY_A);       

        // Collect the cru students and staff who lead groups
        $leaders = CRU_Contacts::get_contacts_short();

        if ($target_areas === NULL
            || !is_array($target_areas)
            || $leaders === NULL
            || !is_array($leaders)) {
            wp_die(__('Failed to retrieve information from the database'));
        } else {
?>
<form class="ajax-form" method="post" name="add-group" id="add-group" action="<?php echo(admin_url('admin.php?page=cru-handle-action')); ?>">
    <input type="hidden" name="_cru_add_small_group_nonce" value="<?php echo(wp_create_nonce('cru_add_small_group')); ?>">
    <input type="hidden" name="action" value="cru_add_small_group">
    <table class="form-table">
    <tbody>
    <tr class="form-field form-required">
        <th scope="row"><label for="leader">Leader <span class="description">(required)</span></label></th>
        <td>
            <select name="leader">
                <?php
                // Print out an <option> for each leader
                foreach ($leaders as $leader) {
                ?>
                    <option value="<?php echo esc_attr($leader['ID']);?>"><?php echo($leader['display_name']); ?></option>
                <?php
                    }
                ?>
            </select name="leader">
        </td>
    </tr>
    <tr class="form-field form-required">
        <th scope="row"><label for="day">Day <span class="description">(required)</span></label></th>  
        <td>
            <select name="day" id="day">
                <option selected="true">Monday</option>
                <option>Tuesday</option>
                <option>Wednesday</option>
                <option>Thursday</option>
                <option>Friday</option>
                <option>Saturday</option>
                <option>Sunday</option>
            </select>
        </td>
    </tr>
    <tr class="form-field form-required">
        <th scope="row"><label for="time">Time <span class="description">(required)</span></label></th>
        <td>
            <select name="time" id="time"> 
				<option value="TBD">TBD</option> 
                <?php

                // Print out the time options from 8:00AM to 11:30AM
                for ($i = 8; $i <= 11; $i++) {
                    for ($j = 0; $j < 2; $j++) {
						$time = sprintf("%d:%002dAM", $i, $j * 30);
                ?>                     
                <option value="<?php echo $time?>"><?php echo $time; ?></option>
                <?php              
                    }
                }
                ?>
            
                <option value="12:00PM">12:00PM</option> 
                <option value="12:30PM">12:30PM</option>           
        
                <?php
                // Print out the time options from 1:00PM to 10:00PM
                for ($i = 1; $i <= 10; $i++) {
                    for ($j = 0; $j < 2; $j++) {
						$time = sprintf("%d:%002dPM", $i, $j * 30);
                ?>                     
                <option value="<?php echo $time?>"><?php echo $time; ?></option>
                <?php              
                    }
                }
                ?>
            <select>
        </td>
    </tr>
    <tr class="form-field form-required">
        <th scope="row"><label for="men">Men's Group</label></th>
        <td>
            <input type="checkbox" name="men" id="men" value="true">
        </td>
    </tr>
    <tr class="form-field form-required">
        <th scope="row"><label for="women">Women's Group</label></th>
        <td>
            <input type="checkbox" name="women" id="women" value="true">
        </td>
    </tr>
    <tr class="form-field form-required">
        <th scope="row"><label for="area">Target Area <span class="description">(required)</span></label></th>
        <td>
        <select name="area_id">
            <?php
                // Print out the target areas the group could be a part of
                foreach ($target_areas as $target_area) { ?>
            <option value="<?php echo esc_attr($target_area['area_id']); ?>"><?php echo($target_area['area_name']); ?></option>
            <?php
                }
            ?>
        </select>
        </td>
    </tr>
    <tr class="form-field form-required">
        <th scope="row"><label for="location">Location: <span class="description">(required)</span></label></th>
        <td>
        <textarea name="location" id="location" aria-required="true" rows="4" cols="50"></textarea>
        </td>
    </tr>
    </tbody>
    </table>
    <p class="submit"><input name="add_group" id="add_group" class="button button-primary" value="Add New Small Group " type="submit"></p>
</form>
</div>
<?php                
        }
    } // public function add_page()


    /**
     * Render the small groups edit page
     *
     */
    public function edit_page() {
        global $wpdb;

        if (!current_user_can('edit_small_groups')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        // Fetch any action results that were forwarded
        $action_result = CRU_Utils::get_action_result();

?>
<div class="wrap">
<h2 class="cru_admin_header">
Edit Small Group
</h2>

<?php
        CRU_Utils::print_action_result($action_result);

        if (CRU_Utils::get_request_param('group_id') === FALSE) {         
            wp_die(__("No group ID given"));
        } else {
        
            $small_groups_table = $wpdb->prefix . CRU_Utils::_small_groups_table;
            $target_areas_table = $wpdb->prefix . CRU_Utils::_target_areas_table;
            $area_contacts_table = $wpdb->prefix . CRU_Utils::_area_contacts_table;

            $group_query = $wpdb->prepare("SELECT * FROM $small_groups_table "
                                    . "INNER JOIN $target_areas_table ON $small_groups_table.area_id ="
                                    . "$target_areas_table.area_id WHERE group_id = %s",
                                    CRU_Utils::get_request_param('group_id'));
            $group = $wpdb->get_row($group_query, ARRAY_A);

            $target_area_query = "SELECT * FROM " . $target_areas_table;
            $target_areas = $wpdb->get_results($target_area_query, ARRAY_A);

            // Collect the cru students and staff who lead groups
            $leaders = CRU_Contacts::get_contacts_short();

            if ($group === NULL || !is_array($group) || $target_areas === NULL 
               || !is_array($target_areas) || $leaders === NULL || !is_array($leaders)) {
               wp_die(__('Invalid group_id'));
            } else {
?>   
<form class="ajax-form" method="post" name="edit-group" id="edit-group" action="<?php echo(admin_url('admin.php?page=cru-handle-action')); ?>">
    <input type="hidden" name="_cru_edit_small_group_nonce" value="<?php echo(wp_create_nonce("cru_edit_small_group-" . CRU_Utils::get_request_param('group_id') )); ?>">
    <input type="hidden" name="action" value="cru_edit_small_group">
    <input type="hidden" name="group_id" value="<?php echo esc_attr(CRU_Utils::get_request_param('group_id')); ?>">
    <table class="form-table">
    <tbody>
    <tr class="form-field form-required">
        <th scope="row"><label for="leader">Leader <span class="description">(required)</span></label></th>
        <td>
            <select name="leader">
                <?php
                foreach ($leaders as $leader) {
                ?>
                    <option value="<?php echo esc_attr($leader['ID']);?>" <?php if ($leader['ID'] === $group['contact_id']) echo('selected="true"'); ?>><?php echo($leader['display_name']); ?></option>
                <?php
                    }
                ?>
            </select>
        </td>
    </tr>
    <tr class="form-field form-required">
        <th scope="row"><label for="day">Day <span class="description">(required)</span></label></th>  
        <td>
            <select name="day">
            <option<?php if ($group['day'] == 'Monday') echo(' selected="true"'); ?>>Monday</option>
            <option<?php if ($group['day'] == 'Tuesday') echo(' selected="true"'); ?>>Tuesday</option>
            <option<?php if ($group['day'] == 'Wednesday') echo(' selected="true"'); ?>>Wednesday</option>
            <option<?php if ($group['day'] == 'Thursday') echo(' selected="true"'); ?>>Thursday</option>
            <option<?php if ($group['day'] == 'Friday') echo(' selected="true"'); ?>>Friday</option>
            <option<?php if ($group['day'] == 'Saturday') echo(' selected="true"'); ?>>Saturday</option>
            <option<?php if ($group['day'] == 'Sunday') echo(' selected="true"'); ?>>Sunday</option>
            </select> 
        </td>
    </tr>
    <tr class="form-field form-required">
        <th scope="row"><label for="time">Time <span class="description">(required)</span></label></th>
        <td>
            <select name="time" id="time">
				<option <?php if ($group['time'] == "TBD") echo ('selected="true"'); ?> value="TBD">TBD</option> 
                <?php

                // Print out the time options from 8:00AM to 11:30AM
                for ($i = 8; $i <= 11; $i++) {
                    for ($j = 0; $j < 2; $j++) {
						$time = sprintf("%d:%002dAM", $i, $j * 30);
                ?>                     
                <option <?php if($group['time'] == $time) echo('selected="true"'); ?> value="<?php echo $time?>"><?php echo $time; ?></option>
                <?php              
                    }
                }
                ?>
            
                <option<?php if ($group['time'] == "12:00PM") echo(' selected="true"'); ?>>12:00PM</option> 
                <option<?php if ($group['time'] == "12:30PM") echo(' selected="true"'); ?>>12:30PM</option>           
        
                <?php
                // Print out the time options from 1:00PM to 10:00PM
                for ($i = 1; $i <= 10; $i++) {
                    for ($j = 0; $j < 2; $j++) {
						$time = sprintf("%d:%002dPM", $i, $j * 30);
                ?>                     
                <option<?php if($group['time'] == $time) echo(' selected="true"'); ?> value="<?php echo $time?>"><?php echo $time; ?></option>
                <?php              
                    }
                }
                ?>
			</select>
        </td>
    </tr>
    <tr class="form-field form-required">
        <th scope="row"><label for="men">Men's Group</label></th>
        <td>
        <input type="checkbox" name="men" id="men" value="true" <?php if ($group['men'] != 0) echo('checked="true"'); ?>>
        </td>
    </tr>
    <tr class="form-field form-required">
        <th scope="row"><label for="women">Women's Group</label></th>
        <td>
        <input type="checkbox" name="women" id="women" value="true" <?php if ($group['women'] != 0) echo('checked="true"'); ?>>
        </td>
    </tr>
    <tr class="form-field form-required">
        <th scope="row"><label for="area">Target Area <span class="description">(required)</span></label></th>
        <td>
        <select name="area_id">
            <?php
                // Print out the target areas the group could be a part of
                foreach ($target_areas as $target_area) {
                    if ($target_area['area_id'] === $group['area_id']) {
                        echo('<option value="' . esc_attr($target_area['area_id']) . "\" checked=\"true\">" . $target_area['area_name'] . '</option>');
                    } else {
                        echo('<option value="' . esc_attr($target_area['area_id']) . '">' . $target_area['area_name'] . '</option>');
                    }
                }
            ?>
        </select>
        </td>
    </tr>
    <tr class="form-field form-required">
        <th scope="row"><label for="location">Location <span class="description">(required)</span></label></th>
        <td>
        <textarea name="location" id="location" aria-required="true" rows="4" cols="50"><?php echo($group['location']); ?></textarea>
        </td>
    </tr>
    </tbody>
    </table>
    <p class="submit"><input name="edit_small_group" id="edit_group" class="button button-primary" value="Edit Small Group " type="submit"></p>
</form>
</div>
<?php                
            }        
        }
    } // public function edit_page()
}
?>
