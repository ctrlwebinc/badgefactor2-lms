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

$autoevaluation_form_type = get_post_meta( $badge_page->ID, 'autoevaluation_form_type', true );

?>
<div class="c-bf2">
	<div class="c-bf2__section c-bf2__request">
		<header class="c-bf2__header">
			<h1 class="c-bf2__title"><?php echo $badge->name; ?></h1>
		</header>
		<div class="c-bf2__body">
			<?php if ( is_plugin_active( 'bf2-gravityforms/bf2-gravityforms.php' ) && 'gravityforms' === $autoevaluation_form_type ) : ?>
				<?php
				$form_id = get_post_meta( $badge_page->ID, 'autoevaluation_form_id', true );
				echo do_shortcode( sprintf( '[gravityform id="%s"]', $form_id ) );
				?>
			<?php else : ?>
				<?php // TODO Manage wrongful url access. ?>
			<?php endif; ?>
		</div>
	</div>
</div>
