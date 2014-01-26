<?php

// Check if this function exists to see if we are being invoked as a plugin
// in wordpress context, otherwise we need to bailout and not output anything
if (!function_exists('add_action')) {
    exit(1);
}

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
require_once("CRU_Utils.php");

class CRU_Slides_Module {

    public function __construct($action_dispatcher) {
        $this->action_dispatcher = $action_dispatcher;
    }


    /**
     * Action dispatcher used for this modules action handling
     */
    public $action_dispatcher;

    /**
     *
     * Register the module with Wordpress
     *
     */
    public function register_module() {
        add_action('admin_menu', array($this, 'add_menu'));

        // TODO Add contact AJAX handlers
    }

    /**
     *
     * Install the module by updating the database
     *
     */
    public function install_module() {
        global $wpdb;
        global $cru_db_version;

        // TODO Add Query to create database
        $sql = "TODO";

        dbDelta($sql);
    }

    /**
     * Add menu to Wordpress
     */
    public function add_menu() {
        // Settings for the function call below
        $page_title = 'CRU Slides';
        $menu_title = 'CRU Slides';
        $menu_slug = 'cru-slides';
        $capability = 'cru_admin';
        $function = array($this, 'main_page' );
         
        // TODO Add icon
        $icon_url = plugins_url(PURDUE_CRU_PLUGIN_NAME . "/images/contact.png");

        $page = add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function, $icon_url);

        // TODO Register more pages here
    }
}
?>
