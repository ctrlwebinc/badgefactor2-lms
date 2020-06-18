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
use BadgeFactor2\BadgrUser;
use BadgeFactor2\BadgrProvider;
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
class BadgrBackpackTest extends WP_UnitTestCase {

	private function generateRandomString($length = 10) {
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $randomString = 'l';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }
	    return $randomString;
	}

/* 	public function test_can_store_client() {
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
	
	 	public function test_admin_creates_user_awards_a_badge_and_checks_user_backpack () {
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

		BadgrProvider::setClient( $adminClient);

		$random_suffix = $this->generateRandomString();
		// New user creation

		$firstname = 'Zeus' . $random_suffix;
		$lastname = 'God';
		$email = 'zeus.' . $random_suffix . '.god@example.net';
		$password = 'pass456PASS';

		// Create the WP user
		$user_data = array(
		'user_pass' => $password,
		'user_login' =>$firstname,
		'user_email' => $email,
		'first_name' => $firstname,
		'last_name' => $lastname,
		);

		$wp_user_id = wp_insert_user( $user_data );

		// Through WP hook, creating WP user creates user on Badgr server
		$badgr_user = BadgrUser::make_from_user_id( $wp_user_id );

		// Password grant client for user
		$userClientParameters = [
			'badgr_user' => $badgr_user,
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
	} */

 	public function test_admin_awards_own_badge_and_checks_own_backpack () {

		 $badgr_user = BadgrUser::make_from_user_id(1);
		 $badgr_recipient = getenv('BADGR_ADMIN_USERNAME');

		// Password grant admin client
		$adminClientParameters = [
			'username' => $badgr_recipient,
			'as_admin' => true,
			'badgr_server_public_url' => getenv('BADGR_SERVER_PUBLIC_URL'),
			'badgr_server_internal_url' => getenv('BADGR_SERVER_INTERNAL_URL'),
			'badgr_server_flavor' => BadgrClient::FLAVOR_LOCAL_R_JAMIROQUAI,
			'badgr_password' => getenv('BADGR_ADMIN_PASSWORD'),
			'client_id'     => getenv('BADGR_SERVER_PASSWORD_GRANT_CLIENT_ID'),
			'badgr_user' => $badgr_user,
		];

		$adminClient = null;

		try {
			$adminClient = BadgrClient::makeInstance($adminClientParameters);
			$adminClient->getAccessTokenFromPasswordGrant();
		} catch ( BadMethodCallException $e ) {
			$this->fail('Exception thrown on client creation: ' . $e->getMessage());
		}

		// Get existing backpack
		$existing_backpack = BadgrProvider::get_all_assertions_from_user_backpack( $badgr_user );

		// Now award a new badge
		BadgrProvider::setClient( $adminClient);

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
		$assertion_slug = BadgrProvider::add_assertion( $issuer_slug, $badge_class_slug, $badgr_recipient);

		$this->assertTrue( false !== $assertion_slug );
		$this->assertNotEmpty( $assertion_slug);

		// Fetch new backpack and compare with previous
		$updated_backpack = BadgrProvider::get_all_assertions_from_user_backpack( $badgr_user );

		$assertion_absent_from_existing = false;

		foreach ( $existing_backpack as $assertion) {
			if ( $assertion->entityId == $assertion_slug ) {
				$assertion_absent_from_existing = true;
				break;
			}
		}

		$this->assertFalse($assertion_absent_from_existing);

		$assertion_present_in_updated = false;

		foreach ( $updated_backpack as $assertion) {
			if ( $assertion->entityId == $assertion_slug ) {
				$assertion_present_in_updated = true;
				break;
			}
		}

		$this->assertTrue($assertion_present_in_updated);
	}

	public function test_user_can_accept_new_assertions() {
		$badgr_user = BadgrUser::make_from_user_id(1);
		$badgr_recipient = getenv('BADGR_ADMIN_USERNAME');

	   // Password grant admin client
	   $adminClientParameters = [
		   'username' => $badgr_recipient,
		   'as_admin' => true,
		   'badgr_server_public_url' => getenv('BADGR_SERVER_PUBLIC_URL'),
		   'badgr_server_internal_url' => getenv('BADGR_SERVER_INTERNAL_URL'),
		   'badgr_server_flavor' => BadgrClient::FLAVOR_LOCAL_R_JAMIROQUAI,
		   'badgr_password' => getenv('BADGR_ADMIN_PASSWORD'),
		   'client_id'     => getenv('BADGR_SERVER_PASSWORD_GRANT_CLIENT_ID'),
		   'badgr_user' => $badgr_user,
	   ];

	   $adminClient = null;

	   try {
		   $adminClient = BadgrClient::makeInstance($adminClientParameters);
		   $adminClient->getAccessTokenFromPasswordGrant();
	   } catch ( BadMethodCallException $e ) {
		   $this->fail('Exception thrown on client creation: ' . $e->getMessage());
	   }

	   // Now award 2 new badges
	   BadgrProvider::setClient( $adminClient);

	   $random = $this->generateRandomString(5);

	   // Create issuer
	   $issuer_slug = BadgrProvider::add_issuer( 'TestIssuer' . $random, 'issuer' . $random . '@example.net' , 'http://' . $random . 'example.net', 'A Description for ' . $random );

	   $this->assertTrue( false !== $issuer_slug );
	   $this->assertNotEmpty( $issuer_slug);

	   $assertion_slugs = array( );

	   for ( $i=0;$i<2;$i++ ) {
		   // Setup a random string to avoid data collisions
		   $random = $this->generateRandomString(5);

		   // Add a badge class
		   $badge_class_slug = BadgrProvider::add_badge_class( 'BadgeClass' . $random, $issuer_slug, 'Description for ' . $random, dirname(__FILE__).'/resources/test_badge_image.svg' );

		   $this->assertTrue( false !== $badge_class_slug );
		   $this->assertNotEmpty( $badge_class_slug);

		   // Issue a badge
		   $assertion_slug = BadgrProvider::add_assertion( $issuer_slug, $badge_class_slug, $badgr_recipient);

		   $this->assertTrue( false !== $assertion_slug );
		   $this->assertNotEmpty( $assertion_slug);

		   $assertion_slugs[] = $assertion_slug;
	   }

	   // Fetch each new assertion and confirm Unaccepted status

	   foreach ( $assertion_slugs as $slug) {
		   $fetched_assertion = BadgrProvider::get_assertion_details_from_user_backpack( $badgr_user, $slug);
		   $this->assertTrue( false !== $fetched_assertion );
		   $this->assertEquals('Unaccepted', $fetched_assertion->acceptance);
	   }

	   // Accept assertion and confirm result
	   $accepted_assertion_slug = $assertion_slugs[0];
	   $this->assertTrue( BadgrProvider::accept_assertion_in_user_backpack( $badgr_user, $accepted_assertion_slug));
	   $fetched_assertion = BadgrProvider::get_assertion_details_from_user_backpack( $badgr_user, $accepted_assertion_slug);
	   $this->assertTrue( false !== $fetched_assertion );
	   $this->assertEquals('Accepted', $fetched_assertion->acceptance);

	}

	public function test_can_exclude_unaccepted_and_rejected_assertions_from_backpack_list() {
		$badgr_user = BadgrUser::make_from_user_id(1);
		$badgr_recipient = getenv('BADGR_ADMIN_USERNAME');

	   // Password grant admin client
	   $adminClientParameters = [
		   'username' => $badgr_recipient,
		   'as_admin' => true,
		   'badgr_server_public_url' => getenv('BADGR_SERVER_PUBLIC_URL'),
		   'badgr_server_internal_url' => getenv('BADGR_SERVER_INTERNAL_URL'),
		   'badgr_server_flavor' => BadgrClient::FLAVOR_LOCAL_R_JAMIROQUAI,
		   'badgr_password' => getenv('BADGR_ADMIN_PASSWORD'),
		   'client_id'     => getenv('BADGR_SERVER_PASSWORD_GRANT_CLIENT_ID'),
		   'badgr_user' => $badgr_user,
	   ];

	   $adminClient = null;

	   try {
		   $adminClient = BadgrClient::makeInstance($adminClientParameters);
		   $adminClient->getAccessTokenFromPasswordGrant();
	   } catch ( BadMethodCallException $e ) {
		   $this->fail('Exception thrown on client creation: ' . $e->getMessage());
	   }

	   // Now award 3 new badges
	   BadgrProvider::setClient( $adminClient);

	   $random = $this->generateRandomString(5);

	   // Create issuer
	   $issuer_slug = BadgrProvider::add_issuer( 'TestIssuer' . $random, 'issuer' . $random . '@example.net' , 'http://' . $random . 'example.net', 'A Description for ' . $random );

	   $this->assertTrue( false !== $issuer_slug );
	   $this->assertNotEmpty( $issuer_slug);

	   $assertion_slugs = array( );

	   for ( $i=0;$i<3;$i++ ) {
		   // Setup a random string to avoid data collisions
		   $random = $this->generateRandomString(5);

		   // Add a badge class
		   $badge_class_slug = BadgrProvider::add_badge_class( 'BadgeClass' . $random, $issuer_slug, 'Description for ' . $random, dirname(__FILE__).'/resources/test_badge_image.svg' );

		   $this->assertTrue( false !== $badge_class_slug );
		   $this->assertNotEmpty( $badge_class_slug);

		   // Issue a badge
		   $assertion_slug = BadgrProvider::add_assertion( $issuer_slug, $badge_class_slug, $badgr_recipient);

		   $this->assertTrue( false !== $assertion_slug );
		   $this->assertNotEmpty( $assertion_slug);

		   $assertion_slugs[] = $assertion_slug;
	   }

	   // Fetch each new assertion and confirm Unaccepted status

	   foreach ( $assertion_slugs as $slug) {
		   $fetched_assertion = BadgrProvider::get_assertion_details_from_user_backpack( $badgr_user, $slug);
		   $this->assertTrue( false !== $fetched_assertion );
		   $this->assertEquals('Unaccepted', $fetched_assertion->acceptance);
	   }
	}

}
