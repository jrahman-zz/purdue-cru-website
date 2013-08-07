<?php

// Exit if accessed directly
if ( !defined('ABSPATH')) exit;

/**
 * Header Template
 *
 *
 * @file           header.php
 * @package        PurdueCRU 
 * @author         Jason P. Rahman
 * @copyright      2013 Purdue CRU
 * @license        license.txt
 * @version        Release: 1.0
 * @filesource     wp-content/themes/PurdueCRU/header.php
 * @link           http://codex.wordpress.org/Theme_Development#Document_Head_.28header.php.29
 * @since          Release 0.1
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html class="no-js" <?php language_attributes(); ?>> <!--<![endif]-->
<head>

<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0">

<title>
	<?php bloginfo('name'); ?> | <?php is_front_page() ? bloginfo('description') : wp_title(''); ?>
</title>

<?php wp_head(); ?>
</head>

<body <?php body_class()?>>
	<!-- Facebook API -->
	<div id="fb-root"></div>
	<script>(function(d, s, id) {
	  var js, fjs = d.getElementsByTagName(s)[0];
	  if (d.getElementById(id)) return;
	  js = d.createElement(s); js.id = id;
	  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=414221521986994";
	  fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));</script>
	<!-- end of Facebook API -->

	<a id="top" name="top"></a>
	<div id="header">
		<div id="header-wrapper">
			<div id="logo" class="no-js">
				<a href="<?php echo get_home_url(); ?>">
					<img id="logo-img" src="<?php echo get_template_directory_uri(); ?>/images/cru_logo_lockup.gif" width="180" height="150" alt="Purdue CRU">
				</a>
			</div>

			<div id="friday-night" class="no-js">
				Join us Fridays @ 7:00PM in EE129
			</div>

            <!--<div id="social-box" class="hide-600">
                <span id="social-header">Find us on</span></br>
                <a id="facebook-icon" class="social-icon" href="http://www.facebook.com/PurdueCru" target="_blank" title="Facebook">
                    <img width="64" height="64" src="<?php echo get_template_directory_uri(); ?>/images/32/facebook.png"/>
	            </a>
	            <a id="twitter-icon" class="social-icon" href="http://www.twitter.com/PurdueCru" target="_blank" title="Twitter">
                    <img width="64" height="64" src="<?php echo get_template_directory_uri(); ?>/images/32/twitter.png"/>
	            </a>
	            <a id="vimeo-icon" class="social-icon" href="http://www.vimeo.com/user3469843" target="_blank" title="Vimeo">
                    img width="64" height="64" src="<?php echo get_template_directory_uri(); ?>/images/32/vimeo.png"/>
	            </a>
            </div>-->
			<!--<div class="right-banner  hide-600">
				<img class="right-banner-img" src="<?php echo get_template_directory_uri(); ?>/images/d.jpg">
			</div>-->
			<div class="clearfix"></div>

		<?php
		/* Standard desktop navigation */
		$config = array(
			'theme_location'	=> 'header-menu',
			'container' 		=> 'div',
			'container_class' 	=> 'menu-container no-js',
			'container_id'		=> 'menu-container',
			'menu_class'		=> 'menu no-js',
			'depth'				=> '2',
			'items_wrap'		=> "<span id=\"menu-header\" class=\"no-js\">Purdue Cru</span><a id=\"menu-icon\" class=\"no-js\"><img width=\"40\" height=\"40\" src=\"" . get_template_directory_uri() . "/images/menu-icon.png\"></a><div class=\"clearfix\"></div><ul id=\"%1\$s\" class=\"%2\$s\">%3\$s</ul><div class=\"clearfix\"></div>",
            'fallback_cb'       => 'cru_fallback_menu'
		);
		wp_nav_menu($config); 

		?>	
		</div> <!-- end of #header-wrapper  -->
	</div> <!-- end of #header -->
	<div id="background-bar-1"></div>
	<div id="background-bar-2"></div>
	<div class="content">
	
<!-- End of the header  -->
