<?php

/*
Plugin Name: Purdue CRU Wordpress Plugin
Description: Plugin for managing Purdue CRU Small Groups, Target Areas, and Contact information
Version: 2.0
Author: Jason P Rahman (jprahman93@gmail.com, rahmanj@purdue.edu)
*/

// Check if this function exists to see if we are being invoked as a plugin
// in wordpress context, otherwise we need to bailout and not output anything
if (!function_exists('add_action')) {
    exit(1);
}

/**
 * Set the timezone for eastern time
 */
date_default_timezone_set('America/Indianapolis');

// Export the path to the plugin to allow themes to access plugin files
define('PURDUE_CRU_PLUGIN_PATH', plugin_dir_path(__FILE__) );

require_once("CRU_Plugin.php");
require_once("CRU_Small_Groups_Module.php");
require_once("CRU_Admin_Module.php");
require_once("CRU_Target_Areas_Module.php");
require_once("CRU_Contacts_Module.php");
require_once("CRU_Action_Dispatcher.php");

// Create the dispatcher which will run module actions
$dispatcher = new CRU_Action_Dispatcher();

// Initialize our plugin modules
$small_groups_module = new CRU_Small_Groups_Module($dispatcher);
$target_areas_module = new CRU_Target_Areas_Module($dispatcher);
$contacts_module = new CRU_Contacts_Module($dispatcher);
$admin_module = new CRU_Admin_Module($dispatcher);

// Include the dispatcher in the modules since it needs to register itself with the plugin
$modules = array($dispatcher,
                $small_groups_module,
                $target_areas_module,
                $contacts_module,
                $admin_module);

// Create our plugin object to register all of our hooks and actions
$CRU_plugin = new CRU_Plugin($modules);

?>
