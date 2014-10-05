<?php
/*
Plugin Name: BNS Login
Plugin URI: http://buynowshop.com/plugins/bns-login/
Description: A simple plugin providing a link to the dashboard; and, a method to log in and out of your blog in the footer of the theme. This is ideal for those not wanting to use the meta widget/code links.
Version: 2.4
Text Domain: bns-login
Author: Edward Caissie
Author URI: http://edwardcaissie.com/
License: GNU General Public License v2
License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

/**
 * BNS Login
 * A simple plugin providing a link to the dashboard; and, a method to log in
 * and out of your blog in the footer of the theme. This is ideal for those not
 * wanting to use the meta widget/code links.
 *
 * @package     BNS_Login
 * @link        http://buynowshop.com/plugins/bns-login/
 * @link        https://github.com/Cais/bns-login/
 * @link        http://wordpress.org/extend/plugins/bns-login/
 * @version     2.4
 * @author      Edward Caissie <edward.caissie@gmail.com>
 * @copyright   Copyright (c) 2009-2014, Edward Caissie
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
 * @version     2.3.2
 * @date        December 2013
 * Add the option to put custom stylesheet in `/wp-content/` folder
 *
 * @version     2.3.3
 * @date        March 2014
 * Inline documentation, version compatibility, and copyright years updated
 *
 * @version     2.4
 * @date        October 2014
 */
class BNS_Login {
	/**
	 * Constructor
	 *
	 * @package          BNS_Login
	 * @since            1.9
	 *
	 * @uses             (global) wp_version
	 * @uses             add_action
	 * @uses             add_shortcode
	 *
	 * @version          2.3.3
	 * @date             March 29, 2014
	 * Updated required WordPress version to 3.6
	 *
	 * @version          2.4
	 * @date             October 4, 2014
	 * Updated required version check to 3.8 for `dashicons` inclusion
	 * Defined `BNS_CUSTOM_PATH` and `BNS_CUSTOM_URL` for customizations
	 */
	function __construct() {
		/** Check installed WordPress version for compatibility */
		global $wp_version;
		$exit_ver_msg = __( 'BNS Login requires a minimum of WordPress 3.8, <a href="http://codex.wordpress.org/Upgrading_WordPress">Please Update!</a>', 'bns-login' );
		/** Version 3.8 is required for `dashicons` inclusion */
		if ( version_compare( $wp_version, "3.8", "<" ) ) {
			exit ( $exit_ver_msg );
		}
		/** End if - version compare */

		/** Enqueue Scripts and Styles */
		add_action(
			'wp_enqueue_scripts', array(
				$this,
				'scripts_and_styles'
			)
		);

		/** Add BNS Login to Footer */
		add_action( 'wp_footer', array( $this, 'bns_login_output' ) );

		/** Add Jetpack compatibility for infinite scroll */
		add_filter(
			'infinite_scroll_credit', array(
				$this,
				'jetpack_infinite_scroll_compatibility'
			)
		);

		/** Add Shortcode functionality to text widgets */
		add_action( 'widget_text', 'do_shortcode' );

		/** Add Shortcode for this plugin */
		add_shortcode( 'bns_login', array( $this, 'bns_login_form' ) );

		/** Add Plugin Row Meta details */
		add_filter(
			'plugin_row_meta', array(
				$this,
				'bns_login_plugin_meta'
			), 10, 2
		);

		/** Define location for BNS plugin customizations */
		if ( ! defined( 'BNS_CUSTOM_PATH' ) ) {
			define( 'BNS_CUSTOM_PATH', WP_CONTENT_DIR . '/bns-customs/' );
		}
		/** end if - not defined */
		if ( ! defined( 'BNS_CUSTOM_URL' ) ) {
			define( 'BNS_CUSTOM_URL', content_url( '/bns-customs/' ) );
		}
		/** end if - not defined */

	}
	/** End function - construct */


