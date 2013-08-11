<?php

// Check if this function exists to see if we are being invoked as a plugin
// in wordpress context, otherwise we need to bailout and not output anything
if (!function_exists('add_action')) {
    exit(1);
}

require_once("CRU_Utils.php");

global $cru_db_version;
$cru_db_version = "1.0";


/**
 * Primary class for the Purdue CRU Website Plugin
 *
 * @author Jason P Rahman (jprahman93@gmail.com, rahmanj@purdue.edu)
 */
class CRU_Plugin {

    /**
     *
     * Perform initialization such as adding action hooks
     *
     */
    public function __construct($modules) {
        $this->_capability = 'edit_user';
        $this->modules = $modules;

        // Enqueue scripts and style sheets
        add_action('admin_print_styles', array($this, 'cru_add_admin_styles'));

        // Register (de)activation hooks
        register_activation_hook("bootstrap.php", array($this, 'cru_install_plugin'));
        register_deactivation_hook("bootstrap.php", array($this, 'cru_uninstall_plugin'));
    } // public function __construct()

    /**
     *
     * Variable to store modules in
     *
     */
    public $modules;

    /**
     *
     * Perform one time initialization for the plugin
     *
     */
    public function cru_install_plugin() {

        add_role("cru_staff", "CRU Staff");
        add_role("cru_student", "CRU Student");

        // Init each role with the correct CRU permissions
        $role = get_role("cru_staff");
        if ($role != NULL) {
            $role->add_cap("add_target_areas");
            $role->add_cap("edit_target_areas");
            $role->add_cap("delete_target_areas");
            $role->add_cap("add_small_groups");
            $role->add_cap("edit_small_groups");
            $role->add_cap("delete_small_groups");
            $role->add_cap("edit_cru_contacts");
            $role->add_cap("edit_cru_options");
            $role->add_cap("cru_admin");
            $role->add_cap("read");
        }
        
        $role = get_role("cru_student");
        if ($role != NULL) {
            $role->add_cap("add_small_groups");
            $role->add_cap("edit_small_groups");
            $role->add_cap("delete_small_groups");
            $role->add_cap("edit_cru_contacts");
            $role->add_cap("read");
        }

        $role = get_role("administrator");
        if ($role != NULL) {
            $role->add_cap("add_target_areas");
            $role->add_cap("edit_target_areas");
            $role->add_cap("delete_target_areas");
            $role->add_cap("add_small_groups");
            $role->add_cap("edit_small_groups");
            $role->add_cap("delete_small_groups");
            $role->add_cap("edit_cru_contacts");
            $role->add_cap("edit_cru_options");
            $role->add_cap("cru_admin");
        }

        // Install each module if possible
        if (is_array($this->modules)) {
            foreach ($this->modules as $module) {
                if (method_exists($module, 'install_module')) {
                    $module->install_module();
                }
            }
        }

        // Install or update the database
        $this->cru_install_database();

    } // public function cru_install_plugin()

    /**
     *
     * Run init to add the modules
     *
     */
    public function init_plugin() {
        // Add admin menu pages
        if (is_array($this->modules)) {
            foreach ($this->modules as $module) {
                if (method_exists($module, 'register_module')) {
                    $module->register_module();
                }
            }
        }
    }


    /**
     *
     * Run a query through dbDelta to update/create the database
     *
     */
    public function cru_install_database() {
        global $wpdb;
        global $cru_db_version;

        $target_areas_table = $wpdb->prefix . CRU_Utils::_target_areas_table;
        $small_groups_table = $wpdb->prefix . CRU_Utils::_small_groups_table;
        $area_contacts_table = $wpdb->prefix . CRU_Utils::_area_contacts_table;
        $providers_table = $wpdb->prefix . CRU_Utils::_provider_table;
        
        // Define SQL to create database tables
        // Presently dbDelta throws a hissy fit over the foreign keys
        $sql = "CREATE TABLE $target_areas_table (
        area_id mediumint(9) NOT NULL AUTO_INCREMENT,
        area_name char(64) NOT NULL,
        PRIMARY KEY  (area_id)
        );
        CREATE TABLE $small_groups_table (
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
        ");
        CREATE TABLE $area_contacts_table (
        area_id mediumint(9) NOT NULL,
        contact_id bigint(20) unsigned NOT NULL,
        affiliation_type tinyint(3) NOT NULL," .
        /*FOREIGN KEY  (area_id) REFERENCES $target_area_table(area_id),
        FOREIGN KEY  (contact_id) REFERENCES $wpdb->users(ID),*/
        "PRIMARY KEY  (area_id,contact_id,affiliation_type)
        );
        CREATE TABLE $providers_table (
        provider_id tinyint(5) NOT NULL AUTO_INCREMENT,
        provider_name char(50) NOT NULL,
        provider_gateway char(125) NOT NULL,
        PRIMARY KEY  (provider_id)
        );";
            
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        dbDelta($sql);

        // Populate the providers table with a pre-selected list of providers
        $wpdb->insert($providers_table,
            array('provider_id' => 1, 'provider_name' => "AT&T", 'provider_gateway' => 'txt.att.net'));
        $wpdb->insert($providers_table,
            array('provider_id' => 2, 'provider_name' => "Verizon", 'provider_gateway' => 'vtext.com'));
        $wpdb->insert($providers_table,
            array('provider_id' => 3, 'provider_name' => "Sprint", 'provider_gateway' => "messaging.sprintpcs.com"));
        $wpdb->insert($providers_table,
            array('provider_id' => 4, 'provider_name' => "T-Mobile", 'provider_gateway' => "tmomail.net"));
        $wpdb->insert($providers_table,
            array('provider_id' => 5, 'provider_name' => "Straight Talk", 'provider_gateway' => "vtext.com"));
        $wpdb->insert($providers_table,
            array('provider_id' => 6, 'provider_name' => "TracFone", 'provider_gateway' => "mmst5.tracfone.com"));
        
        add_option("cru_db_version", $cru_db_version);
    } // public function cru_install_database()


    // TODO Consider implementing this
    
    /** 
     *
     *
     *
     */
    public function cru_uninstall_plugin() {

    }
 
    /**
     *
     * Inject plugin admin stylesheets into the header
     *
     */
    public function cru_add_admin_styles() {
        wp_register_style("cru_admin_style", plugins_url("PurdueCRU/css/cru_admin_style.css"));
        wp_enqueue_style("cru_admin_style");
    }
}
?>
