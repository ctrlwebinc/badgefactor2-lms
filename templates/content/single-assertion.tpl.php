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
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 * @phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
 */

use BadgeFactor2\Models\Assertion;
use BadgeFactor2\Post_Types\BadgePage;

global $bf2_template;

/*
 * You can override this template by copying it in your theme, in a
 * badgefactor2/ subdirectory, and modifying it there.
 */
?>
<article <?php post_class(); ?>>
	<div class="entry-content">
		<div class="content alignwide">
			<h1 class="badge__name"><?php echo $bf2_template->fields['badge']->name; ?></h1>
			<div class="badge__container">
				<div class="badge__badge">
					<figure>
						<img class="badge__image" src="<?php echo $bf2_template->fields['assertion']->image; ?>" alt="<?php echo $bf2_template->fields['badge']->name; ?> <?php echo __( 'badge issued to', BF2_DATA['TextDomain'] ); ?> <?php echo $bf2_template->fields['assertion']->recipient->plaintextIdentity; ?>">
					</figure>
					<div class="badge__issued-to">
						<?php echo __( 'Issued to', BF2_DATA['TextDomain'] ); ?>
						<a href="../.." class="badge__issued-to-link">
							<?php echo $bf2_template->fields['assertion']->recipient->plaintextIdentity; ?>
						</a>
					</div><!-- .badge__issued-to -->
					<div class="badge__issue-date">
						<?php echo __( 'Issue date:', BF2_DATA['TextDomain'] ); ?> 
						<?php echo gmdate( 'Y-m-d H:i', strtotime( $bf2_template->fields['assertion']->issuedOn ) ); ?>
					</div><!-- .badge__issued-date -->
					<a href="#" class="badge__issued-to-link"></a>
					<div class="badge__issued">
						<h3><?php echo __( 'Issued by', BF2_DATA['TextDomain'] ); ?></h3>
						<a target="_blank" href="<?php echo $bf2_template->fields['issuer']->url; ?>"><?php echo $bf2_template->fields['issuer']->name; ?></a>
					</div><!-- .badge__issued -->
				</div><!-- .badge__badge -->
				<div class="badge__description">
					<div class="badge__text text">
					<?php echo $bf2_template->fields['badge']->description; ?>
					</div>
				</div><!-- .badge__description -->
				<div class="badge__links">
					<?php do_action( 'bf2_assertion_links' ); ?>
				</div><!-- .badge_links -->
			</div><!-- .badge__container -->
		</div><!-- .content -->
	</div><!-- .entry-content --> 
</article>