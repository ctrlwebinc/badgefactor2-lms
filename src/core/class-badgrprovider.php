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

use \Datetime;
use BadgeFactor2\BadgrUser;

/**
 * BadgrProvider Class.
 */
class BadgrProvider {

	use Paginatable;

	/**
	 * Undocumented variable
	 *
	 * @var BadgrClient
	 */
	private static $client = null;

	/**
	 * Undocumented function
	 *
	 * @param BadgrClient $client The BadgrClient to use.
	 * @return void
	 */
	public static function set_client( BadgrClient $client ) {
		self::$client = $client;
	}

	/**
	 * Undocumented function
	 *
	 * @return BadgrClient|null
	 */
	private static function get_client() {
		if ( null === self::$client ) {
			// Get the logged in user client.
			try {
				self::$client = BadgrUser::get_or_make_user_client();
				return self::$client;
			} catch ( \Exception $e ) {
				// Add debugging here as required.
			}
			// Try to get the user 1 client (if we're in background, we'll need the admin client anyway).
			try {
				self::$client = ( BadgrUser::make_from_user_id( 1 ) )->get_client();
				if ( null !== self::$client ) {
					return self::$client;
				}
			} catch ( \Exception $e ) {
				// Add debugging here as required.
			}
			self::$client = new BadgrClient();
			return self::$client;
		}

		return self::$client;
	}

	/**
	 * Add user to Badgr Server.
	 *
	 * @param string $firstname First name.
	 * @param string $lastname Last name.
	 * @param string $email Email address.
	 * @param string $password User Badgr password.
	 * @return string|boolean
	 */
	public static function add_user( $firstname, $lastname, $email, $password ) {

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
			'password'             => $password,
		);

		// Make POST request to /v1/user/profile.
		$response = self::get_client()->post( '/v1/user/profile', $request_body );

		// Check for 201 response.
		if ( null !== $response && $response->getStatusCode() === 201 ) {
			// Return slug-entity_id or false if unsucessful.
			$response_info = json_decode( $response->getBody() );
			if ( isset( $response_info->slug ) && strlen( $response_info->slug ) > 0 ) {
				return $response_info->slug;
			}
		}

