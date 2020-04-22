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

use WP_Post;

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
	public static function all() {
		return BadgrProvider::get_all_issuers();
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
	public static function create( $values ) {
		if ( self::validate( $values ) ) {
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

	}

	public static function get_columns() {
		return array(
			'entityId'  => __( 'Slug', 'badgefactor2' ),
			'name'      => __( 'Name', 'badgefactor2' ),
			'email'     => __( 'Email', 'badgefactor2' ),
			'createdAt' => __( 'Created on', 'badgefactor2' ),
		);
	}

	public static function get_sortable_columns() {
		return array(
			'name'       => array( 'name', true ),
			'email'      => array( 'email', false ),
			'createdAt' => array( 'email', false ),
		);
	}

	public static function validate( $values ) {
		return true;
	}

	/*

		$date = strtotime( $badgr_object->createdAt );

		$object                        = new \stdClass();
		$object->ID                    = -99;
		$object->post_author           = 1;
		$object->post_date             = date( 'Y-m-d H:i:s', $date );
		$object->post_date_gmt         = gmdate( 'Y-m-d H:i:s', $date );
		$object->post_content          = $badgr_object->description;
		$object->post_title            = $badgr_object->name;
		$object->post_excerpt          = '';
		$object->post_status           = 'publish';
		$object->comment_status        = 'closed';
		$object->ping_status           = 'closed';
		$object->post_password         = '';
		$object->post_name             = $badgr_object->entityId;
		$object->to_ping               = '';
		$object->pinged                = '';
		$object->post_modified         = date( 'Y-m-d H:i:s', $date );
		$object->post_modified_gmt     = gmdate( 'Y-m-d H:i:s', $date );
		$object->post_content_filtered = '';
		$object->post_parent           = 0;
		$object->guid                  = get_option( 'siteurl' ) . '?post_type=issuer&source=badgr&id=' . $badgr_object->entityId;
		$object->menu_order            = 0;
		$object->post_type             = 'issuer';
		$object->post_mime_type        = '';
		$object->comment_count         = 0;
		$object->issuer_email          = $badgr_object->email;
		$object->issuer_url            = $badgr_object->url;
		$object->filter                = 'raw';
		*/
}
