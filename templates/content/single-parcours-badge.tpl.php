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
use Cadre21_Registration\C21_Helper;
use BadgeFactor2\Helpers\Template;
$member = isset( $wp_query->query_vars['member'] ) ? get_user_by( 'slug', $wp_query->query_vars['member'] ) : null;
global $bf2_template;
$fields = $bf2_template->fields;
$path_elements = explode('/parcours/', get_permalink());
$pathway_slug = rtrim(end($path_elements),'/');

?>
<div class="c-bf2 c-bf2--single section-inner" <?php post_class(); ?> id="post-<?php the_ID(); ?>">

	<div id="bf2-pathways-page-wrapper" data-pathways-pathway_slug="<?php echo $pathway_slug; ?>" class="main-badge-page main-parcours-page-warp">
		<?php echo c21_breadcrumbs( $post ) ?>

			<div class="badge-page-warp parcours-page-warp">
				<div id="bf2-pathways-action-list"  class="badge-page-sidebar parcours-sidebar">
					
				</div>

				<div class="badge-page-content parcours-page-content">
					<div class="course-features">
						<?php if(isset($fields["parcours_latest_update_date"]) && !empty($fields["parcours_latest_update_date"])){ ?>
						<div class="course-feature-last-update">
							<i class="fa fa-clock-o"></i>Mise à jour : <span><?php echo C21_Helper::year_and_season_from_date(  $fields["parcours_latest_update_date"] ); ?></span>
						</div>
						<?php } ?>
						<?php if(isset($fields["required_time_hours"]) && !empty($fields["required_time_hours"])){ ?>
						<div class="course-page-feature-required-time">
							<span class="chart-label">Temps reconnu</span>
							<span class="required-time"><?php echo ( $fields["required_time_hours"] ); ?> heures</span>
						</div>
						<?php } ?>
					</div>

					<h3 class="body-section-title"><?php the_title(); ?></h3>
					<?php the_content(); ?>

					<div class="section-separator"></div>
					
					<div id="bf2-pathways-diagram-container" >
					</div>
					
					<div class="section-separator"></div>
					
					<div class="required_techniques feature-chart">
							<?php if(isset($fields["exigence_technologiques"]) && !empty($fields["exigence_technologiques"])){ ?>
							<div class="row">
								<div class="course-chart-label chart-label">
									Exigences technologiques
								</div>
								<div class="course-chart-value">
									<?php echo nl2br( $fields["exigence_technologiques"] ); ?>
								</div>
							</div>
							<?php } ?>
							
							<?php if(isset($fields["public_cible"]) && !empty($fields["public_cible"])){ ?>
							<div class="row">
								<div class="course-chart-label chart-label">
									Public cible
								</div>
								<div class="course-chart-value">
									<?php echo nl2br( $fields["public_cible"] ); ?>
								</div>
							</div>
							<?php } ?>
					</div>

					<div class="section-separator"></div>
				

					<div class="support-links">
						<div class="row">
							<div class="technical-support">
								<a href="<?php echo '/soutien-technique/?badge='.urlencode($pathway_slug); ?>" class="c21-secondary-transparent-button" target="_blank">Soutien technique</a>
							</div>
							<div class="support">
								<a href="<?php echo '/soutien-daccompagnement/?badge='.urlencode($pathway_slug); ?>" class="c21-secondary-transparent-button" target="_blank">Soutien d’accompagnement</a>
							</div>
						</div>
					</div>

				</div>
				<div id="bf2-steps-modal" class="bu-modal modal" tabindex="-1" role="dialog">
					<div class="modal-dialog bu-modal-dialog bu-modal-xl modal-xl" role="document">
						<div class="bu-modal-content modal-content">
						</div>
					</div>
				</div>
			</div>
		
	</div>
</div>
