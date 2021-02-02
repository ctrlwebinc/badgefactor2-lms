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
}