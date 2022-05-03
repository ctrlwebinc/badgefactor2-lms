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
 * @phpcs:disable WordPress.WP.I18n.NonSingularStringLiteralText
 */

namespace BadgeFactor2\Shortcodes;

use BadgeFactor2\Post_Types\BadgePage;
use stdClass;

/**
 * Shortcodes Class.
 */
class Badges {

	/**
	 * Badges Shortcode Init.
	 *
	 * @return void
	 */
	public static function init_hooks() {
		add_action( 'init', array( self::class, 'init' ) );
	}

	/**
	 * Init hook.
	 *
	 * @return void
	 */
	public static function init() {
		add_shortcode( 'bf2-badges', array( self::class, 'list' ) );
	}


	/**
	 * List.
	 *
	 * @param array  $atts Attributes.
	 * @param string $content Content.
	 * @param string $tag Tag.
	 * @return string
	 */
	public function list( string $tag, $atts = array(), $content = null ) {

		$atts = array_change_key_case( (array) $atts, CASE_LOWER );

		// Override default attributes with user attributes.
		$list_atts = shortcode_atts(
			array(
				'badge-image-size'       => 'full',
				'badge-title-tag'        => 'h3',
				'display-issuer'         => 'true',
				'badge-issuer-label-tag' => 'h4',
				'badge-issuer-label'     => __( 'Issued by:', BF2_DATA['TextDomain'] ),
				'badge-issuer-tag'       => 'h4',
				'display-excerpt'        => 'false',
			),
			$atts,
			$tag
		);

		$badge_pages = BadgePage::all( -1 );

		// Start output.
		$o = '';

		// Start container.
		$o .= '<section class="bf2-badges-section">';

		if ( ! $badge_pages ) {

			$msg = __( 'There are no badges currently available.', BF2_DATA['TextDomain'] );
			$o  .= sprintf( '<p>%s</p>', $msg );

		} else {

			// Start Badges.
			$o .= '<ul class="badges-list">';

			// Loop Badges.
			foreach ( $badge_pages as $badge_page ) {
				$badge = $badge_page->badge;

				$o .= '<li class="badge">';

				// Badge Image.
				$badge_image = $badge->image;

				$o .= '<figure>';
				$o .= sprintf( '<a href="%s" class="badge-link-image">', get_permalink( $badge ) );
				$o .= sprintf( '<img class="badge-image" src="%s" alt="%s">', $badge_image, $badge->name );
				$o .= sprintf( '<figcaption class="badge-name">%s</figcaption>', $badge->name );
				$o .= '</a>';
				$o .= '</figure>';

				// Badge Page title.
				$o .= sprintf( '<a href="%s" class="badge-link-title"><%s class="badge-title">%s</%s></a>', get_permalink( $badge_page ), esc_html__( $list_atts['badge-title-tag'], $tag ), $badge_page->post_title, esc_html__( $list_atts['badge-title-tag'], $tag ) );

				// Badge Issuer.
				if ( 'false' !== esc_html__( $list_atts['display-issuer'], $tag ) ) {
					// Start Badge Issuer.
					$o .= '<div class="badge-issuer">';

					$o .= sprintf(
						'<%s class="badge-issuer-label">%s</%s><%s class="badge-issuer-name">%s</%s>',
						esc_html__( $list_atts['badge-issuer-label-tag'], $tag ),
						esc_html__( $list_atts['badge-issuer-label'], $tag ),
						esc_html__( $list_atts['badge-issuer-label-tag'], $tag ),
						esc_html__( $list_atts['badge-issuer-tag'], $tag ),
						$badge->issuer->name,
						esc_html__( $list_atts['badge-issuer-tag'], $tag )
					);

					// End Badge Issuer.
					$o .= '</div>';
				}

				// Badge Issuer.
				if ( 'true' === esc_html__( $list_atts['display-excerpt'], $tag ) ) {
					// Badge Page Excerpt.
					$o .= sprintf( '<div class="badge-excerpt">%s</div>', get_the_excerpt( $badge_page ) );
				}

				$o .= '</li>';
			}

			// End Badges.
			$o .= '</ul>';
		}

		return $o;
	}
}
