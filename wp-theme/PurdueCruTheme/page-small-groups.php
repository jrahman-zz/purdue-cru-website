<?php
/*
Template Name: Small Groups Template
*/
	get_header();
?>
	<div class="page-section content-section">
		<div class="content-wrapper page-content">
			<div class="left-column">
				<?php if (have_posts()) { while (have_posts()) { the_post(); } }?>

				<h2 id="post-<?php the_ID(); ?>" class="page-title no-js"><?php the_title();?></h2>
				<p>
				<?php
				the_content();
				?>
				</p>

			<?php
				$target_areas = cru_get_target_areas();
			?>
				<div id="small-groups">
			<?php
			foreach ($target_areas as $area_id => $contacts) {
				if (count($contacts) > 0) { ?>
				<div class="area-name">
                    <span class="area-name"><?php echo $contacts[0]['area_name']; ?></span>
                </div>
				<div class="area-info">
                <div class="area-contacts-header">
				    <span class="area-contacts-header">Area Staff</span>
                </div>
				<?php
				} /* if (count($contacts) > 0) */

				foreach ($contacts as $contact) {
				?>
				<span class="area-contact">
					<span class="area-contact-name area-contact-info">
					<?php if (isset($contact['first_name']) && isset($contact['last_name'])) {
						$name = $contact['first_name'] . " " . $contact['last_name']; 
					} else if (isset($contact['display_name'])) {
						$name = $contact['display_name'];
					} else {
                        $name = $contact['user_nicename'];
                    }
					echo $name;
							
					if (isset($contact['user_email'])) {
						echo " <em>(" . $contact['user_email'] . ")</em>"; 
					} ?>
					</span>
				</span>
				<?php
				} /* foreach ($contacts as $contact) */

                // Display groups for each area
				$small_groups = cru_get_small_groups($area_id);
				if (count($small_groups) > 0) {
					?>
					<div class="small-groups-header">
                        <span class="small-groups-header">Small Groups</span>
                    </div>
					<?php
                    
                    /* Separate groups by gender */
                    $mens_groups = array();
                    $womens_groups = array();
                    $mixed_groups = array();

                    foreach ($small_groups as $small_group) {
                        if ($small_group['men'] && !$small_group['women']) {
                            array_push($mens_groups, $small_group);
                        } else if ($small_group['women'] && !$small_group['men']) {
                            array_push($womens_groups, $small_group);
                        } else if ($small_group['men'] && $small_group['women']) {
                            array_push($mixed_groups, $small_group);
                        }
                    }
                    $all_groups = array("Men" => $mens_groups, "Women" => $womens_groups, "Men and Women" => $mixed_groups);

                    foreach ($all_groups as $gender => $groups) {
                    if (count($groups) > 0) { ?>
                    <span class="gender-header"><?php echo $gender; ?></span>
					<?php foreach ($groups as $small_group) { ?>
                    <span class="small-group">
					<?php
					if (isset($small_group['first_name']) && isset($small_group['last_name'])) {
						$name = $small_group['first_name'] . " " . $small_group['last_name']; 
					} else {
						$name = $small_group['display_name'];
					}
					echo $small_group['day'] . " " . $small_group['time'] . " - " . $small_group['location'];				
					echo "</br><em>(" . $name . " " . $small_group['user_email'] . ")</em>"; 
					?>
                    </span>
                    <?php
					} /* foreach ($groups as $small_group) */
                    ?>
                    <?php
                    } /* if (count($groups) > 0) */
                    } /* foreach ($all_groups as $gender => $groups) */
				} /* if (count($small_groups) > 0) */?>
				</div>
			<?php
			} /* End of foreach ($target_areas as $target_area => $contacts) */
			?>
			</div> <!-- end of #small-groups -->
			</div> <!-- end of .left-column -->
			
			<div class="sidebar-container">
				<?php
				get_sidebar();
				?>
			</div> <!-- end of .sidebar-container -->
			
			<div class="clearfix"></div>
			<div class="divider">&nbsp</div>
		</div> <!-- end of .content-wrapper -->
	</div> <!-- end of .content-section -->
<?php
	get_footer();
?>
