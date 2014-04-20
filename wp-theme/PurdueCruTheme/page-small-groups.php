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
            <?php
                // Display groups for each area
				$small_groups = cru_get_small_groups($area_id);
                foreach ($small_groups as $small_group) { ?>
                    <div class="small-group">
		            <?php
                    echo "<span style=\"font-weight: bold\">" . $small_group['day'] . " " . $small_group['time'] . "</span> &#8212; " . $small_group['location'];				
					if (isset($small_group['first_name']) && isset($small_group['last_name'])) {
						$name = $small_group['first_name'] . " " . $small_group['last_name']; 
					} else {
						$name = $small_group['display_name'];
					}
                    if (isset($small_group['phone_number'])) {
                        $phone_number = " " . $small_group['phone_number'] . " ";
                    } else {
                        $phone_number = " ";
                    }
                    echo "</br><em>(" . $name . " " . $phone_number . "<a href=\"mailto:" . $small_group['user_email'] . "\">" . $small_group['user_email'] . "</a>)</em>"; 
					?>
                    </div>
                    <?php
                    } /* foreach ($small_groups as $groups) */
                    
				} /* if (count($contacts) > 0) */?>
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
