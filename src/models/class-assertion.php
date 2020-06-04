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

namespace BadgeFactor2\Models;

use BadgeFactor2\Admin\Lists\Badges;
use BadgeFactor2\Admin\Lists\Issuers;
use BadgeFactor2\Badgr_Entity;
use BadgeFactor2\BadgrProvider;

/**
 * Assertion Class.
 */
class Assertion implements Badgr_Entity {

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
	public static function all( $per_page = 10, $page_number = 1 ) {
		if ( isset( $_GET['filter_type'] ) && isset( $_GET['filter_value'] ) ) {
			$filter_type  = $_GET['filter_type'];
			$filter_value = $_GET['filter_value'];
			if ( 'Issuers' === $filter_type ) {
				return BadgrProvider::get_all_assertions_by_issuer_slug( $filter_value );
			} elseif ( 'Badges' === $filter_type ) {
				return BadgrProvider::get_all_assertions_by_badge_class_slug( $filter_value );
			}
		}

		return array();
	}

	/**
	 * Retrieve issuer from Badgr provider.
	 *
	 * @param string $entity_id Issuer ID.
	 * @return WP_Post Virtual WP_Post representation of the entity.
	 */
	public static function get( $entity_id ) {
		return BadgrProvider::get_assertion_by_assertion_slug( $entity_id );
	}

	/**
	 * Create Issuer through Badgr provider.
	 *
	 * @param array $values Associated array of values of issuer to create.
	 * @return string|boolean Id of created issuer, or false on error.
	 */
	public static function create( $values, $files = null ) {
		if ( self::validate( $values, $files ) ) {
			return BadgrProvider::add_assertion( $values['issuer'],  $values['badge'],  $values['recipient'] );
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
			// FIXME.
			// return BadgrProvider::update_badge_class( $entity_id, $values['name'], $values['description'], $values['image'] );
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
			'image'				=> __( 'Issued Badge', 'badgefactor2' ),
			'issuer'            => __( 'Issuer', 'badgefactor2' ),
			'badgeclass'		=> __( 'Badge', 'badgefactor2' ),
			'recipient'			=> __( 'Recipient', 'badgefactor2' ),
			'createdAt'         => __( 'Created on', 'badgefactor2' ),
		);
	}

	public static function get_sortable_columns() {
		return array(
			'entityId'          => array( 'entityId', true ),
			'recipient'			=> array( 'recipient', true ),
			'createdAt'         => array( 'createdAt', false ),
		);
	}

	public static function validate( $values ) {
		return true;
	}
}
