<?php

/*
Plugin Name: Purdue Cru
Description: Plugin for managing Purdue Cru Small Groups, Target Areas, and Contact information
Version: 2.1.1
Author: Jason P Rahman (jprahman93@gmail.com, rahmanj@purdue.edu)
Tags: Purdue, CRU, Campus Crusade for Christ

License: BSD License
License URI: license.txt 

Copyright (c) 2013, 2014, Purdue Cru
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:
    * Redistributions of source code must retain the above copyright
      notice, this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright
      notice, this list of conditions and the following disclaimer in the
      documentation and/or other materials provided with the distribution.
    * Neither the name of the Purdue Cru nor the
      names of its contributors may be used to endorse or promote products
      derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL Purdue Cru BE LIABLE FOR ANY
DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

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
define('PURDUE_CRU_PLUGIN_PATH', plugin_dir_path(__FILE__));

// Export the plugin name (folder name)
define('PURDUE_CRU_PLUGIN_NAME', 'PurdueCruPlugin');

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
$admin_module = new CRU_Admin_Module($dispatcher);
$contacts_module = new CRU_Contacts_Module($dispatcher);

// Include the dispatcher in the modules since it needs to register itself with the plugin
$modules = array($dispatcher,
                $small_groups_module,
                $target_areas_module,
                $contacts_module,
                $admin_module);

// Create our plugin object to register all of our hooks and actions
$CRU_plugin = new CRU_Plugin($modules);

// Register (de)activation hooks
register_activation_hook(__FILE__, array($CRU_plugin, 'cru_install_plugin'));
register_deactivation_hook(__FILE__, array($CRU_plugin, 'cru_uninstall_plugin'));
$CRU_plugin->init_plugin();

?>
