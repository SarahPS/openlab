<?php /* Template Name: Clubs Archive */


remove_action('genesis_post_title', 'genesis_do_post_title');
add_action('genesis_post_title', 'cuny_do_course_archive_title');
function cuny_do_course_archive_title() {
	echo '<h1 class="entry-title">Clubs in Our Community</h1>';
}

remove_action('genesis_post_content', 'genesis_do_post_content');
add_action('genesis_post_content', 'cuny_club_archive' );
function cuny_club_archive() {
/* ?>
<div id="featured-club">
	<p>Featured club information idunt mi ut dolor accumsan et semper nunc egestas sollicitudin eleifend est placerat sit amet? Aenean tincidunt mi ut dolor accumsan et semper nunc egestas. Narrow down your search with aenean tincidunt mi ut dolor accumsan et semper nunc egestas sollicitudin eleifend est placerat sit amet? Aenean tincidunt mi ut dolor accumsan et semper nunc egestas. Narrow down your search with aenean tincidunt mi ut dolor accumsan et semper nunc egestas sollicitudin eleifend est placerat sit amet?</p>
</div>
<hr />
<?php */
global $wpdb,$bp;
if ($_GET['group_sequence'] == "") {
	$_GET['group_sequence'] = "alphabetical";
}
switch ($_GET['group_sequence']) {
	case "alphabetical":
		$display_option = "Alphabetical";
		$option_value = "alphabetical";
		break;
	case "newest":
		$display_option = "Newest";
		$option_value = "newest";
		break;
	case "active":
		$display_option = "Last Active";
		$option_value = "active";
		break;
	default: 
		$display_option = "Select Desired Sequence";
		$option_value = "";
		break;
}
?>
<form id="group_seq_form" name="group_seq_form" action="#" method="get">
	<select name="group_sequence" onchange="document.forms['group_seq_form'].submit();">
		<option value="<?php echo $option_value; ?>"><?php echo $display_option; ?></option>
		<option value='alphabetical'>Alphabetical</option>
		<option value='newest'>Newest</option>
		<option value='active'>Last Active</option>
	</select>
	<input type="submit" name="group_seq_submit" value="Sequence">
</form>
<?php
$ids="9999999";
$rs = $wpdb->get_results( "SELECT group_id FROM {$bp->groups->table_name_groupmeta} where meta_key='wds_group_type' and meta_value='club'" );
foreach ( (array)$rs as $r ) $ids.= ",".$r->group_id;
if ($_GET['group_sequence'] != "") {
	$sequence_type = "type=" . $_GET['group_sequence'] . "&";
}
if($_POST['group_search']){
	$search_terms="search_terms=".$_POST['group_search']."&";
}
if($_GET['search']){
	$search_terms="search_terms=".$_GET['search']."&";
}
if ( bp_has_groups( $sequence_type.$search_terms.'include='.$ids.'&per_page=12&max=' . $instance['max_groups'] ) ) : ?>

	<ul id="club-list" class="item-list">
	<?php $count = 1 ?>
		<?php while ( bp_groups() ) : bp_the_group(); ?>
			<li class="club<?php echo cuny_o_e_class($count) ?>">
				<div class="item-avatar alignleft">
					<a href="<?php bp_group_permalink() ?>"><?php echo bp_get_group_avatar(array( 'type' => 'full', 'width' => 135, 'height' => 135 )) ?></a>
				</div>
				<div class="item">
					<h2 class="item-title"><a href="<?php bp_group_permalink() ?>" title="<?php bp_group_name() ?>"><?php bp_group_name() ?></a></h2>
					<div class="created">Club Since: <?php bp_group_date_created(); ?></div>
					<?php
					     $len = strlen(bp_get_group_description());
					     if ($len > 135) {
						$this_description = substr(bp_get_group_description(),0,135);
						$this_description = str_replace("</p>","",$this_description);
						echo $this_description.'&hellip; (<a href="'.bp_get_group_permalink().'">View More</a>)</p>';
					     } else {
						bp_group_description();
					     }
					?>
				</div>
				
			</li>
				<?php if ( $count % 2 == 0 ) { echo '<hr style="clear:both;" />'; } ?>
		<?php $count++ ?>
		<?php endwhile; ?>
	</ul>

<?php else: ?>

	<div class="widget-error">
		<?php _e('There are no clubs to display.', 'buddypress') ?>
	</div>

<?php endif; ?>

		
<?php if (get_next_posts_link()) : ?>

		<div class="pagination-links" id="group-dir-pag-top">            
			<?php bp_groups_pagination_links() ?>
		</div>

<?php else : ?>

<?php endif; ?>            


<?php

}

add_action('genesis_before_sidebar_widget_area', 'cuny_buddypress_courses_actions');
function cuny_buddypress_courses_actions() { ?>
	<?php global $bp;
	if($bp->loggedin_user->id > 0){?>
    <div class="generic-button"><a href="<?php echo bp_get_root_domain() . '/' . BP_GROUPS_SLUG . '/create/step/group-details/?type=club&new=true' ?>"><?php _e( 'Create a Club', 'buddypress' ) ?></a></div>
    <?php } ?>
    <div class="archive-search">
    <form method="post">
    <input type="text" name="group_search" value="<?php echo $_POST['group_search'];?>" />
    <input type="submit" name="group_search_go" value="Search" />
    </form>
    </div>
<?php 
}

genesis();
