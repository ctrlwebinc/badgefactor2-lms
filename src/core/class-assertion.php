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
 * Assertion Class.
 */
class Assertion {

	/**
	 * Assertion Badgr Entity ID / Slug.
	 *
	 * @var string
	 */
	public $entity_id;

	/**
	 * Retrieve all issuers from Badgr provider.
	 *
	 * @return array|boolean Issuers array or false in case of error.
	 */
	public static function all() {
		return BadgrProvider::get_all_badge_classes();
	}

	/**
	 * Retrieve issuer from Badgr provider.
	 *
	 * @param string $entity_id Issuer ID.
	 * @return WP_Post Virtual WP_Post representation of the entity.
	 */
	public static function get( $entity_id ) {
		return BadgrProvider::get_badge_class_by_badge_class_slug( $entity_id );
	}

	/**
	 * Create Issuer through Badgr provider.
	 *
	 * @param array $values Associated array of values of issuer to create.
	 * @return string|boolean Id of created issuer, or false on error.
	 */
	public static function create( $values ) {
		if ( self::validate( $values ) ) {
			return BadgrProvider::add_badge_class( $values['name'], $values['issuer_slug'], $values['description'], $values['image'] );
		}
		return false;
	}

	/**
	 * Update issuer through Badgr provider.
	 *
	 * @param string $entity_id Issuer ID.
	 * @param array  $values Associative array of values to change.
	 * @return boolean Whether or not update has succeeded.
	 */
	public static function update( $entity_id, $values ) {
		if ( self::validate( $values ) ) {
			return BadgrProvider::update_badge_class( $entity_id, $values['name'], $values['description'], $values['image'] );
		}
		return false;

	}

	/**
	 * Delete an Issuer through Badgr provider.
	 *
	 * @param string $entity_id Slug / Entity ID.
	 * @return boolean Whether or not deletion has succeeded.
	 */
	public static function delete( $entity_id ) {

	}

	public static function get_columns() {
		return array(
			'entityId'          => __( 'Slug', 'badgefactor2' ),
			'issuerOpenBadgeId' => __( 'Link', 'badgefactor2' ),
			'issuedOn'          => __( 'Issued on', 'badgefactor2' ),
			// TODO Add pertinent fields
		);
	}

	public static function get_sortable_columns() {
		return array(
			'entityId'          => array( 'entityId', true ),
			'issuerOpenBadgeId' => array( 'issuerOpenBadgeId', false ),
			'issuedOn'          => array( 'issuedOn', false ),
		);
	}

	public static function validate( $values ) {
		return true;
	}
}
