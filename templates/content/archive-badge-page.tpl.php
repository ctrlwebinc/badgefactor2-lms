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
 * You can override this template by copying it in your theme, in a
 * badgefactor2/ subdirectory, and modifying it there.
 */

global $bf2_template;
?>
<main class="section-inner" <?php post_class(); ?> id="post-<?php the_ID(); ?>" role="main">
	<div class="c-bf2 c-bf2--badges">
		<header class="c-bf2__header">
			<h1 class="c-bf2__title"><?php echo __( 'Badges' ); ?></h1>
		</header>
		<div class="c-bf2__body">
			<?php foreach ( $bf2_template->fields['badgepages']['by_category'] as $category ): ?>
			<section class="c-bf2__section">
				<h2 class="c-bf2__section__title"><?php echo $category['term']->name; ?></h2>
				<p class="c-bf2__section__description">
					<?php echo $category['term']->description; ?>
				</p>
				<div class="c-bf2__list__items">
					<?php foreach ( $category['badgepages'] as $badgepage ): ?>
					<div class="c-bf2__badge c-bf2__list__item">
						<a class="c-bf2__badge__inner" href="<?php echo get_permalink( $badgepage->ID ); ?>">
							<img class="c-bf2__badge__image" src="<?php echo $badgepage->badge->image; ?>" alt="<?php echo $badgepage->badge->name; ?>">
							<h3 class="c-bf2__badge__title"><?php echo $badgepage->badge->name; ?></h3>
						</a>
					</div>
					<?php endforeach; ?>
				</div>
			</section>
			<?php endforeach; ?>
		</div>
	</div>
</main>
