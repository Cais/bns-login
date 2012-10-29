<?php
/*
Plugin Name: BNS Login
Plugin URI: http://buynowshop.com/plugins/bns-login/
Description: A simple plugin providing a link to the dashboard; and, a method to log in and out of your blog in the footer of the theme. This is ideal for those not wanting to use the meta widget/code links.
Version: 2.0
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
 * @version     2.0
 * @author      Edward Caissie <edward.caissie@gmail.com>
 * @copyright   Copyright (c) 2009-2012, Edward Caissie
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
 * @version 2.0
 * @date    October 29, 2012
 * Remove `load_textdomain` as redundant
 * Add Shortcode functionality to text widgets
 * Add Shortcode for this plugin
 * Refactored to use hooks instead of array elements
 */

class BNS_Login {
    /**
     * Constructor
     *
     * @package BNS_Login
     * @since   1.9
     *
     * @uses    add_action
     * @uses    add_shortcode
     */
    function __construct(){

        /**
         * Check installed WordPress version for compatibility
         *
         * @package     BNS_Login
         * @since       1.0
         *
         * @uses        (global) wp_version
         *
         * @internal    Version 3.0 being used in reference to home_url()
         *
         * @version     1.8
         * Last revised November 22, 2011
         * Re-write to be i18n compatible
         */
        global $wp_version;
        $exit_ver_msg = __( 'BNS Login requires a minimum of WordPress 3.0, <a href="http://codex.wordpress.org/Upgrading_WordPress">Please Update!</a>', 'bns-login' );
        if ( version_compare( $wp_version, "3.0", "<" ) ) {
            exit ( $exit_ver_msg );
        }

        /** Enqueue Scripts and Styles */
        add_action( 'wp_enqueue_scripts', array( $this, 'Scripts_and_Styles' ) );

        /** Add BNS Login to Footer */
        add_action( 'wp_footer', array( $this, 'add_bns_login' ) );

        /** Add Shortcode functionality to text widgets */
        add_action( 'widget_text', 'do_shortcode' );
        /** Add Shortcode for this plugin */
        add_shortcode( 'bns_login', array( $this, 'add_bns_login_shortcode' ) );

    }

    /**
     * Enqueue Plugin Scripts and Styles
     *
     * Adds plugin stylesheet and allows for custom stylesheet to be added by end-user.
     *
     * @package BNS_Login
     * @since   1.6
     *
     * @uses    plugin_dir_path
     * @uses    plugin_dir_url
     * @uses    wp_enqueue_style
     *
     * @version 1.8
     * Add conditional check for custom stylesheet
     */
    function Scripts_and_Styles() {
        /* Enqueue Scripts */
        /* Enqueue Styles */
        wp_enqueue_style( 'BNS-Login-Style', plugin_dir_url( __FILE__ ) . 'bns-login-style.css', array(), '1.8', 'screen' );
        if ( is_readable( plugin_dir_path( __FILE__ ) . 'bns-login-custom-style.css' ) ) {
            wp_enqueue_style( 'BNS-Login-Custom-Style', plugin_dir_url( __FILE__ ) . 'bns-login-custom-style.css', array(), '1.8', 'screen' );
        }
    }

    /**
     * BNS Login Main
     * Main function that will accept parameters
     *
     * @package BNS_Login
     * @since   0.1
     *
     * @uses    apply_filters
     * @uses    get_current_site
     * @uses    home_url
     * @uses    is_user_logged_in
     * @uses    wp_logout_url
     * @uses    wp_parse_args
     * @uses    wp_register
     *
     * @return  mixed|string|void
     *
     * @version 2.0
     * @date    October 29, 2012
     * Refactored to use hooks instead of array elements
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
         */
        $login          = apply_filters( 'bns_login_here',          sprintf( __( 'Log in here!', 'bns-login' ) ) );
        $after_login    = apply_filters( 'bns_login_after_login',   sprintf( __( 'You are logged in!', 'bns-login' ) ) );
        $logout         = apply_filters( 'bns_login_logout',        sprintf( __( 'Logout', 'bns-login' ) ) );
        $goto           = apply_filters( 'bns_login_goto',          sprintf( __( 'Go to Dashboard', 'bns-login' ) ) );
        $separator      = apply_filters( 'bns_login_separator',     sprintf( __( ' &deg;&deg; ' ) ) );
        $sep            = apply_filters( 'bns_login_sep',           '<span class="bns-login-separator">' . $separator . '</span>' );

        /** The real work gets done next ...  */
        $login_url = home_url( '/wp-admin/' );
        if ( is_user_logged_in() ) {
            $output .= '<div id="bns-logged-in" class="bns-login">' . $after_login . $sep;
            /** Multisite - logout returns to Multisite main domain page */
            if ( function_exists( 'get_current_site' ) ) {
                $current_site = get_current_site();
                /** @noinspection PhpUndefinedFieldInspection */
                $home_domain = 'http://' . $current_site->domain . $current_site->path;
                $logout_url = wp_logout_url( $home_domain );
            } else {
                $logout_url = wp_logout_url( home_url() );
            }
            $output .= '<a href="' . $logout_url . '" title="' . $logout . '">' . $logout . '</a>' . $sep;
            $output .= '<a href="' . $login_url . '" title="' . $goto . '">' . $goto . '</a></div>';
        } else {
            /** if user is not logged in display login; or, register if allowed */
            $output .= '<div id="bns-logged-out" class="bns-login">';
            $output .= '<a href="' . $login_url . '" title="' . $login . '">' . $login . '</a>';
            $output .= wp_register( $sep, '', false );
            $output .= '</div>';
        }

        return apply_filters( 'bns_login_main', $output );

    }

    /**
     * Add BNS Login
     * Echos the Main function output
     *
     * @package BNS_Login
     * @since   1.0
     *
     * @uses    bns_login_main
     *
     * @version 2.0
     * @date    October 29, 2012
     * Removed parameters - see changes to `bns_login_main`
     */
    function add_bns_login() {
        /** BNS_Login pre-populated with empty parameters as guidelines */
        echo $this->bns_login_main();
    }

    /**
     * Add BNS Login Shortcode
     * Returns `bns_login_main` as the shortcode output
     *
     * @package BNS_login
     * @since   2.0
     *
     * @return mixed|string|void
     */
    function add_bns_login_shortcode() {
        /** Return the Main function rather than echo */
        return $this->bns_login_main();
    }

}
$bns_login = new BNS_Login();