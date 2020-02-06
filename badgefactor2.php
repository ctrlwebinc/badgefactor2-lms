<?php
/**
 * Plugin Name: Badge Factor 2
 * Plugin URI: https://github.com/ctrlwebinc/badgefactor2
 * GitHub Plugin URI: https://ctrlwebinc/badgefactor2
 * Description: Issues and manages Open Badges with Badgr server
 * Author: ctrlweb
 * Version: 1.0.0
 * Author URI: https://badgefactor2.com/
 * License: GNU AGPL
 * Text Domain: badgefactor2
 * Domain Path: /languages
 */

/*
 * Badge Factor 2
 * Copyright (C) 2019 ctrlweb, ctrlweb
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

namespace BadgeFactor2;

defined( 'ABSPATH' ) || exit;

// Define BF2_FILE.
if ( ! defined( 'BF2_FILE' ) ) {
	define( 'BF2_FILE', __FILE__ );
}

// Include the main WooCommerce class.
if ( ! class_exists( 'BadgeFactor2' ) ) {
	require_once dirname( __FILE__ ) . '/src/class.badgefactor2.php';
}

/**
 * Returns the main instance of BadgeFactor2.
 *
 * @since  2.0.0-alpha
 * @return BadgeFactor2
 */
function BadgeFactor2() {
	return BadgeFactor2::instance();
}

// Global for backwards compatibility.
$GLOBALS['badgefactor2'] = BadgeFactor2();
