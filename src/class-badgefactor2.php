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

use BadgeFactor2\Admin\CMB2_Fields\Addons;
use BadgeFactor2\Admin\CMB2_Fields\Recipient;
use BadgeFactor2\Admin\CMB2_Fields\Badge;
use BadgeFactor2\Admin\CMB2_Fields\Badge_Request_Approver;
use BadgeFactor2\Admin\CMB2_Fields\Badge_Request_Content;
use BadgeFactor2\Admin\CMB2_Fields\Badge_Request_Rejection_Reason;
use BadgeFactor2\Admin\CMB2_Fields\Badge_Request_Revision_Reason;
use BadgeFactor2\Admin\CMB2_Fields\Badge_Request_Status;
use BadgeFactor2\Admin\CMB2_Fields\Badge_Request_Type;
use BadgeFactor2\Admin\CMB2_Fields\Dates;
use BadgeFactor2\Admin\Notices;
use BadgeFactor2\Helpers\Constant;
use BadgeFactor2\Widgets\User_Assertions_Widget;

/**
 * Badge Factor 2 Main Class.
 */
class BadgeFactor2 {


	/**
	 * Badge Factor 2 Version
	 *
	 * @var string
	 */
	public $version = '1.6.2';

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
		Badgr_Public_Urls::init_hooks();
		AssertionPrivacy::init_hooks();

		// Shortcodes.
		Shortcodes\Badges::init_hooks();

		// Widgets.
		Widgets::init_hooks();

		// Badgr.
		BadgrClient::init_hooks();
		BadgrUser::init_hooks();

		// Post Types.
		Post_Types\BadgePage::init_hooks();
		Post_Types\BadgeRequest::init_hooks();
		Post_Types\ParcoursBadge::init_hooks();
		// Roles.
		Roles\Approver::init_hooks();

		// Helpers with hooks.
		Helpers\SocialShare::init_hooks();
		Helpers\BuddypressXProfile::init_hooks();

		// Gateway
		LaravelBadgesUtilityGateway::init_hooks();

		// Admin.
		if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			BadgeFactor2_Admin::init_hooks();
			BuddyPress::init_hooks();
			Addons::init_hooks();
			Badge::init_hooks();
			Badge_Request_Approver::init_hooks();
			Badge_Request_Content::init_hooks();
			Badge_Request_Rejection_Reason::init_hooks();
			Badge_Request_Revision_Reason::init_hooks();
			Badge_Request_Status::init_hooks();
			Badge_Request_Type::init_hooks();
			Dates::init_hooks();
			Recipient::init_hooks();
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
		require_once 'phar://' . BF2_ABSPATH . 'lib/league-guzzlehttp-intervention.phar/vendor/autoload.php';

		// Helpers.
		require_once BF2_ABSPATH . 'src/helpers/class-buddypress.php';
		require_once BF2_ABSPATH . 'src/helpers/class-data-import.php';
		require_once BF2_ABSPATH . 'src/helpers/class-migration.php';
		require_once BF2_ABSPATH . 'src/helpers/class-template.php';
		require_once BF2_ABSPATH . 'src/helpers/class-text.php';
		require_once BF2_ABSPATH . 'src/helpers/class-social-share.php';
		require_once BF2_ABSPATH . 'src/helpers/class-buddypress-xprofile.php';

		// Traits.
		require_once BF2_ABSPATH . 'src/core/trait-singleton.php';
		require_once BF2_ABSPATH . 'src/core/trait-paginatable.php';
		require_once BF2_ABSPATH . 'src/core/trait-wp-sortable.php';

		// Interfaces.
		require_once BF2_ABSPATH . 'src/core/interface-badgr-entity.php';
		require_once BF2_ABSPATH . 'src/core/interface-page-controller.php';

		// Core Classes.
		require_once BF2_ABSPATH . 'src/core/class-badgrclient.php';
		require_once BF2_ABSPATH . 'src/core/class-badgrprovider.php';
		require_once BF2_ABSPATH . 'src/core/class-badgruser.php';
		require_once BF2_ABSPATH . 'src/core/class-page-controller.php';
		require_once BF2_ABSPATH . 'src/core/class-assertion-privacy.php';


		// Models.
		require_once BF2_ABSPATH . 'src/models/class-issuer.php';
		require_once BF2_ABSPATH . 'src/models/class-badgeclass.php';
		require_once BF2_ABSPATH . 'src/models/class-assertion.php';

