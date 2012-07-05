<?php

/**
 * Group blogs functionality
 */

/**
 * Utility function for fetching the group id for a blog
 */
function openlab_get_group_id_by_blog_id( $blog_id ) {
	global $wpdb, $bp;

	$group_id = $wpdb->get_var( $wpdb->prepare( "SELECT group_id FROM {$bp->groups->table_name_groupmeta} WHERE meta_key = 'wds_bp_group_site_id' AND meta_value = %d", $blog_id ) );

	return (int) $group_id;
}

/**
 * Utility function for fetching the site id for a group
 */
function openlab_get_site_id_by_group_id( $group_id = 0 ) {
	if ( !$group_id ) {
		$group_id = bp_get_current_group_id();
	}

	return (int) groups_get_groupmeta( $group_id, 'wds_bp_group_site_id' );
}


////////////////////////
/// MEMBERSHIP SYNC ////
////////////////////////

/**
 * Add user to the group blog when joining the group
 */
function openlab_add_user_to_groupblog( $group_id, $user_id ) {
	$blog_id = groups_get_groupmeta( $group_id, 'wds_bp_group_site_id' );

	if ( $blog_id ) {
		if ( groups_is_user_admin( $user_id, $group_id ) ) {
		      $role = "administrator";
		} else if ( groups_is_user_mod( $user_id, $group_id ) ){
		      $role = "editor";
		} else {
		      $role = "author";
		}
		add_user_to_blog( $blog_id, $user_id, $role );
	}
}
add_action( 'groups_join_group', 'openlab_add_user_to_groupblog', 10, 2 );

/**
 * Join a user to a groupblog when joining the group
 *
 * This function exists because the arguments are passed to the hook in the wrong order
 */
function openlab_add_user_to_groupblog_accept( $user_id, $group_id ) {
	openlab_add_user_to_groupblog( $group_id, $user_id );
}
add_action( 'groups_accept_invite', 'openlab_add_user_to_groupblog_accept', 10, 2 );


/**
 * Remove user from group blog when leaving group
 */
function openlab_remove_user_from_groupblog( $group_id, $user_id ) {
	$blog_id = groups_get_groupmeta( $group_id, 'wds_bp_group_site_id' );

	if ( $blog_id ) {
		remove_user_from_blog( $user_id, $blog_id );
	}
}
add_action( 'groups_leave_group', 'openlab_remove_user_from_groupblog', 10, 2 );

/**
 * When a user visits a group blog, check to see whether the user should be an admin, based on
 * membership in the corresponding group.
 *
 * See http://openlab.citytech.cuny.edu/redmine/issues/317 for more discussion.
 */
function openlab_force_blog_role_sync() {
	global $bp, $wpdb;

	if ( !is_user_logged_in() ) {
		return;
	}

	// Is this blog associated with a group?
	$group_id = $wpdb->get_var( $wpdb->prepare( "SELECT group_id FROM {$bp->groups->table_name_groupmeta} WHERE meta_key = 'wds_bp_group_site_id' AND meta_value = %d", get_current_blog_id() ) );

	if ( $group_id ) {

		// Get the user's group status, if any
		$member = $wpdb->get_row( $wpdb->prepare( "SELECT is_admin, is_mod FROM {$bp->groups->table_name_members} WHERE is_confirmed = 1 AND is_banned = 0 AND group_id = %d AND user_id = %d", $group_id, get_current_user_id() ) );

		$userdata = get_userdata( get_current_user_id() );

		if ( !empty( $member ) ) {
			$status = 'author';

			if ( $member->is_admin ) {
				$status = 'administrator';
			} else if ( $member->is_mod ) {
				$status = 'editor';
			}

			$role_is_correct = in_array( $status, $userdata->roles );

			if ( !$role_is_correct ) {
				$user = new WP_User( get_current_user_id() );
				$user->set_role( $status );
			}
		} else {
			$role_is_correct = empty( $userdata->roles );

			if ( !$role_is_correct ) {
				remove_user_from_blog( get_current_user_id(), get_current_blog_id() );
			}
		}

		if ( !$role_is_correct ) {
			// Redirect, just for good measure
			echo '<script type="text/javascript">window.location="' . $_SERVER['REQUEST_URI'] . '";</script>';
		}
	}
}
add_action( 'init', 'openlab_force_blog_role_sync', 999 );


