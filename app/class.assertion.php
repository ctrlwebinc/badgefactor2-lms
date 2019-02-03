<?php
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


class Assertion {

	private static $initiated = false;

	public static function init() {
		if (!self::$initiated) {
			self::init_hooks();
		}
	}

	public static function init_hooks() {
		$labels = [
			'name' => __("Assertions", 'badgefactor2'),
			'singular_name' => __("Assertion", 'badgefactor2'),
			'add_new_item' => __("Add New Assertion", 'badgefactor2'),
			'edit_item' => __("Edit Assertion", 'badgefactor2'),
			'search_items' => __("Search Assertions", 'badgefactor2'),
			'not_found' => __("No assertions found.", 'badgefactor2'),
			'not_found_in_trash' => __("No assertions found in Trash.", 'badgefactor2'),
		];
		register_post_type('assertion', [
			'labels' => $labels,
			'public' => true,
			'show_in_menu' => 'badgefactor2',
		]);
		self::$initiated = true;
	}
}