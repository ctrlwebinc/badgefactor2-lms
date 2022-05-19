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

    const MEDIA_FACEBOOK = 'facebook'; // should be 1200×630, Recommended ratio: 1.91:1
    const MEDIA_TWITTER = 'twitter'; // ratio of 2:1 with minimum dimensions of 300x157 or maximum of 4096x4096 pixels
    const MEDIA_LINKEDIN= 'linkedin'; // Minimum image dimensions: 1200 (w) x 627 (h) pixels, Recommended ratio: 1.91:1

    /**
	 * Init tasks
	 *
	 * @return void
	 */
	public static function init_hooks() {
		add_action( 'init', array( self::class, 'init' ) );
		add_filter( 'query_vars', array( self::class, 'hook_query_vars' ) );
		add_action( 'template_redirect', array( self::class, 'hook_template_redirect' ) );
        // add hook for header calls header override function
        add_action( 'wp_head', array( self::class, 'handleHeaders' ) );

	}
    
    /**
	 * Init hook.
	 *
	 * @return void
	 */
	public static function init() {
		// TODO: add auth/welcome.
		add_rewrite_rule(
            'bf2/(share)/(' . self::MEDIA_LINKEDIN . '|' . self::MEDIA_FACEBOOK . '|' . self::MEDIA_TWITTER . ')/(.*)',
            'index.php?bf2=$matches[1]&media=$matches[2]&url=$matches[3]',
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
		$vars[] = 'media';
		$vars[] = 'url';
		return $vars;
	}

	public static function hook_template_redirect() {
		$bf2 = get_query_var( 'bf2' );
		if ( $bf2 ) {
			if ( 'share' === $bf2 ) {
                $media = get_query_var('media');
                $url = base64_decode( get_query_var('url') );
                self::serveShareImage( $media, $url );
                exit();
            }
		}
	}

    public static function getShares( $assertion) {
        $share_image_relative_url_start = site_url('/bf2/share/');

        return [
            self::MEDIA_FACEBOOK => [
                'sharing_url' => 'http://facebook.com', // https://www.facebook.com/sharer/sharer.php?u=https://iqpf.ctrlweb.dev/apprenants/ctrlweb/badges/badge-numero-3/
                'sharing_text' => 'Share on Facebook',
                'sharing_classes' => 'share_facebook',
                'url' => '/apprenants/ctrlweb/badge5',
                'description' => 'Une description pour Facebook',
                'titre' => 'Tahina a reçu le badge de planificateur financier',
                'image_url' => $share_image_relative_url_start . self::MEDIA_FACEBOOK . '/' . base64_encode( $assertion->image),
            ],
            self::MEDIA_TWITTER => [
                'sharing_url' => 'http://twitter.com', // href="https://twitter.com/intent/tweet?text=Hello%20world&url=https://iqpf.ctrlweb.dev/apprenants/ctrlweb/badges/badge-numero-3/
                'sharing_text' => 'Share on Twitter',
                'sharing_classes' => 'share_twitter',
                'url' => '/apprenants/ctrlweb/badge5',
                'description' => 'Une description pour Twitter',
                'titre' => 'Tahina a reçu le badge de planificateur financier',
                'image_url' => $share_image_relative_url_start . self::MEDIA_TWITTER . '/' . base64_encode( $assertion->image),
            ],
            self::MEDIA_LINKEDIN => [
                'sharing_url' => 'http://linkedin.com', // https://www.linkedin.com/sharing/share-offsite/?url=https://iqpf.ctrlweb.dev/apprenants/ctrlweb/badges/badge-numero-3/
                'sharing_text' => 'Share on LinkedIn',
                'sharing_classes' => 'share_linkedin',
                'url' => '/apprenants/ctrlweb/badge5',
                'description' => 'Une description pour LinkedIn',
                'titre' => 'Tahina a reçu le badge de planificateur financier',
                'image_url' => $share_image_relative_url_start . self::MEDIA_LINKEDIN . '/' . base64_encode( $assertion->image),
            ],
        ];
    }

    public static function serveShareImage( $media, $url) {
        try {

            $client = new Client();

            $response = $client->get($url);

            $img = Image::make($response->getBody());
            switch ($media) {
                case self::MEDIA_FACEBOOK:
                    $img = $img->resizeCanvas( 1200, 630, 'center');
                    break;
                case self::MEDIA_LINKEDIN:
                    $img = $img->resizeCanvas( 1200, 627, 'center');
                    break;
                case self::MEDIA_TWITTER:
                    $img = $img->resizeCanvas( 800, 400, 'center');
                    break;                    
            }
            $img->resize(100, 100);
            echo $img->response('png');

        } catch ( \Exception $e ) {

        }

        return;

    }

    public static function handleHeaders() {
        global $bf2_template;
        // déterminer sir on est sur une page d'asserstion
        // tester global $bf2_template et son contenu

        // Si oui, émettre les tags OG

        // Boucle sur tous les réseaux sociaux
        if ( isset($bf2_template->fields['assertion']) ) {
            echo '<!-- bf2 og tags -->' . PHP_EOL;
            if ( is_array( $bf2_template->fields['sharing'] ) ) {
                foreach( $bf2_template->fields['sharing'] as $key => $value ) {
                    if ( 'facebook' == $key ) { // linkedin uses the same og tags
                        echo '<meta property="og:url"                content="' . $value['url'] . '" />' . PHP_EOL;
                        echo '<meta property="og:type"               content="assertion" />' . PHP_EOL;
                        echo '<meta property="og:title"              content="' . $value['titre'] . '" />' . PHP_EOL;
                        echo '<meta property="og:description"        content="' . $value['description'] . '" />' . PHP_EOL;
                        echo '<meta property="og:image"              content="' . $value['image_url'] . '" />' . PHP_EOL;
                    }
                    if ( 'twitter' == $key ) {
                        echo '<meta name="twitter:card"        content="summary_large_image">' . PHP_EOL;
                        // echo '<meta name="twitter:site"     content="@yourwebsite">' . PHP_EOL;
                        // echo '<meta name="twitter:creator"  content="@yourtwitterhandle">' . PHP_EOL;
                        echo '<meta name="twitter:title"       content="' . $value['titre'] . '">' . PHP_EOL;
                        echo '<meta name="twitter:description" content="' . $value['description'] . '">' . PHP_EOL;
                        echo '<meta name="twitter:image"       content="' . $value['image_url'] . '">' . PHP_EOL;
                    }
                }
            }
            echo '<!-- /bf2 og tags -->' . PHP_EOL;
        }
    }
}
