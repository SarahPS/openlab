<?php

/**
 * Adds 'local environment' tab
 */
function cuny_local_env_flag() {
	if ( defined( 'IS_LOCAL_ENV' ) && IS_LOCAL_ENV ) {
		?>

		<style type="text/css">
			#local-env-flag {
				position: fixed;
				left: 0;
				top: 50px;
				width: 150px;
				padding: 10px 15px;
				text-align: center;
				background: #f00;
				color: #fff;
				font-size: 1.5em;
				line-height: 1.8em;
				border: 2px solid #666;
				z-index: 1000;
			}
		</style>

		<div id="local-env-flag">
			LOCAL ENVIRONMENT
		</div>

		<?php
	}
}
add_action( 'wp_footer', 'cuny_local_env_flag' );
add_action( 'admin_footer', 'cuny_local_env_flag' );

add_action('wp_enqueue_scripts','wds_jquery');
function wds_jquery() {
		wp_enqueue_script('jquery');
}

add_action('wp_print_styles', 'cuny_site_wide_navi_styles');
function cuny_site_wide_navi_styles() {
	global $blog_id;
	$sw_navi_styles = WPMU_PLUGIN_URL . '/css/sw-navi.css';

	if ( $blog_id == 1 )
		return;

	wp_register_style( 'SW_Navi_styles', $sw_navi_styles );
	wp_enqueue_style( 'SW_Navi_styles' );
}

add_action('wp_footer', 'cuny_login_popup_script');
function cuny_login_popup_script() {

		echo '<script type="text/javascript">';
		echo 'jQuery(document).ready(function(){';

				echo 'jQuery("#popup-login-link").show();';
				echo 'jQuery("#cuny-popup-login").hide();';

				echo 'jQuery("#popup-login-link").click(function(){';
					echo 'jQuery("#cuny-popup-login").slideToggle();';
					echo 'jQuery("#sidebar-user-login").focus();';
				echo '});';
				echo 'jQuery(".close-popup-login").click(function(){';
					echo 'jQuery("#cuny-popup-login").hide();';
				echo '});';
			echo '});';
		echo '</script>';

}

add_action( 'wp_head', 'cuny_site_wide_google_font');
function cuny_site_wide_google_font() {
	echo "<link href='http://fonts.googleapis.com/css?family=Arvo' rel='stylesheet' type='text/css'>";
}

add_action('cuny_bp_adminbar_menus', 'cuny_bp_admin_menu');
function cuny_bp_admin_menu() {
	 global $bp;
	 //print_r($bp);
	 	if ( !is_user_logged_in() )
		return;

	      //echo '<pre>';
	      	//print_r($bp);
	      //echo '</pre>';
	 ?>
<ul class="main-nav">

	<li class="<?php if ( strpos($_SERVER['REQUEST_URI'],"members")
			                      &&
		              !strpos($_SERVER['REQUEST_URI'],"friends")
			                      &&
		              !strpos($_SERVER['REQUEST_URI'],"messages")) {
		                    echo ' selected-page'; }
		    ?>" id="bp-adminbar-account-menu"><a href="<?php echo bp_loggedin_user_domain() ?>">My Profile</a>
    	<ul>
        <?php
		foreach( (array)$bp->bp_options_nav['profile'] as $subnav_item ) {
			$link = str_replace( $bp->displayed_user->domain, $bp->loggedin_user->domain, $subnav_item['link'] );
			$name = str_replace( $bp->displayed_user->userdata->user_login, $bp->loggedin_user->userdata->user_login, $subnav_item['name'] );
			$alt = ( 0 == $sub_counter % 2 ) ? ' class="alt"' : '';
			echo '<li' . $alt . '><a id="bp-admin-' . $subnav_item['css_id'] . '" href="' . $link . '">' . $name . '</a></li>';
			$sub_counter++;
		}
		$link = $bp->loggedin_user->domain."settings/";
		echo '<li' . $alt . '><a id="bp-admin-settings" href="' . $link . '">Settings</a></li>';
		?>
        </ul>

    </li>
	<li class="<?php if ( strpos($_SERVER['REQUEST_URI'],"friends") ) { echo ' selected-page'; } ?>"><a href="<?php echo $bp->loggedin_user->domain . $bp->friends->slug ?>">My Friends</a>