	/**
	 * Enqueue Plugin Scripts and Styles
	 *
	 * Adds plugin stylesheet and allows for custom stylesheet to be added by
	 * end-user.
	 *
	 * @package    BNS_Login
	 * @since      1.6
	 *
	 * @uses       BNS_Login::plugin_data
	 * @uses       plugin_dir_path
	 * @uses       plugin_dir_url
	 * @uses       wp_enqueue_style
	 *
	 * @version    1.8
	 * Add conditional check for custom stylesheet
	 *
	 * @version    2.1
	 * @date       May 2, 2013
	 * Added plugin version data dynamically to enqueue calls
	 * Added (enqueued) 'BNS Login Form Style' to style the form
	 *
	 * @version    2.3.3
	 * @date       March 29, 2014
	 * Extracted `plugin_data` into its own method
	 *
	 * @version    2.4
	 * @date       October 4, 2014
	 * Added `dashicons` dependency to main style sheet providing access to the icons
	 * Implement the use of `../wp-content/bns-customs/` for customizations
	 */
	function scripts_and_styles() {

		$bns_login_data = $this->plugin_data();

		/* Enqueue Styles */
		wp_enqueue_style( 'BNS-Login-Style', plugin_dir_url( __FILE__ ) . 'bns-login-style.css', array( 'dashicons' ), $bns_login_data['Version'], 'screen' );
		wp_enqueue_style( 'BNS-Login-Form-Style', plugin_dir_url( __FILE__ ) . 'bns-login-form-style.css', array(), $bns_login_data['Version'], 'screen' );

		/**
		 * Add custom styles
		 * NB: This location will be over-written when the plugin is updated due
		 * to core WordPress functionality. Please place your custom stylesheet
		 * in the /wp-content/bns-customs/ folder (you may need to created this
		 * via this FTP) to better future proof your customizations.
		 * @Deprecated  2.4
		 */
		if ( is_readable( plugin_dir_path( __FILE__ ) . 'bns-login-custom-style.css' ) ) {
			wp_enqueue_style( 'BNS-Login-Custom-Style', plugin_dir_url( __FILE__ ) . 'bns-login-custom-style.css', array(), $bns_login_data['Version'], 'screen' );
		}
		/** End if - is readable */

		/** Read the custom stylesheet from ../wp-content/bns-customs/ */
		if ( is_readable( BNS_CUSTOM_PATH . 'bns-login-custom-style.css' ) ) {
			wp_enqueue_style( 'BNS-Login-Custom-Style', BNS_CUSTOM_URL . 'bns-login-custom-style.css', array(), $bns_login_data['Version'], 'screen' );
		}
		/** End if - is readable */

	}
	/** End function - scripts and styles */


	/**
	 * BNS Login Main
	 * Main function that will accept parameters
	 *
	 * @package     BNS_Login
	 * @since       0.1
	 *
	 * @uses        add_filter
	 * @uses        apply_filters
	 * @uses        esc_attr
	 * @uses        esc_html
	 * @uses        esc_url
	 * @uses        get_current_site
	 * @uses        home_url
	 * @uses        is_multisite
	 * @uses        is_ssl
	 * @uses        is_user_logged_in
	 * @uses        wp_logout_url
	 * @uses        wp_parse_args
	 * @uses        wp_register
	 *
	 * @internal    Opus Primus (v1.3+) contains code to activate Dashicons
	 *
	 * @return  mixed|string|void
	 *
	 * @version     2.0
	 * @date        November 19, 2012
	 * Add wrapping classes around output elements
	 * Refactored to use filters instead of array elements
	 *
	 * @version     2.0.1
	 * @date        February 2, 2013
	 * Changed Multisite conditional to use `is_multisite`
	 *
	 * @version     2.4
	 * @date        October 5, 2014
	 * Added filter toggle to use `dashicons` instead of text
	 * Added some basic sanitization to URL components and structures
	 * Added `is_ssl()` to detect correct protocol for logout return URL
	 */
	function bns_login_main() {
		/** Initialize $output - start with an empty string */
		$output = '';
		/**
		 * Defaults values:
		 * @var $login          string - anchor text for log in link
		 * @var $after_login    string - user is logged in message
		 * @var $logout         string - anchor text for log out link
		 * @var $goto           string - anchor text linking to "Dashboard"
		 * @var $separator      string - characters used to separate link/message texts
		 * @var $sep            string - $separator wrapper for styling purposes, etc. - just in case ...
		 *
		 * @internal *_title is used to preserve the default text strings if dashicons are used
		 */
		$login        = apply_filters( 'bns_login_here', sprintf( __( 'Log in here!', 'bns-login' ) ) );
		$login_title  = esc_attr( $login );
		$after_login  = apply_filters( 'bns_login_after_login', sprintf( __( 'You are logged in!', 'bns-login' ) ) );
		$logout       = apply_filters( 'bns_login_logout', sprintf( __( 'Logout', 'bns-login' ) ) );
		$logout_title = esc_attr( $logout );
		$goto         = apply_filters( 'bns_login_goto', sprintf( __( 'Go to Dashboard', 'bns-login' ) ) );
		$goto_title   = esc_attr( $goto );
		$separator    = apply_filters( 'bns_login_separator', sprintf( __( ' &deg;&deg; ' ) ) );
		$sep          = apply_filters( 'bns_login_sep', '<span class="bns-login-separator">' . $separator . '</span>' );
		$login_url    = esc_url( apply_filters( 'bns_login_url', home_url( '/wp-admin/' ) ) );

		/** @var bool $dashed_set - intended as boolean toggle to use dashicons instead of text */
		$dashed_set = apply_filters( 'bns_login_dashed_set', false );
		if ( $dashed_set ) {
			$login       = apply_filters( 'bns_login_here', '<span class="dashicons dashicons-lock"></span>' );
			$after_login = apply_filters( 'bns_login_after_login', '<span class="dashicons dashicons-visibility"></span>' );
			$logout      = apply_filters( 'bns_login_logout', '<span class="dashicons dashicons-dismiss"></span>' );
			$goto        = apply_filters( 'bns_login_goto', '<span class="dashicons dashicons-dashboard"></span>' );
			$sep         = apply_filters( 'bns_login_sep', ' ' );
		}
		/** End if - use dashicons */

		/** The real work gets done next ...  */
		if ( is_user_logged_in() ) {
			$output .= '<div id="bns-logged-in" class="bns-login">' . '<span class="bns-after-login">' . $after_login . '</span>' . $sep;
			/** Multisite - logout returns to current site home page */
			if ( is_multisite() ) {
				$current_site = get_current_site();
				$protocol     = is_ssl() ? 'https://' : 'http://';
				$home_domain  = $protocol . $current_site->domain . $current_site->path;
				$logout_url   = wp_logout_url( $home_domain );
			} else {
				$logout_url = wp_logout_url( home_url() );
			}
			/** End if - is multisite */
			$output .= '<a class="bns-logout-url" href="' . $logout_url . '" title="' . $logout_title . '">' . $logout . '</a>' . $sep;
			$output .= '<a class="bns-login-url" href="' . $login_url . '" title="' . $goto_title . '">' . $goto . '</a></div>';
		} else {
			/** Display login message */
			$output .= '<div id="bns-logged-out" class="bns-login">';
			$output .= '<a class="bns-login-url" href="' . $login_url . '" title="' . $login_title . '">' . $login . '</a>';

			/** Show register link if new users allowed in site settings */
			if ( $dashed_set ) {
				add_filter( 'register', array(
					$this,
					'dashicons_register_link'
				) );
			}
			$output .= wp_register( $sep, '', false );

			$output .= '</div>';
		}

		/** End if - is user logged in */

		return $output;

	}
	/** End function - bns login main */