		// Controllers.
		require_once BF2_ABSPATH . 'src/controllers/class-assertion-controller.php';
		require_once BF2_ABSPATH . 'src/controllers/class-badgepage-controller.php';
		require_once BF2_ABSPATH . 'src/controllers/class-badgerequest-controller.php';
		require_once BF2_ABSPATH . 'src/controllers/class-issuer-controller.php';
		
		require_once BF2_ABSPATH . 'src/controllers/class-parcours-controller.php';
		// Shortcodes.
		require_once BF2_ABSPATH . 'src/public/shortcodes/class-badges.php';
		require_once BF2_ABSPATH . 'src/public/shortcodes/class-issuers.php';

		// Widgets.
		require_once BF2_ABSPATH . 'src/public/widgets/class-user-assertions-widget.php';
		require_once BF2_ABSPATH . 'src/public/widgets/class-widgets.php';

		// Post Types.
		require_once BF2_ABSPATH . 'src/roles/class-approver.php';
		require_once BF2_ABSPATH . 'src/post-types/class-badgepage.php';
		require_once BF2_ABSPATH . 'src/post-types/class-badgerequest.php';
		
		require_once BF2_ABSPATH . 'src/post-types/class-parcours_badge.php';

		// Public (site) class.
		require_once BF2_ABSPATH . 'src/public/class-badgefactor2-public.php';
		require_once BF2_ABSPATH . 'src/public/class-badgr-public-urls.php';

		// Admin / CLI classes.
		if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			require_once BF2_ABSPATH . 'src/admin/class-notices.php';
			require_once BF2_ABSPATH . 'src/admin/class-badgefactor2-admin.php';
			require_once BF2_ABSPATH . 'src/admin/class-badgr-list.php';
			require_once BF2_ABSPATH . 'src/admin/buddypress/class-buddypress.php';
			require_once BF2_ABSPATH . 'src/admin/cmb2-fields/class-addons.php';
			require_once BF2_ABSPATH . 'src/admin/cmb2-fields/class-badge.php';
			require_once BF2_ABSPATH . 'src/admin/cmb2-fields/class-badge-request-approver.php';
			require_once BF2_ABSPATH . 'src/admin/cmb2-fields/class-badge-request-content.php';
			require_once BF2_ABSPATH . 'src/admin/cmb2-fields/class-badge-request-rejection-reason.php';
			require_once BF2_ABSPATH . 'src/admin/cmb2-fields/class-badge-request-revision-reason.php';
			require_once BF2_ABSPATH . 'src/admin/cmb2-fields/class-badge-request-status.php';
			require_once BF2_ABSPATH . 'src/admin/cmb2-fields/class-badge-request-type.php';
			require_once BF2_ABSPATH . 'src/admin/cmb2-fields/class-dates.php';
			require_once BF2_ABSPATH . 'src/admin/cmb2-fields/class-recipient.php';
			require_once BF2_ABSPATH . 'src/admin/lists/class-issuers.php';
			require_once BF2_ABSPATH . 'src/admin/lists/class-badges.php';
			require_once BF2_ABSPATH . 'src/admin/lists/class-assertions.php';
		}

		// Gateway
		require_once BF2_ABSPATH . 'src/gateways/class-laravel-badges-utility-gateway.php';

		// CLI-only classes.
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			require_once BF2_ABSPATH . 'src/cli/class-badgefactor2-cli.php';
			require_once BF2_ABSPATH . 'src/cli/class-badgr-cli.php';
			require_once BF2_ABSPATH . 'src/cli/class-laravel-badges-utility-cli.php';
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
		require_once dirname( BF2_FILE ) . '/src/helpers/class-constant.php';

		$upload_dir = wp_upload_dir( null, false );

		Constant::define( 'BF2_ABSPATH', dirname( BF2_FILE ) . '/' );
		Constant::define( 'BF2_BASEURL', plugin_dir_url( BF2_FILE ) );
		Constant::define( 'BF2_PLUGIN_BASENAME', plugin_basename( BF2_FILE ) );
		Constant::define( 'BF2_VERSION', $this->version );
		Constant::define( 'BF2_LOG_DIR', $upload_dir['basedir'] . '/bf2-logs/' );

		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		Constant::define( 'BF2_DATA', get_plugin_data( BF2_FILE ) );
	}
}
