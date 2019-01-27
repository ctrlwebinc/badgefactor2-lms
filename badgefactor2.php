<?php
/**
 * Plugin Name: Badge Factor 2
 * Plugin URI: https://github.com/DigitalPygmalion/badgefactor2
 * GitHub Plugin URI: https://DigitalPygmalion/badgefactor2
 * Description: Issues and manages Open Badges with Badgr server
 * Author: Digital Pygmalion
 * Version: 1.0.0
 * Author URI: https://digitalpygmalion.com/
 * License: GNU AGPL
 * Text Domain: badgefactor2
 * Domain Path: /languages
 */

/*
 * Badge Factor 2
 * Copyright (C) 2019 Digital Pygmalion Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace BadgeFactor2;

require dirname(__FILE__).'/vendor/autoload.php';
require_once dirname(__FILE__).'/CMB2/init.php';
require_once dirname(__FILE__).'/class.badgefactor2.php';

if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
	require_once( dirname(__FILE__).'/class.badgefactor2-admin.php');
	add_action( 'init', array( BadgeFactor2_Admin::class, 'init' ) );
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once( dirname(__FILE__).'/class.badgefactor2-cli.php');
}

add_action( 'init', array( BadgeFactor2::class, 'init' ) );