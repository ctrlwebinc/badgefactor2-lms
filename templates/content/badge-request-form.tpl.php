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
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 * @phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
 * @phpcs:disable WordPress.WP.I18n.NonSingularStringLiteralDomain
 */

use BadgeFactor2\Helpers\Template;
use BadgeFactor2\Post_Types\BadgeRequest;

global $bf2_template;

$form_type = get_post_meta( $bf2_template->fields['badge_page']->ID, 'badge_request_form_type', true );

?>
<div class="c-bf2">
	<div class="c-bf2__section c-bf2__request">
		<header class="c-bf2__header">
			<h1 class="c-bf2__title"><?php echo $bf2_template->fields['badge']->name; ?></h1>
		</header>
		<div class="c-bf2__body">
			<?php
			switch ( $form_type ) {
				case 'gravityforms':
					if ( is_plugin_active( 'bf2-gravityforms/bf2-gravityforms.php' ) ) {
						if ( BadgeRequest::is_in_progress( $bf2_template->fields['badge']->entityId ) ) {
							echo sprintf( '<p>%s</p>', __( 'A request has already been submitted.', BF2_DATA['TextDomain'] ) );
						} elseif ( BadgeRequest::is_granted( $bf2_template->fields['badge']->entityId ) ) {
							echo sprintf( '<p>%s</p>', __( 'This badge has already been granted to you.', BF2_DATA['TextDomain'] ) );
						} else {
							$form_id = get_post_meta( $bf2_template->fields['badge_page']->ID, 'badge_request_form_id', true );
							echo do_shortcode( sprintf( '[bf2-gf-badge-request gravityform_id="%s"]', $form_id ) );
						}
					}
					break;
				case 'basic':
				default:
					include( Template::locate( 'basic-badge-request-form' ) );
					break;
			}
			?>
		</div>
	</div>
</div>