////////////////////////
///     ACTIVITY     ///
////////////////////////

/**
 * Get blog posts into group streams
 */
function openlab_group_blog_activity( $activity ) {

	if ( $activity->type != 'new_blog_post' && $activity->type != 'new_blog_comment' )
		return $activity;

	$blog_id = $activity->item_id;

	if ( 'new_blog_post' == $activity->type ) {
		$post_id = $activity->secondary_item_id;
		$post    = get_post( $post_id );
	} else if ( 'new_blog_comment' == $activity->type ) {
		$comment = get_comment( $activity->secondary_item_id );
		$post_id = $comment->comment_post_ID;
		$post    = get_post( $post_id );
	}

	$group_id = openlab_get_group_id_by_blog_id( $blog_id );

	if ( !$group_id )
		return $activity;

	$group = groups_get_group( array( 'group_id' => $group_id ) );

	// Verify if we already have the modified activity for this blog post
	$id = bp_activity_get_activity_id( array(
		'user_id'           => $activity->user_id,
		'type'              => $activity->type,
		'item_id'           => $group_id,
		'secondary_item_id' => $activity->secondary_item_id
	) );

	// if we don't have, verify if we have an original activity
	if ( !$id ) {
		$id = bp_activity_get_activity_id( array(
			'user_id'           => $activity->user_id,
			'type'              => $activity->type,
			'item_id'           => $activity->item_id,
			'secondary_item_id' => $activity->secondary_item_id
		) );
	}

	// If we found an activity for this blog post then overwrite that to avoid have multiple activities for every blog post edit
	if ( $id ) {
		$activity->id = $id;
	}

	// Replace the necessary values to display in group activity stream
	$activity->action = sprintf( __( '%s wrote a new blog post %s in the group %s:', 'groupblog'), bp_core_get_userlink( $post->post_author ), '<a href="' . get_permalink( $post->ID ) .'">' . esc_html( $post->post_title ) . '</a>', '<a href="' . bp_get_group_permalink( $group ) . '">' . esc_html( $group->name ) . '</a>' );

	$activity->item_id       = (int)$group_id;
	$activity->component     = 'groups';

	$public = get_blog_option( $blog_id, 'blog_public' );

	if ( 0 > (float) $public ) {
		$activity->hide_sitewide = 1;
	} else {
		$activity->hide_sitewide = 0;
	}

	// prevent infinite loops
	remove_action( 'bp_activity_before_save', 'openlab_group_blog_activity' );

	return $activity;
}
add_action( 'bp_activity_before_save', 'openlab_group_blog_activity' );


////////////////////////
///  MISCELLANEOUS   ///
////////////////////////


/**
 * The following function overrides the BP_Blogs_Blog::get() in function bp_blogs_get_blogs(),
 * when looking at the my-sites page, so that the only blogs shown are those without a group
 * attached to them.
 */
