<?php

/**
 * WordPress
 */
define( 'DB_NAME', '' );
define( 'DB_USER', '' );
define( 'DB_PASSWORD', '' );
define( 'DB_HOST', '' );

/**
 * bbPress (BP version)
 *
 * These should be the same as your WP info
 */
define( 'BBDB_NAME', '' );
define( 'BBDB_USER', '' );
define( 'BBDB_PASSWORD', '' );
define( 'BBDB_HOST', '' );

/**
 * Other environment specific constants
 */
define( 'IS_LOCAL_ENV', true ); // Leave this as true, except on staging and production environments
define( 'WP_DEBUG', false );
define( 'DOMAIN_CURRENT_SITE', 'openlab.citytech.cuny.edu' );
define( 'PATH_CURRENT_SITE', '/' );

@ini_set('log_errors','On');
@ini_set('display_errors','Off');
@ini_set('error_log','/usr/home/openlab/public_html/php_error.log');

?>
