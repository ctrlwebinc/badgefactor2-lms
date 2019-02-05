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


class Issuer {

	private static $initiated = false;

	public static function init_hooks() {

		add_action('init', [Issuer::class, 'init'], 9966);
		add_action('cmb2_admin_init', [Issuer::class, 'cmb2_admin_init']);

		self::$initiated = true;
	}

	public static function init() {
		$labels = [
			'name' => __("Issuers", 'badgefactor2'),
			'singular_name' => __("Issuer", 'badgefactor2'),
			'add_new_item' => __("Add New Issuer", 'badgefactor2'),
			'edit_item' => __("Edit Issuer", 'badgefactor2'),
			'search_items' => __("Search Issuers", 'badgefactor2'),
			'not_found' => __("No issuers found.", 'badgefactor2'),
			'not_found_in_trash' => __("No issuers found in Trash.", 'badgefactor2'),
		];

		register_post_type('issuer', [
			'labels' => $labels,
			'public' => true,
			'show_in_menu' => 'badgefactor2',
		]);
	}

	public static function cmb2_admin_init() {
		$cmb = new_cmb2_box([
			'id' => 'issuer_fields',
			'title'         => __('Issuer Fields', 'badgefactor2'),
			'object_types'  => ['issuer'],
			'context'       => 'normal',
			'priority'      => 'high',
			'show_names'    => true, // Show field names on the left
			// 'cmb_styles' => false, // false to disable the CMB stylesheet
			// 'closed'     => true, // Keep the metabox closed by default
		]);

		$cmb->add_field([
			'name' => 'email',
			'desc' => '',
			'id'   => '_email',
			'type' => 'text_emails',

		]);
	}
}