function openlab_filter_groupblogs_from_my_sites( $blogs, $params ) {

	// Note: It may be desirable to expand the locations where this filtering happens
	// I'm just playing it safe for the time being
	if ( !is_page( 'my-sites' ) ) {
		return $blogs;
	}

	global $bp, $wpdb;

	// return apply_filters( 'bp_blogs_get_blogs', BP_Blogs_Blog::get( $type, $per_page, $page, $user_id, $search_terms ), $params );
	//  get( $type, $limit = false, $page = false, $user_id = 0, $search_terms = false )

	// Set up the necessary variables for the rest of the function, out of $params
	$type         = $params['type'];
	$limit        = $params['per_page'];
	$page         = $params['page'];
	$user_id      = $params['user_id'];
	$search_terms = $params['search_terms'];

	// The magic: Pull up a list of blogs that have associated groups, and exclude them
	$exclude_blogs = $wpdb->get_col( $wpdb->prepare( "SELECT meta_value FROM {$bp->groups->table_name_groupmeta} WHERE meta_key = 'wds_bp_group_site_id'" ) );

	if ( !empty( $exclude_blogs ) ) {
		$exclude_sql = $wpdb->prepare( " AND b.blog_id NOT IN (" . implode( ',', $exclude_blogs ) . ") " );
	} else {
		$exclude_sql = '';
	}

	if ( !is_user_logged_in() || ( !is_super_admin() && ( $user_id != $bp->loggedin_user->id ) ) )
		$hidden_sql = "AND wb.public = 1";
	else
		$hidden_sql = '';

	$pag_sql = ( $limit && $page ) ? $wpdb->prepare( " LIMIT %d, %d", intval( ( $page - 1 ) * $limit), intval( $limit ) ) : '';

	$user_sql = !empty( $user_id ) ? $wpdb->prepare( " AND b.user_id = %d", $user_id ) : '';

	switch ( $type ) {
		case 'active': default:
			$order_sql = "ORDER BY bm.meta_value DESC";
			break;
		case 'alphabetical':
			$order_sql = "ORDER BY bm2.meta_value ASC";
			break;
		case 'newest':
			$order_sql = "ORDER BY wb.registered DESC";
			break;
		case 'random':
			$order_sql = "ORDER BY RAND()";
			break;
	}

	if ( !empty( $search_terms ) ) {
		$filter = like_escape( $wpdb->escape( $search_terms ) );
		$paged_blogs = $wpdb->get_results( "SELECT b.blog_id, b.user_id as admin_user_id, u.user_email as admin_user_email, wb.domain, wb.path, bm.meta_value as last_activity, bm2.meta_value as name FROM {$bp->blogs->table_name} b, {$bp->blogs->table_name_blogmeta} bm, {$bp->blogs->table_name_blogmeta} bm2, {$wpdb->base_prefix}blogs wb, {$wpdb->users} u WHERE b.blog_id = wb.blog_id AND b.user_id = u.ID AND b.blog_id = bm.blog_id AND b.blog_id = bm2.blog_id AND wb.archived = '0' AND wb.spam = 0 AND wb.mature = 0 AND wb.deleted = 0 {$hidden_sql} AND bm.meta_key = 'last_activity' AND bm2.meta_key = 'name' AND bm2.meta_value LIKE '%%$filter%%' {$user_sql} {$exclude_sql} GROUP BY b.blog_id {$order_sql} {$pag_sql}" );
		$total_blogs = $wpdb->get_var( "SELECT COUNT(DISTINCT b.blog_id) FROM {$bp->blogs->table_name} b, {$wpdb->base_prefix}blogs wb, {$bp->blogs->table_name_blogmeta} bm, {$bp->blogs->table_name_blogmeta} bm2 WHERE b.blog_id = wb.blog_id AND bm.blog_id = b.blog_id AND bm2.blog_id = b.blog_id AND wb.archived = '0' AND wb.spam = 0 AND wb.mature = 0 AND wb.deleted = 0 {$hidden_sql} AND bm.meta_key = 'name' AND bm2.meta_key = 'description' AND ( bm.meta_value LIKE '%%$filter%%' || bm2.meta_value LIKE '%%$filter%%' ) {$user_sql} {$exclude_sql}" );
	} else {
		$paged_blogs = $wpdb->get_results( $wpdb->prepare( "SELECT b.blog_id, b.user_id as admin_user_id, u.user_email as admin_user_email, wb.domain, wb.path, bm.meta_value as last_activity, bm2.meta_value as name FROM {$bp->blogs->table_name} b, {$bp->blogs->table_name_blogmeta} bm, {$bp->blogs->table_name_blogmeta} bm2, {$wpdb->base_prefix}blogs wb, {$wpdb->users} u WHERE b.blog_id = wb.blog_id AND b.user_id = u.ID AND b.blog_id = bm.blog_id AND b.blog_id = bm2.blog_id {$user_sql} AND wb.archived = '0' AND wb.spam = 0 AND wb.mature = 0 AND wb.deleted = 0 {$hidden_sql} {$exclude_sql} AND bm.meta_key = 'last_activity' AND bm2.meta_key = 'name' GROUP BY b.blog_id {$order_sql} {$pag_sql}" ) );
		$total_blogs = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(DISTINCT b.blog_id) FROM {$bp->blogs->table_name} b, {$wpdb->base_prefix}blogs wb WHERE b.blog_id = wb.blog_id {$user_sql} AND wb.archived = '0' AND wb.spam = 0 AND wb.mature = 0 AND wb.deleted = 0 {$hidden_sql} {$exclude_sql}" ) );
	}

	$blog_ids = array();
	foreach ( (array)$paged_blogs as $blog ) {
		$blog_ids[] = $blog->blog_id;
	}

	$blog_ids = $wpdb->escape( join( ',', (array)$blog_ids ) );
	$paged_blogs = BP_Blogs_Blog::get_blog_extras( $paged_blogs, $blog_ids, $type );

	return array( 'blogs' => $paged_blogs, 'total' => $total_blogs );
}
add_filter( 'bp_blogs_get_blogs', 'openlab_filter_groupblogs_from_my_sites', 10, 2 );

