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

/**
 * Singleton Trait.
 */
trait Singleton {

	protected static $_instance = array();

	/**
	 * Protected class constructor to prevent direct object creation.
	 */
	protected function __construct() { }

	/**
	 * Prevent object cloning
	 */
	final protected function __clone() { }

	/**
	 * To return new or existing Singleton instance of the class from which it is called.
	 * As it sets to final it can't be overridden.
	 *
	 * @return object Singleton instance of the class.
	 */
	final public static function get_instance() {

		/**
		 * Returns name of the class the static method is called in.
		 */
		$called_class = get_called_class();

		if ( ! isset( static::$_instance[ $called_class ] ) ) {

			static::$_instance[ $called_class ] = new $called_class();

		}

		return static::$_instance[ $called_class ];

	}

}
