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
?>
<main id="site-content" role="main">
	<header class="archive-header has-text-align-center header-footer-group">
		<div class="archive-header-inner section-inner medium">
			<h1 class="archive-title"><span class="color-accent"><?php echo __( 'Badges' ); ?></h1>
		</div><!-- .archive-header-inner -->
	</header><!-- .archive-header -->

<?php
if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();

		$badge_entity_id = get_post_meta( $post->ID, 'badge', true );
		$badge           = BadgeFactor2\Models\BadgeClass::get( $badge_entity_id );
		$issuer          = BadgeFactor2\Models\Issuer::get( $badge->issuer );

		?>
		<article <?php post_class(); ?> id="post-<?php the_ID(); ?>">
			<header class="badge-page-header">
				<div class="badge-page-header-inner">
					<?php the_title( '<h2 class="badge-page-title"><a href="' . esc_url( get_permalink() ) . '">', '</a></h2>' ); ?>
				</div>
			</header>
			<div class="badge-page-inner">
				<div class="badge">
					<div class="badge-inner">
						<figure>
							<img src="<?php echo $badge->image; ?>" alt="<?php echo $badge->name; ?>">
						</figure>
						<div class="badge-issued">
							<h3><?php echo __( 'Issued by', BF2_DATA['TextDomain'] ); ?></h3>
							<a target="_blank" href="<?php echo $issuer->url; ?>"><?php echo $issuer->name; ?></a>
						</div>
					</div>
				</div>
				<div class="entry-content">
					<?php the_excerpt(); ?>
				</div>
			</div>
			<div class="section-inner">
			<?php
			edit_post_link();
			?>
			</div>
		</article>
			<?php if ( $wp_query->current_post + 1 !== $wp_query->post_count ) : ?>
		<hr class="post-separator styled-separator is-style-wide section-inner" aria-hidden="true">
		<?php endif; ?>
		<?php
	endwhile;
endif;
?>
</main>
<?php
get_footer();