/**
 * This function checks the blog_public option of the group site, and depending on the result,
 * returns whether the current user can view the site.
 */
function wds_site_can_be_viewed() {
	global $user_ID;
	$blog_public = false;
	$group_id = bp_get_group_id();
	$wds_bp_group_site_id=groups_get_groupmeta($group_id, 'wds_bp_group_site_id' );

	if($wds_bp_group_site_id!=""){
		$blog_private = get_blog_option( $wds_bp_group_site_id, 'blog_public' );

		switch ( $blog_private ) {
			case '-3' : // todo?
			case '-2' :
				if ( is_user_logged_in() ) {
					$user_capabilities = get_user_meta($user_ID,'wp_' . $wds_bp_group_site_id . '_capabilities',true);
					if ($user_capabilities != "") {
						$blog_public = true;
					}
				}
				break;

			case '-1' :
				if ( is_user_logged_in() ) {
					$blog_public = true;
				}
				break;

			default :
				$blog_public = true;
				break;
		}
	}
	return $blog_public;
}

/**
 * Validate a URL format
 */
function openlab_validate_url( $url ) {
	if ( 0 !== strpos( $url, 'http' ) ) {
		// Let's guess that http was left off
		$url = 'http://' . $url;
	}

	$url = trailingslashit( $url );

	return $url;
}

/**
 * Given a site URL, try to get feed URLs
 */
function openlab_find_feed_urls( $url ) {

	// Supported formats
	$formats = array(
		'wordpress' => array(
			'posts'    => '{{URL}}feed',
			'comments' => '{{URL}}feed/comments'
		),
		'blogger' => array(
			'posts'    => '{{URL}}feeds/posts/default?alt=rss',
			'comments' => '{{URL}}feeds/comments/default?alt=rss'
		),
		'drupal' => array(
			'posts'    => '{{URL}}posts/feed'
		)
	);

	$feed_urls = array();

	foreach( $formats as $ftype => $f ) {
		$maybe_feed_url = str_replace( '{{URL}}', trailingslashit( $url ), $f['posts'] );
		$maybe_feed = wp_remote_get( $maybe_feed_url );
		if ( 200 == $maybe_feed['response']['code'] ) {
			$feed_urls['posts'] = $maybe_feed_url;
			$feed_urls['type']  = $ftype;

			// Test the comment feed
			if ( isset( $f['comments'] ) ) {
				$maybe_comments_feed_url = str_replace( '{{URL}}', trailingslashit( $url ), $f['comments'] );
				$maybe_comments_feed = wp_remote_get( $maybe_comments_feed_url );

				if ( 200 == $maybe_comments_feed['response']['code'] ) {
					$feed_urls['comments'] = $maybe_comments_feed_url;
				}
			}

			break;
		}

	}

	return $feed_urls;
}

?>