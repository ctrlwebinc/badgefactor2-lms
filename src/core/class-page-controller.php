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
 * @phpcs:disable WordPress.WP.I18n.NonSingularStringLiteralDomain
 */

namespace BadgeFactor2;

use BadgeFactor2\Helpers\Template;

/**
 * Base Page Controller Class.
 */
class Page_Controller implements Page_Controller_Interface {

	/**
	 * Post Type.
	 *
	 * @var string
	 */
	protected static $post_type = 'post';

	/**
	 * Returns or outputs archive template with $fields array.
	 *
	 * @param string $default_template Default template (for filter hook).
	 * @return void|string
	 */
	public static function archive( $default_template = null ) {
		$template = Template::locate( 'archive-' . static::get_post_type(), $default_template );
		if ( ! $template ) {
			return $default_template;
		}
		global $post;
		// Added check for true === $default_template to allow template return for custom templates.
		if ( true === $default_template || static::$post_type === $post->post_type ) {
			if ( $default_template ) {
				status_header( 200 );
				return $template;
			} else {
				include $template;
				exit;
			}
		}
		if ( $default_template ) {
			return $default_template;
		}
	}


	/**
	 * Returns or outputs single template with $fields array.
	 *
	 * @param string $default_template Default template (for filter hook).
	 * @return void|string
	 */
	public static function single( $default_template = null ) {

		$template = Template::locate( 'single-' . static::get_post_type(), $default_template );
		if ( ! $template ) {
			return $default_template;
		}
		global $post;
		// Added check for true === $default_template to allow template return for custom templates.
		if ( true === $default_template || static::$post_type === $post->post_type ) {
			if ( $default_template ) {
				status_header( 200 );
				return $template;
			} else {
				include $template;
				exit;
			}
		}
		if ( $default_template ) {
			return $default_template;
		}
	}


	/**
	 * Returns custom title for template.
	 *
	 * @param array $titles Titles array.
	 * @return string
	 */
	public static function title( $titles = array() ) {
		return $titles;
	}


	/**
	 * Returns the post type managed by this controller.
	 *
	 * @return string
	 */
	public static function get_post_type() {
		return static::$post_type;
	}
}
