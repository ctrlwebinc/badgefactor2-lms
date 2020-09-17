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

use BadgeFactor2\BadgrClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Middleware;




/**
 * Badgr Client Test.
 */
class BadgrClientTest extends WP_UnitTestCase {

	public function test_can_create_client() {

		// Needs userName, isAdmin, Badgr server public url and badgrServerFlavor
		$basicParameters = [
			'username' => 'dave@example.net',
			'as_admin' => true,
			'badgr_server_public_url' => 'http://127.0.0.1:8000',
			'badgr_server_flavor' => BadgrClient::FLAVOR_LOCAL_R_JAMIROQUAI,
		];

		$client = null;

		try {
			$client = BadgrClient::make_instance($basicParameters);
		} catch ( BadMethodCallException $e ) {

		}

		$this->assertNotNull($client);
	}

	public function test_creation_missing_key_params_generates_exception() {

		// Needs userName, isAdmin, Badgr server public url and badgrServerFlavor
		$basicParameters = [
			'username' => 'dave@example.net',
			'as_admin' => true,
			'badgr_server_public_url' => 'http://127.0.0.1:8000',
			'badgr_server_flavor' => BadgrClient::FLAVOR_LOCAL_R_JAMIROQUAI,
		];

		foreach ($basicParameters as $key => $value) {
			$client = null;

			$incompleteParameters = $basicParameters;
			unset($incompleteParameters[$key]);

			try {
				$client = BadgrClient::make_instance($incompleteParameters);

				// We shouldn't make it to the next line if exceptions are generated
				$this->fail('Exception not thrown');
			} catch ( BadMethodCallException $e ) {
				$this->assertTrue(true);
			}
		}
	}

	public function test_client_creation_accepts_additional_parameters() {

		// Basic parameters userName, isAdmin, Badgr server public url and badgrServerFlavor
		$parameters = [
			'username' => 'dave@example.net',
			'as_admin' => true,
			'badgr_server_public_url' => 'http://127.0.0.1:8000',
			'badgr_server_flavor' => BadgrClient::FLAVOR_LOCAL_R_JAMIROQUAI,
		];

		// client_id is an additional parameter
		$parameters['client_id'] = 'AClientId';

		$client = null;

		try {
			$client = BadgrClient::make_instance($parameters);
		} catch ( BadMethodCallException $e ) {

		}

		$this->assertNotNull($client);
	}

