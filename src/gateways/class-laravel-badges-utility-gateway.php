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

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;


/**
 * BadgrProvider Class.
 */
class LaravelBadgesUtilityGateway {

    public static $clientInstance;

    protected function getClientInstance() {
        if ( null === self::$clientInstance ) {
            self::$clientInstance = new Client();
        }

        return self::$clientInstance;
    }

    protected function getLBUGatewayUrl() {
        if ( defined('LBU_URL') ) {
            return LBU_URL;
        }

        return 'https://localhost';
    }
    
    public function iAmHere() {
        return 'I am here.';
    }

    public function simplePostToLBU() {

        $client = $this->getClientInstance();
		$method = 'POST';
        $args = [
            'json' => [
                'word1' => 'Hello',
                'word2' => 'World',
            ],
            'headers' => [
                'Accept' => 'application/json',
            ],
        ];

        try {
            $response = $client->request( $method, $this->getLBUGatewayUrl() . '/from-wp', $args );

            return $response->getBody();

        } catch ( ConnectException $e ) {
            return 'Connect exception';
        } catch ( GuzzleException $e ) {
            return 'Guzzle exception';
        }
    }

    // Listen to ajax requests through wp rest: setup, declare callback

    // Send events to lbu

    // Get starting status when starting a pathway
}
