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
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.VariableConstantNameFound
 */

namespace BadgeFactor2;

use BadgeFactor2\Admin\CMB2_Field_Addons;
use BadgeFactor2\Admin\Notices;

/**
 * Badge Factor 2 Main Class.
 */
class BadgeFactor2 {


	/**
	 * Badge Factor 2 Version
	 *
	 * @var string
	 */
	public $version = '2.0.0-alpha';

	/**
	 * The single instance of the class.
	 *
	 * @var BadgeFactor2
	 * @since 2.0.0-alpha
	 */
	protected static $_instance = null;

	/**
	 * The plugin's required WordPress version.
	 *
	 * @var string
	 *
	 * @since 2.0.0-alpha
	 */
	public static $required_wp_version = '4.9.9';

	/**
	 * Whether or not the plugin is initialized.
	 *
	 * @var boolean
	 */
	private static $initialized = false;

	/**
	 * Main Badge Factor 2 Instance.
	 *
	 * Ensures only one instance of Badge Factor 2 is loaded or can be loaded.
	 *
	 * @return BadgeFactor2 - Main instance.
	 * @since 2.0.0-alpha
	 * @static
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * BadgeFactor2 Constructor.
	 */
	public function __construct() {
		$this->define_constants();
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Badge Factor 2 Init Hooks.
	 *
	 * @return void
	 */
	public static function init_hooks() {

		// Core.
		BadgeFactor2_Public::init_hooks();

		// Shortcodes.
		Shortcodes\Badges::init_hooks();

		// Badgr.
		BadgrClient::init_hooks();
		BadgrUser::init_hooks();

		// Post Types.
		Post_Types\BadgePage::init_hooks();
		Post_Types\BadgeRequest::init_hooks();

		// Roles.
		Roles\Approver::init_hooks();

		// Admin.
		if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			BadgeFactor2_Admin::init_hooks();
			CMB2_Field_Addons::init_hooks();
			Notices::init_hooks();
		}

		self::$initialized = true;
	}

	/**
	 * Badge Factor 2 Includes.
	 *
	 * @return void
	 */
	public function includes() {

		// Libraries.
		require_once BF2_ABSPATH . 'lib/CMB2/init.php';
		require_once BF2_ABSPATH . 'lib/cmb-field-select2/cmb-field-select2.php';
		require_once 'phar://' . BF2_ABSPATH . 'lib/league-oauth2-client.phar/vendor/autoload.php';

		// Traits.
		require_once BF2_ABSPATH . 'src/core/trait-singleton.php';
		require_once BF2_ABSPATH . 'src/core/trait-paginatable.php';
		require_once BF2_ABSPATH . 'src/core/trait-wp-sortable.php';

		// Interfaces.
		require_once BF2_ABSPATH . 'src/core/interface-badgr-entity.php';

		// Helpers.
		require_once BF2_ABSPATH . 'src/helpers/class-template.php';

		// Core Classes.
		require_once BF2_ABSPATH . 'src/core/class-badgrclient.php';
		require_once BF2_ABSPATH . 'src/core/class-badgrprovider.php';
		require_once BF2_ABSPATH . 'src/core/class-badgruser.php';

		// Models.
		require_once BF2_ABSPATH . 'src/models/class-issuer.php';
		require_once BF2_ABSPATH . 'src/models/class-badgeclass.php';
		require_once BF2_ABSPATH . 'src/models/class-assertion.php';

		// Shortcodes.
		require_once BF2_ABSPATH . 'src/public/shortcodes/class-badges.php';
		require_once BF2_ABSPATH . 'src/public/shortcodes/class-issuers.php';

		// Post Types.
		require_once BF2_ABSPATH . 'src/roles/class-approver.php';
		require_once BF2_ABSPATH . 'src/post-types/class-badgepage.php';
		require_once BF2_ABSPATH . 'src/post-types/class-badgerequest.php';

		// Public (site) class.
		require_once BF2_ABSPATH . 'src/public/class-badgefactor2-public.php';

		// Admin / CLI classes.
		if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			require_once BF2_ABSPATH . 'src/admin/class-notices.php';
			require_once BF2_ABSPATH . 'src/admin/class-badgefactor2-admin.php';
			require_once BF2_ABSPATH . 'src/admin/class-badgr-list.php';
			require_once BF2_ABSPATH . 'src/admin/class-cmb2-field-addons.php';
			require_once BF2_ABSPATH . 'src/admin/lists/class-issuers.php';
			require_once BF2_ABSPATH . 'src/admin/lists/class-badges.php';
			require_once BF2_ABSPATH . 'src/admin/lists/class-assertions.php';
		}

		// CLI-only classes.
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			require_once BF2_ABSPATH . 'src/cli/class-badgefactor2-cli.php';
			require_once BF2_ABSPATH . 'src/cli/class-badgr-cli.php';
		}
	}


	/**
	 * Checks whether or not plugin is initialized.
	 *
	 * @return boolean
	 */
	public static function is_initialized() {
		return self::$initialized;
	}


	/**
	 * Define BadgeFactor2 Constants.
	 *
	 * @return void
	 */
	private function define_constants() {
		$upload_dir = wp_upload_dir( null, false );

		$this->define( 'BF2_ABSPATH', dirname( BF2_FILE ) . '/' );
		$this->define( 'BF2_BASEURL', plugin_dir_url( BF2_FILE ) );
		$this->define( 'BF2_PLUGIN_BASENAME', plugin_basename( BF2_FILE ) );
		$this->define( 'BF2_VERSION', $this->version );
		$this->define( 'BF2_LOG_DIR', $upload_dir['basedir'] . '/bf2-logs/' );

		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		$this->define( 'BF2_DATA', get_plugin_data( BF2_FILE ) );
	}

	/**
	 * Define constant if not already set.
	 *
	 * @param string      $name Constant name.
	 * @param string|bool $value Constant value.
	 * @return void
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

}
