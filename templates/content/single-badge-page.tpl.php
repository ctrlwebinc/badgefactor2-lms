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

<div class="c-bf2 c-bf2--single section-inner" <?php post_class(); ?> id="post-<?php the_ID(); ?>">
	<?php if ( true === $bf2_template->fields['display-autoevaluation-form'] ) : ?>
		<?php include( Template::locate( 'autoevaluation-form' ) ); ?>
	<?php endif; ?>
	<?php if ( true === $bf2_template->fields['display-badge-request-form'] ) : ?>
		<?php include( Template::locate( 'badge-request-form' ) ); ?>
	<?php endif; ?>
	<?php if ( true === $bf2_template->fields['display-page'] ) : ?>
		<article class="c-bf2__single">
			<header class="c-bf2__header">
				<h1 class="c-bf2__title"><?php echo $bf2_template->fields['badge']->name; ?></h1>
			</header>
			<div class="c-bf2__body">
				<h3 class="c-bf2__body__title"><?php echo __( 'Description', BF2_DATA['TextDomain'] ); ?></h3>
				<p class="c-bf2__body__content">
					<?php echo $bf2_template->fields['badge_page']->post_content; ?>
				</p>
				<h3 class="c-bf2__body__title"><?php echo __( 'Criteria', BF2_DATA['TextDomain'] ); ?></h3>
				<p class="c-bf2__body__content">
					<?php echo $bf2_template->fields['badge_criteria']; ?>
				</p>
			</div>
			<aside class="c-bf2__sidebar">
				<div class="c-bf2__badge">
					<div class="c-bf2__badge__inner">
						<img class="c-bf2__badge__image" src="<?php echo $bf2_template->fields['badge']->image; ?>" alt="<?php echo $bf2_template->fields['badge']->name; ?>">
						<h3 class="c-bf2__badge__title">
							<?php echo __( 'Issued by', BF2_DATA['TextDomain'] ); ?>
							<a target="_blank" href="<?php echo $bf2_template->fields['issuer']->url; ?>"><?php echo $bf2_template->fields['issuer']->name; ?></a>
						</h3>
						<div class="c-bf2__badge__actions">
							<?php if ( $bf2_template->fields['courses'] ) : ?>
								<?php foreach ( $bf2_template->fields['courses'] as $course ) : ?>
								<div class="c-bf2__badge__action">
									<?php if ( $course->is_accessible ) : ?>
										<a class="c-bf2__btn" href="<?php echo get_permalink( $course ); ?>"><?php echo __( 'Take this course', BF2_DATA['TextDomain'] ); ?></a>
									<?php elseif ( $course->is_purchasable ) : ?>
										<a class="c-bf2__btn" href="<?php echo get_permalink( $course ); ?>"><?php echo __( 'Get this course', BF2_DATA['TextDomain'] ); ?></a>
									<?php else : ?>
										<?php echo __( 'This course is not currently available.', BF2_DATA['TextDomain'] ); ?>
									<?php endif; ?>
								</div>
								<?php endforeach; ?>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</aside>
		</article>
		<article class="c-bf2__single">
			<header class="c-bf2__header">
				<h1 class="c-bf2__title"><?php echo __( 'Members who have obtained this badge', BF2_DATA['TextDomain'] ); ?></h1>
			</header>
			<div class="c-bf2__body">
				<!-- TODO -->
			</div>
		</article>
	<?php endif; ?>
</div>
