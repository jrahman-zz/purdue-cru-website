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
 * Primary class for the Purdue Cru Website Plugin
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
        //$this->cru_install_database();

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
     * Run module uninstallation hooks
     *
     */
    public function cru_uninstall_plugin() {
        if (is_array($this->modules)) {
            foreach ($this->modules as $module) {
                if (method_exists($module, 'uninstall_module')) {
                    $module->uninstall_module();
                }
            }
        }
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
