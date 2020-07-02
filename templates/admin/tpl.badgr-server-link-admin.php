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
 */

?>
<div class="cmb-row table-layout">
	<div class="cmb-th">
		<label for="badgr_server_status">Server Admin</label>
	</div>
	<div class="cmb-td">
        <?php
            if ( null == ( $badgr_admin_user = \BadgeFactor2\BadgrUser::get_admin_instance( ) ) ) {
                echo '<a href="' . site_url( \BadgeFactor2\BadgrClient::START_ADMIN_LINK_URL) . '" class="button button-primary">Link admin account</a>';
            } else {
                echo $badgr_admin_user->get_wp_username();
            }
        ?>
	</div>
</div>