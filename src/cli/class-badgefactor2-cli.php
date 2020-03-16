<?php
/*
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
 */

/**
 * @package Badge_Factor_2
 */

namespace BadgeFactor2;

use WP_CLI;
use WP_CLI_Command;

WP_CLI::add_command('bf2', BadgeFactor2_CLI::class);

/**
 * Manage Open Badges.
 */
class BadgeFactor2_CLI extends WP_CLI_Command
{
	public function add_user( $args, $assoc_args ) {

		if (count($args) != 3) {
			WP_CLI::error('Usage: add_user firstname lastname email');
		}

		if (!filter_var($args[2], FILTER_VALIDATE_EMAIL)) {
			WP_CLI::error('Please provide a valid email as the 3rd argument');
		}

		$slug = BadgrProvider::add_user($args[0], $args[1], $args[2]);

		if ($slug) {
			WP_CLI::success('User added with slug ' . $slug);
		} else {
			WP_CLI::error('Adding user failed.');
		}
	}

	public function check_user_verified( $args, $assoc_args ) {
		if (count($args) != 1) {
			WP_CLI::error('Usage: check_user_verified slug');
		}

		$verified = BadgrProvider::check_user_verified($args[0]);

		if ($verified)
			WP_CLI::success('User is verified');
		else
			WP_CLI::success('User is not verified');
	}

	public function getUserBadgrInfo( $args, $assoc_args ) {
		if (count($args) != 1) {
			WP_CLI::error('Usage: getUserBadgrInfo user_id');
		}

		$user = get_userdata ($args[0]);

		if ($user == false)
			WP_CLI::error('No such user ' . $args[0]);

		$state = get_user_meta( $user->ID, 'badgr_user_state', true);
		$slug = get_user_meta( $user->ID, 'badgr_user_slug', true);
			
		WP_CLI::success(sprintf('User %s has state %s and slug %s', $args[0], $state, $slug));
	}

	public function listIssuers( $args, $assoc_args ) {
        if (count($args) != 0) {
            WP_CLI::error('Usage: listIssuers');
        }

        $issuers = BadgrProvider::getAllIssuers();
        if ( false == $issuers) {
            WP_CLI::error('Error retrieving issuers');
        }

        WP_CLI::success( 'Issuers successfully retrieved : ' . json_encode( $issuers ) );
	}
}
