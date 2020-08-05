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
$badge           = BadgeFactor2\Models\BadgeClass::get( $badge_entity_id );
$issuer          = BadgeFactor2\Models\Issuer::get( $badge->issuer );
$course          = BadgeFactor2\Post_Types\BadgePage::get_course( $post->ID );
?>
<?php if ( 1 === intval( get_query_var( 'form' ) ) ) : ?>
	<?php include( Template::locate( 'partials/badge-request-form' ) ); ?>
<?php else : ?>
<section id="primary" class="content-area">
	<main id="main" class="site-main">
	<article id="badge-" <?php post_class(); ?>>
		<div class="entry-content">
		<div class="content">
			<h1 class="badge_name"><?php echo $badge->name; ?></h1>
			<div class="badge_container">
				<div class="badge_badge">
					<figure>
						<img class="badge_image" src="<?php echo $badge->image; ?>" alt="<?php echo $badge->name; ?>">
					</figure>
					<div class="badge_issued">
						<h3><?php echo __( 'Issued by', BF2_DATA['TextDomain'] ); ?></h3>
						<a target="_blank" href="<?php echo $issuer->url; ?>"><?php echo $issuer->name; ?></a>
					</div>
				</div>
				<div class="badge_description">
					<?php echo $badge->description; ?>
				</div>
				<div class="badge_actions">
					<?php if ( $course ) : ?>
						<?php if ( BadgeFactor2\Post_Types\Course::is_accessible() ) : ?>
						<a href="<?php echo get_permalink( $course ); ?>"><?php echo __( 'Take this course', BF2_DATA['TextDomain'] ); ?></a>
						<?php elseif ( BadgeFactor2\Post_Types\Course::is_purchasable() ) : ?>
						<a href="<?php echo get_permalink( $course ); ?>"><?php echo __( 'Get this course', BF2_DATA['TextDomain'] ); ?></a>
						<?php else : ?>
							<?php echo __( 'This course is not currently accessible.', BF2_DATA['TextDomain'] ); ?>
						<?php endif; ?>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</article>
	</main>
</section>
<?php endif; ?>
<?php
get_footer();
