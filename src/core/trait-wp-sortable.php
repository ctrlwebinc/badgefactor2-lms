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
 * Singleton Trait.
 */
trait WP_Sortable {

	/**
	 * Protected class constructor to prevent direct object creation.
	 */
	protected function __construct() { }


	/**
	 * Prevent object cloning
	 */
	final protected function __clone() { }


	/**
	 * Sort.
	 *
	 * @param array $array Array.
	 * @param array $internal_orderby Internal order by.
	 * @return void
	 */
	final public static function sort( &$array, $internal_orderby = null ) {

		if ( is_array( $array ) ) {

			$orderby = $_GET['orderby'] ?? 'createdAt';
			$order   = $_GET['order'] ?? 'desc';

			usort(
				$array,
				function( $a, $b ) use ( $order, $orderby, $internal_orderby ) {
					if ( $internal_orderby && array_key_exists( $orderby, $internal_orderby ) ) {
						$a       = $a->$orderby;
						$b       = $b->$orderby;
						$orderby = $internal_orderby[ $orderby ];
					}
					if ( ! isset( $a->$orderby ) || ! isset( $b->$orderby ) ) {
						return 0;
					}
					return 'desc' === $order ? strcmp( $b->$orderby, $a->$orderby ) : strcmp( $a->$orderby, $b->$orderby ); }
			);
		}
	}
}
