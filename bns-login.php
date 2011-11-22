<?php
/*
Plugin Name: BNS Login
Plugin URI: http://buynowshop.com/plugins/bns-login/
Description: A simple plugin providing a link to the dashboard; and, a method to log in and out of your blog in the footer of the theme. This is ideal for those not wanting to use the meta widget/code links.
Version: 1.8
Text Domain: bns-login
Author: Edward Caissie
Author URI: http://edwardcaissie.com/
License: GNU General Public License v2
License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

/*  Last revision: June 17, 2011 version 1.7  */

/**
 * BNS Login plugin
 *
 * A simple plugin providing a link to the dashboard; and, a method to log in
 * and out of your blog in the footer of the theme. This is ideal for those not
 * wanting to use the meta widget/code links.
 *
 * @package     BNS_Login
 * @link        http://buynowshop.com/plugins/bns-login/
 * @link        https://github.com/Cais/bns-login/
 * @link        http://wordpress.org/extend/plugins/bns-login/
 * @version     1.8
 * @author      Edward Caissie <edward.caissie@gmail.com>
 * @copyright   Copyright (c) 2009-2011, Edward Caissie
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 2, as published by the
 * Free Software Foundation.
 *
 * You may NOT assume that you can use any other version of the GPL.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, write to:
 *
 *      Free Software Foundation, Inc.
 *      51 Franklin St, Fifth Floor
 *      Boston, MA  02110-1301  USA
 *
 * The license for this software can also likely be found here:
 * http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Last revised November 22, 2011
 */

global $wp_version;
$exit_ver_msg = 'BNS Login requries a minimum of WordPress 3.0, <a href="http://codex.wordpress.org/Upgrading_WordPress">Please Update!</a>';
if ( version_compare( $wp_version, "3.0", "<" ) ) { // for `home_url()`
	exit ( $exit_ver_msg );
}

// Add BNS Login Scripts and Stylesheets
function BNS_Login_Scripts_and_Styles() {
    /* Scripts */
    /* Styles */
  	wp_enqueue_style( 'BNS-Login-Style', plugin_dir_url( __FILE__ ) . '/bns-login-style.css', array(), '1.7', 'screen' );
  	wp_enqueue_style( 'BNS-Login-Custom-Style', plugin_dir_url( __FILE__ ) . '/bns-login-custom-style.css', array(), '1.7', 'screen' );
}
add_action( 'wp_enqueue_scripts', 'BNS_Login_Scripts_and_Styles' );

/* Main function that will accept paramaters */
function BNS_Login( $args = '' ) {
		$values = array( 'login' => '', 'after_login' => '', 'logout' => '', 'register' => '', 'goto' => '', 'sep' => '' );
    $args = wp_parse_args( $args, $values );

    /* Initialize output */
    $output = '';
    /* Defaults values */
		$login        = empty( $args['login'] ) ? sprintf( __( 'Log in here!' ) ) : $args['login'];
		$after_login  = empty( $args['after_login'] ) ? sprintf( __( 'You are logged in!' ) ) : $args['after_login'];
		$logout       = empty( $args['logout'] ) ? sprintf( __( 'Logout' ) ) : $args['logout'];
		$register     = empty( $args['register'] ) ? sprintf( __( 'Register' ) ) : $args['register'];
		$goto         = empty( $args['goto'] ) ? sprintf( __( 'Go to Dashboard' ) ) : $args['goto'];
    $separator    = empty( $args['separator'] ) ? sprintf( __( ' &deg;&deg; ' ) ) : $args['separator'];
    /* Wrap separator in its own class for easier styling ... just in case */
    $sep          = '<span class="bns-login-separator">' . $separator . '</span>';

    /* Set login and register URL */
  	$login_url = home_url( '/wp-admin/' );
    $register_url = home_url( '/wp-login.php?action=register' );
  	
  	/* The real work gets done next ...  */
  	if ( is_user_logged_in() ) {
  		$output .= '<div id="bns-logged-in" class="bns-login">' . $after_login . $sep;
      /* WPMU, Multisite - logout returns to WPMU, or Multisite, main domain page */
  		if ( function_exists( 'get_current_site' ) ) {
  			$current_site = get_current_site();
  			$home_domain = 'http://' . $current_site->domain . $current_site->path;
  			$logout_url = wp_logout_url( $home_domain );
  		} else {
  		  $logout_url = wp_logout_url( home_url() );
  		}
      $output .= '<a href="' . $logout_url . '" title="' . $logout . '">' . $logout . '</a>' . $sep;
  		$output .= '<a href="' . $login_url . '" title="' . $goto . '">' . $goto . '</a></div>';
  	} else { /* user is not logged in => login; or, register if allowed */
  		$output .= '<div id="bns-logged-out" class="bns-login">';
      $output .= '<a href="' . $login_url . '" title="' . $login . '">' . $login . '</a>';
      $output .= __( wp_register( $sep, '', false ) );
      $output .= '</div>';
  	}
  	$output = apply_filters( 'BNS_Login', $output, $args );
  	return $output;
}

/* Modify the BNS_Login call to change the default parameters --- Major Hack?!
 * 
 * TO-DO: Consider adding options page?
 *
 * Uses the following parameters (see defaults in BNS_Login function)
 *    login       => anchor text to login URL
 *    after_login => message showing end-user is logged in
 *    logout      => anchor text to logout URL
 *    goto        => anchor text to dashboard / Administration Panels
 *    separator   => character(s) used to separate the anchor texts
 *
 */
function Add_BNS_Login() {
    /* BNS_Login pre-populated with empty parameters as guidelines */
    echo BNS_Login( 'login=&after_login=&logout=&goto=&separator=' );
}
add_action( 'wp_footer', 'Add_BNS_Login' );
?>