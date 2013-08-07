<?php

// Check if this function exists to see if we are being invoked as a plugin
// in wordpress context, otherwise we need to bailout and not output anything
if (!function_exists('add_action')) {
    exit(1);
}

require_once("CRU_Utils.php");

/**
 *
 * Action dispatcher to handle actions sent to the admin page
 * 
 */
class CRU_Action_Dispatcher {
    
    public function __construct() {
        $this->handlers = array();
    }

    /**
     *
     * Register the module with Wordpress
     *
     */
    public function register_module() {
        add_action('admin_menu', array($this, 'add_menu'));
        add_action('admin_init', array($this, 'handle_action'));
    }

    /**
     *
     * Register an action handler to be run on a given action
     *
     */
    public function register_action($action_name, $action_handler) {

        // TODO Consider adding validation for callability
        $this->handlers[$action_name] = $action_handler;
    }

    /**
     *
     * Associative array of action handler callables
     *
     */
    public $handlers;

    /**
     *
     * Add the dummy handle action page, this is for our action dispatcher
     *
     */
    public function add_menu() {
        $page_title = 'Handle Action';
        $menu_title = 'Handle Action';
        $menu_slug = 'cru-handle-action';
        $parent_slug = NULL;
        $capability = 'read';
        $function = array($this, 'handle_action_page');
        
        $page = add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function);
    }

    /**
     *
     * Dummy function for the handle action page
     *
     */
    public function handle_action_page() {
        // Give a nice "scram off" message
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    /**
     *
     * Run action handlers registered by the modules
     *
     */
    public function handle_action() {

        // Check that the handle action page was requested
        if (isset($_GET['page']) && $_GET['page'] == "cru-handle-action") {
            $action = CRU_Utils::get_request_param('action');
            if ($action !== FALSE && isset($this->handlers[$action])) {
                
                $action_result = array("page" => "/admin.php?missing=bad");
                if (is_callable($this->handlers[$action])) {
                    $action_result = call_user_func($this->handlers[$action]);
                }
                    
                // Build the URL based on the action result
                // Note that message is included ONLY if success is also sent
                $page = $action_result["page"];
                if (isset($action_result['success'])) {
                    $page .= "&success=" . urlencode($action_result['success']);
                    if (isset($action_result['message'])) {
                        $page .= "&message=" . urlencode($action_result['message']);
                    }
                }
                $new_location = admin_url($page);
                wp_safe_redirect($new_location);               
            } else {
                // Redirect back to the main admin if we were not given an action
                wp_safe_redirect(admin_url("/admin.php?missing=true"));
            }
        }
    } // public function handle_action()
}
?>
