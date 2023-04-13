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
class Parcours_Controller extends Page_Controller {

	/**
	 * Post Type.
	 *
	 * @var string
	 */
	protected static $post_type = 'parcours-badge';


	/**
	 * Returns or outputs archive template with $fields array.
	 *
	 * @param string $default_template Default template (for filter hook).
	 * @return void|string
	 */
	public static function archive( $default_template = null ) {

		global $post;
		if ( static::$post_type === $post->post_type ) {

			
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

			add_filter( 'body_class', function ( $classes ) {
				$classes[] = 'badge-page-template-default';
				return $classes;	
			});
			$meta = array(
				"parcours_latest_update_date" => get_post_meta($post->ID, "parcours_latest_update_date", true),
				"required_time_hours" => get_post_meta($post->ID, "required_time_hours", true),
				"exigence_technologiques" => get_post_meta($post->ID, "exigence_technologiques", true),
				"public_cible"  => get_post_meta($post->ID, "public_cible", true)
			);
			global $bf2_template;
			$bf2_template         = new stdClass();
			$bf2_template->fields = $meta;
		}

		return parent::single( $default_template );
	}
}
