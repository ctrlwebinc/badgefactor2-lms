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

use BadgeFactor2\BadgrProvider;
use BadgeFactor2\Models\BadgeClass;
use BadgeFactor2\Models\Issuer;
use BadgeFactor2\Page_Controller;
use BadgeFactor2\Post_Types\BadgePage;
use BadgeFactor2\Post_Types\Course;
use stdClass;
use WP_Query;
use BadgeFactor2\AssertionPrivacy;

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

					$badge_entity_id = get_post_meta( $badgepage->ID, 'badge', true );
					$badge           = BadgeClass::get( $badge_entity_id );

					if ( $badge ) {
						$fields['badgepages']['by_category'][ $term->slug ]['badgepages'][ $i ]->badge_entity_id = $badge_entity_id;
						$fields['badgepages']['by_category'][ $term->slug ]['badgepages'][ $i ]->badge           = $badge;
						$fields['badgepages']['by_category'][ $term->slug ]['badgepages'][ $i ]->issuer          = Issuer::get( $badge->issuer );
					} else {
						$fields['badgepages']['by_category'][ $term->slug ]['badgepages'][ $i ]->badge_entity_id = null;
						$fields['badgepages']['by_category'][ $term->slug ]['badgepages'][ $i ]->badge           = null;
						$fields['badgepages']['by_category'][ $term->slug ]['badgepages'][ $i ]->issuer          = null;
					}
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

			$current_user    = wp_get_current_user();
			$has_free_access = apply_filters( 'bf2_has_free_access', null );

			$fields['badge_page']      = $post;
			$fields['badge_entity_id'] = get_post_meta( $post->ID, 'badge', true );
			$fields['badge_criteria']  = get_post_meta( $post->ID, 'badge_criteria', true );
			$fields['badge_approval_type'] = get_post_meta( $post->ID, 'badge_approval_type', true );
			$fields['badge']           = BadgeClass::get( $fields['badge_entity_id'] );
			$fields['issuer']          = $fields['badge'] ? Issuer::get( $fields['badge']->issuer ) : null;
			$fields['courses']         = BadgePage::get_courses( $post->ID );

			if ( 1 === intval( get_query_var( 'form' ) ) &&
				class_exists( 'BadgeFactor2\BF2_Courses' ) &&
				class_exists( 'BadgeFactor2\BF2_WooCommerce' ) ) {

				// A Course is linked to the badge page.
				$course_id = get_post_meta( $post->ID, 'course', true );
				if ( $course_id ) {
					// A product is linked to the course.
					$product_id = get_post_meta( $course_id, 'course_product', true );
					if ( $product_id ) {
						// The client has not purchased this product and the product isn't free, redirect to the product page.
						$is_free = get_post_meta( $product_id, 'price', true ) == null;
						if ( ! $is_free && ! $has_free_access && ! wc_customer_bought_product( $current_user->user_email, $current_user->ID, $product_id ) ) {
							wp_redirect( get_permalink( $product_id ) );
							exit;
						}
					}
				}
			}

			$fields['display-autoevaluation-form'] = false;
			$fields['display-badge-request-form']  = false;
			$fields['display-page']                = true;
			if ( 1 === intval( get_query_var( 'form' ) ) ) {
				$fields['display-members'] = false;

				if ( 1 === intval( get_query_var( 'autoevaluation' ) ) ) {
					$fields['display-autoevaluation-form'] = true;
					$fields['display-page']                = false;
				} else {
					$fields['display-badge-request-form'] = true;
					$fields['display-page']               = false;
				}

			} else {
				$fields['display-members'] = true;
				$assertions = BadgrProvider::get_all_assertions_by_badge_class_slug( $fields['badge_entity_id'] );
				if ( ! $assertions ) {
					$assertions = array();
				}
				usort(
					$assertions,
					function( $a, $b ) {
						$datetime1 = strtotime( $a->issuedOn );
						$datetime2 = strtotime( $b->issuedOn );

						return $datetime2 - $datetime1;
					}
				);
				$members = array();
				foreach ( $assertions as $assertion ) {
					$user = get_user_by( 'email', $assertion->recipient->plaintextIdentity );
					if ( $user ) {
						if (
							!AssertionPrivacy::has_privacy_flag( $fields['badge_entity_id'], $user->ID) // check badge visibility
							&& FALSE === $assertion->revoked // hide revoked assertion
						) {
							$members[ $assertion->recipient->plaintextIdentity ] = $user;
						}
					}
					if ( count( $members ) >= 4 ) {
						break;
					}
				}
				$fields['members_count'] = count( $assertions );
				$fields['members']       = $members;
			}

			foreach ( $fields['courses'] as $i => $course ) {
				$fields['courses'][ $i ]->is_accessible  = Course::is_accessible( $course->ID );
				$fields['courses'][ $i ]->is_purchasable = Course::is_purchasable( $course->ID );

				if ( class_exists( 'BadgeFactor2\BF2_WooCommerce' ) ) {

					global $product;
					$product = wc_get_product( get_post_meta( $course->ID, 'course_product', true ) );

					if ( $product ) {
						$fields['courses'][ $i ]->price             = wc_price( $product->get_price() );
						$fields['courses'][ $i ]->unformatted_price = $product->get_price();
						$fields['courses'][ $i ]->cart_button       = apply_filters(
							'woocommerce_loop_add_to_cart_link',
							sprintf(
								'<a class="c-bf2__btn" href="%s" rel="nofollow" data-product_id="%s" data-product_sku="%s" class="button %s product_type_%s">%s</a>',
								wc_get_cart_url() . esc_url( $product->add_to_cart_url() ),
								esc_attr( $product->get_id() ),
								esc_attr( $product->get_sku() ),
								$product->is_purchasable() ? 'add_to_cart_button' : '',
								esc_attr( $product->get_type() ),
								esc_html( $product->add_to_cart_text() )
							),
							$product
						);
					}
				}
			}

			global $bf2_template;
			$bf2_template         = new stdClass();
			$bf2_template->fields = $fields;

		}

		return parent::single( $default_template );
	}
}
