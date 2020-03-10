<?php
/*
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
 */

/**
 * @package Badge_Factor_2
 */

namespace BadgeFactor2;

class BadgrProvider {

	public static function init_hooks() {
		add_action( 'init', array( BadgrProvider::class, 'init' ), 9966 );
		add_action( 'cmb2_admin_init', array( BadgrProvider::class, 'cmb2_admin_init' ) );
	}

	public static function init() {
		return;
	}

	public static function cmb2_admin_init() {
		return;
	}

	public static function addUser($firstname,$lastname,$email) {

		// Setup body
		$requestBody = [
		  'first_name'=> $firstname,
		  'last_name'=> $lastname,
		  'email'=> $email,
		  'url'=> '',
		  'telephone'=> '',
		  'slug'=> '',
		  'agreed_terms_version'=> 1,
		  'marketing_opt_in'=> false,
		  'has_password_set'=> false,
		  'source' => 'bf2',
		  'password' => 'password1234',
		];

		// Make POST request to /v1/user/profile
		$response = BadgrClient::post('/v1/user/profile', $requestBody);

		// Check for 201 response
		if (null !== $response && $response->getStatusCode() == 201) {
			// Return slug-entity_id or false if unsucessful
			$responseInfo = json_decode($response->getBody());
			if (isset($responseInfo->slug) && strlen($responseInfo->slug) > 0)
				return $responseInfo->slug;
		}

		return false;
	}

	public static function checkUserVerified($userEntityId) {

		// Make GET request to /v2/users/{slug-entity_id}
		$response = BadgrClient::get('/v2/users/' . $userEntityId);

		// Check for 200 response
		if (null !== $response && $response->getStatusCode() == 200) {
			// Check for a non-null recipient field
			$responseInfo = json_decode($response->getBody());
			if (isset($responseInfo->status->success) &&
				$responseInfo->status->success == true &&
				isset($responseInfo->result[0]) &&
					null != $responseInfo->result[0]->recipient)
				return true;
		}

		return false;
	}

	public static function updateUser($slug,$firstname,$lastname,$email) {
		// Setup body
		$requestBody = [
			'firstName'=> $firstname,
			'lastName'=> $lastname,
			'emails'=> [[
				'email' => $email,
				'primary' => true,
	  		]],
		];

		// Make POST request to /v2/users/{slug}
		$response = BadgrClient::put('/v2/users/' . $slug, $requestBody);

		// Check for 200 response
		if (null !== $response && $response->getStatusCode() == 200) {
			return true;
		}

		return false;
	}

}
