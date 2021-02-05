<?php
/**
 * Badge Factor 2
 * Copyright (C) 2021 ctrlweb
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

use BadgeFactor2\Models\Assertion;
use BadgeFactor2\Post_Types\BadgePage;

/*
 * You can override this template by copying it in your theme, in a
 * badgefactor2/ subdirectory, and modifying it there.
 */

global $bp;
$user = get_user_by( 'id', $bp->displayed_user->id );
$assertions = Assertion::all_for_user( $user );

?>

<main class="section-inner" role="main">
	<div class="c-bf2">

		<header class="c-bf2__header">
			<h2 class="c-bf2__title"><?php echo __( 'Badges obtained', BF2_DATA['TextDomain'] ); ?></h2>
		</header>

		<div class="c-bf2__body">
			<section class="c-bf2__section">
				<div class="c-bf2__list__items">
				<?php foreach ( $assertions as $assertion ) : ?>
					<?php
					$badge     = BadgeFactor2\Models\BadgeClass::get( $assertion->badgeclass );
					$issuer    = BadgeFactor2\Models\Issuer::get( $badge->issuer );
					$badgepage = BadgePage::get_by_badgeclass_id( $assertion->badgeclass );
					?>

					<div class="c-bf2__badge c-bf2__list__item">
						<a class="c-bf2__badge__inner" href="badges/<?php echo $badgepage->post_name; ?>">
							<img class="c-bf2__badge__image" src="<?php echo $assertion->image; ?>" alt="<?php echo $assertion->name; ?>">
							<h3 class="c-bf2__badge__title"><?php echo $badge->name; ?></h3>
						</a>
					</div>
				<?php endforeach; ?>
				</div>
			</section>
		</div>
	</div>
</main>