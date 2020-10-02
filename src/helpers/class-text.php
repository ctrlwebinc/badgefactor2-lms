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

namespace BadgeFactor2\Helpers;

/**
 * Text helper class.
 */
class Text {

	/**
	 * Generates a random password.
	 *
	 * @return string Randomly generated password.
	 */
	public static function generate_random_password() {
		return self::generate_random_string( 11, 'p' );
	}

	/**
	 * Generate random string.
	 *
	 * @param integer $length Length of string.
	 * @param string  $first_letter First letter.
	 * @return string
	 */
	public static function generate_random_string( $length = 10, $first_letter = '' ) {
		$alphabet        = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
		$alpha_max_index = strlen( $alphabet ) - 1;
		$random_string   = $first_letter;
		for ( $i = 0; $i < $length; $i++ ) {
			$random_string .= $alphabet[ rand( 0, $alpha_max_index ) ];
		}
		return $random_string;

	}
}
