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

namespace BadgeFactor2\Admin\Lists;

use BadgeFactor2\Admin\Badgr_List;
use BadgeFactor2\Models\BadgeClass;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Badges extends Badgr_List {

	/**
	 * Class constructor.
	 */
	public function __construct() {

		parent::__construct(
			BadgeClass::class,
			__( 'Badge', 'badgefactor2' ),
			__( 'Badges', 'badgefactor2' ),
			'badges'
		);
	}

	public function validate() {
		// TODO
		return true;
	}

}
