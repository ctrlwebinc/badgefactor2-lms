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
 * Badgr Entity Interface.
 */
interface Badgr_Entity {

	/**
	 * Retrieve all entities from Badgr provider.
	 *
	 * @return array|boolean Object instances array or false in case of error.
	 */
	public static function all();

	/**
	 * Retrieve single entity from Badgr provider.
	 *
	 * @param string $entity_id Slug / Entity ID.
	 * @return WP_Post Virtual WP_Post representation of the entity.
	 */
	public static function get( $entity_id );

	/**
	 * Create entity through Badgr provider.
	 *
	 * @param array $values Associated array of values of entity to create.
	 * @return string|boolean Id of created entity, or false on error.
	 */
	public static function create ( $values );

	/**
	 * Update single entity through Badgr provider.
	 *
	 * @param string $entity_id Slug / Entity ID.
	 * @param array  $values Associative array of values to change.
	 * @return boolean Whether or not update has succeeded.
	 */
	public static function update( $entity_id, $values );

	/**
	 * Delete a single entity through Badgr provider.
	 *
	 * @param string $entity_id Slug / Entity ID.
	 * @return boolean Whether or not deletion has succeeded.
	 */
	public static function delete( $entity_id );

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public static function get_columns();

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public static function get_sortable_columns();

}
