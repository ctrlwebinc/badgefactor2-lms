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

namespace BadgeFactor2;

use BadgeFactor2\Helpers\Migration;
use WP_CLI;
use WP_CLI_Command;
use BadgeFactor2\Post_Types\BadgePage;
use BadgeFactor2\AssertionPrivacy;

WP_CLI::add_command( 'bf2-lbu', LaravelBadgesUtility_CLI::class );

/**
 * Interact with Laravel Badges Utility.
 */
class LaravelBadgesUtility_CLI extends WP_CLI_Command {

	/**
	 * Undocumented function.
	 *
	 * @param array $args Arguments.
	 * @param array $assoc_args Associative arguments.
	 * @return void
	 */
	public function hello_world( $args, $assoc_args ) {

		WP_CLI::success( 'LBU gateway says hello');
	}
}
