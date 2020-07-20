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
 * @phpcs:disable WordPress.WP.I18n.NonSingularStringLiteralDomain
 */

namespace BadgeFactor2\Admin;

/**
 * CMB2 Add-Ons Field.
 */
class CMB2_Field_Addons {

	/**
	 * Init Hooks.
	 *
	 * @return void
	 */
	public static function init_hooks() {
		add_filter( 'cmb2_render_addons', array( self::class, 'render_addons' ), 10, 5 );
	}


	/**
	 * Render Add-Ons.
	 *
	 * @param CMB2_Field $field Field.
	 * @param string     $field_escaped_value Field escaped value.
	 * @param string     $field_object_id Field object id.
	 * @param string     $field_object_type Field object type.
	 * @param CMB2_Types $field_type_object Field Type Object.
	 * @return void
	 */
	public static function render_addons( $field, $field_escaped_value, $field_object_id, $field_object_type, $field_type_object ) {
		$addons = array(
			'bf2-courses'     => array(
				'title' => __( 'BadgeFactor2 Courses', BF2_DATA['TextDomain'] ),
				'url'   => 'https://github.com/ctrlwebinc/bf2-courses',
			),
			'bf2-woocommerce' => array(
				'title' => __( 'BadgeFactor2 WooCommerce', BF2_DATA['TextDomain'] ),
				'url'   => 'https://github.com/ctrlwebinc/bf2-woocommerce',
			),
		);

		echo '<table class="addons"><tbody>';

		foreach ( $addons as $name => $addon ) {

			echo '<tr class="addon">';
			echo sprintf( '<td class="addon-name">%s</td>', $addon['title'] );
			echo sprintf( '<td class="addon-status %s">%s</td>', self::plugin_status( $name ), self::plugin_status( $name, $addon['url'] ) );
			echo '</tr>';
		}

		echo '</tbody></table>';
	}


	/**
	 * Plugin Status.
	 *
	 * @param string $plugin_name Plugin name.
	 * @param string $url URL.
	 * @return string
	 */
	private static function plugin_status( $plugin_name, $url = null ) {
		$is_active    = is_plugin_active( sprintf( '%s/%s.php', $plugin_name, $plugin_name ) );
		$is_installed = file_exists( sprintf( '%s/%s.php', $plugin_name, $plugin_name ) );
		if ( $is_active ) {
			return $url ? __( 'Active', BF2_DATA['TextDomain'] ) : 'active';
		}
		if ( $is_installed ) {
			return $url ? __( 'Inactive', BF2_DATA['TextDomain'] ) : 'inactive';
		}
		return $url ? sprintf( '<a target="_blank" href="%s">%s</a>', $url, __( 'Get', BF2_DATA['TextDomain'] ) ) : 'not-installed';
	}


}