<!--	<ul> -->
<?php
/*
        if ( !$friend_ids = wp_cache_get( 'friends_friend_ids_' . $bp->loggedin_user->id, 'bp' ) ) {
            $friend_ids = BP_Friends_Friendship::get_random_friends( $bp->loggedin_user->id );
            wp_cache_set( 'friends_friend_ids_' . $bp->loggedin_user->id, $friend_ids, 'bp' );
	      }

*/
?>
            <?php //if ( $friend_ids ) { ?>



              <?php //for ( $i = 0; $i < count( $friend_ids ); $i++ ) { ?>
<!--
                <li>
                  <?php //echo bp_core_get_userlink($friend_ids[$i]) ?>
                </li>
-->
              <?php //} ?>


            <?php //} else { ?>
<!--
		      <li><?php //bp_word_or_name( __( "You haven't connected with any friends.", 'buddypress' ), __( "%s hasn't created any friend connections yet.", 'buddypress' ) ) ?></li>
			  <hr />
              <li><a href="<?php //echo bp_get_root_domain() . '/people/' ?>">+ <?php //_e( 'Add a Friend', 'buddypress' ) ?></a></li>
-->
          <?php //} ?>

<!-- </ul> -->
	</li>
	<li class="<?php if ( is_page('my-courses') ) { echo ' selected-page'; } ?>"><a href="<?php echo $bp->root_domain ?>/my-courses/">My Courses</a><ul>
<?php
        if ( !$friend_ids = wp_cache_get( 'cuny_course_ids_' . $bp->loggedin_user->id, 'bp' ) ) {
            $course_info = BP_Groups_Group::wds_get_by_meta( 5, null, $bp->loggedin_user->id, false, false, 'wds_group_type', 'Course');
            wp_cache_set( 'cuny_course_ids_' . $bp->loggedin_user->id, $course_ids, 'bp' );
	      }

	      $course_info = $course_info[groups];
	       if(count( $course_info )>0){
	      	for ( $i = 0; $i < count( $course_info ); $i++ ) {
	      		echo '<li>';
	      			$groups_slug = groups_get_group(array( 'group_id' => $course_info[$i]->id))->slug;
	      			$groups_name = groups_get_group(array( 'group_id' => $course_info[$i]->id))->name;
	      			echo '<a href="' . $bp->root_domain .'/groups/' . $groups_slug .'">' . $groups_name .'</a>';
	      		echo '</li>';
	      	}
		 }else{
			 echo "<li>You do not have any courses.</li>";
		  }

	      $faculty = xprofile_get_field_data( 'Account Type', get_current_user_id() );
		  if ( is_super_admin( get_current_user_id() ) || $faculty == "Faculty" ) {
			  ?>
			  <hr />
			 <a href="<?php echo bp_get_root_domain() . '/' . BP_GROUPS_SLUG . '/create/step/group-details/?type=course&new=true' ?>">+ <?php _e( 'New Course', 'buddypress' ) ?></a>
	      <?php } ?>
          </ul></li>

	<li class="<?php if ( is_page('my-projects') ) { echo ' selected-page'; } ?>"><a href="<?php echo $bp->root_domain ?>/my-projects/">My Projects</a><ul>
<?php
        if ( !$project_ids = wp_cache_get( 'cuny_project_ids_' . $bp->loggedin_user->id, 'bp' ) ) {
            $project_info = BP_Groups_Group::wds_get_by_meta( 5, null, $bp->loggedin_user->id, false, false, 'wds_group_type', 'Project');
            wp_cache_set( 'cuny_project_ids_' . $bp->loggedin_user->id, $project_ids, 'bp' );
		}

	      $project_info = $project_info[groups];
	      if(count( $project_info )>0){
		  //print_r($project_info);
	      	for ( $i = 0; $i < count( $project_info ); $i++ ) {
	      		echo '<li>';
	      			$project_slug = groups_get_group(array( 'group_id' => $project_info[$i]->id))->slug;
	      			$project_name = groups_get_group(array( 'group_id' => $project_info[$i]->id))->name;
	      			echo '<a href="' . $bp->root_domain .'/groups/' . $project_slug .'">' . $project_name .'</a>';
	      		echo '</li>';
	      	}
	      }else{
			 echo "<li>You do not have any projects.</li>";
		  }
	      ?>
	      <hr />
	      <li><a href="<?php echo bp_get_root_domain() . '/projects/' ?>">+ <?php _e( 'Join Projects', 'buddypress' ) ?></a></li>
          <li><a href="<?php echo bp_get_root_domain() . '/' . BP_GROUPS_SLUG . '/create/step/group-details/?type=project&new=true' ?>">+ <?php _e( 'New Project', 'buddypress' ) ?></a></li>
	      </ul></li>
	<li class="<?php if ( is_page('my-clubs') ) { echo ' selected-page'; } ?>"><a href="<?php echo $bp->root_domain ?>/my-clubs/">My Clubs</a><ul>
