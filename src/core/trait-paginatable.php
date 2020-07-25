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
 * Paginatable Trait.
 */
trait Paginatable {


	/**
	 * Protected class constructor to prevent direct object creation.
	 */
	protected function __construct() { }


	/**
	 * Prevent object cloning
	 */
	final protected function __clone() { }


	/**
	 * Paginate.
	 *
	 * @param array   $array Array.
	 * @param integer $page Page number.
	 * @param integer $limit Number per page.
	 * @return array Paginated array.
	 */
	final public static function paginate( $array, $page = 1, $limit = 10 ) {
		if ( is_array( $array ) ) {
			$total       = count( $array );
			$total_pages = ceil( $total / $limit );
			$page        = min( $page, $total_pages );
			$offset      = ( $page - 1 ) * $limit;
			if ( $offset < 0 ) {
				$offset = 0;
			}
			return array_slice( $array, $offset, $limit );
		}
	}

	/**
	 * Undocumented function
	 *
	 * @param integer $page Page number starting at 1.
	 * @param integer $limit Limit of results per page.
	 * @return array
	 */
	final public static function calculate_server_side_pagination( $page = 1, $limit = 10 ) {

		return ( array(
			'limit'  => $limit,
			'offset' => $limit * ( $page - 1 ),
		) );
	}
}
