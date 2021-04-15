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
 * @phpcs:disable WordPress.WP.I18n.NonSingularStringLiteralDomain
 */

namespace BadgeFactor2\Controllers;

use BadgeFactor2\Helpers\Template;
use BadgeFactor2\Page_Controller;
use BadgeFactor2\Post_Types\BadgePage;

/**
 * Badge Request Controller Class.
 */
class Issuer_Controller extends Page_Controller {

	/**
	 * Post Type.
	 *
	 * @var string
	 */
	protected static $post_type = 'issuer';


	/**
	 * Returns or outputs archive template with $fields array.
	 *
	 * @param string $default_template Default template (for filter hook).
	 * @return void|string
	 */
	public static function archive( $default_template = null ) {
		global $wp;

		$template = Template::locate( 'archive-' . static::get_post_type(), $default_template );

		if ( isset( $wp->query_vars['issuers'] ) && 1 == $wp->query_vars['issuers'] ) {
			if ( $default_template ) {
				header( 'Location: ' . home_url( $wp->request . '/cadre21/', true ) );
				exit;
			} else {
				include $template;
				exit;
			}
		}
		return $default_template;		
	}


	/**
	 * Outputs single template with $fields array.
	 *
	 * @param string $default_template Default template (for filter hook).
	 * @return void|string
	 */
	public static function single( $default_template = null ) {
		global $wp;
		global $wp_query;
		global $bf2_template;

		$fields = array();

		$template = Template::locate( 'single-' . static::get_post_type(), $default_template );
		if ( isset( $wp->query_vars['issuer'] ) ) {
			$issuer = \BadgeFactor2\Models\Issuer::get_by_name( $wp->query_vars['issuer'] );
			if ( $issuer ) {
				$wp_query->is_home = false;

				$fields['issuer'] = $issuer;
				$fields['badges'] = \BadgeFactor2\Models\BadgeClass::get_by_issuer( $issuer->entityId );
				foreach ( $fields['badges'] as $badge ) {
					$badge->badge_page = BadgePage::get_by_badgeclass_id( $badge->entityId );
					$badge->badge_page->permalink = \get_permalink( $badge->badge_page );
				}

				$fields['issuers'] = \BadgeFactor2\Models\Issuer::all( -1 );

				$bf2_template         = new \stdClass();
				$bf2_template->fields = $fields;

				if ( $default_template ) {
					status_header( 200 );
					return $template;
				} else {
					include $template;
					exit;
				}
			}
		}

		return parent::single( $default_template );
	}

}
