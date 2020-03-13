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
 * Email Class.
 */
class Email {

	/**
	 * Email Init.
	 *
	 * @return void
	 */
	public static function init_hooks() {
		add_action( 'cmb2_admin_init', array( Email::class, 'cmb2_admin_init' ) );
	}

	/**
	 * CMB2 Admin Init hook.
	 *
	 * @return void
	 */
	public static function cmb2_admin_init() {

		$cmb = new_cmb2_box(
			array(
				'id'           => 'badgefactor2_emails_page',
				'title'        => esc_html__( 'Verified Emails', 'badgefactor2' ),
				'object_types' => array( 'options-page' ),
				'option_key'   => 'badgefactor2_verified_emails', // The option key and admin menu page slug.
				'parent_slug'  => 'badgefactor2', // Make options page a submenu item of the themes menu.
				// 'capability'      => 'manage_options', // Cap required to view options-page.
				// 'position'        => 1, // Menu position. Only applicable if 'parent_slug' is left empty.
				// 'admin_menu_hook' => 'network_admin_menu', // 'network_admin_menu' to add network-level options page.
				// 'display_cb'      => false, // Override the options-page form output (CMB2_Hookup::options_page_output()).
				// 'save_button'     => esc_html__( 'Save Theme Options', 'cmb2' ), // The text for the options-page save button. Defaults to 'Save'.
				// 'disable_settings_errors' => true, // On settings pages (not options-general.php sub-pages), allows disabling.
				// 'message_cb'      => 'yourprefix_options_page_message_callback'.
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
