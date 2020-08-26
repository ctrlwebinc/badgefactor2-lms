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

use BadgeFactor2\Helpers\Template;

/*
 * You can override this template by copying it in your theme, in a
 * badgefactor2/ subdirectory, and modifying it there.
 */

get_header();

$badge_page      = $post;
$badge_entity_id = get_post_meta( $post->ID, 'badge', true );
$badge_criteria  = get_post_meta($post->ID, 'badge_criteria', true);
$badge           = BadgeFactor2\Models\BadgeClass::get( $badge_entity_id );
$issuer          = BadgeFactor2\Models\Issuer::get( $badge->issuer );
$course          = BadgeFactor2\Post_Types\BadgePage::get_course( $post->ID );

?>



<?php if ( 1 === intval( get_query_var( 'form' ) ) ) : ?>
	<?php include( Template::locate( 'partials/badge-request-form' ) ); ?>
<?php else : ?>

<main <?php post_class(); ?> id="post-<?php the_ID(); ?> site-content" role="main">
	<section id="primary" class="section-inner">
		<div id="badge" class="badge">
			<div class="content">
				<h1 class="badge-name">
					<span class="badge-pre-title"><?php echo $badge->entityType; ?></span>
					<span class="badge-title"><?php echo $badge->name; ?></span>
				</h1>
				<div class="badge-container">
					<h3>Description</h3>
					<p class="badge-description">
						<?php echo $badge_page->post_content; ?>
					</p>
					<h3>Critères d'obtentions</h3>
					<p class="badge-criteria">
						<?php echo $badge_criteria ?>
					</p>
				</div>
			</div>
			<div class="sidebar">
				<div class="badge-container">
					<img class="badge-image" src="<?php echo $badge->image; ?>" alt="<?php echo $badge->name; ?>">
					<div class="badge-issued">
						<h3 class="badge-issued-title">
							<?php echo __( 'Issued by', BF2_DATA['TextDomain'] ); ?>
							<a target="_blank" href="<?php echo $issuer->url; ?>"><?php echo $issuer->name; ?></a>
						</h3>
					</div>
					
					<div class="badge-actions">
						<?php if ( $course ) : ?>
							<div class="badge-actions-course">
								<?php if ( BadgeFactor2\Post_Types\Course::is_accessible() ) : ?>
									<a class="btn" href="<?php echo get_permalink( $course ); ?>"><?php echo __( 'Take this course', BF2_DATA['TextDomain'] ); ?></a>
								<?php elseif ( BadgeFactor2\Post_Types\Course::is_purchasable() ) : ?>
									<a class="btn" href="<?php echo get_permalink( $course ); ?>"><?php echo __( 'Get this course', BF2_DATA['TextDomain'] ); ?></a>
								<?php else : ?>
									<?php //echo __( 'This course is not currently accessible.', BF2_DATA['TextDomain'] ); ?>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</div>
					
				</div>
			</div>
		</div>
	</section>
</main>
<?php endif; ?>
<?php
get_footer();
