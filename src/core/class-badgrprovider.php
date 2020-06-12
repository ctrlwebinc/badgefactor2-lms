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

use BadgeFactor2\BadgrUser;

/**
 * BadgrProvider Class.
 */
class BadgrProvider {

	private static $client = null;

	public static function setClient( BadgrClient $client) {
		self::$client = $client;
	}

	private static function getClient() {
		if ( null == self::$client ) {
			// TODO use user client methods
			// return BadgrClient::getOrMakeUserClient();
			self::$client = BadgrClient::makeClientFromSavedOptions();
		}

		return self::$client;
	} 

	/**
	 * BadgrProvider Init.
	 *
	 * @return void
	 */
	public static function init_hooks() {
		add_action( 'init', array( BadgrProvider::class, 'init' ), 9966 );
		add_action( 'cmb2_admin_init', array( BadgrProvider::class, 'cmb2_admin_init' ) );
	}

	/**
	 * Init hook.
	 *
	 * @return void
	 */
	public static function init() {
		// TODO.
	}

	/**
	 * CMB2 Admin Init hook.
	 *
	 * @return void
	 */
	public static function cmb2_admin_init() {
		// TODO.
	}

	/**
	 * Add user to Badgr Server.
	 *
	 * @param string $firstname First name.
	 * @param string $lastname Last name.
	 * @param string $email Email address.
	 * @return string|boolean
	 */
	public static function add_user( $firstname, $lastname, $email ) {

		// Setup body.
		$request_body = array(
			'first_name'           => $firstname,
			'last_name'            => $lastname,
			'email'                => $email,
			'url'                  => '',
			'telephone'            => '',
			'slug'                 => '',
			'agreed_terms_version' => 1,
			'marketing_opt_in'     => false,
			'has_password_set'     => false,
			'source'               => 'bf2',
			'password'             => self::generate_random_password(),
		);

		// Make POST request to /v1/user/profile.
		$response = self::getClient()->post( '/v1/user/profile', $request_body );

		// Check for 201 response.
		if ( null !== $response && $response->getStatusCode() == 201 ) {
			// Return slug-entity_id or false if unsucessful.
			$response_info = json_decode( $response->getBody() );
			if ( isset( $response_info->slug ) && strlen( $response_info->slug ) > 0 ) {
				return $response_info->slug;
			}
		}

		return false;
	}

