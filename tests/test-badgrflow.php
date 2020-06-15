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

use \BadgeFactor2\BadgrClient;
use \BadgeFactor2\BadgrProvider;

/**
 * Badgr Client Test.
 */
class BadgrFlowTest extends WP_UnitTestCase {

	private function callPrivateStaticMethod( $class, $method, ...$args ) {
		$reflector = new ReflectionClass( $class );
		$method    = $reflector->getMethod( $method );
		$method->setAccessible( true );
		return $method->invoke( null, ...$args );
	}

	private function generateRandomString($length = 10) {
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $randomString = 'l';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }
	    return $randomString;
	}
    /**
     * @backupStaticAttributes enabled
	 * @runInSeparateProcess
     */
	public function test_issuer_to_assertion_flow() {

		// Setup a completely client and check that we can get the profile info
		$clientParameters = [
			'username' => getenv('BADGR_ADMIN_USERNAME'),
			'as_admin' => true,
			'badgr_server_public_url' => getenv('BADGR_SERVER_PUBLIC_URL'),
			'badgr_server_flavor' => BadgrClient::FLAVOR_LOCAL_R_JAMIROQUAI,
			'badgr_server_internal_url'    => getenv('BADGR_SERVER_INTERNAL_URL'),
			'client_id'     => getenv('BADGR_SERVER_CLIENT_ID'),
			'client_secret' => getenv('BADGR_SERVER_CLIENT_SECRET'),
			'access_token' => getenv('BADGR_SERVER_ACCESS_TOKEN'),
			'refresh_token' => getenv('BADGR_SERVER_REFRESH_TOKEN'),
			'token_expiration' => getenv('BADGR_SERVER_TOKEN_EXPIRATION'),
		];

		$client = BadgrClient::makeInstance( $clientParameters );

		BadgrProvider::setClient( $client );

		// Check that we can retreive information on the authorized user
		// Make GET request to /v2/users/self.
		$response = $client->get( '/v2/users/self' );

		// Check response isn't null.
		$this->assertNotNull($response);

		// Check response has status code 200.
		$this->assertEquals( 200, $response->getStatusCode() );

		$response_info = json_decode( $response->getBody() );

		// Check that entity id exists
		$this->assertTrue( isset( $response_info->result[0]->entityId ) );

		// Setup a random string to avoid data collisions
		$random = $this->generateRandomString(5);

		// Create issuer
		$issuer_slug = BadgrProvider::add_issuer( 'TestIssuer' . $random, 'issuer' . $random . '@example.net' , 'http://' . $random . 'example.net', 'A Description for ' . $random );

		$this->assertTrue( false !== $issuer_slug );
		$this->assertNotEmpty( $issuer_slug);

		// Add a badge class
		$badge_class_slug = BadgrProvider::add_badge_class( 'BadgeClass' . $random, $issuer_slug, 'Description for ' . $random, dirname(__FILE__).'/resources/test_badge_image.svg' );

		$this->assertTrue( false !== $badge_class_slug );
		$this->assertNotEmpty( $badge_class_slug);

		// Issue a badge
		$assertion_slug = BadgrProvider::add_assertion( $issuer_slug, $badge_class_slug, 'recipient' . $random . '@example.net');

		$this->assertTrue( false !== $assertion_slug );
		$this->assertNotEmpty( $assertion_slug);

	}
    /**
     * @backupStaticAttributes enabled
	 * @runInSeparateProcess
     */
	public function test_issuer_to_new_user_assertion_flow() {

		// Setup a completely client and check that we can get the profile info
		$clientParameters = [
			'username' => getenv('BADGR_ADMIN_USERNAME'),
			'as_admin' => true,
			'badgr_server_public_url' => getenv('BADGR_SERVER_PUBLIC_URL'),
			'badgr_server_flavor' => BadgrClient::FLAVOR_LOCAL_R_JAMIROQUAI,
			'badgr_server_internal_url'    => getenv('BADGR_SERVER_INTERNAL_URL'),
			'client_id'     => getenv('BADGR_SERVER_CLIENT_ID'),
			'client_secret' => getenv('BADGR_SERVER_CLIENT_SECRET'),
			'access_token' => getenv('BADGR_SERVER_ACCESS_TOKEN'),
			'refresh_token' => getenv('BADGR_SERVER_REFRESH_TOKEN'),
			'token_expiration' => getenv('BADGR_SERVER_TOKEN_EXPIRATION'),
		];

		$client = BadgrClient::makeInstance( $clientParameters );

		BadgrProvider::setClient( $client );

		// Check that we can retreive information on the authorized user
		// Make GET request to /v2/users/self.
		$response = $client->get( '/v2/users/self' );

		// Check response isn't null.
		$this->assertNotNull($response);

		// Check response has status code 200.
		$this->assertEquals( 200, $response->getStatusCode() );

		$response_info = json_decode( $response->getBody() );

		// Check that entity id exists
		$this->assertTrue( isset( $response_info->result[0]->entityId ) );

		// Setup a random string to avoid data collisions
		$random = $this->generateRandomString(5);

		// Create a user
		$firstname = 'Zeus' . $random;
		$lastname = 'God';
		$email = 'zeus.god.' . $random . '@example.net';
		$password = 'pass456PASS';

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
		$response = $client->post( '/v1/user/profile', $request_body );

		$newUserSlug = null;

		// Check for 201 response.
		if ( null !== $response && $response->getStatusCode() == 201 ) {
			// Return slug-entity_id or false if unsucessful.
			$response_info = json_decode( $response->getBody() );
			if ( isset( $response_info->slug ) && strlen( $response_info->slug ) > 0 ) {
				$newUserSlug =  $response_info->slug;
			}
		}

		// Create issuer
		$issuer_slug = BadgrProvider::add_issuer( 'TestIssuer' . $random, 'issuer' . $random . '@example.net' , 'http://' . $random . 'example.net', 'A Description for ' . $random );

		$this->assertTrue( false !== $issuer_slug );
		$this->assertNotEmpty( $issuer_slug);

		// Add a badge class
		$badge_class_slug = BadgrProvider::add_badge_class( 'BadgeClass' . $random, $issuer_slug, 'Description for ' . $random, dirname(__FILE__).'/resources/test_badge_image.svg' );

		$this->assertTrue( false !== $badge_class_slug );
		$this->assertNotEmpty( $badge_class_slug);

		// Issue a badge
		$assertion_slug = BadgrProvider::add_assertion( $issuer_slug, $badge_class_slug, $email);

		$this->assertTrue( false !== $assertion_slug );
		$this->assertNotEmpty( $assertion_slug);

	}
}
