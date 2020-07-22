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
 */

namespace BadgeFactor2\Shortcodes;

use BadgeFactor2\Models\Issuer;

/**
 * Shortcodes Class.
 */
class Issuers {


	/**
	 * Issuers Shortcode Init.
	 *
	 * @return void
	 */
	public static function init_hooks() {
		add_action( 'init', array( Issuers::class, 'init' ) );
	}


	/**
	 * Init hook.
	 *
	 * @return void
	 */
	public static function init() {
		add_shortcode( 'bf2-issuers', array( Issuers::class, 'list' ) );
	}


	/**
	 * List.
	 *
	 * @param array  $atts Attributes.
	 * @param string $content Content.
	 * @param string $tag Tag.
	 * @return void
	 */
	public function list( $atts = array(), $content = null, $tag = '' ) {
		$issuers = Issuer::all( -1 );
		if ( $issuers ) {
			foreach ( $issuers as $issuer ) {
				echo $issuer->name;
			}
		} else {
			echo __( 'No issuer for the moment.', BF2_DATA['TextDomain'] );
		}
	}
}