	/**
	 * Checks email status in Badgr Server.
	 *
	 * @param string $user_entity_id Badgr User Entity ID.
	 * @return boolean Whether or not user has a verified email.
	 */
	public static function check_user_verified( $user_entity_id ) {

		// Make GET request to /v2/users/{slug-entity_id}.
		$response = self::getClient()->get( '/v2/users/' . $user_entity_id );

		// Check for 200 response.
		if ( null !== $response && $response->getStatusCode() == 200 ) {
			// Check for a non-null recipient field.
			$response_info = json_decode( $response->getBody() );
			if ( isset( $response_info->status->success ) &&
				$response_info->status->success == true &&
				isset( $response_info->result[0] ) &&
					null !== $response_info->result[0]->recipient ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Updates Badgr User.
	 *
	 * @param string $slug Slug.
	 * @param string $firstname First name.
	 * @param string $lastname Last name.
	 * @param string $email Email address.
	 * @return boolean Whether or not User has been updated.
	 */
	public static function update_user( $slug, $firstname, $lastname, $email ) {
		// Setup body.
		$request_body = array(
			'firstName' => $firstname,
			'lastName'  => $lastname,
			'emails'    => array(
				array(
					'email'   => $email,
					'primary' => true,
				),
			),
		);

		// Make POST request to /v2/users/{slug}.
		$response = self::getClient()->put( '/v2/users/' . $slug, $request_body );

		// Check for 200 response.
		if ( null !== $response && $response->getStatusCode() == 200 ) {
			return true;
		}

		return false;
	}

	/**
	 * Generates a random password.
	 *
	 * @return string Randomly generated password.
	 */
	protected static function generate_random_password() {
		$alphabet        = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
		$pass            = array( 'p' ); // Start with a letter.
		$alpha_max_index = strlen( $alphabet ) - 1;
		for ( $i = 0; $i < 11; $i++ ) {
			$n      = rand( 0, $alpha_max_index );
			$pass[] = $alphabet[ $n ];
		}
		return implode( $pass );
	}

	/**
	 * Get all issuers from Badgr Server.
	 *
	 * @return array|boolean Issuers array or false on error.
	 */
	public static function get_all_issuers() {
		// Make GET request to /v2/issuers.
		$response = self::getClient()->get( '/v2/issuers' );

		// Check for 200 response.
		if ( null !== $response && 200 === $response->getStatusCode() ) {
			$response_info = json_decode( $response->getBody() );
			if ( isset( $response_info->status->success ) &&
				$response_info->status->success == true &&
				isset( $response_info->result ) && is_array( $response_info->result ) ) {
				return $response_info->result;
			}
		}

		return false;
	}

	/**
	 * Get Badgr Issuer by entity ID / slug.
	 *
	 * @param string $slug Entity ID / slug.
	 * @return void
	 */
	public static function get_issuer_by_slug( $slug ) {
		// Make GET request to /v2/issuers/{entity_id}.
		$response = self::getClient()->get( '/v2/issuers/' . $slug );

		// Check for 200 response.
		if ( null !== $response && $response->getStatusCode() == 200 ) {
			$response_info = json_decode( $response->getBody() );
			if ( isset( $response_info->status->success ) &&
				$response_info->status->success == true &&
				isset( $response_info->result[0] ) ) {
				return $response_info->result[0];
			}
		}

		return false;
	}

	/**
	 * Delete Badgr Issuer by entity ID / slug.
	 *
	 * @param string $slug Entity ID / slug.
	 * @return void
	 */
	public static function delete_issuer( $slug ) {
		// Make DELETE request to /v2/issuers/{entity_id}.
		$response = self::getClient()->delete( '/v2/issuers/' . $slug );

		// Check for 204 or 404 response.
		if ( null !== $response && ( $response->getStatusCode() == 204 || $response->getStatusCode() == 404 ) ) {

			return true;
		}

		return false;
	}

	/**
	 * Add an issuer to Badgr Server.
	 *
	 * @param string $issuer_name Issuer name.
	 * @param string $email Issuer email address.
	 * @param string $url Issuer URL.
	 * @param string $description Issuer description.
	 * @return string|boolean Issuer Entity ID or false on error.
	 */
	public static function add_issuer( $issuer_name, $email, $url, $description ) {

		// Setup body.
		$request_body = array(
			'name'        => $issuer_name,
			'image'       => null,
			'email'       => $email,
			'url'         => $url,
			'description' => $description,
		);

		// Make POST request to /v2/issuers.
		$response = self::getClient()->post( '/v2/issuers', $request_body );

		// Check for 201 response.
		if ( null !== $response && $response->getStatusCode() == 201 ) {
			// Return slug-entity_id or false if unsuccessful.
			$response_info = json_decode( $response->getBody() );
			if ( isset( $response_info->status->success ) &&
				$response_info->status->success == true &&
				isset( $response_info->result[0]->entityId ) ) {
				return $response_info->result[0]->entityId;
			}
		}

		return false;
	}

	/**
	 * Update Badgr Issuer.
	 *
	 * @param string $issuer_slug Issuer slug.
	 * @param string $issuer_name Issuer name.
	 * @param string $email Issuer email.
	 * @param string $url Issuer URL.
	 * @param string $description Issuer Description.
	 * @return string|boolean Issuer Entity ID or false on error.
	 */
	public static function update_issuer( $issuer_slug, $issuer_name, $email, $url, $description=null ) {

		// Setup body.
		$request_body = array(
			'name'        => $issuer_name,
			'email'       => $email,
			'url'         => $url,
		);

		if ( null !== $description) {
			$request_body['description'] = $description;
		}

		// Make PUT request to /v2/issuers/{entity_id}.
		$response = self::getClient()->put( '/v2/issuers/' . $issuer_slug, $request_body );

		// Check for 200 response.
		if ( null !== $response && $response->getStatusCode() == 200 ) {
			return true;
		}

		return false;
	}

	/**
	 * Add BadgeClass to Badgr Server.
	 *
	 * @param string $class_name BadgeClass name.
	 * @param string $issuer_slug Issuer slug.
	 * @param string $description BadgeClass description.
	 * @param string $image Badge Image.
	 * @return string|boolean BadgeClass Entity ID or false on error.
	 */
	public static function add_badge_class( $class_name, $issuer_slug, $description, $image = null ) {

		try {
			$image_raw_data = file_get_contents( $image );
			$mime_type      = mime_content_type( $image );

			// Badgr doesn't seem to like just svg add the +xml.
			if ( 'image/svg' === $mime_type ) {
				$mime_type .= '+xml';
			}
			$image_data = 'data:' . $mime_type . ';base64,' . base64_encode( $image_raw_data );
		} catch ( \Exception $e ) {
			return false;
		}
		// Setup body.
		$request_body = array(
			'name'        => $class_name,
			'image'       => $image_data,
			'issuer'      => $issuer_slug,
			'description' => $description,
		);

		// Make POST request to /v2/badgeclasses.
		$response = self::getClient()->post( '/v2/badgeclasses', $request_body );

		// Check for 201 response.
		if ( null !== $response && 201 === $response->getStatusCode() ) {
			// Return slug-entity_id or false if unsuccessful.
			$response_info = json_decode( $response->getBody() );
			if ( isset( $response_info->status->success ) &&
				$response_info->status->success == true &&
				isset( $response_info->result[0]->entityId ) ) {
				return $response_info->result[0]->entityId;
			}
		}

		return false;
	}

	/**
	 * Retrieve all badges by issuer slug from Badgr.
	 *
	 * @param string $issuer_slug Issuer Entity ID / Slug.
	 * @return void TODO.
	 */
	public static function get_all_badge_classes_by_issuer_slug( $issuer_slug ) {
		// Make GET request to /v2/issuers/{entity_id}/badgeclasses.
		$response = self::getClient()->get( '/v2/issuers/' . $issuer_slug . '/badgeclasses' );

		// Check for 200 response.
		if ( null !== $response && 200 === $response->getStatusCode() ) {
			$response_info = json_decode( $response->getBody() );
			if ( isset( $response_info->status->success ) &&
				$response_info->status->success == true &&
				isset( $response_info->result ) && is_array( $response_info->result ) ) {
				return $response_info->result;
			}
		}

		return false;
	}

	/**
	 * Retrieve all badges from Badgr.
	 *
	 * @return void TODO.
	 */
	public static function get_all_badge_classes( ) {
		// Make GET request to /v2/badgeclasses.
		$response = self::getClient()->get( '/v2/badgeclasses' );

		// Check for 200 response.
		if ( null !== $response && 200 === $response->getStatusCode() ) {
			$response_info = json_decode( $response->getBody() );
			if ( isset( $response_info->status->success ) &&
				$response_info->status->success == true &&
				isset( $response_info->result ) && is_array( $response_info->result ) ) {
				return $response_info->result;
			}
		}

		return false;
	}

	/**
	 * TODO.
	 *
	 * @param string $badge_class_slug Badge Entity ID / Slug.
	 * @return void TODO.
	 */
	public static function get_badge_class_by_badge_class_slug( $badge_class_slug ) {
		// Make GET request to /v2/badgeclasses/{entity_id}.
		$response = self::getClient()->get( '/v2/badgeclasses/' . $badge_class_slug );

		// Check for 200 response.
		if ( null !== $response && $response->getStatusCode() == 200 ) {
			$response_info = json_decode( $response->getBody() );
			if ( isset( $response_info->status->success ) &&
				$response_info->status->success == true &&
				isset( $response_info->result[0] ) ) {
				return $response_info->result[0];
			}
		}

		return false;
	}

	/**
	 * Update BadgeClass on Badgr Server.
	 *
	 * @param string $badge_class_slug Badge Class slug.
	 * @param string $class_name BadgeClass name.
	 * @param string $description BadgeClass description.
	 * @param string $image Badge Image.
	 * @return string|boolean BadgeClass Entity ID or false on error.
	 */
	public static function update_badge_class( $badge_class_slug, $class_name, $description, $image = null ) {

		$image_data = null;

		if ( null !== $image) {
			try {
				$image_raw_data = file_get_contents( $image );
				$mime_type      = mime_content_type( $image );

				// Badgr doesn't seem to like just svg add the +xml.
				if ( 'image/svg' === $mime_type ) {
					$mime_type .= '+xml';
				}
				$image_data = 'data:' . $mime_type . ';base64,' . base64_encode( $image_raw_data );
			} catch ( \Exception $e ) {

			}
		}

		// Setup body.
		$request_body = array(
			'name'        => $class_name,
			'description' => $description,
		);

		if ( null !== $image_data) {
			$request_body['image'] = $image_data;
		}

		// Make PUT request to /v2/badgeclasses/{entity_id}.
		$response = self::getClient()->put( '/v2/badgeclasses/' . $badge_class_slug, $request_body );

		// Check for 200 response.
		if ( null !== $response && $response->getStatusCode() == 200 ) {
			return true;
		}

		return false;
	}

	/**
	 * Delete Badgr Badge Class by entity ID / slug.
	 *
	 * @param string $slug Entity ID / slug.
	 * @return void
	 */
	public static function delete_badge_class( $slug ) {
		// Make DELETE request to /v2/badgeclasses/{entity_id}.
		$response = self::getClient()->delete( '/v2/badgeclasses/' . $slug );

		// Check for 204 or 404 response.
		if ( null !== $response && ( $response->getStatusCode() == 204 || $response->getStatusCode() == 404 ) ) {

			return true;
		}

		return false;
	}

	/**
	 * Add assertion to Badgr Server.
	 *
	 * @param string $issuer_slug Issuer slug.
	 * @param string $badge_class_slug BadgeClass slug.
	 * @param string $recipient_identifier Recipient identifier.
	 * @param string $recipient_type Recipient type.
	 * @return string|false Assertion slug or false on error.
	 */
	public static function add_assertion( $issuer_slug, $badge_class_slug, $recipient_identifier, $recipient_type = 'email' ) {

		// Setup body.
		$request_body = array(
			'recipient_identifier' => $recipient_identifier,
			'recipient_type'       => $recipient_type,
			'create_notification'  => false,
		);

		// Make POST request to /v1/issuer/issuers/{issuerSlug}/badges/{slug}/assertions.
		$response = self::getClient()->post( '/v1/issuer/issuers/' . $issuer_slug . '/badges/' . $badge_class_slug . '/assertions', $request_body );

		// Check for 201 response.
		if ( null !== $response && $response->getStatusCode() == 201 ) {
			// Return slug-entity_id or false if unsuccessful.
			$response_info = json_decode( $response->getBody() );
			if ( isset( $response_info->slug ) && strlen( $response_info->slug ) > 0 ) {
				return $response_info->slug;
			}
		}

		return false;
	}

	/**
	 * TODO.
	 *
	 * @param string $badge_class_slug BadgeClass slug.
	 * @return void TODO.
	 */
	public static function get_all_assertions_by_badge_class_slug( $badge_class_slug ) {
		// Make GET request to /v2/badgeclasses/{entity_id}/assertions.
		$response = self::getClient()->get( '/v2/badgeclasses/' . $badge_class_slug . '/assertions' );

		// Check for 200 response.
		if ( null !== $response && 200 === $response->getStatusCode() ) {
			$response_info = json_decode( $response->getBody() );
			if ( isset( $response_info->status->success ) &&
				$response_info->status->success == true &&
				isset( $response_info->result ) && is_array( $response_info->result ) ) {
				return $response_info->result;
			}
		}

		return false;
	}

	/**
	 * TODO.
	 *
	 * @param string $issuer_slug Issuer slug.
	 * @return void TODO.
	 */
	public static function get_all_assertions_by_issuer_slug( $issuer_slug ) {
		// Make GET request to /v2/issuers/{entity_id}/assertions.

		$response = self::getClient()->get( '/v2/issuers/' . $issuer_slug . '/assertions', array(
			'include_revoked' => true
		)  );


		// Check for 200 response.
		if ( null !== $response && 200 === $response->getStatusCode() ) {
			$response_info = json_decode( $response->getBody() );
			if ( isset( $response_info->status->success ) &&
				$response_info->status->success == true &&
				isset( $response_info->result ) && is_array( $response_info->result ) ) {
				return $response_info->result;
			}
		}

		return false;
	}

	/**
	 * TOOD.
	 *
	 * @param string $assertion_slug Assertion slug.
	 * @return void TODO.
	 */
	public static function get_assertion_by_assertion_slug( $assertion_slug ) {
		// Make GET request to /v2/assertions/{entity_id}.
		$response = self::getClient()->get( '/v2/assertions/' . $assertion_slug );

		// Check for 200 response.
		if ( null !== $response && $response->getStatusCode() == 200 ) {
			$response_info = json_decode( $response->getBody() );
			if ( isset( $response_info->status->success ) &&
				$response_info->status->success == true &&
				isset( $response_info->result[0] ) ) {
				return $response_info->result[0];
			}
		}

		return false;
	}

	/**
	 * Delete / Revoke Badge / Assertion by entity ID / slug.
	 *
	 * @param string $slug Entity ID / slug.
	 * @return void
	 */
	public static function revoke_assertion( $slug, $reason ) {

		$request_body = array(
			'revocation_reason' => $reason,
		);

		// Make DELETE request to /v2/assertions/{entity_id}.
		$response = self::getClient()->delete( '/v2/assertions/' . $slug, $request_body );

		// Check for 200 response.
		if ( null !== $response && $response->getStatusCode() == 200 ) {

			return true;
		}

		return false;
	}

	// Given a BadgrUser, get all assertions from that user's backpack
	public static function get_all_assertions_from_user_backpack ( BadgrUser $badgr_user ) {
		$reponse = $badgr_user->get_client()->get('/v2/backpack/assertions');

		// Check for 200 response.
		if ( null !== $response && 200 === $response->getStatusCode() ) {
			$response_info = json_decode( $response->getBody() );
			if ( isset( $response_info->status->success ) &&
				$response_info->status->success == true &&
				isset( $response_info->result ) && is_array( $response_info->result ) ) {
				return $response_info->result;
			}
		}

		return false;
	}

	// Given a BadgrUser, get details of an assertion
	public static function get_assertion_details_from_user_backpack (BadgrUser $badgr_user, $slug ) {
		$reponse = $badgr_user->get_client()->get('/v2/backpack/assertions/' . $slug );

		// Check for 200 response.
		if ( null !== $response && $response->getStatusCode() == 200 ) {
			$response_info = json_decode( $response->getBody() );
			if ( isset( $response_info->status->success ) &&
				$response_info->status->success == true &&
				isset( $response_info->result[0] ) ) {
				return $response_info->result[0];
			}
		}

		return false;
	}
}
