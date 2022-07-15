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
 * @package Badge_Factor_2
 *
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound 
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.VariableConstantNameFound
 */

namespace BadgeFactor2\Helpers;

/**
 * BuddyPress helper class.
 */
class BuddyPress {

	/**
	 * Checks whether or not BuddyPress is active.
	 *
	 * @return boolean
	 */
	public static function is_active() {
		return function_exists( 'bp_is_active' );
	}


	/**
	 * Returns Member page name.
	 *
	 * @return string
	 */
	public static function get_members_page_name() {
		return bp_core_get_directory_pages()->members->name;
	}

	public static function user_menu_items() {
		
		$login_slug = 'connexion';
		$registration_slug = 'inscription';
		$login_page_title = 'Connexion';
		$registration_page_title = 'Inscription'; 
		$current_user = wp_get_current_user();

		$login_permalink = site_url( $login_slug ) . '/';
		$registration_permalink = site_url( $registration_slug ) . '/';

		$login_page = get_page_by_path( $login_slug );
		if ( !is_null( $login_page ) ) {
			$login_page_title = $login_page->post_title;
		}

		$registration_page = get_page_by_path( $registration_slug );
		if ( !is_null( $registration_page ) ) {
			$registration_page_title = $registration_page->post_title;
		}

		// Handles permalink with WPML
		if ( class_exists( 'SitePress' ) ) {
			$my_current_lang = apply_filters( 'wpml_current_language', NULL );
			
			$login_page = get_page_by_path( $login_slug );
			if ( !is_null( $login_page ) ) {
				$translated_login_page_id = apply_filters( 'wpml_object_id', $login_page->ID, 'page', FALSE, $my_current_lang );
				$login_permalink = get_permalink( $translated_login_page_id );
				$translated_login_page = get_post( $translated_login_page_id );
				if ( !is_null( $translated_login_page ) ) 
					$login_page_title = $translated_login_page->post_title;
			}

			$registration_page = get_page_by_path( $registration_slug );
			if ( !is_null( $registration_page ) ) {
				$translated_registration_page_id = apply_filters( 'wpml_object_id', $registration_page->ID, 'page', FALSE, $my_current_lang );
				$registration_permalink = get_permalink( $translated_registration_page_id );
				$translated_registration_page = get_post( $translated_registration_page_id );
				if ( !is_null( $translated_registration_page ) ) 
					$registration_page_title = $translated_registration_page->post_title;
			}
		}
		
		$item = '';

		if ( ! empty( $current_user->user_login ) ) {
			$avatar = \bp_core_fetch_avatar(
				array(
					'item_id' => $current_user->ID,
					'email'   => $current_user->user_email,
					'type'    => 'full',
					'height'  => 100,
					'width'   => 100,
				)
			);
			
			$display_name = ( $current_user->first_name != '' ) ? $current_user->first_name : $current_user->last_name;
			
			$item .= '<li class="menu-item bf2-user-menu">';
			$item .=     '<a href="#">';
			$item .=	     $avatar;
			$item .=		 $display_name;
			$item .=     '</a>';
			$item .=	 '<ul class="dropdown">';
			$item .=	     '<li><a href="' . bp_loggedin_user_domain( get_current_user_id() ) . '" title="' . __( 'My account', BF2_DATA['TextDomain'] ) . '"><i class="fa fa-user" aria-hidden="true"></i> ' . __( 'My account', BF2_DATA['TextDomain'] ) . '</a></li>';
			$item .=		 '<li><a class="header-main-top-sublist-link" href="' . wp_logout_url( '/' ) . '" title="' . __( 'Logout', BF2_DATA['TextDomain'] ) . '"><i class="fa fa-sign-out"></i> ' . __( 'Logout', BF2_DATA['TextDomain'] ) . '</a></li>';
			$item .=	 '</ul>';
			$item .= '</li>';
		} else {
			$item .= '<li class="menu-item">';
			$item .=     '<a href="' . $login_permalink . '">' . $login_page_title . '</a>';
			$item .= '</li>';
			$item .= '<li class="menu-item">';
			$item .=     '<a href="' . $registration_permalink . '">' . $registration_page_title . '</a>';
			$item .= '</li>';
		}
		
		return $item;
	}
}
