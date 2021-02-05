<?php
/**
 * Badge Factor 2
 * Copyright (C) 2021 ctrlweb
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
 * @phpcs:disable WordPress.WP.I18n.NonSingularStringLiteralDomain
 */

namespace BadgeFactor2\Widgets;

use BadgeFactor2\Controllers\Assertion_Controller;
use BadgeFactor2\Helpers\Template;

/**
 * User Assertions Widget.
 */
class User_Assertions_Widget extends \WP_Widget {

	/**
	 * Hooks init.
	 *
	 * @return void
	 */
	public static function init_hooks() {
		add_action( 'widgets_init', array( self::class, 'load_widget' ), 10 );
	}


	/**
	 * Load widget hook.
	 *
	 * @return void
	 */
	public static function load_widget() {
		register_widget( self::class );
	}


	/**
	 * Constructor.
	 */
	function __construct() {
		parent::__construct(
			self::class,
			__( 'User Badges', BF2_DATA['TextDomain'] ),
			array(
				'description' => __( 'Badges list for current user.', BF2_DATA['TextDomain'] ),
			)
		);
	}


	/**
	 * Displays the widget.
	 *
	 * @param array $args Arguments.
	 * @param array $instance Instance.
	 * @return void
	 */
	public function widget( $args, $instance ) {

		// Before and after widget arguments are defined by themes.
		echo $args['before_widget'];
		echo Template::include_to_var( Assertion_Controller::archive( true ) );
		echo $args['after_widget'];
	}

	/**
	 * Widget admin form.
	 *
	 * @param array $instance Widget instance.
	 * @return void
	 */
	public function form( $instance ) {
	}

	/**
	 * Widget admin form update.
	 *
	 * @param array $new_instance New instance.
	 * @param array $old_instance Old instance.
	 * @return array Instance.
	 */
	public function update( $new_instance, $old_instance ) {
		return $new_instance;
	}

}
