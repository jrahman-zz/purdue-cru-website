<?php

// Exit if accessed directly
if ( !defined('ABSPATH')) exit;

get_header();
?>
	
<!-- Page stuff goes here -->
		<div class="page-section slide-section hide-600">
		<div class="content-wrapper slide-wrapper">
            <div class="slide-sidebar" style="float: left;"></div>
            <div class="slide-sidebar" style="float: right;"></div>
			<div id="slide-control" class="slide-control">
			<ul class="slides">
			<li class="slide">
				<img class="slide-image" src="<?php echo get_template_directory_uri()?>/images/berlin-project-2.jpg" alt="Purdue Cru is an authentic community of students passionate about sharing Jesus Christ.">
				<div class="slide-message">
					<h1 class="slide-header">Christ Centered Community</h1>				
					<p class="slide-text">We are an authentic community of students at Purdue passionate about sharing Jesus Christ. Get in touch with a small group in your residence hall, visit us Friday nights, or attend one of our many retreats and conferences to learn more!
                    </p>
					<a href="<?php echo(get_site_url()); ?>/get-involved/" class="button slide-button">Learn More</a>
				</div>
				<div class="clearfix"></div>
			</li>
			<li class="slide" style="display: none">
				<img class="slide-image" src="<?php echo get_template_directory_uri()?>/images/gtb-project-1.jpg" alt="Sign up for an exciting adventure on a summer project.">
				<div class="slide-message">
					<h1 class="slide-header">Summer Projects</h1>				
					<p class="slide-text">Summer projects are 1-12 week trips where students develop a deeper walk with God, live in life-transforming community, receive training in communicating their faith, and experience a new adventure! </p>
					<a href="http://www.gosummerproject.com" class="button slide-button">Learn More</a>
				</div>
				<div class="clearfix"></div>
			</li>
			<li class="slide" style="display: none">
				<img class="slide-image" src="<?php echo get_template_directory_uri()?>/images/cru-logo.jpg" alt="Join the Purdue Cru community Friday nights at 7:00PM.">
				<div class="slide-message">
					<h1 class="slide-header">Friday Night Cru</h1>				
					<p class="slide-text">Friday Night Cru is our weekly meeting where we gather for worship, teaching from the Bible, and discussion about how Biblical principles apply to our life at college. Join us in EE129 at 7pm! </p>
				</div>
				<div class="clearfix"></div>
			</li>
			</ul>
			</div> <!-- end of .slide_control -->
		</div> <!-- end of .content-wrapper -->
		</div> <!-- end of .slide-section -->
   
		<div class="page-section info-section"> 
			<div class="content-wrapper info-wrapper">
			<div class="info-column">
				<h2 class="info-header">What We Are About</h2>
				<p>
				Cru is a caring community of students at Purdue passionate about connecting people to Jesus.
                Every Friday we hold a campus wide meeting and come together as a community of believers in Christ.
				</p>
			</div>
            <div class="info-column">
				<?php
				cru_events_widget("PurdueCru");
				?>
			</div>
			<div class="info-column">
				<h2 class="info-header">Small Groups</h2>
				<p>
				Small group bible studies are the heart of our movement. These meetings occur across campus lead by students. No matter your background, everyone is welcome! Feel free to check out a listing of small groups or contact us to learn more.
				</p>
			</div>
            <div class="social-banner hide-600">
                <div class="social-media social-icons hide-600">
                    <?php cru_social_icon_widget(); ?>            
                </div>
                <div class="social-media fb-like hide-600" data-href="http://www.facebook.com/PurdueCru" data-width="250" data-show-faces="false" data-send="false"></div>
                </div>
            <div class="clearfix"></div>
			</div> <!-- end of .content-wrapper -->
		</div> <!-- end of .info-section -->

	<?php 
	get_footer();
	?>
