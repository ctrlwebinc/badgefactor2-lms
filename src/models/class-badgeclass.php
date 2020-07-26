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
 * @phpcs:disable WordPress.WP.I18n.NonSingularStringLiteralDomain
 * @phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
 */

namespace BadgeFactor2\Models;

use BadgeFactor2\Badgr_Entity;
use BadgeFactor2\BadgrProvider;
use BadgeFactor2\WP_Sortable;

/**
 * Badge Class.
 */
class BadgeClass implements Badgr_Entity {

	use WP_Sortable;

	/**
	 * Badge Badgr Entity ID / Slug.
	 *
	 * @var string
	 */
	public $entity_id;


	/**
	 * Retrieve all badges from Badgr provider.
	 *
	 * @param int   $elements_per_page Elements per page.
	 * @param int   $paged Page number.
	 * @param array $filter Filter to use.
	 *
	 * @return array|boolean Badges array or false in case of error.
	 */
	public static function all( $elements_per_page = null, $paged = null, $filter = array() ) {
		$args = array();

		if ( empty( $elements_per_page ) ) {
			$args['elements_per_page'] = $_GET['posts_per_page'] ?? 10;
		} else {
			$args['elements_per_page'] = $elements_per_page;
		}
		if ( -1 !== $args['elements_per_page'] ) {
			if ( empty( $paged ) ) {
				$args['paged'] = $_GET['paged'] ?? 1;
			} else {
				$args['paged'] = $paged;
			}
		}
		$badges = BadgrProvider::get_all_badge_classes( $args );
		if ( isset( $filter['issuer'] ) ) {
			foreach ( $badges as $i => $badge ) {
				if ( $badge->issuer !== $filter['issuer'] ) {
					unset( $badges[ $i ] );
				}
			}
		}

		WP_Sortable::sort( $badges );

		return $badges;
	}


	/**
	 * Count badge classes.
	 *
	 * @return int
	 */
	public static function count() {

		$count = BadgrProvider::get_all_badge_classes_count();

		return $count;
	}


	/**
	 * Retrieve badge from Badgr provider.
	 *
	 * @param string $entity_id Badge ID.
	 *
	 * @return WP_Post Virtual WP_Post representation of the entity.
	 */
	public static function get( $entity_id ) {
		return BadgrProvider::get_badge_class_by_badge_class_slug( $entity_id );
	}


	/**
	 * Create Badge through Badgr provider.
	 *
	 * @param array $values Associated array of values of badge to create.
	 * @param array $files Files.
	 *
	 * @return string|boolean Id of created badge, or false on error.
	 */
	public static function create( $values, $files = null ) {
		if ( self::validate( $values, $files ) ) {
			return BadgrProvider::add_badge_class( $values['name'], $values['issuer_slug'], $values['description'], $files['image']['tmp_name'] );
		}
		return false;
	}


	/**
	 * Update badge through Badgr provider.
	 *
	 * @param string $entity_id Badge ID.
	 * @param array  $values Associative array of values to change.
	 *
	 * @return boolean Whether or not update has succeeded.
	 */
	public static function update( $entity_id, $values ) {

		$badge = BadgeClass::get( $entity_id );

		if ( $badge && self::validate( $values ) ) {
			if ( ! isset( $values['image'] ) ) {
				$values['image'] = null;
			}

			return BadgrProvider::update_badge_class( $entity_id, $values['name'], $values['description'], $values['image'] );
		}
		return false;

	}


	/**
	 * Delete an Badge through Badgr provider.
	 *
	 * @param string $entity_id Slug / Entity ID.
	 *
	 * @return boolean Whether or not deletion has succeeded.
	 */
	public static function delete( $entity_id ) {
		return BadgrProvider::delete_badge_class( $entity_id );
	}


	/**
	 * Get columns.
	 *
	 * @return array
	 */
	public static function get_columns() {
		return array(
			'name'      => __( 'Name', BF2_DATA['TextDomain'] ),
			'issuer'    => __( 'Issuer', BF2_DATA['TextDomain'] ),
			'image'     => __( 'Image', BF2_DATA['TextDomain'] ),
			'createdAt' => __( 'Created on', BF2_DATA['TextDomain'] ),
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
			'issuer'    => array( 'issuer', false ),
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
		if ( ! isset( $values['name'] ) || ! isset( $values['issuer_slug'] ) || ! isset( $values['description'] ) ) {
			return false;
		}
		// TODO File ok.
		if ( false ) {

		}
		// Right type.
		if ( ! is_string( $values['name'] ) || ! is_string( $values['issuer_slug'] ) || ! is_string( $values['description'] ) ) {
			return false;
		}
		// Not too big.
		if ( strlen( $values['name'] ) > 255 || strlen( $values['issuer_slug'] ) > 255 ) {
			return false;
		}
		// Not too small.
		if ( strlen( $values['name'] ) < 1 || strlen( $values['issuer_slug'] ) < 16 ) {
			return false;
		}

		return true;
	}

	/**
	 * Undocumented function.
	 *
	 * @return int
	 */
	public static function migrate_badge_classes() {
		// Get badges posts without badgr slug enriched with organisation issuer slug.
		global $wpdb;

		$badges = $wpdb->get_results(
			"SELECT bc.*, os.meta_value AS issuer_slug, i.meta_value AS image_name FROM wp_posts AS bc
		JOIN wp_postmeta AS o
		ON bc.ID = o.post_id
		JOIN wp_postmeta AS os
		ON o.meta_value = os.post_id
		JOIN wp_postmeta AS a
		ON bc.ID = a.post_id
		JOIN wp_postmeta as i
		ON a.meta_value = i.post_id
		WHERE bc.post_type = 'badges' AND
		bc.post_status != 'trash' AND
		o.meta_key = 'organisation' AND
		os.meta_key = 'badgr_issuer_slug' AND
		a.meta_key = '_thumbnail_id' AND
		i.meta_key = '_wp_attached_file'
		AND NOT EXISTS
		(SELECT bcs.meta_id FROM wp_postmeta as bcs
		 WHERE bcs.meta_key = 'badgr_badge_class_slug' AND bc.ID = bcs.post_id);",
			OBJECT_K
		);

		$count = 0;

		foreach ( $badges as $badge_post_id => $badge_post ) {

			$class_name  = $badge_post->post_title;
			$issuer_slug = $badge_post->issuer_slug;
			// TODO: trim description.
			$description = $badge_post->post_content;
			$image       = get_home_path() . 'wp-content/uploads/' . $badge_post->image_name;

			$badge_class_slug = BadgrProvider::add_badge_class( $class_name, $issuer_slug, $description, $image );

			if ( false === $badge_class_slug ) {
				update_post_meta( $badge_post_id, 'badgr_badge_class_failed', 'failed' );
				continue;
			}

			// Save slug in post meta.
			update_post_meta( $badge_post_id, 'badgr_badge_class_slug', $badge_class_slug );
			$count++;
		}

		return $count;
	}

	/**
	 * Select options.
	 *
	 * @return array
	 */
	public static function select_options() {
		$options = array();

		$badges = self::all( -1 );

		if ( $badges ) {
			foreach ( $badges as $badge ) {
				$options[ $badge->entityId ] = $badge->name;
			}
		}

		return $options;

	}
}