<?php
        if ( !$friend_ids = wp_cache_get( 'cuny_course_ids_' . $bp->loggedin_user->id, 'bp' ) ) {
            $course_info = BP_Groups_Group::wds_get_by_meta( 5, null, $bp->loggedin_user->id, false, false, 'wds_group_type', 'club');
            wp_cache_set( 'cuny_course_ids_' . $bp->loggedin_user->id, $course_ids, 'bp' );
		}

	      $course_info = $course_info[groups];
	      if(count( $course_info )>0){
	      	for ( $i = 0; $i < count( $course_info ); $i++ ) {
	      		echo '<li>';
	      			$groups_slug = groups_get_group(array( 'group_id' => $course_info[$i]->id))->slug;
	      			$groups_name = groups_get_group(array( 'group_id' => $course_info[$i]->id))->name;
	      			echo '<a href="' . $bp->root_domain .'/groups/' . $groups_slug .'">' . $groups_name .'</a>';
	      		echo '</li>';
	      	}
	      }else{
				echo "<li>You do not have any clubs.</li>";
		  }
	      ?>
	      <hr />
	      <li><a href="<?php echo bp_get_root_domain() . '/clubs/' ?>">+ <?php _e( 'Join Clubs', 'buddypress' ) ?></a></li>
          <li><a href="<?php echo bp_get_root_domain() . '/' . BP_GROUPS_SLUG . '/create/step/group-details/?type=club&new=true' ?>">+ <?php _e( 'New Club', 'buddypress' ) ?></a></li>
	      </ul></li>
	<li class="<?php if ( is_page('my-sites') ) { echo ' selected-page'; } ?>"><a href="<?php echo $bp->root_domain ?>/my-sites/">My Sites</a>
    	<ul>
        	<?php if ( bp_has_blogs('user_id='.$bp->loggedin_user->id) ) :
			  while ( bp_blogs() ) : bp_the_blog(); ?>
			  	<li>
	      			<a href="<?php bp_blog_permalink() ?>"><?php bp_blog_name() ?></a>
	      		<ul><li><a href="<?php bp_blog_permalink() ?>wp-admin">Dashboard</a></li>
	      			<li><a href="<?php bp_blog_permalink() ?>wp-admin/post-new.php">New Post</a></li>
	      			<li><a href="<?php bp_blog_permalink() ?>wp-admin/edit.php">Manage Posts</a></li>
	      			<li><a href="<?php bp_blog_permalink() ?>wp-admin/edit-comments.php">Manage Comments</a></li></ul></li>
			  <?php endwhile;
			endif; ?>
        	<hr />
	     	<a href="<?php echo bp_get_root_domain() . '/sites/create/'; ?>">+ <?php _e( 'New Site', 'buddypress' ) ?></a>
        </ul>
    </li>
	<li class="<?php if ( strpos($_SERVER['REQUEST_URI'],"messages") ) { echo ' selected-page'; } ?>"><a href="<?php echo bp_loggedin_user_domain() ?>messages/">My Messages</a>
    	<ul>
        <?php
		foreach( (array)$bp->bp_options_nav['messages'] as $subnav_item ) {
			$link = str_replace( $bp->displayed_user->domain, $bp->loggedin_user->domain, $subnav_item['link'] );
			$name = str_replace( $bp->displayed_user->userdata->user_login, $bp->loggedin_user->userdata->user_login, $subnav_item['name'] );
			$alt = ( 0 == $sub_counter % 2 ) ? ' class="alt"' : '';
			echo '<li' . $alt . '><a id="bp-admin-' . $subnav_item['css_id'] . '" href="' . $link . '">' . $name . '</a></li>';
			$sub_counter++;
		}
		?>

		<?php 	if ( $notifications = bp_core_get_notifications_for_user( $bp->loggedin_user->id ) ) { ?>
		<?php echo '<li>Notifications<span>(' . count( $notifications ) ?>)</span><ul><?php

			if ( $notifications ) {
				$counter = 0;
				for ( $i = 0; $i < count($notifications); $i++ ) {
					$alt = ( 0 == $counter % 2 ) ? ' class="alt"' : ''; ?>

					<li<?php echo $alt ?>><?php echo $notifications[$i] ?></li>

					<?php $counter++;
				}
				?> </ul></li>
			<?php } else { ?>

				<li><a href="<?php echo $bp->loggedin_user->domain ?>"><?php _e( 'No new notifications', 'buddypress' ); ?></a></li>

			<?php
			} ?>

		<?php
			} else { ?>
			<li><a href="<?php echo $bp->loggedin_user->domain ?>"><?php _e( 'No new notifications', 'buddypress' ); ?></a></li>
			<?php } ?>
        </ul>
    </li>

	</ul></li>
	</ul>
