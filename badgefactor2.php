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
 * Plugin Name: Badge Factor 2
 * Plugin URI: https://github.com/ctrlwebinc/badgefactor2
 * GitHub Plugin URI: https://github.com/ctrlwebinc/badgefactor2
 * Description: Issues and manages Open Badges with Badgr server
 * Author: ctrlweb
 * Version: 2.0.0-alpha
 * Author URI: https://badgefactor2.com/
 * License: GNU AGPL
 * Text Domain: badgefactor2
 * Domain Path: /languages
 *
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound
 */

namespace BadgeFactor2;

defined( 'ABSPATH' ) || exit;

load_plugin_textdomain( 'badgefactor2', false, basename( dirname( __FILE__ ) ) . '/languages' );

// Define BF2_FILE.
if ( ! defined( 'BF2_FILE' ) ) {
	define( 'BF2_FILE', __FILE__ );
}

// Include the main BadgeFactor2 class.
if ( ! class_exists( 'BadgeFactor2' ) ) {
	require_once dirname( __FILE__ ) . '/src/class-badgefactor2.php';
}

/**
 * Returns the main instance of BadgeFactor2.
 *
 * @since  2.0.0-alpha
 * @return BadgeFactor2
 */
function badge_factor_2() {
	return BadgeFactor2::instance();
}

// Global for backwards compatibility.
$GLOBALS['badgefactor2'] = badge_factor_2();
