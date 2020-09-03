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

get_header();

$customPostType 	  = 'badge-page';
$customTaxonomy 	  = 'badge-category';
$termsByBadgeCategory = get_terms($customTaxonomy);

?>

<main class="section-inner" <?php post_class(); ?> id="post-<?php the_ID(); ?>" role="main">
	<div class="c-bf2">

		<header class="c-bf2__header">
			<h1 class="c-bf2__title"><?php echo __( 'Badges' ); ?></h1>
		</header>

		<div class="c-bf2__body">
			<?php
			foreach($termsByBadgeCategory as $custom_term) {

				wp_reset_query();

				$args = array('post_type' => $customPostType,
					'tax_query' => array(
						array(
							'taxonomy' => $customTaxonomy,
							'field'    => 'slug',
							'terms'    => $custom_term->slug
						)
					)
				);

				$loop = new WP_Query($args);
				
				if($loop->have_posts()) { ?>
					<section class="c-bf2__section">
						<h2 class="c-bf2__section__title"><?php echo $custom_term->name; ?></h2>
						<p class="c-bf2__section__description">
							<?php echo $custom_term->description;?>
						</p>
						<div class="c-bf2__list__items">
							<?php
							while($loop->have_posts()) : $loop->the_post();
								
								$badge_entity_id = get_post_meta( $post->ID, 'badge', true );
								$badge           = BadgeFactor2\Models\BadgeClass::get( $badge_entity_id );
								$issuer          = BadgeFactor2\Models\Issuer::get( $badge->issuer ); ?>

								<div class="c-bf2__badge c-bf2__list__item">
									<a class="c-bf2__badge__inner" href="<?php echo get_permalink($post->ID); ?>">
										<img class="c-bf2__badge__image" src="<?php echo $badge->image; ?>" alt="<?php echo $badge->name; ?>">
										<h3 class="c-bf2__badge__title"><?php echo $badge->name; ?></h3>
									</a>
								</div>
								
							<?php
							endwhile;
							?>
						</div>
					</section>
				<?php
				}
				?>
			<?php
			}
			?>
		</div>

	</div>
</main>
<?php
get_footer();
