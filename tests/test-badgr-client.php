<?php

use \BadgeFactor2\BadgrClient;

class BadgrClientTest extends WP_UnitTestCase {

	private function callPrivateStaticMethod($class,$method,...$args) {
		$reflector = new ReflectionClass($class);
		$method = $reflector->getMethod($method);
		$method->setAccessible(true);
		return $method->invoke(null,...$args);
	}

	public function test_can_create_client() {
		$this->assertNotNull(new \BadgeFactor2\BadgrClient);
	}

	public function test_make_provider_returns_provider() {
		$this->assertEquals(\League\OAuth2\Client\Provider\GenericProvider::class,get_class($this->callPrivateStaticMethod(BadgrClient::class,'makeProvider')));
	}

	public function test_badgr_client_initially_inactive() {
		$this->assertFalse(BadgrClient::is_active());
	}

	public function test_badgr_client_options() {
		$options = get_option( 'badgefactor2_badgr_settings' );
		
		// No options set: get_options will return false initially
		$this->assertFalse($options);

		// Without options, client is not active, not configured, not initialized
		$this->assertFalse(BadgrClient::is_active());
		$this->assertFalse($this->callPrivateStaticMethod(BadgrClient::class,'is_configured'));
		$this->assertFalse($this->callPrivateStaticMethod(BadgrClient::class,'is_initialized'));

		// Add options for urls, client id and client secret
		update_option( 'badgefactor2_badgr_settings', [
			'badgr_server_public_url' => 'http://localhost:8000',
			'badgr_server_client_id' => 'a key',
			'badgr_server_client_secret' => 'a secret',
		]);

		// With options for urls, client id and client secret client is configured
		$this->assertFalse(BadgrClient::is_active());
		$this->assertTrue($this->callPrivateStaticMethod(BadgrClient::class,'is_configured'));
		$this->assertFalse($this->callPrivateStaticMethod(BadgrClient::class,'is_initialized'));

	}

}