	/**
	 * Dashicons Register Link
	 * Changes the `Register` test to a `key` icon
	 *
	 * @package BNS_Login
	 * @since   2.4
	 *
	 * @uses    admin_url
	 * @uses    apply_filters
	 * @uses    esc_url
	 * @uses    get_option
	 * @uses    is_user_logged_in
	 * @uses    wp_registration_url
	 *
	 * @return string
	 */
	function dashicons_register_link() {
		if ( ! is_user_logged_in() ) {
			if ( get_option( 'users_can_register' ) ) {
				$link = apply_filters( 'bns_login_sep', ' ' ) . '<a href="' . esc_url( wp_registration_url() ) . '">' . '<span class="dashicons dashicons-admin-network"></span>' . '</a>';
			} else {
				$link = '';
			}
		} else {
			$link = apply_filters( 'bns_login_sep', ' ' ) . '<a href="' . admin_url() . '">' . __( 'Site Admin' ) . '</a>';
		}

		return $link;
	}
	/** End function - dashicons register link */


	/**
	 * Add BNS Login
	 * Echos the Main function output
	 *
	 * @package BNS_Login
	 * @since   1.0
	 *
	 * @uses    bns_login_main
	 * @uses    do_action
	 *
	 * @version 2.0
	 * @date    November 6, 2012
	 * Removed parameters - see changes to `bns_login_main`
	 * Add empty hooks before and after main output
	 */
	function bns_login_output() {
		/** Add empty hook before output */
		do_action( 'bns_login_before_output' );

		/** Output to screen */
		echo $this->bns_login_main();

		/** Add empty hook after output */
		do_action( 'bns_login_after_output' );

	}
	/** End function - bns login output */


