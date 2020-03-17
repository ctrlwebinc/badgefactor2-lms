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
 * BadgrProvider Class.
 */
class BadgrProvider {

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
			'password'             => self::generateRandomPassword(),
    );

		// Make POST request to /v1/user/profile.
		$response = BadgrClient::post( '/v1/user/profile', $request_body );

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
	 * Checks whether or not user has a verified email in Badgr Server.
	 *
	 * @param string $user_entity_id Badgr User Entity ID.
	 * @return bool
	 */
	public static function check_user_verified( $user_entity_id ) {

		// Make GET request to /v2/users/{slug-entity_id}.
		$response = BadgrClient::get( '/v2/users/' . $user_entity_id );

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
	 * @return bool
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
		$response = BadgrClient::put( '/v2/users/' . $slug, $request_body );

		// Check for 200 response.
		if ( null !== $response && $response->getStatusCode() == 200 ) {
			return true;
		}

		return false;
	}

    protected static function generateRandomPassword() {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = ['p']; // Start with a letter
        $alphaMaxIndex = strlen($alphabet) - 1;
        for ($i = 0; $i < 11; $i++) {
            $n = rand(0, $alphaMaxIndex);
            $pass[] = $alphabet[$n];
        }
        return implode($pass);
    }

    public static function getAllIssuers() {
        // Make GET request to /v2/issuers
        $response = BadgrClient::get('/v2/issuers');

        // Check for 200 response
        if (null !== $response && $response->getStatusCode() == 200) {
            $responseInfo = json_decode($response->getBody());
            if (isset($responseInfo->status->success) &&
                $responseInfo->status->success == true &&
                isset($responseInfo->result) && is_array($responseInfo->result)) {
                return $responseInfo->result;
            }
        }

        return false;
    }

    public static function addIssuer($issuerName, $email, $url, $description) {

	    // Setup body.
        $request_body = array(
            'name'                 => $issuerName,
            'image'                => null,
            'email'                => $email,
            'url'                  => $url,
            'description'          => $description,
        );

        // Make POST request to /v2/issuers.
        $response = BadgrClient::post( '/v2/issuers', $request_body );

        // Check for 201 response.
        if ( null !== $response && $response->getStatusCode() == 201 ) {
            // Return slug-entity_id or false if unsuccessful.
            $responseInfo = json_decode( $response->getBody() );
            if ( isset($responseInfo->status->success) &&
                $responseInfo->status->success == true &&
                isset($responseInfo->result[0]->entityId) ) {
                return $responseInfo->result[0]->entityId;
            }
        }

        return false;
    }

    public static function add_badge_class($className, $issuer_slug, $description, $image=null) {

	    try {
	        $imageRawData = file_get_contents($image);
	        $mimeType = mime_content_type($image);

	        // Badgr doesn't seem to like just svg add the +xml
	        if ('image/svg' == $mimeType) {
	            $mimeType .= '+xml';
            }
            $imageData = 'data:' . $mimeType . ';base64,' . base64_encode($imageRawData);
        } catch (\Exception $e) {
	        return false;
        }
        // Setup body.
        $request_body = array(
            'name'                 => $className,
            'image'                => $imageData,
            'issuer'               => $issuer_slug,
            'description'          => $description,
        );

        // Make POST request to /v2/badgeclasses.
        $response = BadgrClient::post( '/v2/badgeclasses', $request_body );

        // Check for 201 response.
        if ( null !== $response && $response->getStatusCode() == 201 ) {
            // Return slug-entity_id or false if unsuccessful.
            $responseInfo = json_decode( $response->getBody() );
            if ( isset($responseInfo->status->success) &&
                $responseInfo->status->success == true &&
                isset($responseInfo->result[0]->entityId) ) {
                return $responseInfo->result[0]->entityId;
            }
        }

        return false;
    }

    public static function add_assertion($issuer_slug, $badge_class_slug, $recipient_identifier, $recipient_type='email') {

        // Setup body.
        $request_body = array(
            'recipient_identifier' => $recipient_identifier,
            'recipient_type'      => $recipient_type,
            'create_notification' => false,
        );

        // Make POST request to /v1/issuer/issuers/{issuerSlug}/badges/{slug}/assertions.
        $response = BadgrClient::post( '/v1/issuer/issuers/' . $issuer_slug .'/badges/' . $badge_class_slug . '/assertions', $request_body );

        // Check for 201 response.
        if ( null !== $response && $response->getStatusCode() == 201 ) {
            // Return slug-entity_id or false if unsuccessful.
            $responseInfo = json_decode( $response->getBody() );
            if ( isset( $responseInfo->slug ) && strlen( $responseInfo->slug ) > 0 ) {
                return $responseInfo->slug;
            }
        }

        return false;
    }

}
