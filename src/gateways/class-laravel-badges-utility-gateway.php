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
use BadgeFactor2\BadgrProvider;
use BadgeFactor2\Models\Assertion;


/**
 * BadgrProvider Class.
 */
class LaravelBadgesUtilityGateway {

    public static $clientInstance;

    public static function init_hooks() {
		add_action( 'init', array( self::class, 'init' ) );
    }

    public static function init() {
        add_action( 'rest_api_init', [self::class,'setupRestRoutes']);
    }

    public static function setupRestRoutes() {
        register_rest_route( 'lbu/v1', '/emit', [
            'methods' => 'POST',
            'callback' => [self::class,'handleEmit'],
            'permission_callback' => function() {return true;},
         ] );
    }

    public static function handleEmit( \WP_REST_Request $request ) {
        $parameters = $request->get_json_params();

        if ( isset($parameters['badge']) && '' != $parameters['badge'] && isset($parameters['recipient']) && '' != $parameters['recipient']) {
            $assertionParameters = [
                'badge' => $parameters['badge'],
                'recipient' => $parameters['recipient'],
            ];
    
            return Assertion::create($assertionParameters);
         }
        
        return false;
    }

    protected static function getClientInstance() {
        if ( null === self::$clientInstance ) {
            self::$clientInstance = new Client();
        }

        return self::$clientInstance;
    }

    protected static function getLBUGatewayUrl() {
        if ( defined('LBU_URL') ) {
            return LBU_URL;
        }

        return 'https://localhost';
    }

    public static function postNewAssertion( $recipient, $badgeClass, $assertionSlug) {
        $client = self::getClientInstance();
		$method = 'POST';
        $args = [
            'json' => [
                'operation' => 'newAssertion',
                'badge' => $badgeClass,
                'recipient' => $recipient,
                'assertion' => $assertionSlug
            ],
            'headers' => [
                'Accept' => 'application/json',
            ],
        ];

        try {
            $response = $client->request( $method, self::getLBUGatewayUrl() . '/feep-pathway/collect-wp-events', $args );

            return $response->getBody();

        } catch ( ConnectException $e ) {
            error_log('ConnectException ' . $e->getMessage());
        } catch ( GuzzleException $e ) {
            error_log('GuzzleException ' . $e->getMessage());
        }
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
            error_log('ConnectException ' . $e->getMessage());
        } catch ( GuzzleException $e ) {
            error_log('GuzzleException ' . $e->getMessage());
        }
    }

    // Listen to ajax requests through wp rest: setup, declare callback

    // Send events to lbu

    // Get starting status when starting a pathway
}
