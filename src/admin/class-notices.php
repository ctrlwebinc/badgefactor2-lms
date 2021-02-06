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
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound
 * @phpcs:disable WordPress.WP.I18n.NonSingularStringLiteralDomain
 */

namespace BadgeFactor2\Admin;

/**
 * Notices.
 */
class Notices {

	const TYPE_ALERT   = 'alert';
	const TYPE_ERROR   = 'error';
	const TYPE_SUCCESS = 'success';

	/**
	 * Notices array.
	 *
	 * @var array Notices.
	 */
	private static $notices = array();


	/**
	 * Init hooks.
	 *
	 * @return void
	 */
	public static function init_hooks() {
		add_action( 'admin_print_styles', array( __CLASS__, 'display' ) );
	}


	/**
	 * Adds notice.
	 *
	 * @param string $type Notice Type.
	 * @param string $message Notice Message.
	 */
	public static function add( $type, $message ) {

		switch ( $type ) {
			case self::TYPE_ALERT:
				$class = 'notice notice-alert';
				break;
			case self::TYPE_SUCCESS:
				$class = 'notice notice-success';
				break;
			case self::TYPE_ERROR:
			default:
				$class = 'notice notice-error';
				break;
		}

		$notice        = array(
			'class'   => $class,
			'message' => $message,
		);
		self::$notices = array_unique( array_merge( self::get(), array( $notice ) ) );
	}


	/**
	 * Gets notices array.
	 *
	 * @return array
	 */
	public static function get() {
		return self::$notices;
	}


	/**
	 * Displays admin notices.
	 *
	 * @return void
	 */
	public static function display() {
		foreach ( self::get() as $notice ) {
			add_action( 'admin_notices', self::print( $notice ) );
		}
	}


	/**
	 * Outputs admin notice.
	 *
	 * @param  string $notice Notice to print.
	 * @return void
	 */
	public static function print( $notice ) {
		echo '<div class="' . $notice['class'] . ' is-dismissible"><p>' . $notice['message'] . '</p>';
		echo '<button type="button" class="notice-dismiss"><span class="screen-reader-text">';
		echo __( 'Dismiss this message.', BF2_DATA['TextDomain'] );
		echo '</span></button></div>';
	}
}
