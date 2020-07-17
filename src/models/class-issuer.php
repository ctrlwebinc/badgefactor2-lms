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
use WP_Query;
use BadgeFactor2\Badgr_Entity;
use BadgeFactor2\BadgrProvider;
use BadgeFactor2\WP_Sortable;

/**
 * Issuer Class.
 */
class Issuer implements Badgr_Entity {

	use WP_Sortable;

	public static $meta_key_for_badgr_issuer_slug = 'badgr_issuer_slug';

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
	 * @param int   $elements_per_page Elements per page.
	 * @param int   $paged Page number.
	 * @param array $filter Filter to use.
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

		$issuers = BadgrProvider::get_all_issuers(
			array(
				'elements_per_page' => $elements_per_page,
				'paged'             => $paged,
			)
		);

		$orderby = $_GET['orderby'] ?? 'createdAt';
		$order   = $_GET['order'] ?? 'desc';

		WP_Sortable::sort( $issuers );

		return $issuers;
	}


	/**
	 * Retrieve issuer from Badgr provider.
	 *
	 * @param string $entity_id Issuer ID.
	 *
	 * @return WP_Post Virtual WP_Post representation of the entity.
	 */
	public static function get( $entity_id ) {
		return BadgrProvider::get_issuer_by_slug( $entity_id );
	}


	/**
	 * Create Issuer through Badgr provider.
	 *
	 * @param array $values Associated array of values of issuer to create.
	 * @param array $files Files.
	 *
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
	 *
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
	 *
	 * @return boolean Whether or not deletion has succeeded.
	 */
	public static function delete( $entity_id ) {
		return BadgrProvider::delete_issuer( $entity_id );
	}


	/**
	 * Get columns.
	 *
	 * @return array
	 */
	public static function get_columns() {
		return array(
			'name'      => __( 'Name', 'badgefactor2' ),
			'email'     => __( 'Email', 'badgefactor2' ),
			'createdAt' => __( 'Created on', 'badgefactor2' ),
		);
	}


	/**
	 * Get sortable columns.
	 *
	 * @return array
	 */
	public static function get_sortable_columns() {
		return array(
			'name'      => array( 'name', true ),
			'email'     => array( 'email', false ),
			'createdAt' => array( 'createdAt', false ),
		);
	}


	/**
	 * Validate.
	 *
	 * @param array $values Values.
	 * @param array $files Files.
	 *
	 * @return bool
	 */
	public static function validate( $values, $files = null ) {

		// Not empty.
		if ( ! isset( $values['name'] ) || ! isset( $values['email'] ) || ! isset( $values['url'] ) || ! isset( $values['description'] ) ) {
			return false;
		}
		// Right type.
		if ( ! is_string( $values['name'] ) || ! is_string( $values['email'] ) || ! is_string( $values['url'] ) || ! is_string( $values['description'] ) ) {
			return false;
		}
		// Not too big.
		if ( strlen( $values['name'] ) > 1024 || strlen( $values['email'] ) > 254 || strlen( $values['url'] ) > 254 ) {
			return false;
		}
		// Not too small.
		if ( strlen( $values['name'] ) < 1 || strlen( $values['email'] ) < 3 || strlen( $values['url'] ) < 8 ) {
			return false;
		}
		// Email format is ok.
		if ( ! filter_var( $values['email'], FILTER_VALIDATE_EMAIL ) ) {
			return false;
		}
		// URL format is ok.
		if ( ! preg_match( "/\b(?:(?:https?):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $values['url'] ) ) {
			return false;
		}

		return true;
	}


	/**
	 * Get select-formatted options.
	 *
	 * @return array
	 */
	public static function select_options() {
		$issuers = Issuer::all( -1 );

		$post_options = array();
		if ( $issuers ) {
			foreach ( $issuers as $issuer ) {
				$post_options[ $issuer->entityId ] = $issuer->name;
			}
		}

		return $post_options;
	}

	public static function migrate_issuers( $only_published = false) {
		// Get all posts of organisation type
		$query = new WP_Query( array ( 
			'post_type' => 'organisation',
			'nopaging' => true,
			'meta_key' => self::$meta_key_for_badgr_issuer_slug,
			'meta_compare' => 'NOT EXISTS',
			'post_status' => $only_published ? 'publish' : 'any',
		) );

		$posts_to_process = $query->posts;
		$count = 0;

		// For each post
		foreach( $posts_to_process as $post_to_process) {
			// Extract name
			$name = $post_to_process->post_name;
			// Extract email
			$email = $name . '@example.net';
			// Extract url
			$url = 'https://' . $name . '.cadre21.org';
			// Extract description
			if ( strlen($post_to_process->post_content) > 0 ) {
				$description = $post_to_process->post_content;
			} else {
				$description = 'Émetteur ' . $name;
			}

			// Create an issuer
			$slug = BadgrProvider::add_issuer( $name, $email, $url, $description );

			if ( false === $slug ) {
				return false;
			}
			// Save slug in post meta
			update_post_meta( $post_to_process->ID, self::$meta_key_for_badgr_issuer_slug, $slug );
			$count++;
		}

		return $count;
	}
}
