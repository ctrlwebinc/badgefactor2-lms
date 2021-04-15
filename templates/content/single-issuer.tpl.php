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
 */

/*
 * You can override this template by copying it in your theme.
 * See README for details.
 */

use BadgeFactor2\Helpers\Template;

global $bf2_template;
?>


<div class="profile-organisation-intro"></div>

<section class="profile-members-badges">
	<div class="profile-members-badges-heading"><span class="separator-prefix"></span><span class="separator-prefix"></span>
		<h3 class="profile-organisation-badges-heading-title">Badges disponibles<small class="profile-members-badges-available"><?php echo count( $bf2_template->fields['badges'] ); ?> disponible<?php echo count( $bf2_template->fields['badges'] ) >= 2 ? 's' : ''; ?></small></h3>
		<ul class="profile-organisation-badges-cta">
			<!-- TODO fix page switcher -->
			<select>
			<?php foreach ( $bf2_template->fields['issuers'] as $issuer ) : ?>
				<option <?php if (strtolower( $issuer->name ) === strtolower( $bf2_template->fields['issuer']->name ) ): ?>selected <?php endif; ?>value="<?php echo $issuer->name; ?>"><?php echo strtoupper( $issuer->name ); ?></option>
			<?php endforeach; ?>
			</select>
		</ul>

	</div>
	<ul class="profile-members-badges-list">
	<?php foreach ( $bf2_template->fields['badges'] as $badge): ?>
		<li class="profile-members-badge">
			<figure class="profile-members-badge-figure">
				<a href="<?php echo $badge->badge_page->permalink; ?>" class="profile-members-badge-link">
					<img src="<?php echo $badge->image; ?>" class="profile-members-badge-image">
				</a>
				<figcaption class="profile-members-badge-details">
					<span class="profile-members-badge-description"><?php echo $badge->name; ?></span>
				</figcaption>
			</figure>
		</li>
	<?php endforeach; ?>

			</ul>
</section>
