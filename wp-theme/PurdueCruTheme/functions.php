<?php
/**
 * Purdue Cru functions and definitions.
 *
 * Sets up the theme and provides some helper functions, which are used
 * in the theme as custom template tags. Others are attached to action and
 * filter hooks in WordPress to change core functionality.
 *
 * When using a child theme (see http://codex.wordpress.org/Theme_Development and
 * http://codex.wordpress.org/Child_Themes), you can override certain functions
 * (those wrapped in a function_exists() call) by defining them first in your child theme's
 * functions.php file. The child theme's functions.php file is included before the parent
 * theme's file, so the child theme functions would be used.
 *
 * Functions that are not pluggable (not wrapped in function_exists()) are instead attached
 * to a filter or action hook.
 *
 * For more information on hooks, actions, and filters, see http://codex.wordpress.org/Plugin_API.
 *
 * @package PurdueCru
 * @since Release 1.0
 */

/*
 *
 * Pickup the contents of the plugin
 *
 */
if (defined('PURDUE_CRU_PLUGIN_PATH')) {
    require_once(PURDUE_CRU_PLUGIN_PATH . "CRU_Messaging.php");
    $plugin_missing = false;
} else {
    $plugin_missing = true;
}

/*
 *
 * Init our log service
 * 
 */
if (defined('WLS')) {
    if (!wls_is_registered("Cru-Log")) {
        wls_register("Cru-Log", "Log messages for the Cru plugin and theme");
    }

    function cru_log($level, $message) {
        wls_simple_log("Cru-Log", $message, $level);
    }

    define('CRU_NOTICE', WLS_NOTICE);
    define('CRU_INFO', WLS_INFO);
    define('CRU_DEBUG', WLS_DEBUG);
    define('CRU_ERROR', WLS_ERROR);
} else {
    function cru_log($level, $message) {}

    define('CRU_NOTICE', '');
    define('CRU_INFO', '');
    define('CRU_DEBUG', '');
    define('CRU_ERROR', '');
}

/**
 * 
 * Set the timezone for eastern time, this is needed so date and time conversions work correctly
 *
 */
date_default_timezone_set('America/Indianapolis');

/**
 *
 * Enqueues the scripts and stylesheets for the theme
 *
 */
function cru_enqueue_scripts_styles() {

	wp_register_script('jquery.flexslider', get_template_directory_uri() . "/js/jquery.flexslider.min.js", array("jquery"));
	wp_register_script('site', get_template_directory_uri() . "/js/site.js", array("jquery", "jquery.flexslider"));

	if (!is_admin()) {
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery.flexslider');
		wp_enqueue_script('site');	
		wp_enqueue_style('cru-style', get_template_directory_uri() . '/style.css');
		wp_enqueue_style('flexslider-style', get_template_directory_uri() . '/css/flexslider.css');
	}
}
add_action('wp_enqueue_scripts', 'cru_enqueue_scripts_styles');


/**
 *
 * Register our header menu so the admin can customize it
 *
 */
function cru_register_nav_menu() {
	register_nav_menu("header-menu", __("Header Menu"));
}
add_action('init', 'cru_register_nav_menu');

/** 
 *
 *
 * 
 */
function cru_fallback_menu() {
    $config = array(
        'show_home'     => '1',
        'depth'         => '1',
        'menu_class'    => 'menu-container no-js'
    );
    wp_page_menu($config);
}

/** 
 *
 * Add no-js class to the list items
 *
 */
function cru_menu_item_css($classes, $item) {
    if (is_array($classes)) {
        array_push($classes, 'no-js');
    } else if ($classes != "") {
        $classes = array($classes, 'no-js');
    } else {
        $classes = array('no-js');
    }
    return $classes;
}
add_filter('nav_menu_css_class', 'cru_menu_item_css', 10, 2);


/**
 * Extends the default WordPress body class
 *
 * @since Relase 0.1
 *
 * @param array Existing class values.
 * @return array Filtered class values.
 */
function cru_body_class( $classes ) {
	$background_color = get_background_color();
	return $classes;
}
add_filter( 'body_class', 'cru_body_class' );


