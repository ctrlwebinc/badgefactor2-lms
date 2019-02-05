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


class Badge {

	private static $initialized = false;



	public static function init_hooks() {

		add_action('init', [Badge::class, 'init'], 9966);
		self::$initialized = true;
	}


	public static function init() {
		$labels = [
			'name' => __("Badges", 'badgefactor2'),
			'singular_name' => __("Badge", 'badgefactor2'),
			'add_new_item' => __("Add New Badge", 'badgefactor2'),
			'edit_item' => __("Edit Badge", 'badgefactor2'),
			'search_items' => __("Search Badges", 'badgefactor2'),
			'not_found' => __("No badges found.", 'badgefactor2'),
			'not_found_in_trash' => __("No badges found in Trash.", 'badgefactor2'),
		];
		register_post_type('badge', [
			'labels' => $labels,
			'public' => true,
			'show_in_menu' => 'badgefactor2',
		]);
	}
}