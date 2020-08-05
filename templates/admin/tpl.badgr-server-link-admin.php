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
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 */

?>
<div class="cmb-row table-layout">
	<div class="cmb-th">
		<label for="badgr_server_status">Server Admin</label>
	</div>
	<div class="cmb-td">
		<?php
		$badgr_admin_user = \BadgeFactor2\BadgrUser::get_admin_instance();
		if ( null === $badgr_admin_user ) {
			$protected_url = wp_nonce_url( \BadgeFactor2\BadgrClient::START_ADMIN_LINK_URL, \BadgeFactor2\BadgrClient::ADMIN_INIT_NONCE_ACTION );
			echo '<a href="' . $protected_url . '" class="button button-primary">Link admin account</a>';
		} else {
			echo $badgr_admin_user->get_wp_username();
		}
		?>
	</div>
</div>
