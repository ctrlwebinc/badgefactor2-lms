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
 *
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound
 */

namespace BadgeFactor2\Helpers;


/**
 * Text helper class.
 */
class BuddypressXProfile {

    /**
	 * Init tasks
	 *
	 * @return void
	 */
	public static function init_hooks() {
	    /* add this in your theme if you want to show this permanently */
		/*add_action( 'bp_init', array( self::class, 'bp_xprofile_default_avatar_field' ) );*/
    }
    
    /**
     * Creates a select box for default avatar
     */
    public static function bp_xprofile_default_avatar_field() {
	
        $country_list_id = xprofile_get_field_id_from_name('Country');
        
        $country_list_args = array(
            'field_group_id' => 1,
            'type' => 'selectbox',
            'name' => 'Country',
            'description' => '<b>Please select your country</b>',
            // 'is_required' => true,
            'can_delete' => true,
            'order_by' => 'default'
        );
        if ( NULL == $country_list_id ) {
            $country_list_id = xprofile_insert_field($country_list_args);
        }
        
        if ($country_list_id) {
            $countries = array('USA','Germany', 'Mada'); // Google for “country list php” and replace this one
            foreach ($countries as $i => $country) {
                $c = self::get_bp_xprofile_field_id_from_name($country);
                if (is_null($c)) {
                    xprofile_insert_field(array(
                        'field_group_id' => 1,
                        'parent_id' => $country_list_id,
                        'type' => 'selectbox', // it is 'selectbox' not 'option'
                        'name' => $country,
                        'option_order' => $i+1
                    ));
                }
            }
        }
    }

    public static function get_bp_xprofile_field_id_from_name( $field_name ) {
        global $wpdb;

		$bp = buddypress();

        if ( empty( $bp->profile->table_name_fields ) || empty( $field_name ) ) {
			return false;
		}

		$id = bp_core_get_incremented_cache( $field_name, 'bp_xprofile_fields_by_name' );
		if ( false === $id ) {
			$sql = $wpdb->prepare( "SELECT id FROM {$bp->profile->table_name_fields} WHERE name = %s", $field_name );
			$id = $wpdb->get_var( $sql );
			bp_core_set_incremented_cache( $field_name, 'bp_xprofile_fields_by_name', $id );
		}

        return is_numeric( $id ) ? (int) $id : $id;
    }
}
