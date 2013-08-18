<?php

// Check if this function exists to see if we are being invoked as a plugin
// in wordpress context, otherwise we need to bailout and not output anything
if (!function_exists('add_action')) {
    exit(1);
}

/**
 *
 * Admin module
 *
 */
class CRU_Admin_Module {
    
    public function __construct($action_dispatcher) {
        $this->action_dispatcher = $action_dispatcher;
        $action_dispatcher->register_action("cru_flush_facebook_cache", array($this, 'flush_cache'));                
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
        add_action('admin_init', array($this, 'register_options'));

        // Add ajax handler
        add_action('wp_ajax_flush_event_cache', array($this, 'flush_cache'));
    }

    /**
     *
     * Install the module with Wordpress
     *
     */
    public function install_module() {
        $this->init_options();
    }

    /**
     *
     * Add the admin page to the admin menu
     *
     */
    public function add_menu() {
        $page_title = 'CRU Admin';
        $menu_title = 'CRU Admin';
        $menu_slug = 'cru-admin';
        $capability = 'cru_admin';
        $function = array($this, 'main_page');
        $icon_url = plugins_url(PURDUE_CRU_PLUGIN_NAME . "/images/settings.png");

        $page = add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function, $icon_url);
    }

    /**
     *
     * Handle flush cache action
     *
     */
    public function flush_cache() {
        $feed = get_option('cru-facebook-feed', '');

        $transient_name = "cru-facebook-events-$feed";
        delete_transient($transient_name);
        $action_result = array('success' => '1', 'message' => 'Cleared the cache');
        $action_result["page"] = "admin.php?page=cru-admin";

        if (CRU_Utils::get_request_param('ajax') === "true") {
            echo json_encode($action_result);
            die();
        }
        return $action_result;
    }

    /**
     *
     * Render the admin page
     *
     */
    public function main_page() {

        if (!current_user_can('cru_admin')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        // Fetch any action results that were forwarded
        $action_result = CRU_Utils::get_action_result();

?>
<div class="wrap">
<div class="icon32" id="icon-options-general"></div>
    <h2><?php echo __( 'Purdue CRU Options' ); ?></h2>
    <?php CRU_Utils::print_action_result($action_result); ?>
    <h2 class="cru_admin_header"><?php echo __('Facebook cache'); ?></h2>
  
    <a class="button" href="<?php echo esc_attr(admin_url('admin.php?page=cru-handle-action&action=cru_flush_facebook_cache')); ?>">
        Clear Facebook Event Cache
    </a>
    <h2>
    <?php echo __('Plugin/Theme Options'); ?>
    </h2>

    <form action="<?php echo 'options.php'; ?>" method="POST">
<?php
        // TODO Consider AJAX possibilities for this
	    settings_fields('cru-options');
	    do_settings_sections('cru-options');
?>
        <input type="hidden" value="<?php echo admin_url('admin.php?page=cru-admin'); ?>" name="_wp_http_referer">
	    <p class="submit"><input name="Submit" type="submit" class="button-primary" value="<?php echo __('Save Changes'); ?>"/></p>
    </form>
</div>
<?php
    }
 

    /**
     *
     * Register our options with Wordpress
     *
     */
    public function register_options() {

        add_settings_section("cru-email-options", "Email Options",
                            array($this, 'email_options'), "cru-options");
        add_settings_section("cru-facebook-options", "Facebook Options",
                            array($this, 'facebook_options'), "cru-options");
        add_settings_section("cru-captcha-options", "Captcha Options",
                            array($this, 'captcha_options'), "cru-options");
    

        /* Email settings */
        register_setting('cru-options', 'cru-email-address');
        add_settings_field('cru-email-address', 'From Email Address',
                            array($this, 'email_address_option'), 'cru-options',
                            'cru-email-options');
    
        register_setting('cru-options', 'cru-email-password');
        add_settings_field('cru-email-password', 'From Email Password',
                            array($this, 'email_password_option'), 'cru-options',
                            'cru-email-options');
    
        register_setting('cru-options', 'cru-email-port');
        add_settings_field('cru-email-port', 'From Email Port',
                            array($this, 'email_port_option'), 'cru-options',
                            'cru-email-options');
    
        register_setting('cru-options', 'cru-email-host');
        add_settings_field('cru-email-host', 'From Email Host',
                            array($this, 'email_host_option'), 'cru-options',
                            'cru-email-options');
    
        register_setting('cru-options', 'cru-email-default-address1');
        add_settings_field('cru-default-address1', 'First Default Address',
                            array($this, 'email_default_address1_option'), 'cru-options',
                            'cru-email-options');
    
        register_setting('cru-options', 'cru-email-default-address2');
        add_settings_field('cru-default-address2', 'Second Default Address',
                            array($this, 'email_default_address2_option'), 'cru-options',
                            'cru-email-options');
    
        register_setting('cru-options', 'cru-email-default-address3');
        add_settings_field('cru-default-address3', 'Third Default Address',
                            array($this, 'email_default_address3_option'), 'cru-options',
                            'cru-email-options');

        /* Facebook settings */
        register_setting('cru-options', 'cru-facebook-app-id');
        add_settings_field('cru-facebook-app-id', 'Facebook API App ID',
                            array($this, 'facebook_app_id_option'), 'cru-options',
                            'cru-facebook-options');
    
        register_setting('cru-options', 'cru-facebook-app-secret');
        add_settings_field('cru-facebook-app-secret', 'Facebook API App Secret',
                            array($this, 'facebook_app_secret_option'), 'cru-options',
                            'cru-facebook-options');
    
        register_setting('cru-options', 'cru-facebook-feed');
        add_settings_field('cru-facebook-feed', 'Facebook event feed',
                            array($this, 'facebook_feed_option'), 'cru-options',
                            'cru-facebook-options');

        /* Captcha settings */
        register_setting('cru-options', 'cru-captcha-public-key');
        add_settings_field('cru-captcha-public-key', 'Captcha API public key',
                            array($this, 'captcha_public_key_option'), 'cru-options',
                            'cru-captcha-options');
    
        register_setting('cru-options', 'cru-captcha-private-key');
        add_settings_field('cru-captcha-private-key', 'Captcha API private key',
                            array($this, 'captcha_private_key_option'), 'cru-options',
                            'cru-captcha-options');
    }

    /**
     *
     * Create all the options in the database
     *
     */
    public function init_options() {

        /* Create email settings */
        add_option('cru-email-address', '');
        add_option('cru-email-password', '');
        add_option('cru-email-port', '');
        add_option('cru-email-host', '');
        add_option('cru-email-default-address1', '');
        add_option('cru-email-default-address2', '');
        add_option('cru-email-default-address3', '');

        /* Facebook settings */
        add_option('cru-facebook-app-id', '');
        add_option('cru-facebook-app-secret', '');
        add_option('cru-facebook-feed', '');

        /* Captcha settings */
        add_option('cru-captcha-public-key', '');
        add_option('cru-captcha-private-key', '');  
    }

    /**
     *
     * START Option helpers
     * 
     */

    function email_options() {
        echo '<p id="cru_email_options">Contact Form Email Options</p>';
    }

    function facebook_options() {
        echo '<p id="cru_facebook_options">Facebook API Options</p>';
    }

    function captcha_options() {
        echo '<p id="cru_captcha_options">Captcha API Options</p>';
    }

    /* Email options */
    function email_address_option() {
        echo '<input type="text" name="cru-email-address" id="cru-email-address" value="'
        . esc_attr(get_option('cru-email-address', "")) .'"/>'
        . '</br><span class="description">Address from which contact form messages will be sent</span>';
    }

    function email_password_option() {
        echo '<input type="text" name="cru-email-password" id="cru-email-password" value="'
        . esc_attr(get_option('cru-email-password', "")) .'"/>'
        . '</br><span class="description">Password for the account from which contact form messages will be sent</span>';
    }

    function email_host_option() {
        echo '<input type="text" name="cru-email-host" id="cru-email-host" value="'
        . esc_attr(get_option('cru-email-host', "")) .'"/>'
        . '</br><span class="description">Host to send contact form emails from (use ssl://smtphost for ssl)</span>';
    }

    function email_port_option() {
        echo '<input type="text" name="cru-email-port" id="cru-email-port" value="'
        . esc_attr(get_option('cru-email-port', "")) .'"/>'
        . '</br><span class="description">Port on smtp host to send email</span>';
    }

    function email_default_address1_option() {
        echo '<input type="text" name="cru-email-default-address1" id="cru-email-default-address1" value="'
        . esc_attr(get_option('cru-email-default-address1', "")) .'"/>'
        . '</br><span class="description">Default address to send all contact form submissions to</span>';
    }

    function email_default_address2_option() {
        echo '<input type="text" name="cru-email-default-address2" id="cru-email-default-address2" value="'
        . esc_attr(get_option('cru-email-default-address2', "")) .'"/>'
        . '</br><span class="description">Default address to send all contact form submissions to</span>';
    }

    function email_default_address3_option() {
        echo '<input type="text" name="cru-email-default-address3" id="cru-email-default-address3" value="'
        . esc_attr(get_option('cru-email-default-address3', "")) .'"/>'
        . '</br><span class="description">Default address to send all contact form submissions to</span>';
    }

    /* Facebook options */
    function facebook_app_id_option() {
        echo '<input type="text" name="cru-facebook-app-id" id="cru-facebook-app-id" value="'
        . esc_attr(get_option('cru-facebook-app-id', "")) .'"/>'
        . '</br><span class="description">Facebook App ID</span>';
    }

    function facebook_app_secret_option() {
        echo '<input type="text" name="cru-facebook-app-secret" id="cru-facebook-app-secret" value="'
        . esc_attr(get_option('cru-facebook-app-secret', "")) .'"/>'
        . '</br><span class="description">Facebook API App Secret</span>';
    }

    function facebook_feed_option() {
        echo '<input type="text" name="cru-facebook-feed" id="cru-facebook-feed" value="'
        . esc_attr(get_option('cru-facebook-feed', "")) .'"/>'
        . '</br><span class="description">ID of the Facebook object to pull events from</span>';
    }

    /* Captcha options */
    function captcha_public_key_option() {
        echo '<input type="text" name="cru-captcha-public-key" id="cru-captcha-public-key" value="'
        . esc_attr(get_option('cru-captcha-public-key', "")) .'"/>'
        . '</br><span class="description">Public key for captcha service (Recaptcha)</span>';
    }

    function captcha_private_key_option() {
        echo '<input type="text" name="cru-captcha-private-key" id="cru-captcha-private-key" value="'
        . esc_attr(get_option('cru-captcha-private-key', "")) .'"/>'
        . '</br><span class="description">Private key for captcha service (Recaptcha)</span>';
    }

}
?>
