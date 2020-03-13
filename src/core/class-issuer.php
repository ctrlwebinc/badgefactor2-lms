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
 * Issuer Class.
 */
class Issuer {

	/**
	 * Issuer Init.
	 *
	 * @return void
	 */
	public static function init_hooks() {
		add_action( 'cmb2_admin_init', array( Issuer::class, 'cmb2_admin_init' ) );
	}

	/**
	 * Init hook.
	 *
	 * @return void
	 */
	public static function init() {
		// TODO.
	}

	/**
	 * CMB2 Admin Init hook.
	 *
	 * @return void
	 */
	public static function cmb2_admin_init() {
		$cmb = new_cmb2_box(
			array(
				'id'           => 'issuer_fields',
				'title'        => __( 'Issuer Fields', 'badgefactor2' ),
				'object_types' => array( 'issuer' ),
				'context'      => 'normal',
				'priority'     => 'high',
				'show_names'   => true, // Show field names on the left.
				// 'cmb_styles' => false, // false to disable the CMB stylesheet.
				// 'closed'     => true, // Keep the metabox closed by default.
			)
		);

		$cmb->add_field(
			array(
				'name' => 'email',
				'desc' => '',
				'id'   => '_email',
				'type' => 'text_emails',

			)
		);
	}
}
