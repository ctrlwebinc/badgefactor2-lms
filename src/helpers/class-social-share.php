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
use BadgeFactor2\Models\BadgeClass;
use BadgeFactor2\Post_Types\BadgePage;

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

    /**
     * Get shares
     * 
     * @param Assertion $assertion
     * @param BadgePage $badge_page
     * @param boolean $logged_in_user_s_own_page
     */
    public static function getShares( $assertion, $badge_page ) {
        $social_share_data = array();
        $share_image_relative_url_start = site_url('/bf2/share/');
        $description = self::generateOgDescription( $badge_page );
        $title =  self::generateOgTitle( $badge_page );
        $url = self::getCurrentUrl();
        $social_share_settings = get_option( 'badgefactor2_social_media_settings' );
        $badge_url = $assertion->openBadgeId;
        $additional_css_classes = ( $assertion->has_privacy_flag ) ? ' bf2_social_share has_privacy_flag' : '';

        if ( $social_share_settings && array_key_exists( 'bf2_social_media_sharing_' . self::MEDIA_FACEBOOK, $social_share_settings ) ) {
            $social_share_data[self::MEDIA_FACEBOOK] = [
                'sharing_url' => self::generateSharingUrl( $assertion, $badge_page, self::MEDIA_FACEBOOK), 
                'sharing_text' => 'Share on Facebook',
                'sharing_classes' => 'share_facebook' . $additional_css_classes,
                'shareable_url' => $badge_url,
                'url' => $url,
                'description' => $description,
                'titre' => $title,
                'image_url' => $share_image_relative_url_start . self::MEDIA_FACEBOOK . '/' . base64_encode( $assertion->image),
            ];
        }

        if ( $social_share_settings && array_key_exists( 'bf2_social_media_sharing_' . self::MEDIA_TWITTER, $social_share_settings ) ) {
            $social_share_data[self::MEDIA_TWITTER] = [
                'sharing_url' => self::generateSharingUrl( $assertion, $badge_page, self::MEDIA_TWITTER), 
                'sharing_text' => 'Share on Twitter',
                'sharing_classes' => 'share_twitter' . $additional_css_classes,
                'shareable_url' => $badge_url,
                'url' => $url,
                'description' => $description,
                'titre' => $title,
                'image_url' => $share_image_relative_url_start . self::MEDIA_TWITTER . '/' . base64_encode( $assertion->image),
            ];
        }

        if ( $social_share_settings && array_key_exists( 'bf2_social_media_sharing_' . self::MEDIA_LINKEDIN, $social_share_settings ) ) {
            
            $social_share_data[self::MEDIA_LINKEDIN] = [
                'sharing_url' => self::generateSharingUrl( $assertion, $badge_page, self::MEDIA_LINKEDIN), 
                'sharing_text' => 'Share on LinkedIn',
                'sharing_classes' => 'share_linkedin' . $additional_css_classes,
                'shareable_url' => $badge_url, 
                'url' => $url,
                'description' => $description,
                'titre' => $title,
                'image_url' => $share_image_relative_url_start . self::MEDIA_LINKEDIN . '/' . base64_encode( $assertion->image),
            ];
        }
        
        
        return $social_share_data;
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
            echo $img->response('png');

        } catch ( \Exception $e ) {
            // TODO: return a generic bf2 image
        }

        return;

    }

    public static function handleHeaders() {
        global $bf2_template;
        
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

    public static function generateSharingUrl( $assertion,  $badge_page, $social_media ) {
        $badge_url = urlencode( $assertion->openBadgeId );
        
        if ( 'facebook' == $social_media ) {
            $sharing_url = "https://www.facebook.com/sharer/sharer.php?u=" . $badge_url;
        } else if ( 'twitter' == $social_media ) {
            $sharing_url = "https://twitter.com/intent/tweet?";
            $sharing_url .= "text=" . urlencode( $badge_page->post_title );
            $sharing_url .= "&url=" . $badge_url;
        } else if ( 'linkedin' == $social_media ) {
            $sharing_url = "https://www.linkedin.com/sharing/share-offsite/?url=" . $badge_url;
        }

        return $sharing_url;
    }

    public static function generateOgDescription( $badge_page ) {
        if ( !is_null( $badge_page ) ) 
            return wp_trim_words( strip_tags( $badge_page->post_content ), 30, '' );
        else
            return "";
    }

    public static function generateOgTitle( $badge_page ) {
        if ( !is_null( $badge_page ) ) 
            return $badge_page->post_title;
        else
            return "";
    }

    public static function getCurrentUrl() {
        // Program to display URL of current page.
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
            $link = "https";
        else $link = "http";
        
        // Here append the common URL characters.
        $link .= "://";
        
        // Append the host(domain name, ip) to the URL.
        $link .= $_SERVER['HTTP_HOST'];
        
        // Append the requested resource location to the URL
        $link .= $_SERVER['REQUEST_URI'];
        
        // Print the link
        return $link;
    }
}