		return false;
	}
	/**
	 * Undocumented function
	 *
	 * @param string $slug User slug.
	 * @param string $old_password Previous password.
	 * @param string $new_password New password.
	 * @return boolean
	 */
	public static function change_user_password( $slug, $old_password, $new_password ) {
		// Change password.
		$request_body = array(
			'password'        => $new_password,
			'currentPassword' => $old_password,
		);

		$client = self::get_client();
		if ( $client ) {
			$response = $client->put( '/v2/users/' . $slug, $request_body );
		}
		// Check for 200 response.
		if ( null !== $response && $response->getStatusCode() === 200 ) {
			// Return true on success or false if unsucessful.
			$response_info = json_decode( $response->getBody() );
			if ( isset( $response_info->status->success ) && true === $response_info->status->success ) {
				return true;
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
		$response = self::get_client()->get( '/v2/users/' . $user_entity_id );

		// Check for 200 response.
		if ( null !== $response && $response->getStatusCode() === 200 ) {
			// Check for a non-null recipient field.
			$response_info = json_decode( $response->getBody() );
			if ( isset( $response_info->status->success ) &&
			true === $response_info->status->success &&
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
		$response = self::get_client()->put( '/v2/users/' . $slug, $request_body );

		// Check for 200 response.
		if ( null !== $response && $response->getStatusCode() === 200 ) {
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
	 * @param array $params Parameters.
	 *
	 * @return array|boolean Issuers array or false on error.
	 */
	public static function get_all_issuers( $params = array(
		'paged'             => 1,
		'elements_per_page' => -1,
	) ) {
		// Make GET request to /v2/issuers.
		$response = self::get_client()->get( '/v2/issuers' );

		// Check for 200 response.
		if ( null !== $response && 200 === $response->getStatusCode() ) {
			$response_info = json_decode( $response->getBody() );
			if ( isset( $response_info->status->success ) &&
				true === $response_info->status->success &&
				isset( $response_info->result ) && is_array( $response_info->result ) ) {
				if ( $params['elements_per_page'] > 0 ) {
					return self::paginate( $response_info->result, $params['paged'], $params['elements_per_page'] );
				}
				return $response_info->result;
			}
		}

		return false;
	}

	/**
	 * Count issuers.
	 */
	public static function get_all_issuers_count() {
		// Make GET request to /v2/issuers_count.
		$response = self::get_client()->get( '/v2/issuers_count' );

		// Check for 200 response.
		if ( null !== $response && 200 === $response->getStatusCode() ) {
			$response_info = json_decode( $response->getBody() );
			if ( isset( $response_info->count ) && is_numeric( $response_info->count ) ) {
				return intval( $response_info->count );
			}
		}

		return false;
	}

	/**
	 * Get Badgr Issuer by entity ID / slug.
	 *
	 * @param string $slug Entity ID / slug.
	 * @return boolean|object
	 */
	public static function get_issuer_by_slug( $slug ) {
		// Make GET request to /v2/issuers/{entity_id}.
		$response = self::get_client()->get( '/v2/issuers/' . $slug );

		// Check for 200 response.
		if ( null !== $response && $response->getStatusCode() === 200 ) {
			$response_info = json_decode( $response->getBody() );
			if ( isset( $response_info->status->success ) &&
				true === $response_info->status->success &&
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
	 * @return boolean
	 */
	public static function delete_issuer( $slug ) {
		// Make DELETE request to /v2/issuers/{entity_id}.
		$response = self::get_client()->delete( '/v2/issuers/' . $slug );

		// Check for 204 or 404 response.
		if ( null !== $response && ( $response->getStatusCode() === 204 || $response->getStatusCode() === 404 ) ) {

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
		$response = self::get_client()->post( '/v2/issuers', $request_body );

		// Check for 201 response.
		if ( null !== $response && $response->getStatusCode() === 201 ) {
			// Return slug-entity_id or false if unsuccessful.
			$response_info = json_decode( $response->getBody() );
			if ( isset( $response_info->status->success ) &&
				true === $response_info->status->success &&
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
	public static function update_issuer( $issuer_slug, $issuer_name, $email, $url, $description = null ) {

		// Setup body.
		$request_body = array(
			'name'  => $issuer_name,
			'email' => $email,
			'url'   => $url,
		);

		if ( null !== $description ) {
			$request_body['description'] = $description;
		}

		// Make PUT request to /v2/issuers/{entity_id}.
		$response = self::get_client()->put( '/v2/issuers/' . $issuer_slug, $request_body );

		// Check for 200 response.
		if ( null !== $response && $response->getStatusCode() === 200 ) {
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
		$image_data = null;

		if ( null !== $image ) {
			$image_data = self::handle_image_data( $image );

			if ( false === $image_data ) {
				return false;
			}
		}

		// Setup body.
		$request_body = array(
			'name'        => $class_name,
			'image'       => $image_data,
			'issuer'      => $issuer_slug,
			'description' => $description,
		);

		// Make POST request to /v2/badgeclasses.
		$response = self::get_client()->post( '/v2/badgeclasses', $request_body );

		// Check for 201 response.
		if ( null !== $response && 201 === $response->getStatusCode() ) {
			// Return slug-entity_id or false if unsuccessful.
			$response_info = json_decode( $response->getBody() );
			if ( isset( $response_info->status->success ) &&
				true === $response_info->status->success &&
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
	 * @param array  $params Parameters.
	 * @return boolean|object.
	 */
	public static function get_all_badge_classes_by_issuer_slug( $issuer_slug, $params = array(
		'paged'             => 1,
		'elements_per_page' => -1,
	) ) {

		$additional_parameters = array();

		if ( $params['elements_per_page'] > 0 ) {
			$server_side_pagination = self::calculate_server_side_pagination( $params['paged'], $params['elements_per_page'] );
			$additional_parameters  = array_merge( $additional_parameters, $server_side_pagination );
		}

		// Make GET request to /v2/issuers/{entity_id}/badgeclasses.
		$response = self::get_client()->get( '/v2/issuers/' . $issuer_slug . '/badgeclasses', $additional_parameters );

		// Check for 200 response.
		if ( null !== $response && 200 === $response->getStatusCode() ) {
			$response_info = json_decode( $response->getBody() );
			if ( isset( $response_info->status->success ) &&
				true === $response_info->status->success &&
				isset( $response_info->result ) && is_array( $response_info->result ) ) {
				return $response_info->result;
			}
		}

		return false;
	}

	/**
	 * Count badge classes for issuer.
	 *
	 * @param string $issuer_slug Issuer slug.
	 * @return mixed
	 */
	public static function get_all_badge_classes_by_issuer_slug_count( $issuer_slug ) {

		// Make GET request to /v2/badgeclasses_count/issuer/{entity_id}.
		$response = self::get_client()->get('/v2/badgeclasses_count/issuer/' . $issuer_slug );

		// Check for 200 response.
		if ( null !== $response && 200 === $response->getStatusCode() ) {
			$response_info = json_decode( $response->getBody() );
			if ( isset( $response_info->count ) && is_numeric( $response_info->count ) ) {
				return intval( $response_info->count );
			}
		}

		return false;
	}

	/**
	 * Retrieve all badges from Badgr.
	 *
	 * @param array $params Parameters.
	 * @return array|bool TODO.
	 */
	public static function get_all_badge_classes( $params = array(
		'paged'             => 1,
		'elements_per_page' => -1,
	) ) {

		$additional_parameters = array();

		if ( $params['elements_per_page'] > 0 ) {
			$server_side_pagination = self::calculate_server_side_pagination( $params['paged'], $params['elements_per_page'] );
			$additional_parameters  = array_merge( $additional_parameters, $server_side_pagination );
		}

		// Make GET request to /v2/badgeclasses.
		$response = self::get_client()->get( '/v2/badgeclasses', $additional_parameters );

		// Check for 200 response.
		if ( null !== $response && 200 === $response->getStatusCode() ) {
			$response_info = json_decode( $response->getBody() );
			if ( isset( $response_info->status->success ) &&
				true === $response_info->status->success &&
				isset( $response_info->result ) && is_array( $response_info->result ) ) {
					return $response_info->result;
			}
		}

		return false;
	}

	/**
	 * Count badge classes.
	 *
	 * @return mixed
	 */
	public static function get_all_badge_classes_count() {
		$response = self::get_client()->get( '/v2/badgeclasses_count' );

		// Check for 200 response.
		if ( null !== $response && 200 === $response->getStatusCode() ) {
			$response_info = json_decode( $response->getBody() );
			if ( isset( $response_info->count ) && is_numeric( $response_info->count ) ) {
					return intval( $response_info->count );
			}
		}

		return false;
	}

	/**
	 * TODO.
	 *
	 * @param string $badge_class_slug Badge Entity ID / Slug.
	 * @return boolean|object
	 */
	public static function get_badge_class_by_badge_class_slug( $badge_class_slug ) {
		// Make GET request to /v2/badgeclasses/{entity_id}.
		$client = self::get_client();
		if ( $client ) {
			$response = $client->get( '/v2/badgeclasses/' . $badge_class_slug );
		}

		// Check for 200 response.
		if ( null !== $response && $response->getStatusCode() === 200 ) {
			$response_info = json_decode( $response->getBody() );
			if ( isset( $response_info->status->success ) &&
				true === $response_info->status->success &&
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

		if ( null !== $image ) {
			$image_data = self::handle_image_data( $image );

			if ( false === $image_data ) {
				return false;
			}
		}

		// Setup body.
		$request_body = array(
			'name'        => $class_name,
			'description' => $description,
		);

		if ( null !== $image_data ) {
			$request_body['image'] = $image_data;
		}

		// Make PUT request to /v2/badgeclasses/{entity_id}.
		$response = self::get_client()->put( '/v2/badgeclasses/' . $badge_class_slug, $request_body );

		// Check for 200 response.
		if ( null !== $response && $response->getStatusCode() === 200 ) {
			return true;
		}

		return false;
	}

	/**
	 * Delete Badgr Badge Class by entity ID / slug.
	 *
	 * @param string $slug Entity ID / slug.
	 * @return boolean
	 */
	public static function delete_badge_class( $slug ) {
		// Make DELETE request to /v2/badgeclasses/{entity_id}.
		$response = self::get_client()->delete( '/v2/badgeclasses/' . $slug );

		// Check for 204 or 404 response.
		if ( null !== $response && ( $response->getStatusCode() === 204 || $response->getStatusCode() === 404 ) ) {

			return true;
		}

		return false;
	}

	/**
	 * Add assertion to Badgr Server.
	 *
	 * @param string $badge_class_slug BadgeClass slug.
	 * @param string $recipient_identifier Recipient identifier.
	 * @param string $recipient_type Recipient type.
	 * @param mixed  $issued_on Issued on date.
	 * @param mixed  $evidence_url Evidence url.
	 * @param mixed  $evidence_narrative Evidence narrative.
	 * @return string|false Assertion slug or false on error.
	 */
	public static function add_assertion( $badge_class_slug, $recipient_identifier, $recipient_type = 'email', $issued_on = null, $evidence_url = null, $evidence_narrative = null ) {
		// Setup body.
		$request_body = array(
			'recipient' => array(
				'identity' => $recipient_identifier,
				'type'     => $recipient_type,
			),
		);

		if ( null !== $issued_on ) {
			try {
				$issue_date = new DateTime( $issued_on );
			} catch ( \Exception $e ) {
				return false;
			}

			$request_body['issuedOn'] = $issue_date->format( 'c' );
		}

		if ( null !== $evidence_narrative || null !== $evidence_url ) {
			$evidence = array();
			if ( null !== $evidence_narrative ) {
				$evidence['narrative'] = $evidence_narrative;
			}
			if ( null !== $evidence_url ) {
				$evidence['url'] = $evidence_url;
			}
			$request_body['evidence'] = array( $evidence );
		}

		// Make POST request to /v2/badgeclasses/{entity_id}/assertions.
		$response = self::get_client()->post( '/v2/badgeclasses/' . $badge_class_slug . '/assertions', $request_body );

		// Check for 201 response.
		if ( null !== $response && 201 === $response->getStatusCode() ) {
			// Return slug-entity_id or false if unsuccessful.
			$response_info = json_decode( $response->getBody() );
			if ( isset( $response_info->status->success ) &&
				true === $response_info->status->success &&
				isset( $response_info->result[0]->entityId ) ) {
				return $response_info->result[0]->entityId;
			}
		}

		return false;
	}


	/**
	 * TODO.
	 *
	 * @param string $badge_class_slug BadgeClass slug.
	 * @param array  $params Badge class parameters.
	 * @return boolean|object.
	 */
	public static function get_all_assertions_by_badge_class_slug( $badge_class_slug, $params = array(
		'paged'             => 1,
		'elements_per_page' => -1,
	) ) {
		$additional_parameters = array(
			'include_revoked' => true,
		);

		if ( $params['elements_per_page'] > 0 ) {
			$server_side_pagination = self::calculate_server_side_pagination( $params['paged'], $params['elements_per_page'] );
			$additional_parameters  = array_merge( $additional_parameters, $server_side_pagination );
		}

		// Make GET request to /v2/badgeclasses/{entity_id}/assertions.
		$response = self::get_client()->get( '/v2/badgeclasses/' . $badge_class_slug . '/assertions', $additional_parameters );

		// Check for 200 response.
		if ( null !== $response && 200 === $response->getStatusCode() ) {
			$response_info = json_decode( $response->getBody() );
			if ( isset( $response_info->status->success ) &&
				true === $response_info->status->success &&
				isset( $response_info->result ) && is_array( $response_info->result ) ) {
					return $response_info->result;
			}
		}

		return false;
	}

	/**
	 * Count assertions for badge class.
	 *
	 * @param string $badge_class_slug Badge class slug.
	 * @return mixed
	 */
	public static function get_all_assertions_by_badge_class_slug_count( $badge_class_slug ) {
		$response = self::get_client()->get( '/v2/badgeinstances_count/badgeclass/' . $badge_class_slug );

		// Check for 200 response.
		if ( null !== $response && 200 === $response->getStatusCode() ) {
			$response_info = json_decode( $response->getBody() );
			if ( isset( $response_info->count ) && is_numeric( $response_info->count ) ) {
					return intval( $response_info->count );
			}
		}

		return false;
	}

	/**
	 * TODO.
	 *
	 * @param string $issuer_slug Issuer slug.
	 * @param array  $params Assertions parameters.
	 * @return boolean|object.
	 */
	public static function get_all_assertions_by_issuer_slug( $issuer_slug, $params = array(
		'paged'             => 1,
		'elements_per_page' => -1,
	) ) {
		// Make GET request to /v2/issuers/{entity_id}/assertions.

		$additional_parameters = array(
			'include_revoked' => true,
		);

		if ( $params['elements_per_page'] > 0 ) {
			$server_side_pagination = self::calculate_server_side_pagination( $params['paged'], $params['elements_per_page'] );
			$additional_parameters  = array_merge( $additional_parameters, $server_side_pagination );
		}

		$response = self::get_client()->get(
			'/v2/issuers/' . $issuer_slug . '/assertions',
			$additional_parameters
		);

		// Check for 200 response.
		if ( null !== $response && 200 === $response->getStatusCode() ) {
			$response_info = json_decode( $response->getBody() );
			if ( isset( $response_info->status->success ) &&
				true === $response_info->status->success &&
				isset( $response_info->result ) && is_array( $response_info->result ) ) {
				return $response_info->result;
			}
		}

		return false;
	}

	/**
	 * Count assertions for issuer.
	 *
	 * @param string $issuer_slug Issuer slug.
	 * @return int|bool
	 */
	public static function get_all_assertions_by_issuer_slug_count( $issuer_slug ) {
		$response = self::get_client()->get(
			'/v2/badgeinstances_count/issuer/' . $issuer_slug
		);

		// Check for 200 response.
		if ( null !== $response && 200 === $response->getStatusCode() ) {
			$response_info = json_decode( $response->getBody() );
			if ( isset( $response_info->count ) && is_numeric( $response_info->count ) ) {
				return intval( $response_info->count );
			}
		}

		return false;
	}

	/**
	 * TOOD.
	 *
	 * @param string $assertion_slug Assertion slug.
	 * @return boolean|object.
	 */
	public static function get_assertion_by_assertion_slug( $assertion_slug ) {
		// Make GET request to /v2/assertions/{entity_id}.
		$response = self::get_client()->get( '/v2/assertions/' . $assertion_slug );

		// Check for 200 response.
		if ( null !== $response && $response->getStatusCode() === 200 ) {
			$response_info = json_decode( $response->getBody() );
			if ( isset( $response_info->status->success ) &&
				true === $response_info->status->success &&
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
	 * @param string $reason Reason.
	 * @return boolean|object
	 */
	public static function revoke_assertion( $slug, $reason ) {

		$request_body = array(
			'revocation_reason' => $reason,
		);

		// Make DELETE request to /v2/assertions/{entity_id}.
		$response = self::get_client()->delete( '/v2/assertions/' . $slug, $request_body );

		// Check for 200 response.
		if ( null !== $response && $response->getStatusCode() === 200 ) {

			return true;
		}

		return false;
	}

	/**
	 * Undocumented function
	 *
	 * @param BadgrUser $badgr_user Badgr user.
	 * @param boolean   $exclude_not_accepted Set to true to exclude assertions not yet accepted.
	 * @param boolean   $params Parameters.
	 * @return boolean|object
	 */
	public static function get_all_assertions_from_user_backpack( BadgrUser $badgr_user, $exclude_not_accepted = false, $params = array(
		'paged'             => 1,
		'elements_per_page' => -1,
	) ) {
		$response = $badgr_user->get_client()->get( '/v2/backpack/assertions' );

		// Check for 200 response.
		if ( null !== $response && 200 === $response->getStatusCode() ) {
			$response_info = json_decode( $response->getBody() );
			if ( isset( $response_info->status->success ) &&
				true === $response_info->status->success &&
				isset( $response_info->result ) && is_array( $response_info->result ) ) {
					// Filter for acceptance status.
				if ( $exclude_not_accepted ) {
					$result = array();
					foreach ( $response_info->result as $assertion ) {
						if ( 'Accepted' === $assertion->acceptance ) {
							$result[] = $assertion;
						}
					}
				} else {
					$result = $response_info->result;
				}
				if ( $params['elements_per_page'] > 0 ) {
					return self::paginate( $result, $params['paged'], $params['elements_per_page'] );
				} else {
					return $result;
				}
			}
		}

		return false;
	}

	/**
	 * Given a BadgrUser, get details of an assertion
	 *
	 * @param BadgrUser $badgr_user Badgr user.
	 * @param string    $slug Assertion slug.
	 * @return boolean|object
	 */
	public static function get_assertion_details_from_user_backpack( BadgrUser $badgr_user, $slug ) {
		$response = $badgr_user->get_client()->get( '/v2/backpack/assertions/' . $slug );

		// Check for 200 response.
		if ( null !== $response && $response->getStatusCode() === 200 ) {
			$response_info = json_decode( $response->getBody() );
			if ( isset( $response_info->status->success ) &&
				true === $response_info->status->success &&
				isset( $response_info->result[0] ) ) {
				return $response_info->result[0];
			}
		}

		return false;
	}
	/**
	 * Undocumented function
	 *
	 * @param BadgrUser $badgr_user Badgr user.
	 * @param string    $slug Assertion slug.
	 * @return boolean
	 */
	public static function accept_assertion_in_user_backpack( BadgrUser $badgr_user, $slug ) {
		$response = $badgr_user->get_client()->put( '/v2/backpack/assertions/' . $slug, array( 'acceptance' => 'Accepted' ) );

		// Check for 200 response.
		if ( null !== $response && $response->getStatusCode() === 200 ) {
			$response_info = json_decode( $response->getBody() );
			if ( isset( $response_info->status->success ) &&
				true === $response_info->status->success &&
				isset( $response_info->result[0] ) && 'Accepted' === $response_info->result[0]->acceptance ) {
				return true;
			}
		}

		return false;
	}


	/**
	 * Undocumented function
	 *
	 * @return boolean|object
	 */
	public static function get_profile_associated_to_client_in_use() {
		$client = self::get_client();
		if ( $client ) {
			$response = $client->get( '/v2/users/self' );
		}

		// Check for 200 response.
		if ( null !== $response && $response->getStatusCode() === 200 ) {
			$response_info = json_decode( $response->getBody() );
			if ( isset( $response_info->status->success ) &&
				true === $response_info->status->success &&
				isset( $response_info->result[0] ) ) {
				return $response_info->result[0];
			}
		}

		return false;
	}
	/**
	 * Undocumented function
	 *
	 * @param string $image Image path.
	 * @return boolean|string
	 */
	private static function handle_image_data( $image ) {

		if ( ! file_exists( $image ) ) {
			return false;
		}

		$success = false;

		try {
			ob_start();

			$image_raw_data = file_get_contents( $image );
			$mime_type      = mime_content_type( $image );

			// Badgr doesn't seem to like just svg add the +xml.
			if ( 'image/svg' === $mime_type ) {
				$mime_type .= '+xml';
			}

			// If the image is in jpeg or gif format, convert it to png.
			if ( 'image/jpeg' === $mime_type || 'image/gif' === $mime_type ) {
				$gd_image = imagecreatefromstring( $image_raw_data );

				$success = imagepng( $gd_image );

				$image_raw_data = ob_get_contents();
				$mime_type      = 'image/png';
			} else {
				if ( 'image/png' === $mime_type || 'image/svg+xml' === $mime_type ) {
					$success = true;
				}
			}

			$image_data = 'data:' . $mime_type . ';base64,' . base64_encode( $image_raw_data );

		} catch ( \Exception $e ) {
			$success = false;
		} finally {
			ob_end_clean();
			if ( isset( $gd_image ) && null !== $gd_image ) {
				imagedestroy( $gd_image );
			}
		}

		if ( $success ) {
			return $image_data;
		} else {
			return false;
		}
	}
}