/**
 *
 *
 * @param array $input
 * @param string $form_url 
 *
 * @return array 
 */
function cru_validate_contact_form($input, $form_url) {
	$invalid_fields = array();
    $valid_fields = array();
	$result = array();

    // Check if the URL already contains a query string, and if so we simply add on to it
    if (preg_match("/.+\?.+=.+/", $form_url)) {
        $result['url'] = $form_url . "&";
    } else {
        $result['url'] = $form_url . "?";
    }

    /* Recaptcha validation first */
    require_once('includes/captcha/recaptchalib.php');
    $privatekey = get_option('cru-captcha-private-key','');
    $resp = recaptcha_check_answer ($privatekey,
                                $_SERVER["REMOTE_ADDR"],
                                $_POST["recaptcha_challenge_field"],
                                $_POST["recaptcha_response_field"]);

    if (!$resp->is_valid) {
        // What happens when the CAPTCHA was entered incorrectly
        $result['url'] .= "result=invalid&message=captcha";
        $result['result'] = false;
        return $result;
    } 


	/* NOTE: Form field names cannot have spaces */
	$form_fields = array("name"         => array("regex" => '/^([A-Za-z_\']{1,100} *)+$/'),
						"email"         => array("func" => 'cru_validate_email'),
						"subject"       => array("regex" => '/^.{0,50}\w.{0,50}$/'),
						"target-area"   => array("regex" => '/^[A-Za-z_ 0-9]{0,100}$/'),
						"message"       => array("regex" => '/^.{1,5000}$/s')
						);

	// Perform validation on each field
	foreach ($form_fields as $field_name => $requirements) {
		if (isset($input[$field_name])) {
            $is_valid = TRUE;
            if (isset($requirements['regex'])) {
                $is_valid = $is_valid && preg_match($requirements['regex'], $input[$field_name]);
            }

            if (isset($requirements['func'])) {
                $is_valid = $is_valid && $requirements['func']($input[$field_name]);
            }

            if ($is_valid) {
                array_push($valid_fields, $field_name);
            } else {
                array_push($invalid_fields, $field_name);
            }
		}
	}
    
    if ($plugin_missing) {
        $result['url'] .= "result=noplugin";
	} else if (count($invalid_fields) > 0 || count($valid_fields) != count($form_fields)) {
		$result['url'] .= "result=invalid&invalid_fields=" . urlencode(implode(' ', $invalid_fields));
		$result['success'] = false;
	} else if (in_array('target-area', $valid_fields)) {
        
        // Create message
        $message = "A contact form was submitted\n";
        $message .= "Name: " . $input['name'] . "\n";
        $message .= "Email: " . $input['email'] . "\n";
        $message .= "Subject: " . $input['subject'] . "\n";
        $message .= "Message: " . $input['message'];

		// Create subject line
		$subject = "Website Contact Form - " . $input['subject'];
        if (preg_match('/[0-9]+/', $input['target-area'])) {
            // Send message to target area
            $ret = CRU_Messaging::send_target_area_message($subject,
                                                           $input['email'],
                                                           $message,
                                                           intval($input['target-area']));
        } else {
            // Send general message to the default addresses
            $ret = CRU_Messaging::send_target_area_message($subject,
                                                           $input['email'],
                                                           $message);
        }

        // Encode the result in the URL
        if (is_int($ret)) {
            $result['url'] .= "result=success&message=sent&count=" . $ret;
		    $result['success'] = true;
        } else {
            $result['url'] .= "result=failed&message=" . urlencode($ret->GetMessage());
		    $result['success'] = false;
        }
    } else {
        $result['url'] .= "result=invalid";
		$result['success'] = false;
	}
	return $result;
}

/**
 *
 * 
 *
 */
function cru_process_contact_form() {
    /* Process any contact form submission */

	/* Perform checks if the form had been submitted */
	$ret = cru_validate_contact_form($_POST, (isset($_POST['referer'])) ? $_POST['referer'] : get_site_url());
	wp_safe_redirect($ret['url']);
	exit();
}
add_action("admin_post_contact_form", 'cru_process_contact_form');
add_action("admin_post_nopriv_contact_form", 'cru_process_contact_form');

