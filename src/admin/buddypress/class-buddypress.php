<?php
/**
 * Badge Factor 2
 * Copyright (C) 2021 ctrlweb
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
 * @package Badge_Factor_2_Certificates
 *
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.VariableConstantNameFound
 * @phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
 * @phpcs:disable WordPress.WP.I18n.NonSingularStringLiteralDomain
 */

namespace BadgeFactor2;

/**
 * BuddyPress class.
 */
class BuddyPress {

	/**
	 * Init Hooks.
	 *
	 * @return void
	 */
	public static function init_hooks() {
		add_action( 'bp_register_admin_settings', array( self::class, 'bp_admin_seetings' ), 20 );
	}

	/**
	 * BuddyPress Admin Settings.
	 *
	 * @return void
	 */
	public static function bp_admin_seetings() {

		add_settings_section(
			'bp_bf2_settings',
			__( 'Badge Factor 2', BF2_DATA['TextDomain'] ),
			array( self::class, 'section_description' ),
			'buddypress'
		);

		add_settings_field(
			'bp-bf2-display-badges-only',
			__( 'Display Badges only?', BF2_DATA['TextDomain'] ),
			array( self::class, 'display_badges_only' ),
			'buddypress',
			'bp_bf2_settings'
		);

		register_setting(
			'buddypress',
			'bp-bf2-display-badges-only',
			array( 'type' => 'boolean' )
		);

	}

	/**
	 * Section description hookup.
	 *
	 * @return void
	 */
	public static function section_description() {
		echo sprintf( '<p class="description">%s</p>', __( 'Here you can ajust how Badge Factor 2 uses BuddyPress.', BF2_DATA['TextDomain'] ) );

	}

	/**
	 * Display badges only field hookup.
	 */
	public static function display_badges_only() {
		$display_badges_only = bp_get_option( 'bp-bf2-display-badges-only', true );
		echo sprintf( '<input type="checkbox" id="bp-bf2-display-badges-only" name="bp-bf2-display-badges-only" value="1" %s>', $display_badges_only ? 'checked' : '' );
		echo sprintf( '<label for="bp-bf2-display-badges-only">%s</label', __( 'If you check this box, BuddyPress member profile tabs will be overriden, and only the Badges portfolio will be shown.', BF2_DATA['TextDomain'] ) );
	}

}
