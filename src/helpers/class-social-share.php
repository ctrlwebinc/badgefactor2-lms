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
 *
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound
 */

namespace BadgeFactor2\Helpers;

use Intervention\Image\ImageManagerStatic as Image;
use GuzzleHttp\Client;

/**
 * Text helper class.
 */
class SocialShare {

    const MEDIA_FACEBOOK = 'facebook';
    const MEDIA_TWITTER = 'twitter';
    const MEDIA_LINKEDIN= 'linkedin';

    /**
	 * Init tasks
	 *
	 * @return void
	 */
	public static function init_hooks() {
		add_action( 'init', array( self::class, 'init' ) );
		add_filter( 'query_vars', array( self::class, 'hook_query_vars' ) );
		add_action( 'template_redirect', array( self::class, 'hook_template_redirect' ) );
	}
    
    /**
	 * Init hook.
	 *
	 * @return void
	 */
	public static function init() {
		// TODO: add auth/welcome.
		add_rewrite_rule(
			'bf2/(share)/?',
			'index.php?bf2=$matches[1]',
			'top'
		);
	}

    /**
	 * Signal ou interest in bf2 query variable
	 *
	 * @param array $vars Variables.
	 * @return array
	 */
	public static function hook_query_vars( $vars ) {
		$vars[] = 'bf2';
		return $vars;
	}

	public static function hook_template_redirect() {
		$bf2 = get_query_var( 'bf2' );
		if ( $bf2 ) {
			if ( 'share' === $bf2 ) {
                self::serveShareImage();
                exit();
            }
            header( 'Content-Type: text/plain' );
            echo 'Badgr callback: ' . $bf2;
            echo ' Full uri: ' . $_SERVER['REQUEST_URI'];
            exit();
		}
	}

    public static function getShares( $assertion) {
        return [
            self::MEDIA_FACEBOOK => [
                'sharing_url' => 'http://facebook.com',
                'sharing_text' => 'Share on Facebook',
                'sharing_clases' => 'share_facebook',
                'url' => '/apprenants/ctrlweb/badge5',
                'description' => 'Une description pour Facebook',
                'titre' => 'Tahina a reçu le badge de planificateur financier',
                'image_url' => '/bf2/share/' . self::MEDIA_FACEBOOK,
            ],
            self::MEDIA_TWITTER => [
                'sharing_url' => 'http://twitter.com',
                'sharing_text' => 'Share on Twitter',
                'sharing_clases' => 'share_twitter',
                'url' => '/apprenants/ctrlweb/badge5',
                'description' => 'Une description pour Twitter',
                'titre' => 'Tahina a reçu le badge de planificateur financier',
                'image_url' => '/bf2/share/' . self::MEDIA_TWITTER,
            ],
            self::MEDIA_LINKEDIN => [
                'sharing_url' => 'http://linkedin.com',
                'sharing_text' => 'Share on LinkedIn',
                'sharing_clases' => 'share_linkedin',
                'url' => '/apprenants/ctrlweb/badge5',
                'description' => 'Une description pour LinkedIn',
                'titre' => 'Tahina a reçu le badge de planificateur financier',
                'image_url' => '/bf2/share/' . self::MEDIA_LINKEDIN,
            ],
        ];
    }

    public static function serveShareImage() {
        try {

        $client = new Client([
            // Base URI is used with relative requests
            'base_uri' => 'https://badgr-iqpf.ctrlweb.dev',
            // You can set any number of default request options.
            'timeout'  => 2.0,
        ]);

        $response = $client->get('/media/uploads/badges/assertion-0h30U7K7QGaqRj8S1LH6_w.png');

            // create a new image resource
            $img = Image::canvas(800, 600, '#ff0000');

            // send HTTP header and output image data
            echo $img->response('jpg', 70);
        } catch ( \Exception $e ) {

        }

        return;

    }
}