/**
 * Validate a given email address
 *
 * @param $email string Email address to be validated
 *
 * @return boolean true if the address is valid, false otherwise
 */
function cru_validate_email($email)
{
    $isValid = true;
    $atIndex = strrpos($email, "@");
    if (is_bool($atIndex) && !$atIndex)
    {
        $isValid = false;
    } else {
        $domain = substr($email, $atIndex+1);
        $local = substr($email, 0, $atIndex);
        $localLen = strlen($local);
        $domainLen = strlen($domain);
        if ($localLen < 1 || $localLen > 64) {
            // local part length exceeded
            $isValid = false;
        } else if ($domainLen < 1 || $domainLen > 255) {
            // domain part length exceeded
            $isValid = false;
        } else if ($local[0] == '.' || $local[$localLen-1] == '.') {
            // local part starts or ends with '.'
            $isValid = false;
        } else if (preg_match('/\\.\\./', $local)) {
            // local part has two consecutive dots
            $isValid = false;
        }
        else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
            // character not valid in domain part
            $isValid = false;
        } else if (preg_match('/\\.\\./', $domain)) {
            // domain part has two consecutive dots
            $isValid = false;
        }else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
                 str_replace("\\\\","",$local))) {
            // character not valid in local part unless 
            // local part is quoted
            if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\","",$local))) {
                $isValid = false;
            }
        }

        /*if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A"))) {
            // domain not found in DNS
            $isValid = false;
        }*/
    }
    return $isValid;
}

/**
 *
 * Display and print an events widget
 *
 * @param $feed 
 *
 */
function cru_events_widget($feed) {

    try {
        $events = cru_get_events();
    } catch (Exception $e) {
        cru_log(CRU_ERROR, "Exception while retrieving events");
    } ?>

	<div id="events">
		<div id="events-header">
			<span id="events-title" class="events-header-item">Upcoming Events</span>
            <?php $feed = esc_attr(get_option('cru-facebook-feed')); ?>
			<a id="events-link" class="events-header-item" href="http://www.facebook.com/<? echo $feed; ?>/"  target="_blank" title="More Events On Facebook">
				More Events On 					
				<img id="facebook-mini-icon" src="<?php echo get_template_directory_uri()?>/images/facebook-icon.png" width="16" height="16" alt="Facebook">
			</a>
			<div class="clearfix"></div>
		</div>
		<div id="events-container">
        <?php if (is_array($events) && $events != false && count($events) > 0) {
            foreach($events as $event) { 
            $event = cru_format_facebook_event($event);
        ?>
            <div class="event">

        <?php
            if (!is_array($event)) { ?>
            <div class="events-error">
                Sorry, an error occured while looking up this event.
            </div>
         <?php } else { ?>
				<div class="event-icon">
                    <a href="<?php echo $event['link']; ?>">
					    <img class="event-icon" src="<?php echo $event['picture']; ?>">
                    </a>
				</div>
				<div class="event-info">
					<div class="event-title event-text">
					<?php echo $event['name']; ?>
					</div>
					<div class="event-date event-text">
					<?php echo $event['date']; if(isset($event['time'])) echo(" " . $event['time']);  ?>
					</div>
					<div class="event-location event-text">
					<?php if(isset($event['location'])) echo $event['location']; ?>
					</div>
				</div>
        <?php } ?>
				<div class="clearfix"></div>
			</div>
    <?php } 
    } else if (count($events) == 0) { ?>
            <div class="events-error">
                Sorry, no events are currently scheduled. Don't worry, we'll have events planned soon, please check back later.
            </div>
    <?php } else {?>
            <div class="events-error">
                Sorry, but an error occured while searching for events.
            </div>
    <?php } ?>
		</div>
	</div> <!-- End of div#events -->
	<?php
}

require_once "includes/CRU_Facebook_Client.php";
require_once "includes/CRU_Facebook_Object.php";

/**
 *
 * Compare two events based on start time
 *
 */
