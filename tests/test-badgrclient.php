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
			$client = BadgrClient::makeInstance($basicParameters);
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
				$client = BadgrClient::makeInstance($incompleteParameters);

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
			$client = BadgrClient::makeInstance($parameters);
		} catch ( BadMethodCallException $e ) {

		}

		$this->assertNotNull($client);
	}

	public function test_badgr_client_auth_code_connectivity() {

		// Setup a completely configured client and check that we can get the profile info

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

		$client = null;

		try {
			$client = BadgrClient::makeInstance($clientParameters);
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

		$clientParameters = [
			'username' => getenv('BADGR_ADMIN_USERNAME'),
			'as_admin' => true,
			'badgr_server_public_url' => getenv('BADGR_SERVER_PUBLIC_URL'),
			'badgr_server_flavor' => BadgrClient::FLAVOR_LOCAL_R_JAMIROQUAI,
			'badgr_server_internal_url'    => getenv('BADGR_SERVER_INTERNAL_URL'),
			'client_id'     => getenv('BADGR_SERVER_PASSWORD_GRANT_CLIENT_ID'),
			'badgr_password' => getenv('BADGR_ADMIN_PASSWORD'),
		];

		$client = null;

		try {
			$client = BadgrClient::makeInstance($clientParameters);
		} catch ( BadMethodCallException $e ) {
			$this->fail('Exception thrown on client creation: ' . $e->getMessage());
		}

		$this->assertNotNull($client);

		// Attempt to get token
		$client->getAccessTokenFromPasswordGrant();

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

		$clientParameters = [
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
			$client = BadgrClient::makeInstance($clientParameters);
		} catch ( BadMethodCallException $e ) {
			$this->fail('Exception thrown on client creation: ' . $e->getMessage());
		}

		$this->assertNotNull($client);

		try {
			// Attempt to get token
			$client->getAccessTokenFromPasswordGrant();

			// If exception is thrown, we shouldn't get this far
			$this->fail('Bad credentials didn\'t raise exception');
		} catch ( Exception $e) {
			$this->assertTrue(true);
		}
	}

	public function test_badgr_client_password_grant_connectivity_badgrio() {

		// Setup a completely configured client and check that we can get the profile info

		$clientParameters = [
			'username' => getenv('BADGRIO_USERNAME'),
			'as_admin' => false,
			'badgr_server_public_url' => getenv('BADGRIO_URL'),
			'badgr_server_flavor' => BadgrClient::FLAVOR_BADGRIO_01,
			'badgr_password' => getenv('BADGRIO_PASSWORD'),
		];

		$client = null;

		try {
			$client = BadgrClient::makeInstance($clientParameters);
		} catch ( BadMethodCallException $e ) {
			$this->fail('Exception thrown on client creation: ' . $e->getMessage());
		}

		$this->assertNotNull($client);

		// Attempt to get token
		$client->getAccessTokenFromPasswordGrant();

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

    /**
     * @runInSeparateProcess
     */
    public function test_badgr_client_catches_401_but_throws_other_exceptions() {

		// Setup mock Guzzle client
		$mock = new MockHandler([
			    new Response(401,[], '{
    "status": {
        "description": "no valid auth token found",
        "success": false
    },
    "validationErrors": [],
    "fieldErrors": {},
    "result": []
}'),
    new Response(404,[], ''),
    new Response(500,[], ''),
		]);
		$handlerStack = HandlerStack::create($mock);
		$guzzleClient = new Client(['handler' => $handlerStack]);

		// Setup a badgr client instance
		$clientParameters = [
			'username' => 'dev@ctrlweb.ca',
			'as_admin' => false,
			'badgr_server_public_url' => getenv('BADGR_SERVER_PUBLIC_URL'),
			'badgr_server_flavor' => BadgrClient::FLAVOR_LOCAL_R_JAMIROQUAI,
			'badgr_server_internal_url'    => getenv('BADGR_SERVER_INTERNAL_URL'),
			'client_id'     => getenv('BADGR_SERVER_PASSWORD_GRANT_CLIENT_ID'),
			'badgr_password' => getenv('BADGR_SERVER_PASSWORD_GRANT_PASSWORD'),
		];

		$client = null;

		try {
			$client = BadgrClient::makeInstance($clientParameters);
		} catch ( BadMethodCallException $e ) {
			$this->fail('Unexpected exception at client creation.');
		}

		$this->assertNotNull($client);

		// Setup our Guzzle client
		$client::setGuzzleClient($guzzleClient);

		// Make an api call, get a 401
		try {
			$response = $client->get('/v2/user/self');
		}
		catch (\Exception $e) {
			// Shouldn't have an exception
			$this->fail('Unexpected exception.');
		}

		// Make an api call, get a 404
		try {
			$response = $client->get('/v2/user/self');
			// We shouldn't reach this far if an exception is thrown
			$this->fail('Unexpected exception.');
		}
		catch ( GuzzleException $e) {
			// Expect this specific exception
			$this->assertTrue(true);
		} catch ( Exception $e ) {
			// We're not expecting any other type of exception
			$this->fail('Unexpected exception.');
		}

		// Make an api call, get a 500
		try {
			$response = $client->get('/v2/user/self');
			// We shouldn't reach this far if an exception is thrown
			$this->fail('Unexpected exception.');
		}
		catch ( GuzzleException $e) {
			// Expect this specific exception
			$this->assertTrue(true);
		} catch ( Exception $e ) {
			// We're not expecting any other type of exception
			$this->fail('Unexpected exception.');
		}
	}

	public function test_can_store_client() {
		// Setup a completely configured client and store
		$clientParameters = [
			'username' => 'dev@ctrlweb.ca',
			'as_admin' => false,
			'badgr_server_public_url' => getenv('BADGR_SERVER_PUBLIC_URL'),
			'badgr_server_flavor' => BadgrClient::FLAVOR_LOCAL_R_JAMIROQUAI,
			'badgr_server_internal_url'    => getenv('BADGR_SERVER_INTERNAL_URL'),
			'client_id'     => getenv('BADGR_SERVER_PASSWORD_GRANT_CLIENT_ID'),
			'badgr_password' => getenv('BADGR_SERVER_PASSWORD_GRANT_PASSWORD'),
		];

		$client = null;

		try {
			$client = BadgrClient::makeInstance($clientParameters);
		} catch ( BadMethodCallException $e ) {
			$this->fail('Exception thrown on client creation: ' . $e->getMessage());
		}

		$this->assertNotNull($client);

		// Attempt to get token
		$client->getAccessTokenFromPasswordGrant();

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

		// Store entity id for comparison with stored client result
		$entityId = $response_info->result[0]->entityId;

		// Store and retreive the client
		update_user_meta( 1, $client::$user_meta_key_for_client, $client);
		$storedClient = get_user_meta( 1, BadgrClient::$user_meta_key_for_client, true );
		
		$this->assertNotNull($storedClient);

		$response = $storedClient->get( '/v2/users/self' );

		// Check response isn't null.
		$this->assertNotNull($response);

		// Check response has status code 200.
		$this->assertEquals( 200, $response->getStatusCode() );

		$response_info = json_decode( $response->getBody() );

		// Check that entity id exists
		$this->assertTrue( isset( $response_info->result[0]->entityId ) );

		// Check that entityId isn't empty
		$this->assertNotEmpty( $response_info->result[0]->entityId );

		// Store entity id for comparison with stored client result
		$this->assertEquals ( $entityId, $response_info->result[0]->entityId);

	}

	public function test_admin_creates_user_then_user_checks_backpack () {
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
			$adminClient = BadgrClient::makeInstance($adminClientParameters);
			$adminClient->getAccessTokenFromPasswordGrant();
		} catch ( BadMethodCallException $e ) {
			$this->fail('Exception thrown on client creation: ' . $e->getMessage());
		}

		// New user creation
		$firstname = 'Zeus';
		$lastname = 'God';
		$email = 'zeus.god@example.net';
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
		$response = $adminClient->post( '/v1/user/profile', $request_body );

		$newUserSlug = null;

		// Check for 201 response.
		if ( null !== $response && $response->getStatusCode() == 201 ) {
			// Return slug-entity_id or false if unsucessful.
			$response_info = json_decode( $response->getBody() );
			if ( isset( $response_info->slug ) && strlen( $response_info->slug ) > 0 ) {
				$newUserSlug =  $response_info->slug;
			}
		}

		// Password grant client for user
		$userClientParameters = [
			'username' => $email,
			'as_admin' => false,
			'badgr_server_public_url' => getenv('BADGR_SERVER_PUBLIC_URL'),
			'badgr_server_internal_url' => getenv('BADGR_SERVER_INTERNAL_URL'),
			'badgr_server_flavor' => BadgrClient::FLAVOR_LOCAL_R_JAMIROQUAI,
			'client_id'     => getenv('BADGR_SERVER_PASSWORD_GRANT_CLIENT_ID'),
			'badgr_password' => $password,
		];

		$userClient = null;

		try {
			$userClient = BadgrClient::makeInstance($userClientParameters);
			$userClient->getAccessTokenFromPasswordGrant();

		} catch ( BadMethodCallException $e ) {
			$this->fail('Exception thrown on client creation: ' . $e->getMessage());
		}

		// User checks profile
		try {
			$response = $userClient->get( '/v2/users/self' );
		} catch (\Exception $e ) {
			$this->fail('Exception on profile check ' . $e->getMessage());
		}

		$response_info = json_decode( $response->getBody() );

		// Check that entity id exists
		$this->assertTrue( isset( $response_info->result[0]->entityId ) );

		// Check that entityId isn't empty
		$this->assertNotEmpty( $response_info->result[0]->entityId );

		// Check our slug matches
		$this->assertEquals( $newUserSlug, $response_info->result[0]->entityId); 


	}

	public function test_password_client_has_proper_scopes () {

/* 		// Setup Guzzle client
		$container = [];
		$history = Middleware::history($container);
		
		$handlerStack = HandlerStack::create();
		// or $handlerStack = HandlerStack::create($mock); if using the Mock handler.
		
		// Add the history middleware to the handler stack.
		$handlerStack->push($history);
		
		$guzzleClient = new Client(['handler' => $handlerStack]);
		BadgrClient::setGuzzleClient($guzzleClient); */
		
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
			$adminClient = BadgrClient::makeInstance($adminClientParameters);
			$adminClient->getAccessTokenFromPasswordGrant();
			
			// Assert success
			$this->assertTrue(true);
		} catch ( BadMethodCallException $e ) {
			$this->fail('Exception thrown on client creation: ' . $e->getMessage());
		} 

/* 		// Count the number of transactions
		//echo count($container);
		//> 2

		// Iterate over the requests and responses
		foreach ($container as $transaction) {
			echo 'method ' . $transaction['request']->getMethod();
			var_dump($transaction['request']->getHeaders());
			echo ' transaction request body ' . $transaction['request']->getBody();
			//> GET, HEAD
			if ($transaction['response']) {
				echo ' transaction status code ' . $transaction['response']->getStatusCode();
				echo ' transaction body ' . $transaction['response']->getBody();
				//> 200, 200
			} elseif ($transaction['error']) {
				echo ' transaction error ' . $transaction['error'];
				//> exception
			}
			//var_dump($transaction['options']);
			//> dumps the request options of the sent request. 
		} */
	}
}
