<?php
/**
 * Badge Factor 2
 * Copyright (C) 2019 ctrlweb
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * @package Badge_Factor_2
 */

namespace BadgeFactor2;


/**
 * Request Class.
 */
class AssertionPrivacy {

    private static $table_name_suffix = 'bf2_assertion_privacy_flags';

    private static $visibility_nonce_base = 'bf2-assertion-privacy-nonce';

    public static $visibility_toggle_action = 'bf2_toggle_assertion_visibility';

    private static function get_table_name() {
        global $wpdb;

        return $wpdb->prefix . self::$table_name_suffix;
    }

    public static function init_hooks() {
		add_action( 'init', array( self::class, 'init' ) );
        add_action( 'init', array( self::class, 'enqueue_scripts') );
	}

    public static function init() {
        add_action('wp_ajax_' . self::$visibility_toggle_action, array( self::class, 'handle_ajax_visibility_toggle'));
    }

    public static function bf2_install() {

    }

    

    public static function create_table() {

	global $wpdb;

	$table_name = self::get_table_name();
	
	$charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        badge_class_slug varchar(100) NOT NULL DEFAULT '',
        user_id bigint(20) unsigned NOT NULL,
        KEY (badge_class_slug),
        UNIQUE KEY badge_class_slug_user_id_composite (badge_class_slug,user_id),
        KEY user_id (user_id)
      ) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

    }

    public static function has_privacy_flag( $badge_slug, $user_id) {
        global $wpdb;

        $table_name = self::get_table_name();

        $query = "SELECT COUNT(*) FROM $table_name 
        WHERE badge_class_slug = %s AND user_id = %d";

        $flag_count = $wpdb->get_var( $wpdb->prepare($query,[$badge_slug,$user_id]) );

        return ( $flag_count > 0);
    }

    public static function toggle_privacy_flag( $badge_slug, $user_id) {
        global $wpdb;

        $current_state = self::has_privacy_flag( $badge_slug, $user_id);

        if (true == $current_state) {
            // Remove flag
            $wpdb->delete(self::get_table_name(),['badge_class_slug' => $badge_slug, 'user_id' => $user_id], ['%s','%d']);
        } else {
            // Insert flag
            $wpdb->insert(self::get_table_name(), ['badge_class_slug' => $badge_slug, 'user_id' => $user_id], ['%s','%d']);
        }

        return !$current_state;
    }

    public static function generate_ajax_callback_parameters( $badge_slug ) {
        $callback_parameters['nonce'] = wp_create_nonce(self::$visibility_nonce_base);
        $callback_parameters['link'] = admin_url('admin-ajax.php?action=' . self::$visibility_toggle_action . '&badge_slug=' . $badge_slug . '&nonce='. $callback_parameters['nonce']);
        $callback_parameters['ajax_endpoint'] = admin_url('admin-ajax.php');
        $callback_parameters['ajax_action'] = self::$visibility_toggle_action;
        $callback_parameter['badge_slug'] = $badge_slug;

        return compact('callback_parameters');
    }

    public static function handle_ajax_visibility_toggle() {//die(json_encode(check_ajax_referer($_REQUEST['nonce'], self::$visibility_nonce_base, false)));
/*         if ( !wp_verify_nonce( $_REQUEST['nonce'], self::$visibility_nonce_base)) {
            exit('Nonce verification failed');
        } */

        if ( !isset($_REQUEST['badge_slug']) || '' == $_REQUEST['badge_slug']) {
            exit('badge_slug missing');
        }
      
         $new_state = self::toggle_privacy_flag( $_REQUEST['badge_slug'], get_current_user_id());

         $result = ['has_privacy_flag' => $new_state];

         if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $result = json_encode($result);
            echo $result;
         }
         else {
            header("Location: ".$_SERVER["HTTP_REFERER"]);
         }
      
         die();
      
    }

    public static function enqueue_scripts() {
        wp_register_script( 'bf2-privacy-js', BF2_BASEURL . 'assets/js/privacy.js', array( 'jquery' ), BF2_DATA['Version'], true );

        wp_localize_script( 'bf2-privacy-js', 'bf2_privacy_ajax', ['ajax_endpoint' => admin_url('admin-ajax.php'), 'ajax_action' => self::$visibility_toggle_action]);

        wp_enqueue_script( 'bf2-privacy-js' );
    }
}