<?php }

add_action('init','wds_search_override',1);
function wds_search_override(){
	if($_POST['search-submit'] && $_POST['search-terms']){
		if($_POST['search-which']=="members"){
			wp_redirect('http://openlab.citytech.cuny.edu/people/?search='.$_POST['search-terms']);
			exit();
		}elseif($_POST['search-which']=="courses"){
			wp_redirect('http://openlab.citytech.cuny.edu/courses/?search='.$_POST['search-terms']);
			exit();
		}elseif($_POST['search-which']=="projects"){
			wp_redirect('http://openlab.citytech.cuny.edu/projects/?search='.$_POST['search-terms']);
			exit();
		}elseif($_POST['search-which']=="clubs"){
			wp_redirect('http://openlab.citytech.cuny.edu/clubs/?search='.$_POST['search-terms']);
			exit();
		}
	}
}

function cuny_site_wide_bp_search() { ?>
	<form action="<?php echo bp_search_form_action() ?>" method="post" id="search-form">
		<input type="text" id="search-terms" name="search-terms" value="" />
		<?php //echo bp_search_form_type_select() ?>
        <select style="width: auto" id="search-which" name="search-which">
        <option value="members">People</option>
        <option value="courses">Courses</option>
        <option value="projects">Projects</option>
        <option value="clubs">Clubs</option>
        <option value="blogs">Sites</option>
        </select>

		<input type="submit" name="search-submit" id="search-submit" value="<?php _e( 'Search', 'buddypress' ) ?>" />
		<?php wp_nonce_field( 'bp_search_form' ) ?>
	</form><!-- #search-form -->
<?php }


add_action('wp_footer', 'cuny_site_wide_header');
function cuny_site_wide_header() {
	global $blog_id;

	if ( $blog_id == 1 )
		return;


?>

<div id="cuny-sw-header">
	<div id="cuny-sw-header-wrap">
	<?php switch_to_blog(1) ?>
		<a href="<?php echo get_bloginfo('home') ?>" id="cuny-sw-logo"></a>
	<?php restore_current_blog() ?>
		<div class="alignright">
		<div>
			<?php cuny_site_wide_bp_search() ?>
		</div>
		<div>
		<ul class="cuny-navi">
			<?php cuny_site_wide_navi(); ?>
		</ul>
		<ul class="main-nav">
			<?php do_action( 'cuny_bp_adminbar_menus' ); ?>
		</ul>
		</div>
		</div>
	</div>
</div>
<?php }




