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
 *
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound 
 * @phpcs:disable WordPress.WP.I18n.NonSingularStringLiteralDomain
 */

namespace BadgeFactor2\Models;

use BadgeFactor2\Badgr_Entity;
use BadgeFactor2\BadgrProvider;
use BadgeFactor2\BadgrUser;
use BadgeFactor2\Post_Types\BadgeRequest;
use BadgeFactor2\WP_Sortable;
use WP_User;

/**
 * Assertion Class.
 */
class Assertion implements Badgr_Entity {

	use WP_Sortable;

	/**
	 * Assertion Badgr Entity ID / Slug.
	 *
	 * @var string
	 */
	public $entity_id;


	/**
	 * Retrieve all assertions from Badgr provider.
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
		$assertions = array();
		if ( isset( $_GET['filter_type'] ) && isset( $_GET['filter_value'] ) ) {
			$filter_type  = $_GET['filter_type'];
			$filter_value = $_GET['filter_value'];
		} elseif ( isset( $filter['filter_type'] ) && isset( $filter['filter_value'] ) ) {
			$filter_type  = $filter['filter_type'];
			$filter_value = $filter['filter_value'];
		}
		if ( isset( $filter_type ) && isset( $filter_value ) ) {
			if ( 'Issuers' === $filter_type ) {
				$assertions = BadgrProvider::get_all_assertions_by_issuer_slug(
					$filter_value,
					array(
						'elements_per_page' => $elements_per_page,
						'paged'             => $paged,
					)
				);
			} elseif ( 'Badges' === $filter_type ) {
				$assertions = BadgrProvider::get_all_assertions_by_badge_class_slug(
					$filter_value,
					array(
						'elements_per_page' => $elements_per_page,
						'paged'             => $paged,
					)
				);
			}
			static::sort( $assertions, array( 'recipient' => 'plaintextIdentity' ) );
		}

		return $assertions;
	}

	/**
	 * Get random assertion.
	 *
	 * @return Assertion
	 */
	public static function random() {
		$assertion = null;
		$tries = 0;
		do {
			$badges = BadgeClass::all();
			$badge  = $badges[ array_rand( $badges ) ];

			$assertions = static::all( null, null, array( 'filter_type' => 'Badges', 'filter_value' => $badge->entityId ) );
			$assertion  = $assertions[ array_rand( $assertions ) ];
			$tries++;
		} while ( null === $assertion && $tries <= 10 );

		return $assertion;
	}

	/**
	 * Retrieve a specified user's assertions from Badgr provider.
	 *
	 * @param WP_User $user User.
	 * @return array Assertions.
	 */
	public static function all_for_user( WP_User $user ) {
		$badgr_user = new BadgrUser( $user );
		$assertions = BadgrProvider::get_all_assertions_from_user_backpack( $badgr_user );
		if ( ! $assertions ) {
			$assertions = array();
		}
		return $assertions;
	}


	/**
	 * Retrieve a user's assertion from Badgr provider.
	 *
	 * @param string  $badgeclass_entity_id BadgeClass entity id.
	 * @param WP_User $user User.
	 * @return void
	 */
	public static function get_for_user( $badgeclass_entity_id, $user ) {
		$assertions = self::all_for_user( $user );
		foreach ( $assertions as $assertion ) {
			// TODO Look for specific assertion and return it.
		}
	}


	/**
	 * Count assertions.
	 *
	 * @return int
	 */
	public static function count() {
		$count = 0;
		if ( isset( $_GET['filter_type'] ) && isset( $_GET['filter_value'] ) ) {
			$filter_type  = $_GET['filter_type'];
			$filter_value = $_GET['filter_value'];
			if ( 'Issuers' === $filter_type ) {
				$count = BadgrProvider::get_all_assertions_by_issuer_slug_count( $filter_value );
			} elseif ( 'Badges' === $filter_type ) {
				$count = BadgrProvider::get_all_assertions_by_badge_class_slug_count( $filter_value );
			}
		}
		return $count;
	}


	/**
	 * Retrieve issuer from Badgr provider.
	 *
	 * @param string $entity_id Issuer ID.
	 *
	 * @return WP_Post Virtual WP_Post representation of the entity.
	 */
	public static function get( $entity_id ) {
		return $entity_id ? BadgrProvider::get_assertion_by_assertion_slug( $entity_id ) : null;
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
			$assertion_slug = BadgrProvider::add_assertion( $values['badge'], $values['recipient'] );
			if ( $assertion_slug ) {
				$badge_request_id = BadgeRequest::create_badge_request( $values['badge'], $values['recipient'], $assertion_slug );
				return $badge_request_id ? $assertion_slug : false;
			}
		}
		return false;
	}


	/**
	 * Update issuer through Badgr provider.
	 *
	 * @param string $entity_id Issuer ID.
	 * @param array  $values Associative array of values to change.
	 * @param array  $files Files.
	 *
	 * @return boolean Whether or not update has succeeded.
	 */
	public static function update( $entity_id, $values, $files = null ) {
		// Assertion updating is unauthorized.
		return false;
	}


	/**
	 * Delete an Issuer through Badgr provider.
	 *
	 * @param string $entity_id Slug / Entity ID.
	 *
	 * @return bool Whether or not deletion has succeeded.
	 */
	public static function delete( $entity_id ) {
		// Assertion deletion is unauthorized.
		return false;
	}


	/**
	 * Get columns.
	 *
	 * @return array
	 */
	public static function get_columns() {
		return array(
			'image'      => __( 'Issued Badge', BF2_DATA['TextDomain'] ),
			'issuer'     => __( 'Issuer', BF2_DATA['TextDomain'] ),
			'badgeclass' => __( 'Badge', BF2_DATA['TextDomain'] ),
			'recipient'  => __( 'Recipient', BF2_DATA['TextDomain'] ),
			'createdAt'  => __( 'Created on', BF2_DATA['TextDomain'] ),
		);
	}


	/**
	 * Get sortable columns.
	 *
	 * @return array
	 */
	public static function get_sortable_columns() {
		return array(
			'entityId'  => array( 'entityId', true ),
			'recipient' => array( 'recipient', true ),
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
		if ( ! isset( $values['badge'] ) || ! isset( $values['recipient'] ) ) {
			return false;
		}
		// Right type.
		if ( ! is_string( $values['badge'] ) || ! is_string( $values['recipient'] ) ) {
			return false;
		}
		// Not too big.
		if ( strlen( $values['badge'] ) > 254 || strlen( $values['recipient'] ) > 768 ) {
			return false;
		}
		// Not too small.
		if ( strlen( $values['badge'] ) < 16 || strlen( $values['recipient'] ) < 3 ) {
			return false;
		}
		// Email format is ok.
		if ( ! filter_var( $values['recipient'], FILTER_VALIDATE_EMAIL ) ) {
			return false;
		}

		$user = get_user_by( 'email', $values['recipient'] );
		if ( false === $user ) {
			return false;
		}

		return true;
	}
}
