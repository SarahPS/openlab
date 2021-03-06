<?php
/** Start the engine **/
require_once(TEMPLATEPATH.'/lib/init.php');
require_once(STYLESHEETPATH.'/marx_functions.php');

define('BP_DISABLE_ADMIN_BAR', true);

/** Add support with .wrap inside #inner */
add_theme_support( 'genesis-structural-wraps', array( 'header', 'nav', 'subnav', 'inner', 'footer-widgets', 'footer' ) );

remove_action('genesis_sidebar', 'genesis_do_sidebar');

add_action( 'widgets_init', 'cuny_remove_default_widget_areas', 11 );
function cuny_remove_default_widget_areas() {
	unregister_sidebar('sidebar');
	unregister_sidebar('sidebar-alt');
}

/** Add support for custom background **/
add_custom_background();
//add_theme_support( 'genesis-footer-widgets', 5 );

add_action( 'wp_print_styles', 'cuny_no_bp_default_styles', 100 );

// Enqueue Styles For Testimonials Page & sub-pages
add_action('wp_print_styles', 'wds_cuny_ie_styles');
function wds_cuny_ie_styles() { 
  if ( is_admin() )
    return;
    ?>
    <!--[if lte IE 7]>
      <link rel="stylesheet" href="<?php bloginfo( 'stylesheet_directory' ); ?>/css/ie7.css" type="text/css" media="screen" />
    <![endif]-->
    <!--[if IE 8]>
      <link rel="stylesheet" href="<?php bloginfo( 'stylesheet_directory' ); ?>/css/ie8.css" type="text/css" media="screen" />
    <![endif]-->
    <!--[if IE 9]>
      <link rel="stylesheet" href="<?php bloginfo( 'stylesheet_directory' ); ?>/css/ie9.css" type="text/css" media="screen" />
    <![endif]-->
    <?php
}

function cuny_no_bp_default_styles() {
	wp_dequeue_style( 'gconnect-bp' );
	wp_dequeue_script('superfish');
	wp_dequeue_script('superfish-args');

	wp_enqueue_style( 'cuny-bp', get_stylesheet_directory_uri() . '/css/buddypress.css' );
	wp_dequeue_style( 'gconnect-adminbar' );
}
add_action( 'genesis_meta', 'cuny_google_font');
function cuny_google_font() {
	echo "<link href='http://fonts.googleapis.com/css?family=Arvo' rel='stylesheet' type='text/css'>";
}

function cuny_o_e_class($num){
 return $num % 2 == 0 ? " even":" odd";
}

function cuny_third_end_class($num){
 return $num % 3 == 0 ? " last":"";
}

function cuny_default_avatar( $url ) {
	return get_stylesheet_directory_uri() .'/images/avatar.jpg';
}
add_filter( 'bp_core_mysteryman_src', 'cuny_default_avatar' );

remove_action('genesis_before_loop' , 'genesis_do_breadcrumbs');
add_action('genesis_before_footer' , 'genesis_do_breadcrumbs', 5);

add_filter('genesis_breadcrumb_args', 'custom_breadcrumb_args');
function custom_breadcrumb_args($args) {
    $args['labels']['prefix'] = 'You are here:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    $args['prefix']  = '<div id="breadcrumb-container"><div class="breadcrumb">';
    $args['suffix'] = '</div></div>';
    return $args;
}

remove_all_actions('genesis_footer');
//add_action('genesis_footer', 'cuny_creds_footer');
function cuny_creds_footer() {
	echo '<span class="alignleft">© New York City College of Technology</span>';
	echo '<span class="alignright">City University of New York</span>';
}

remove_action( 'wp_footer', 'bp_core_admin_bar', 8 );
add_action('genesis_header','cuny_admin_bar', 10);
function cuny_admin_bar() { 

	cuny_site_wide_bp_search();
	
	cuny_site_wide_navi();
	
	echo '<div id="wp-admin-bar"><div class="padder">';

	// **** Do bp-adminbar-logo Actions ********
	echo '<ul class="main-nav">';

	// **** Do bp-adminbar-menus Actions ********
	do_action( 'cuny_bp_adminbar_menus' );

	echo '</ul>';
	echo "</div></div><!-- #wp-admin-bar -->\n\n";
}

add_action('genesis_after_content', 'cuny_the_clear_div');
function cuny_the_clear_div() {
	echo '<div style="clear:both;"></div>';
}

add_filter( 'wp_title', 'test', 10, 2 );
function test() {
	$title = bp_get_page_title();
	$find = " &#124; Groups &#124; ";
	$replace = " | ";
	$title = str_replace( $find , $replace, $title);
	return $title;
}

remove_filter('get_the_excerpt', 'wp_trim_excerpt');
add_filter('get_the_excerpt', 'cuny_add_links_wp_trim_excerpt');
function cuny_add_links_wp_trim_excerpt($text) {
	$raw_excerpt = $text;
	if ( '' == $text ) {
		$text = get_the_content('');

		$text = strip_shortcodes( $text );

		$text = apply_filters('the_content', $text);
		$text = str_replace(']]>', ']]>', $text);
		$text = strip_tags($text, '<a>');
		$excerpt_length = apply_filters('excerpt_length', 55);

		$excerpt_more = apply_filters('excerpt_more', ' ' . '[...]');
		$words = preg_split('/(<a.*?a>)|\n|\r|\t|\s/', $text, $excerpt_length + 1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE );
		if ( count($words) > $excerpt_length ) {
			array_pop($words);
			$text = implode(' ', $words);
			$text = $text . $excerpt_more;
		} else {
			$text = implode(' ', $words);
		}
	}
	return apply_filters('new_wp_trim_excerpt', $text, $raw_excerpt);

}
//
//    This function switches to the group site, gets the blog_public option and determines if the site is public
//    or not (private = value of "-2") and then, if it is private checks if the logged in user is registered on that blog and if so returns true
//    otherwise (if private and not a registered member) returns false
//
function wds_site_can_be_viewed() {
	global $user_ID;
	$blog_public = false;
	$group_id = bp_get_group_id(); 
	$wds_bp_group_site_id=groups_get_groupmeta($group_id, 'wds_bp_group_site_id' );
	if($wds_bp_group_site_id!=""){
		switch_to_blog($wds_bp_group_site_id);
		$blog_private = get_option('blog_public');
		if ($blog_private != "-2") {
			$blog_public = true;
		} else {
			$user_capabilities = get_user_meta($user_ID,'wp_' . $wds_bp_group_site_id . '_capabilities',true);
			if ($user_capabilities != "") {
				$blog_public = true;
			}
		}
		restore_current_blog();
	}
	return $blog_public;
		

}