function cru_event_compare($eventA, $eventB) {
    $timeA = strtotime($eventA['start_time']);
    $timeB = strtotime($eventB['start_time']);

    if ($timeA < $timeB) {
        return -1;
    } else {
        return 1;
    }
}

/**
 *
 * Get an event listing from the page specified by the currently configured options
 *
 * @return 
 */
function cru_get_events() {
	
	$secret = get_option('cru-facebook-app-secret', '');
	$id     = get_option('cru-facebook-app-id', '');
    $feed   = get_option('cru-facebook-feed', '');

    $transient_name = "cru-facebook-events-$feed";

    // Use cache to improve page load time
    $transient = get_transient($transient_name);
    if ($transient !== false) {
        return $transient;
    }

    if (strlen($secret) * strlen($id) * strlen($feed) == 0) {
        throw new Exception("Missing options for event retrieval");
    }

    try {
	    $client = new CRU_Facebook_Client($id, $secret);
        
        // Limit us to 10 events are be sure to return all needed details
        $list = $client->getConnection($feed, "events", "10&fields=picture%2Cstart_time%2Clocation%2Cname%2Cend_time");
        $events = array();

        // Cache the results away for reuse, expire in 15 minutes
        if (is_array($list)) {
            $now = time();

            // Filter old events
            foreach ($list as $event) {

                // Use an end time to see if this event is current
                if (isset($event['end_time']) && strtotime($event['end_time']) > $now) {
                    array_push($events, $event);

                } else if (isset($event['start_time']) && strtotime($event['start_time']) > ($now + 10800)) {
                    // Only remove if we are more than 3 hours past the start time
                    array_push($events, $event);
                }
            }

            // Sort by time
            usort($events, "cru_event_compare");
 
            cru_log(CRU_NOTICE, "Caching Facebook events");
            set_transient($transient_name, $events, 15 * 60);
        }
        return $events;
    } catch (Exception $exception) {
        return $exception;
    }
}

/**
 *
 * 
 * @param array $event
 * 
 * @return
 */
function cru_format_facebook_event($event) {
	if (!isset($event['name']) || !isset($event['start_time']) || !isset($event['picture']) || 
        !isset($event['picture']['data']) || !isset($event['picture']['data']['url']) || !isset($event['id'])) {
		return false;
	}

	$datetime = cru_format_facebook_time($event['start_time']);

	$eventinfo = array();
	$eventinfo["name"] = $event['name'];
	if (isset($event['location'])) {
        $eventinfo["location"] = $event['location'];
    }
    $eventinfo["picture"] = $event['picture']['data']['url'];
	$eventinfo["date"] = $datetime["date"];
    if (isset($datetime["time"])) {
	    $eventinfo["time"] = $datetime["time"];
    }
	$eventinfo["link"] = "http://www.facebook.com/events/" . $event['id'];
	return $eventinfo;
}

/**
 * Format an ISO date into a simpler to use format
 * 
 * @param string a string representation of the date in ISO format
 * 
 * @return mixed an associative array containing the formated time, or FALSE in event of an error
 */
function cru_format_facebook_time($time_string) {
	$timestamp = strtotime($time_string);
	if ($timestamp == FALSE) {
		return FALSE;
	}
	$datetime = array();
	if (date("Y", $timestamp) != date("Y")) {
		// Event occurs during a different year
		$datetime["date"] = date("D, F j, Y", $timestamp);
	} else {
		// Event occurs the same year
		$datetime["date"] = date("D, F j", $timestamp);
	}

    // Check if the event has "precise time"
    if (preg_match("/[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}(-|\+)[0-9]{4}/", $time_string)) {
	    $datetime["time"] = date("g:iA", $timestamp);
    }
	return $datetime;
}

/**
 *
 *
 *
 */
function cru_twitter_widget() {
	?>
	<a class="twitter-timeline" style="height: 560px" href="https://twitter.com/purduecru"  data-widget-id="316028036866768896">Tweets by @purduecru</a>
		<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
	<?php
}

/**
 *
 * Display the social icon widget
 *
 */