	/**
	 * Jetpack Infinite Scroll Compatibility
	 * Using a similar default $credits string as the Jetpack plugin this adds
	 * the BNS Login output to the Infinite Scroll footer display
	 *
	 * @package BNS_Login
	 * @since   2.3
	 *
	 * @uses    BNS_Login::bns_login_main
	 * @uses    __
	 * @uses    wp_get_theme
	 *
	 * @return  string
	 *
	 * @version 2.3.1
	 * @date    August 15, 2013
	 * Added specific id wrappers for the credit details
	 * Linked Theme reference to Theme URI
	 */
	function jetpack_infinite_scroll_compatibility() {

		$credits = '<div id="infinite-scroll-wordpress-credits">';
		$credits .= '<a href="http://wordpress.org/" rel="generator">' . __( 'Proudly powered by WordPress', 'bns_login' ) . '</a> ';
		$credits .= sprintf( '<span id="infinite-scroll-theme-credits">' . '<a href="' . wp_get_theme()->get( 'ThemeURI' ) . '">' . __( 'Theme: %1$s.', 'bns-login' ) . '</a></span>', wp_get_theme() );
		$credits .= '</div><!-- #infinite-scroll-wordpress-credits -->';
		$credits .= $this->bns_login_main();

		return $credits;

	}
	/** End function - jetpack infinite scroll compatibility */


	/**
	 * BNS Login Form
	 * Borrowed from the core login form and used with the shortcode 'bns_login'
	 * This allows for the 'bns_login' shortcode to accept all of the parameters
	 * of the `wp_login_form` function as the shortcode attributes.
	 *
	 * @package  BNS_Login
	 * @since    2.1
	 *
	 * @param   $args
	 *
	 * @internal $defaults copied from codex '$args' entry
	 * @link     http://codex.wordpress.org/Function_Reference/wp_login_form
	 *
	 * @uses     __
	 * @uses     shortcode_atts
	 * @uses     site_url
	 * @uses     wp_login_form
	 * @uses     wp_parse_args
	 *
	 * @return  string - the login form
	 *
	 * @version  2.1.1
	 * @date     May 8, 2013
	 * Correct default redirect URL to point to 'wp-admin'
	 *
	 * @version  2.2
	 * @date     July 28, 2013
	 * Added dynamic filter parameter `bns_login`
	 */
	function bns_login_form( $args ) {

		$defaults = shortcode_atts(
			array(
				'echo'           => false,
				'redirect'       => site_url( '/wp-admin/' ),
				'form_id'        => 'loginform',
				'label_username' => __( 'Username', 'bns-login' ),
				'label_password' => __( 'Password', 'bns-login' ),
				'label_remember' => __( 'Remember Me', 'bns-login' ),
				'label_log_in'   => __( 'Log In', 'bns-login' ),
				'id_username'    => 'user_login',
				'id_password'    => 'user_pass',
				'id_remember'    => 'rememberme',
				'id_submit'      => 'wp-submit',
				'remember'       => true,
				'value_username' => null,
				'value_remember' => false
			), $args, 'bns_login'
		);

		$login_args = wp_parse_args( $args, $defaults );

		return wp_login_form( $login_args );

	}
	/** End function - bns login form */


	/**
	 * Plugin Data
	 * Returns the plugin header data as an array
	 *
	 * @package    BNS_Login
	 * @since      2.3.3
	 *
	 * @uses       get_plugin_data
	 *
	 * @return array
	 */
	function plugin_data() {
		/** Call the wp-admin plugin code */
		require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		/** @var $plugin_data - holds the plugin header data */
		$plugin_data = get_plugin_data( __FILE__ );

		return $plugin_data;
	}
	/** End function - plugin data */


	/**
	 * BNSFC Plugin Meta
	 * Adds additional links to plugin meta links
	 *
	 * @package    BNS_Login
	 * @since      2.3.3
	 *
	 * @uses       __
	 * @uses       plugin_basename
	 *
	 * @param   $links
	 * @param   $file
	 *
	 * @return  array $links
	 */
	function bns_login_plugin_meta( $links, $file ) {

		$plugin_file = plugin_basename( __FILE__ );

		if ( $file == $plugin_file ) {

			$links = array_merge(
				$links, array(
					'fork_link'    => '<a href="https://github.com/Cais/BNS-Login">' . __( 'Fork on GitHub', 'bns-login' ) . '</a>',
					'wish_link'    => '<a href="http://www.amazon.ca/registry/wishlist/2NNNE1PAQIRUL">' . __( 'Grant a wish?', 'bns-login' ) . '</a>',
					'support_link' => '<a href="http://wordpress.org/support/plugin/bns-login">' . __( 'WordPress Support Forums', 'bns-login' ) . '</a>'
				)
			);

		}

		/** End if - file is the same as plugin */

		return $links;

	}
	/** End function - plugin meta */


}

/** End class - BNS Login */

/** @var $bns_login - new instance of the BNS Login class */
$bns_login = new BNS_Login();