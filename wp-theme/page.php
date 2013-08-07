<?php
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