function cru_social_icon_widget() { ?>
    <span id="social-header">Find us on</span>
    <a id="facebook-icon" class="social-icon" href="http://www.facebook.com/PurdueCru" target="_blank" title="Facebook">
	</a>
	<a id="twitter-icon" class="social-icon" href="http://www.twitter.com/PurdueCru" target="_blank" title="Twitter">
	</a>
	<a id="vimeo-icon" class="social-icon" href="http://www.vimeo.com/user3469843" target="_blank" title="Vimeo">
	</a>

<?php }


/* Database Access Functions */

/**
 * Constants for table names (without wordpress prefix)
 *
 * @var string
 */
define("_small_groups_table", "cru_small_groups");
define("_target_areas_table", "cru_target_areas");
define("_area_contacts_table", "cru_area_contacts"); 
define("_provider_table", "cru_wireless_providers");


/**
 * Constants defining the various affilition types
 *
 *
 */
define("_student_group_leader", 0);
define("_staff_area_leader", 5);

/**
 * Retrieves all targets areas and any primary contacts for that target area
 *
 *
 * @return array An array indexed by area IDs containing an array of associative arrays with information on each contact
 */
function cru_get_target_areas() {
	global $wpdb;
	$target_area_table = $wpdb->prefix . _target_areas_table;
	$area_contacts_table = $wpdb->prefix . _area_contacts_table;

	$areas = array();

    $query = $wpdb->prepare("SELECT areas.area_name, areas.area_id, wp_users.*, meta2.meta_value AS first_name, "
			. " meta3.meta_value AS last_name, meta4.meta_value AS phone_number "
			. "FROM $target_area_table AS areas "
			. "LEFT OUTER JOIN $area_contacts_table AS contacts ON "
			. "contacts.area_id = areas.area_id AND contacts.affiliation_type = '5' "
			. "INNER JOIN wp_users ON wp_users.ID = contacts.contact_id "
			. "LEFT OUTER JOIN wp_usermeta AS meta1 ON "
			. "meta1.user_id = contacts.contact_id AND meta1.meta_key = 'wp_capabilities' AND meta1.meta_value LIKE '%s' "
			. "LEFT OUTER JOIN wp_usermeta AS meta2 "
			. "ON meta2.user_id = contacts.contact_id AND meta2.meta_key = 'first_name' "
			. "LEFT OUTER JOIN wp_usermeta AS meta3 "
			. "ON meta3.user_id = contacts.contact_id AND meta3.meta_key = 'last_name' "
			. "LEFT OUTER JOIN wp_usermeta AS meta4 "
			. "ON meta4.user_id = contacts.contact_id AND meta4.meta_key = 'phone_number'"
			, "%CRU%");

	$results = $wpdb->get_results($query, ARRAY_A);

	foreach ($results as $row) {
		if (!isset($areas[$row['area_id']])) {
			$areas[$row['area_id']] = array();
		} 
		array_push($areas[$row['area_id']], $row);
	}
	return $areas; 
}

/**
 * Retrieve all small group information for a given area
 *
 * @return array An array of associative arrays containing information for each small group 
 *
 */
function cru_get_small_groups($area_id) {
	global $wpdb;
	$small_groups_table = $wpdb->prefix . _small_groups_table;
	
	$query = $wpdb->prepare("SELECT groups.*, users.*, meta1.meta_value AS first_name, "
			. "meta2.meta_value AS last_name, meta3.meta_value AS phone_number "
			. "FROM $small_groups_table AS groups INNER JOIN wp_users ON wp_users.ID = groups.contact_id "
			. "INNER JOIN wp_users AS users ON users.ID = groups.contact_id "
			. "LEFT OUTER JOIN wp_usermeta AS meta1 ON meta1.user_id = groups.contact_id AND meta1.meta_key = 'first_name' "
			. "LEFT OUTER JOIN wp_usermeta AS meta2 ON meta2.user_id = groups.contact_id AND meta2.meta_key = 'last_name' "
			. "LEFT OUTER JOIN wp_usermeta AS meta3 ON meta3.user_id = groups.contact_id AND meta3.meta_key = 'phone_number' "
			. "WHERE groups.area_id = '%s'", $area_id);

	$results = $wpdb->get_results($query, ARRAY_A);
	return $results;
}

?>
