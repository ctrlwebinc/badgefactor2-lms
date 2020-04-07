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
     */
	public function test_issuer_to_assertion_flow() {

		$options = get_option( 'badgefactor2_badgr_settings' );

		// No options set: get_options will return false initially
		$this->assertFalse( $options );

		// Setup options as they should be after a proper auth sequence with badgr
		update_option(
			'badgefactor2_badgr_settings',
			array(
				'badgr_server_public_url'    => getenv('BADGR_SERVER_PUBLIC_URL'),
				'badgr_server_internal_url'    => getenv('BADGR_SERVER_INTERNAL_URL'),
				'badgr_server_client_id'     => getenv('BADGR_SERVER_CLIENT_ID'),
				'badgr_server_client_secret' => getenv('BADGR_SERVER_CLIENT_SECRET'),
				'badgr_server_access_token' => getenv('BADGR_SERVER_ACCESS_TOKEN'),
				'badgr_server_refresh_token' => getenv('BADGR_SERVER_REFRESH_TOKEN'),
				'badgr_server_token_expiration' => getenv('BADGR_SERVER_TOKEN_EXPIRATION'),
			)
		);

		// Check that we can retreive information on the authorized user
		// Make GET request to /v2/users/self.
		$response = BadgrClient::get( '/v2/users/self' );

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

}
