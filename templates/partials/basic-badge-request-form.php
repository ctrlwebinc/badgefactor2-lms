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
 * @phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
 */

?>
<?php
$current_user = wp_get_current_user();
?>
<form class="c-bf2__form">
	<label class="c-bf2__label" for="content"><?php echo __( 'Badge Request', BF2_DATA['TextDomain'] ); ?></label>
	<!-- <input class="c-bf2__input" type="text" name="content" required> -->
	<textarea class="c-bf2__input" name="content" required></textarea>
	<input type="hidden" name="action" value="submit_badge_request_form">
	<input type="hidden" name="badge_id" value="<?php echo $badge->entityId; ?>">
	<input type="hidden" name="type" value="basic">
	
	<input class="c-bf2__submit" type="submit">
	
</form>
