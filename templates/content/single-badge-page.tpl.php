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

// The Courses add-on and the WooCommerce add-on are installed.
if ( 1 === intval( get_query_var( 'form' ) ) &&
	class_exists( 'BadgeFactor2\BF2_Courses' ) &&
	class_exists( 'BadgeFactor2\BF2_WooCommerce' ) ) {

	// A Course is linked to the badge page.
	$course_id = get_post_meta( $post->ID, 'course', true );
	if ( $course_id ) {
		// A product is linked to the course.
		$product_id = get_post_meta( $course_id, 'course_product', true );
		if ( $product_id ) {
			// The client has not purchased this product, redirect to the product page.
			if ( ! wc_customer_bought_product( $current_user->user_email, $current_user->ID, $product_id ) ) {
				wp_redirect( get_permalink( $product_id ) );
				exit;
			}
		}
	}
}

$badge_page      = $post;
$badge_entity_id = get_post_meta( $post->ID, 'badge', true );
$badge_criteria  = get_post_meta( $post->ID, 'badge_criteria', true );
$badge           = BadgeFactor2\Models\BadgeClass::get( $badge_entity_id );
$issuer          = $badge ? BadgeFactor2\Models\Issuer::get( $badge->issuer ) : null;
$courses         = BadgeFactor2\Post_Types\BadgePage::get_courses( $post->ID );

?>

<main class="section-inner" <?php post_class(); ?> id="post-<?php the_ID(); ?>" role="main">
	<?php if ( 1 === intval( get_query_var( 'form' ) ) ) : ?>
		<?php if ( 1 === intval( get_query_var( 'autoevaluation' ) ) ) : ?>
			<?php include( Template::locate( 'partials/autoevaluation-form' ) ); ?>
		<?php else : ?>
			<?php include( Template::locate( 'partials/badge-request-form' ) ); ?>
		<?php endif; ?>
	<?php else : ?>
		<article class="c-bf2__section c-bf2__single">
			<header class="c-bf2__header">
				<h1 class="c-bf2__title"><?php echo $badge->name; ?></h1>
			</header>
			<div class="c-bf2__body">
				<h3 class="c-bf2__body__title"><?php echo __( 'Description', BF2_DATA['TextDomain'] ); ?></h3>
				<p class="c-bf2__body__content">
					<?php echo $badge_page->post_content; ?>
				</p>
				<h3 class="c-bf2__body__title"><?php echo __( 'Criteria', BF2_DATA['TextDomain'] ); ?></h3>
				<p class="c-bf2__body__content">
					<?php echo $badge_criteria; ?>
				</p>
			</div>
			<aside class="c-bf2__sidebar">
				<div class="c-bf2__badge">
					<div class="c-bf2__badge__inner">
						<img class="c-bf2__badge__image" src="<?php echo $badge->image; ?>" alt="<?php echo $badge->name; ?>">
						<h3 class="c-bf2__badge__title">
							<?php echo __( 'Issued by', BF2_DATA['TextDomain'] ); ?>
							<a target="_blank" href="<?php echo $issuer->url; ?>"><?php echo $issuer->name; ?></a>
						</h3>
						<div class="c-bf2__badge__actions">
							<?php if ( $courses ) : ?>
								<?php foreach ( $courses as $course ) : ?>
								<div class="c-bf2__badge__action">
									<?php if ( BadgeFactor2\Post_Types\Course::is_accessible() ) : ?>
										<a class="c-bf2__btn" href="<?php echo get_permalink( $course ); ?>"><?php echo __( 'Take this course', BF2_DATA['TextDomain'] ); ?></a>
									<?php elseif ( BadgeFactor2\Post_Types\Course::is_purchasable() ) : ?>
										<a class="c-bf2__btn" href="<?php echo get_permalink( $course ); ?>"><?php echo __( 'Get this course', BF2_DATA['TextDomain'] ); ?></a>
									<?php else : ?>
										<?php echo __( 'This course is not currently available.', BF2_DATA['TextDomain'] ); ?>
									<?php endif; ?>
								</div>
								<?php endforeach; ?>
							<?php endif; ?>
							<!-- <div class="c-bf2__badge__action">
								<a class="c-bf2__btn" href="http://badge-factor-2.test/badges/coincoin-badge/formulaire">Request this badge</a>
							</div> -->
						</div>
					</div>
				</div>
			</aside>
		</article>
	<?php endif; ?>
</main>
