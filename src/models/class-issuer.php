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

use WP_Post;
use BadgeFactor2\Badgr_Entity;
use BadgeFactor2\BadgrProvider;

/**
 * Issuer Class.
 */
class Issuer implements Badgr_Entity {

	/**
	 * Issuer Badgr Entity ID / Slug.
	 *
	 * @var string
	 */
	public $entity_id;

	/**
	 * Issuer Creation Timestamp.
	 *
	 * @var string
	 */
	public $created_at;

	/**
	 * Issuer Name.
	 *
	 * @var string
	 */
	public $name;

	/**
	 * Issuer Email.
	 *
	 * @var string
	 */
	public $email;

	/**
	 * Issuer URL.
	 *
	 * @var string
	 */
	public $url;

	/**
	 * Issuer Description.
	 *
	 * @var string
	 */
	public $description;

	/**
	 * Retrieve all issuers from Badgr provider.
	 *
	 * @return array|boolean Issuers array or false in case of error.
	 */
	public static function all( $elements_per_page = null, $paged = null, $filter = array() ) {
		if ( empty( $elements_per_page ) ) {
			$elements_per_page = $_GET['posts_per_page'] ?? 10;
		}
		if ( empty( $paged ) ) {
			$paged = $_GET['paged'] ?? 1;
		}

		return BadgrProvider::get_all_issuers(
			array(
				'elements_per_page' => $elements_per_page,
				'paged'             => $paged,
			)
		);
	}

	/**
	 * Retrieve issuer from Badgr provider.
	 *
	 * @param string $entity_id Issuer ID.
	 * @return WP_Post Virtual WP_Post representation of the entity.
	 */
	public static function get( $entity_id ) {
		return BadgrProvider::get_issuer_by_slug( $entity_id );
	}

	/**
	 * Create Issuer through Badgr provider.
	 *
	 * @param array $values Associated array of values of issuer to create.
	 * @return string|boolean Id of created issuer, or false on error.
	 */
	public static function create( $values, $files = null ) {
		if ( self::validate( $values, $files ) ) {
			return BadgrProvider::add_issuer( $values['name'], $values['email'], $values['url'], $values['description'] );
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
			return BadgrProvider::update_issuer( $entity_id, $values['name'], $values['email'], $values['url'], $values['description'] );
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
		return BadgrProvider::delete_issuer( $entity_id );
	}

	public static function get_columns() {
		return array(
			'name'      => __( 'Name', 'badgefactor2' ),
			'email'     => __( 'Email', 'badgefactor2' ),
			'createdAt' => __( 'Created on', 'badgefactor2' ),
		);
	}

	public static function get_sortable_columns() {
		return array(
			'name'      => array( 'name', true ),
			'email'     => array( 'email', false ),
			'createdAt' => array( 'createdAt', false ),
		);
	}

	public static function validate( $values, $files = null ) {
		return true;
	}
}
