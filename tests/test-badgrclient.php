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

/**
 * Badgr Client Test.
 */
class BadgrClientTest extends WP_UnitTestCase {

	private function callPrivateStaticMethod( $class, $method, ...$args ) {
		$reflector = new ReflectionClass( $class );
		$method    = $reflector->getMethod( $method );
		$method->setAccessible( true );
		return $method->invoke( null, ...$args );
	}

	public function test_can_create_client() {
		$this->assertNotNull( new \BadgeFactor2\BadgrClient );
	}

	public function test_make_provider_returns_provider() {
		$this->assertEquals( \League\OAuth2\Client\Provider\GenericProvider::class, get_class( $this->callPrivateStaticMethod( BadgrClient::class, 'make_provider' ) ) );
	}

	public function test_badgr_client_initially_inactive() {
		$this->assertFalse( BadgrClient::is_active() );
	} 

    /**
     * @backupStaticAttributes enabled
     */
    public function test_badgr_client_options() {
		$options = get_option( 'badgefactor2_badgr_settings' );

		// No options set: get_options will return false initially
		$this->assertFalse( $options );

		// Without options, client is not active, not configured, not initialized
		$this->assertFalse( BadgrClient::is_active() );
		$this->assertFalse( $this->callPrivateStaticMethod( BadgrClient::class, 'is_configured' ) );
		$this->assertFalse( $this->callPrivateStaticMethod( BadgrClient::class, 'is_initialized' ) );

		// Add options for urls, client id and client secret
		update_option(
			'badgefactor2_badgr_settings',
			array(
				'badgr_server_public_url'    => 'http://localhost:8000',
				'badgr_server_client_id'     => 'a key',
				'badgr_server_client_secret' => 'a secret',
			)
		);

		// With options for urls, client id and client secret client is configured
		$this->assertFalse( BadgrClient::is_active() );
		$this->assertTrue( $this->callPrivateStaticMethod( BadgrClient::class, 'is_configured' ) );
		$this->assertFalse( $this->callPrivateStaticMethod( BadgrClient::class, 'is_initialized' ) );

	}

    /**
	 * @runInSeparateProcess
     * @backupStaticAttributes enabled
     */
	public function test_badgr_client_connectivity() {

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

		// Check that access token isn't null
		$this->assertNotNull( $this->callPrivateStaticMethod( BadgrClient::class, 'get_access_token' ) );

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

		// Check that entityId isn't empty
		$this->assertNotEmpty( $response_info->result[0]->entityId );

	}

}
