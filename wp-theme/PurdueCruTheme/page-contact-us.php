<?php
/*
Template Name: Contact Us
*/
    /* Form was submitted, redirect back to the page has occured */
    if (isset($_GET['result'])) {
	    if ($_GET['result'] === "success") {

	    } else if ($_GET['result'] === "invalid") {
		    /* Get list of invalid form fields */
		    $banner_message = "Sorry, but some of the information you filled out was invalid. Please check the information and resubmit.";

            // Retrieve the list of invalid fields, if any
		    if (isset($_GET['invalid_fields'])) {
			    $fields = explode(' ', urldecode($_GET['invalid_fields']));
                $invalid_fields = array();
                foreach($fields as $field) {
                    $invalid_fields[$field] = "";
                }
		    } else {
			    $invalid_fields = array();
		    }

            // Check for a given message
            if (true) {

            }
	    } else if ($_GET['result'] === "failed") {
		    $banner_message = "Oops, something went wrong and we were unable to send your message.";
	    }
    }

	get_header();
?>
	<div class="page-section content-section">
		<div class="content-wrapper page-content">
			<div class="left-column">
				<?php if (have_posts()) { while (have_posts()) { the_post(); } }?>

				<h2 id="post-<?php the_ID(); ?>" class="page-title no-js"><?php the_title();?></h2>
				<p><?php the_content(); ?></p>
			<?php 
			if (isset($banner_message)) {
			?>
				<p id="contact-form-banner">
				<?php
					echo $banner_message;
				?>
				</p>
			<?php	
			}

            // Display the contact form if the form was not submitted
			if (!isset($_GET['result']) || $_GET['result'] !== "success") {
			?>
				<form id="contact-form" method="post" action="<?php echo get_site_url(); ?>/wp-admin/admin-post.php">

					<p class="contact-form">
					<label for="name" class="contact-form">Name</label>
                    <em id="contact-form-name-error" class="contact-form-error <?php if (!isset($invalid_fields['name'])) echo "hidden"; ?>"><br />Please enter your name</em><br />
					<input id="name" class="contact-form" type="text" name="name"><br />
					</p>

					<p class="contact-form">
					<label for="email" class="contact-form">Email</label>
                    <em id="contact-form-email-error" class="contact-form-error <?php if (!isset($invalid_fields['email'])) echo "hidden"; ?>"><br />Invalid email address</em><br />
					<input id="email" class="contact-form" type="text" name="email">
					</p>

                    <?php
                    $target_areas = cru_get_target_areas();
                    if (count($target_areas) > 0) {
                    ?>

					<p class="contact-form">
					<label for="target-area" class="contact-form">Residence Hall <em class="contact-form">(optional)</em></label><br />
					<select id="target-area" class="contact-form" name="target-area">
						<option value="">--</option>
					<?php
					
                    // The left outer join we performed in the query will ensure that at least one row was returned
					foreach ($target_areas as $area_id => $contacts) { ?>
						<option value="<?php echo $area_id; ?>"><?php echo $contacts[0]['area_name']; ?></option>
					<?php } ?>
					</select>
					</p>		
                    <?php /* if(count($target_areas)) */
                    } else {
                    ?>
                    <input type="hidden" value="" name="target-area">
                    <?php } ?>

					<p class="contact-form">
					<label for="subject" class="contact-form">Subject</label>
                    <em id="contact-form-subject-error" class="contact-form-error <?php if (!isset($invalid_fields['subject'])) echo "hidden"; ?>"><br />Please include a subject</em><br />
					<input id="subject" class="contact-form" type="text" name="subject">
					</p>

					<p class="contact-form">
					<label for="message" class="contact-form">Message</label>
                    <em id="contact-form-message-error" class="contact-form-error <?php if (!isset($invalid_fields['message'])) echo "hidden"; ?>"><br />Please include a message</em><br />
					<textarea id="message" class="contact-form" style="resize: none" rows="10" cols="50" name="message"></textarea>
					</p>
                <?php $public_key = get_option('cru-captcha-public-key',''); ?>	

                    <p class="contact-form">
                    <script type="text/javascript" 
                        src="http://www.google.com/recaptcha/api/challenge?k=<?php echo $public_key; ?>">
                    </script>
                    <noscript>
                        <iframe 
                            src="http://www.google.com/recaptcha/api/noscript?k=<?php echo $public_key; ?>"
                            height="300" width="500" frameborder="0">
                        </iframe><br>
                        <textarea name="recaptcha_challenge_field" rows="3" cols="40">
                        </textarea>
                        <input type="hidden" name="recaptcha_response_field" value="manual_challenge">
                    </noscript>
                    </p>

					<p class="contact-form">
					<input id="submit" class="contact-form button" type="submit" name="submit" value="Start a conversation">
					</p>

                    
                    <input type="hidden" name="referer" value="<?php echo get_permalink(); ?>">
					<input type="hidden" name="action" value="contact_form">
				</form>
			<?php
			} else {
			?>
			<p class="success-message">
			We've received your message, and we'll be in touch soon.<br /><br />
			Thank you for your interest in Purdue Cru!
			</p>
			<a class="button" href="<?php echo get_home_url(); ?>">Back to the home page</a>
			<a class="button" href="<?php echo get_permalink(); ?>">Back to the contact form</a>
			
			<?php
			}
			?>
			</div> <!-- end of .left-column -->
			
			<div class="sidebar-container">
				<?php
				get_sidebar();
				?>
			</div> <!-- end of .sidebar-container -->
			
			<div class="clearfix"></div>
		</div> <!-- end of .content-wrapper -->
	</div> <!-- end of .content-section -->
<?php
	get_footer();
?>