function cuny_site_wide_navi($args = '') {
global $bp, $wpdb;

switch_to_blog(1);
	$site=site_url();
restore_current_blog();
$departments_tech=array('Advertising Design and Graphic Arts','Architectural Technology','Computer Engineering Technology','Computer Systems Technology','Construction Management and Civil Engineering Technology','Electrical and Telecommunications Engineering Technology','Entertainment Technology','Environmental Control Technology','Mechanical Engineering Technology');
$departments_studies=array('Business','Career and Technology Teacher Education','Dental Hygiene','Health Services Administration','Hospitality Management','Human Services','Law and Paralegal Studies','Nursing','Radiologic Technology and Medical Imaging','Restorative Dentistry','Vision Care Technology');
$departments_arts=array('African-American Studies','Biological Sciences','Chemistry','English','Humanities','Library','Mathematics','Physics','Social Science');

$pos = strpos($site,"openlabdev");
if (!($pos === false)) {
		echo "<div style='text-align:center;width:300px;background-color:red;color:white;font-weight:bold;'>T E S T&nbsp;&nbsp;&nbsp;&nbsp;S I T E</div>";
}
?>

<ul class="menu" id="menu-main-menu"><li class="menu-item<?php if ( is_home() ) { echo ' selected-page'; } ?>"><a href="<?php echo $site;?>">Home</a></li>
	<li id="menu-item-people" class="menu-item<?php if ( is_page('people') ) { echo ' selected-page'; } ?>"><a href="<?php echo $site;?>/people/">People</a>
	<ul class="sub-menu">
		<li class="menu-item"><a href="<?php echo $site;?>/people/faculty/">Faculty</a></li>
		<li class="menu-item"><a href="<?php echo $site;?>/people/students/">Students</a></li>
		<li class="menu-item"><a href="<?php echo $site;?>/people/staff/">Staff</a></li>
	</ul>
	</li>
	<li class="menu-item<?php if ( is_page('courses') ) { echo ' selected-page'; } ?>" id="menu-item-40"><a href="<?php echo $site;?>/courses/">Courses</a>
    	<ul class="sub-menu">
   			<li ><a href="<?php echo $site."/courses/?school=tech"; ?>">School of Technology &amp; Design</a>
   				<ul class="sub-menu">
    			<?php foreach ($departments_tech as $i => $value) {?>
					<li><a href="<?php echo $site."/courses/?school=tech"; ?>&department=<?php echo str_replace(" ","-",strtolower($value)); ?>"><?php echo $value; ?></a></li>
				<?php }?>
                </ul>
            </li>
            <li id="menu-item-91"><a href="<?php echo $site."/courses/?school=studies"; ?>">School of Professional Studies</a>
   				<ul class="sub-menu">
    			<?php foreach ($departments_studies as $i => $value) {?>
					<li><a href="<?php echo $site."/courses/?school=studies"; ?>&department=<?php echo str_replace(" ","-",strtolower($value)); ?>"><?php echo $value; ?></a></li>
				<?php }?>
                </ul>
            </li>
            <li id="menu-item-93"><a href="<?php echo $site."/courses/?school=arts"; ?>">School of Arts &amp; Sciences</a>
   				<ul class="sub-menu">
    			<?php foreach ($departments_arts as $i => $value) {?>
					<li><a href="<?php echo $site."/courses/?school=arts"; ?>&department=<?php echo str_replace(" ","-",strtolower($value)); ?>"><?php echo $value; ?></a></li>
				<?php }?>
                </ul>
            </li>
    	</ul>
    </li>
	<li  id="menu-item-projects" class="menu-item<?php if ( is_page('projects') ) { echo ' selected-page'; } ?>"><a href="<?php echo $site;?>/projects/">Projects</a></li>
	<li id="menu-item-clubs" class="menu-item<?php if ( is_page('clubs') ) { echo ' selected-page'; } ?>"><a href="<?php echo $site;?>/clubs/">Clubs</a></li>
	<li id="menu-item-sites" class="menu-item<?php if ( is_page('all-sites') ) { echo ' selected-page'; } ?>"><a href="<?php echo $site;?>/all-sites/">Sites</a></li>
	<li id="menu-item-help" class="menu-item<?php if ( is_page('help') ) { echo ' selected-page'; } ?>"><a href="<?php echo $site;?>/support/help">Help</a>
	<ul class="sub-menu">
		<li class="menu-item"><a href="<?php echo $site;?>/support/about-city-tech-elab/">About City Tech OpenLab</a></li>
		<li class="menu-item"><a href="<?php echo $site;?>/support/contact-us/">Contact Us</a></li>
		<li class="menu-item"><a href="<?php echo $site;?>/support/privacy-policy/">Privacy Policy</a></li>
		<li class="menu-item"><a href="<?php echo $site;?>/support/terms-of-service/">Terms of Service</a></li>
		<li class="menu-item"><a href="<?php echo $site;?>/support/image-credits/">Image Credits</a></li>
		<li class="menu-item"><a href="<?php echo $site;?>/support/help/">Help</a></li>
		<li class="menu-item"><a href="<?php echo $site;?>/support/faq/">FAQ</a></li>
	</ul>
	</li>
	<?php if($bp->loggedin_user->id){ ?>
		<li class="menu-item"><a href="<?php echo wp_logout_url( home_url() ) ?>">Log Out</a></li>
	<?php }else { ?>
		<li class="menu-item"><a id="popup-login-link" href="#">Log In</a>
			<div id="cuny-popup-login" class="popup-login-wrap" style="display:none">
				<div class="popup-login-content">

						<form name="login-form" id="sidebar-login-form" class="standard-form" action="<?php echo site_url( 'wp-login.php', 'login_post' ) ?>" method="post">
							<label><?php _e( 'Username', 'buddypress' ) ?>
							<input type="text" name="log" id="sidebar-user-login" class="input" value="<?php echo esc_attr(stripslashes($user_login)); ?>" tabindex="1" /></label>

							<label><?php _e( 'Password', 'buddypress' ) ?>
							<input type="password" name="pwd" id="sidebar-user-pass" class="input" value="" tabindex="2" /></label>

							<div><input name="rememberme" type="checkbox" id="sidebar-rememberme" value="forever" tabindex="3" /> <?php _e( 'Keep me logged in', 'buddypress' ) ?>
							<input type="submit" name="wp-submit" id="sidebar-wp-submit" value="<?php _e('Log In'); ?>" tabindex="4" /></div>

							<?php do_action( 'bp_sidebar_login_form' ) ?>
							<input type="hidden" name="testcookie" value="1" />
						</form>
						<a class="forgot-password-link" href="<?php echo site_url('wp-login.php?action=lostpassword', 'login') ?>">Forgot Password?</a>
				</div>
			</div></li>
	<?php } ?>
</ul>

<?php }


