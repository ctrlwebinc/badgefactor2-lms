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

use BadgeFactor2\BadgrIndividualClient;

/**
 * Badgr Client Test.
 */
class IndividualBadgrClientTest extends WP_UnitTestCase {

	public function test_can_create_client() {

		// Needs userName, isAdmin, Badgr server public url and badgrServerFlavor
		$basicParameters = [
			'username' => 'dave@example.net',
			'is_admin' => true,
			'badgr_server_public_url' => 'http://127.0.0.1:8000',
			'badgr_server_flavor' => BadgrIndividualClient::FLAVOR_LOCAL_R_JAMIROQUAI,
		];

		$client = null;

		try {
			$client = BadgrIndividualClient::makeInstance($basicParameters);
		} catch ( BadMethodCallException $e ) {

		}

		$this->assertNotNull($client);
	}
}
