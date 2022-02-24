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

    private static function get_table_name() {
        global $wpdb;

        return $wpdb->prefix . self::$table_name_suffix;
    }

    public static function init_hooks() {
		add_action( 'init', array( self::class, 'init' ) );

	}

    public static function init() {

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
        PRIMARY KEY  (badge_class_slug),
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
}