add_action('wp_footer', 'cuny_site_wide_footer');
function cuny_site_wide_footer() {
global $blog_id;
switch_to_blog(1);
$site=site_url();
restore_current_blog();
?>

<div id="cuny-sw-footer">
<div class="footer-widgets" id="footer-widgets"><div class="wrap"><div class="footer-widgets-1 widget-area"><div class="widget widget_text" id="text-4"><div class="widget-wrap">
	<div class="textwidget"><a href="http://www.citytech.cuny.edu/" target="_blank"><img src="<?php echo $site;?>/wp-content/themes/citytech/images/ctnyc-seal.png" alt="Ney York City College of Technology" border="0" /></a></div>
		</div></div>
</div><div class="footer-widgets-2 widget-area"><div class="widget widget_text" id="text-3"><div class="widget-wrap"><h4 class="widgettitle">About OpenLab</h4>
			<div class="textwidget"><p>OpenLab is an open-source, digital platform designed to support teaching and learning at New York City College of Technology (NYCCT), and to promote student and faculty engagement in the intellectual and social life of the college community.</p></div>
		</div></div>
</div><div class="footer-widgets-3 widget-area"><div class="widget menupages" id="menu-pages-4"><div class="widget-wrap"><h4 class="widgettitle">Support</h4>
<a href="<?php echo $site;?>/support/help/">Help</a> | <a href="<?php echo $site;?>/support/contact-us/">Contact Us</a> | <a href="<?php echo $site;?>/support/privacy-policy/">Privacy Policy</a> | <a href="<?php echo $site;?>/support/terms-of-service/">Terms of Service</a> | <a href="<?php echo $site;?>/support/image-credits/">Credits</a></div></div>
</div><div class="footer-widgets-4 widget-area"><div class="widget widget_text" id="text-6"><div class="widget-wrap"><h4 class="widgettitle">Share</h4>
			<div class="textwidget"><ul class="nav"><li class="rss"><a href="<?php echo $site."/activity/feed/" ?>">RSS</a></li>
            <li>
            <!-- Place this tag in your head or just before your close body tag -->
<script type="text/javascript" src="https://apis.google.com/js/plusone.js"></script>

<!-- Place this tag where you want the +1 button to render -->
<g:plusone size="small"></g:plusone>
            </li>
            </ul></div>
		</div></div>
</div>
<div class="footer-widgets-5 widget-area"><div class="widget widget_text" id="text-7"><div class="widget-wrap"><div class="textwidget"><a href="http://www.cuny.edu/" target="_blank"><img alt="City University of New York" src="<?php echo $site;?>/wp-content/uploads/2011/05/cuny-box.png" /></a></div>
		</div></div>
</div></div><!-- end .wrap --></div>
<div class="footer" id="footer"><div class="wrap"><span class="alignleft">&copy; <a href="http://www.citytech.cuny.edu/" target="_blank">New York City College of Technology</a></span><span class="alignright"><a href="http://www.cuny.edu" target="_blank">City University of New York</a></span></div><!-- end .wrap --></div>
</div>
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', '47613263']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
<?php }