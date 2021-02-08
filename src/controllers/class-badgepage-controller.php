<?php
/**
 * Badge Factor 2
 * Copyright (C) 2021 ctrlweb
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
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 * @phpcs:disable WordPress.WP.I18n.NonSingularStringLiteralDomain
 */

namespace BadgeFactor2\Controllers;

use BadgeFactor2\Models\BadgeClass;
use BadgeFactor2\Models\Issuer;
use BadgeFactor2\Page_Controller;
use BadgeFactor2\Post_Types\BadgePage;
use BadgeFactor2\Post_Types\Course;
use stdClass;
use WP_Query;

/**
 * BadgePage Controller Class.
 */
class BadgePage_Controller extends Page_Controller {

	/**
	 * Post Type.
	 *
	 * @var string
	 */
	protected static $post_type = 'badge-page';


	/**
	 * Returns or outputs archive template with $fields array.
	 *
	 * @param string $default_template Default template (for filter hook).
	 * @return void|string
	 */
	public static function archive( $default_template = null ) {

		global $post;
		if ( static::$post_type === $post->post_type ) {

			$fields = array();

			$terms_by_badge_category = get_terms( 'badge-category' );

			$fields['badgepages'] = array();

			$query = new WP_Query(
				array(
					'post_type' => static::$post_type,
				)
			);

			$fields['badgepages']['all'] = $query->get_posts();

			$fields['badgepages']['by_category'] = array();
			foreach ( $terms_by_badge_category as $term ) {

				$query = new WP_Query(
					array(
						'post_type' => static::$post_type,
						'tax_query' => array(
							array(
								'taxonomy' => 'badge-category',
								'field'    => 'slug',
								'terms'    => $term->slug,
							),
						),
					)
				);
				$fields['badgepages']['by_category'][ $term->slug ] = array(
					'term'       => $term,
					'badgepages' => $query->get_posts(),
				);

				foreach ( $fields['badgepages']['by_category'][ $term->slug ]['badgepages'] as $i => $badgepage ) {
					$fields['badgepages']['by_category'][ $term->slug ]['badgepages'][ $i ]->badge_entity_id = get_post_meta( $badgepage->ID, 'badge', true );
					$fields['badgepages']['by_category'][ $term->slug ]['badgepages'][ $i ]->badge           = BadgeClass::get( $fields['badgepages']['by_category'][ $term->slug ]['badgepages'][ $i ]->badge_entity_id );
					$fields['badgepages']['by_category'][ $term->slug ]['badgepages'][ $i ]->issuer          = Issuer::get( $fields['badgepages']['by_category'][ $term->slug ]['badgepages'][ $i ]->badge->issuer );
				}
			}

			global $bf2_template;
			$bf2_template         = new stdClass();
			$bf2_template->fields = $fields;
		}

		return parent::archive( $default_template );
	}


	/**
	 * Outputs single template with $fields array.
	 *
	 * @param string $default_template Default template (for filter hook).
	 * @return void|string
	 */
	public static function single( $default_template = null ) {

		global $post;
		if ( static::$post_type === $post->post_type ) {

			$fields = array();

			if ( 1 === intval( get_query_var( 'form' ) ) &&
				class_exists( 'BadgeFactor2\BF2_Courses' ) &&
				class_exists( 'BadgeFactor2\BF2_WooCommerce' ) ) {

				// A Course is linked to the badge page.
				$course_id = get_post_meta( $post->ID, 'course', true );
				if ( $course_id ) {
					// A product is linked to the course.
					$product_id = get_post_meta( $course_id, 'course_product', true );
					if ( $product_id ) {
						// The client has not purchased this product, redirect to the product page.
						if ( ! wc_customer_bought_product( wp_get_current_user()->user_email, wp_get_current_user()->ID, $product_id ) ) {
							wp_redirect( get_permalink( $product_id ) );
							exit;
						}
					}
				}
			}
			global $post;

			$fields['display-autoevaluation-form'] = false;
			$fields['display-badge-request-form'] = false;
			$fields['display-page'] = true;
			if ( 1 === intval( get_query_var( 'form' ) ) ) {
				if ( 1 === intval( get_query_var( 'autoevaluation' ) ) ) {
					$fields['display-autoevaluation-form'] = true;
					$fields['display-page'] = false;
				} else {
					$fields['display-badge-request-form'] = true;
					$fields['display-page'] = false;
				}
			}

			$fields['badge_page']      = $post;
			$fields['badge_entity_id'] = get_post_meta( $post->ID, 'badge', true );
			$fields['badge_criteria']  = get_post_meta( $post->ID, 'badge_criteria', true );
			$fields['badge']           = BadgeClass::get( $fields['badge_entity_id'] );
			$fields['issuer']          = $fields['badge'] ? Issuer::get( $fields['badge']->issuer ) : null;
			$fields['courses']         = BadgePage::get_courses( $post->ID );
			foreach ( $fields['courses'] as $i => $course ) {
				$fields['courses'][ $i ]->is_accessible  = Course::is_accessible( $course->ID );
				$fields['courses'][ $i ]->is_purchasable = Course::is_purchasable( $course->ID );
			}

			global $bf2_template;
			$bf2_template         = new stdClass();
			$bf2_template->fields = $fields;
		}

		return parent::single( $default_template );
	}
}
