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

namespace BadgeFactor2\Roles;

/**
 * Approver user helper functions.
 */
class Approver {


	/**
	 * Init hooks.
	 *
	 * @return void
	 */
	public static function init_hooks() {
		add_action( 'init', array( Approver::class, 'add_custom_role_and_capabilities' ), 11 );
	}


	/**
	 * Adds custom roles and capabilities requires by Badge Factor 2.
	 *
	 * @return void
	 */
	public static function add_custom_role_and_capabilities() {
		$approver = add_role(
			'approver',
			__( 'Approver', BF2_DATA['TextDomain'] ),
			array(
				'read'                 => true,
				'edit_posts'           => true,
				'edit_published_posts' => true,
				// FIXME List must be validated at a later development stage.
			)
		);

		if ( null !== $approver ) {
			$approver->add_cap( 'badgefactor2_approve_badge_requests' );
		}
	}


	/**
	 * Get select-formatted options.
	 *
	 * @return array
	 */
	public static function select_options() {
		$args      = array(
			'role'    => 'approver',
			'orderby' => 'user_nicename',
			'order'   => 'ASC',
		);
		$approvers = get_users( $args );

		$post_options = array();
		if ( $approvers ) {
			foreach ( $approvers as $approver ) {
				$post_options[ $approver->ID ] = $approver->user_nicename;
			}
		}

		return $post_options;
	}
}