 	public function test_badgr_client_auth_code_connectivity() {

		// Setup a completely configured client and check that we can get the profile info

		$client_parameters = [
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

		$client = null;

		try {
			$client = BadgrClient::make_instance($client_parameters);
		} catch ( BadMethodCallException $e ) {
			$this->fail('Exception thrown on client creation: ' . $e->getMessage());
		}

		$this->assertNotNull($client);

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

		// Check that entityId isn't empty
		$this->assertNotEmpty( $response_info->result[0]->entityId );

	}

	public function test_badgr_client_password_grant_connectivity() {

		// Setup a completely configured client and check that we can get the profile info

		$client_parameters = [
			'username' => getenv('BADGR_SERVER_PASSWORD_GRANT_USERNAME'),
			'as_admin' => true,
			'badgr_server_public_url' => getenv('BADGR_SERVER_PUBLIC_URL'),
			'badgr_server_flavor' => BadgrClient::FLAVOR_LOCAL_R_JAMIROQUAI,
			'badgr_server_internal_url'    => getenv('BADGR_SERVER_INTERNAL_URL'),
			'client_id'     => getenv('BADGR_SERVER_PASSWORD_GRANT_CLIENT_ID'),
			'badgr_password' => getenv('BADGR_SERVER_PASSWORD_GRANT_PASSWORD'),
		];

		$client = null;

		try {
			$client = BadgrClient::make_instance($client_parameters);
		} catch ( BadMethodCallException $e ) {
			$this->fail('Exception thrown on client creation: ' . $e->getMessage());
		}

		$this->assertNotNull($client);

		// Attempt to get token
		$client->get_access_token_from_password_grant();

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

		// Check that entityId isn't empty
		$this->assertNotEmpty( $response_info->result[0]->entityId );

	}

	public function test_badgr_client_password_grant_bad_credentials_raise_exception() {

		// Setup a completely configured client and check that we can get the profile info

		$client_parameters = [
			'username' => 'dev@ctrlweb.ca',
			'as_admin' => false,
			'badgr_server_public_url' => getenv('BADGR_SERVER_PUBLIC_URL'),
			'badgr_server_flavor' => BadgrClient::FLAVOR_LOCAL_R_JAMIROQUAI,
			'badgr_server_internal_url'    => getenv('BADGR_SERVER_INTERNAL_URL'),
			'client_id'     => getenv('BADGR_SERVER_PASSWORD_GRANT_CLIENT_ID'),
			'badgr_password' => 'WRONG_PASSWORD',
		];

		$client = null;

		try {
			$client = BadgrClient::make_instance($client_parameters);
		} catch ( BadMethodCallException $e ) {
			$this->fail('Exception thrown on client creation: ' . $e->getMessage());
		}

		$this->assertNotNull($client);

		try {
			// Attempt to get token
			$client->get_access_token_from_password_grant();

			// If exception is thrown, we shouldn't get this far
			$this->fail('Bad credentials didn\'t raise exception');
		} catch ( Exception $e) {
			$this->assertTrue(true);
		}
	}

	public function test_badgr_client_password_grant_connectivity_badgrio() {

		// Setup a completely configured client and check that we can get the profile info

		$client_parameters = [
			'username' => getenv('BADGRIO_USERNAME'),
			'as_admin' => false,
			'badgr_server_public_url' => getenv('BADGRIO_URL'),
			'badgr_server_flavor' => BadgrClient::FLAVOR_BADGRIO_01,
			'badgr_password' => getenv('BADGRIO_PASSWORD'),
		];

		$client = null;

		try {
			$client = BadgrClient::make_instance($client_parameters);
		} catch ( BadMethodCallException $e ) {
			$this->fail('Exception thrown on client creation: ' . $e->getMessage());
		}

		$this->assertNotNull($client);

		// Attempt to get token
		$client->get_access_token_from_password_grant();

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

		// Check that entityId isn't empty
		$this->assertNotEmpty( $response_info->result[0]->entityId );

		// Check that the profile conatains the expected information
		$this->assertEquals( getenv('BADGRIO_EXPECTED_LASTNAME'), $response_info->result[0]->lastName);

	}

	public function test_admin_reads_own_backpack () {
		// Password grant admin client
		$adminClientParameters = [
			'username' => getenv('BADGR_ADMIN_USERNAME'),
			'as_admin' => true,
			'badgr_server_public_url' => getenv('BADGR_SERVER_PUBLIC_URL'),
			'badgr_server_internal_url' => getenv('BADGR_SERVER_INTERNAL_URL'),
			'badgr_server_flavor' => BadgrClient::FLAVOR_LOCAL_R_JAMIROQUAI,
			'badgr_password' => getenv('BADGR_ADMIN_PASSWORD'),
			'client_id'     => getenv('BADGR_SERVER_PASSWORD_GRANT_CLIENT_ID'),
		];

		$adminClient = null;

		try {
			$adminClient = BadgrClient::make_instance($adminClientParameters);
			$adminClient->get_access_token_from_password_grant();
		} catch ( BadMethodCallException $e ) {
			$this->fail('Exception thrown on client creation: ' . $e->getMessage());
		}

		// Check backpack
		$response = $adminClient->get('/v2/backpack/assertions');

		$success = false;

		// Check for 200 response.
		if ( null !== $response && 200 === $response->getStatusCode() ) {
			$response_info = json_decode( $response->getBody() );
			if ( isset( $response_info->status->success ) &&
				$response_info->status->success == true &&
				isset( $response_info->result ) && is_array( $response_info->result ) ) {
				$success = true;
			}
		}

		$this->assertTrue( $success );

	}

	public function test_password_client_has_proper_scopes () {
		
		// Password grant admin client
		$adminClientParameters = [
			'username' => getenv('BADGR_ADMIN_USERNAME'),
			'as_admin' => true,
			'badgr_server_public_url' => getenv('BADGR_SERVER_PUBLIC_URL'),
			'badgr_server_internal_url' => getenv('BADGR_SERVER_INTERNAL_URL'),
			'badgr_server_flavor' => BadgrClient::FLAVOR_LOCAL_R_JAMIROQUAI,
			'badgr_password' => getenv('BADGR_ADMIN_PASSWORD'),
			'client_id'     => getenv('BADGR_SERVER_PASSWORD_GRANT_CLIENT_ID'),
		];

		$adminClient = null;

		try {
			$adminClient = BadgrClient::make_instance($adminClientParameters);
			$adminClient->get_access_token_from_password_grant();
			
			// Assert success
			$this->assertTrue(true);
		} catch ( BadMethodCallException $e ) {
			$this->fail('Exception thrown on client creation: ' . $e->getMessage());
		} 
	}

	public function test_unconfigured_client_returns_null_response() {
		$client = new BadgrClient();

		$response = $client->get('anyurl');

		$this->assertNull( $response );
	}
}
