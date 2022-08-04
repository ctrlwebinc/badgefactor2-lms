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

use BadgeFactor2\Models\BadgeClass;
use BadgeFactor2\Post_Types\BadgePage;

/**
 * Send by email helper class.
 */
class SendByEmail {

    /**
	 * Init tasks
	 *
	 * @return void
	 */
	public static function init_hooks() {
		add_action( 'init', array( self::class, 'create_attachment_folder' ) );
		add_action( 'wp_enqueue_scripts', array( self::class, 'add_send_by_email_scripts' ) );
        add_action( 'wp_footer', array( self::class, 'send_certificate_html_code' ) );
		add_action( 'wp_ajax_send_basic_certificate_email', array( self::class, 'send_by_email' ) );
		add_action( 'wp_ajax_nopriv_send_basic_certificate_email', array( self::class, 'send_by_email' ) );
	}

    public static function send_certificate_html_code() {
		?>
		<div class="send_email_popup_overlay">
			<div class="send_email_popup_wrap">
				<div class="send_email_top_bar">
					<i class="fa fa-window-close close_send_email" aria-hidden="true"></i>
				</div>
				<div class="send_email_popup_content">
					<div class="sending_message">
						<span><?php _e( 'We are sending your e-mail...', BF2_DATA['TextDomain'] ) ?></span>
						<div class="progress container">
							<span></span>
							<span></span>
							<span></span>
							<span></span>
						</div>
					</div>	
					<div class="send_email_popup_action_message"></div>
					<p class="description"><?php _e( 'Enter the recipient email address in the field below', BF2_DATA['TextDomain'] ) ?></p>
					<form id="send_email_form" action="" method="post">
						<input type="hidden" id="badge_page" value="<?php echo get_query_var( 'badge' ) ?>" />
						<input type="hidden" id="badge_type" value="" />
						<input type="hidden" id="success_message" value="<?php _e( 'Email successfully sent.', BF2_DATA['TextDomain'] ) ?>" />
						<input type="text" required placeholder="<?php _e( 'E-mail address', BF2_DATA['TextDomain'] ) ?>" id="send_to_email_address" />
						<?php wp_nonce_field('send-basic-certificate', 'send-basic-certificate-nonce');?>
						<p><button type="submit" id="send_email_btn_confirm"><?php _e( 'Send', BF2_DATA['TextDomain'] ) ?></button></p>
					</form>
				</div>
			</div>
		</div>
		<?php
	}

	public static function add_send_by_email_scripts() {
		wp_enqueue_script( 'send-by-email-js', BF2_BASEURL . 'assets/js/send-by-email.js', array('jquery'), '1.0.0', true);
		// wp_localize_script( 'send-by-email-js', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
	}

	/**
	 * Sends certificate/diploma by email
	 */
	public static function send_by_email() {
		$return = array(
			'success'  => false,
			'errors'   => array(),
		);
		$send = false;
		$email_settings = get_option( 'badgefactor2_send_emails_settings' );
		
		$current_user = wp_get_current_user();

		if ( $current_user->ID == 0 ) {
			header( 'Content-Type: application/json' );
			echo json_encode( array( 
				'success' => false, 
				'errors' => [ __( 'You must be logged in to send this email.', BF2_DATA['TextDomain'] ) ]
				) );
			exit;
		}

		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'send-basic-certificate' ) ) {
			header( 'Content-Type: application/json' );
			echo json_encode( array( 'success' => false ) );
			exit;
		}
		$email = $_REQUEST['to_email'] ?? null;
		$bage_page_slug = $_REQUEST['badge_page'] ?? '';
		$type = $_REQUEST['type'] ?? 'certificate';
		
		if ( empty( $email ) || ! filter_var($email, FILTER_VALIDATE_EMAIL ) ) {
			$return['errors']['email'] = __( 'Invalide email address.', BF2_DATA['TextDomain'] );
		}

		// Generates certifate pdf file
		if ( $type == 'certificate' ) {
			$filename = BasicCerficateHelper::generate_and_save_certificate( $bage_page_slug );
		} else {
			$filename = CerficateHelper::generate_and_save_certificate( $bage_page_slug );	
		}
		
		
		$from_email = $current_user->user_email;

		if ( empty( $return['errors'] ) ) {

			$links = self::get_login_registration_links();
			$array_search = [ '$site_name$', '$award_type$', '$registration_link$' ];
			$array_replace = [ get_bloginfo( 'sitename' ), $type, self::generate_link_from_url( $links['registration'] ) ];

			$subject = $email_settings['send_certificate_by_email_subject'];
			$subject = str_replace( $array_search, $array_replace, $subject );
			$body = apply_filters( 'the_content', $email_settings['send_certificate_by_email_body'] );
			$body = str_replace( $array_search, $array_replace, $body );
			$to = $email;
			$headers = array ( 'Content-Type: text/html; charset=UTF-8' );
			$attachments = array( $filename );
			$send = wp_mail( $to, $subject, $body, $headers, $attachments );
		}
		$return['success'] = $send;
		if ( !$send ) {
			$return['errors']['system'] = __( 'There was a problem sending the email.', BF2_DATA['TextDomain'] );
		} else {
			unlink( $filename ); // delete file after sending the email
		}

		header( 'Content-Type: application/json' );
		echo json_encode( $return );
		die;
	}

	
	/**
	 * Sets From email for wp_mail headers
	 */
	public static function new_mail_from() {
		$current_user = wp_get_current_user();

		$from_email = get_bloginfo( 'admin_email' );

		if ( $current_user->ID > 0 ) {
			$from_email = $current_user->user_email;
		}

		return $from_email;
	}

	/**
	 * Sets From name for wp_mail headers
	 */
	public static function new_mail_from_name() {
		$current_user = wp_get_current_user();

		$from_name = get_bloginfo( 'blogname' );

		if ( $current_user->ID > 0 ) {
			$from_name = $current_user->first_name . ' ' . $current_user->last_name;
			$from_name = ( $from_name != '' ) ? $from_name : $current_user->user_nicename;
			$from_name = ( $from_name != '' ) ? $from_name : get_bloginfo( 'blogname' );
		}

		return $from_name;
	}

	/**
	 * Returns registration link
	 */
	public static function get_login_registration_links() {
		
		$options = get_option( 'badgefactor2' );

		$login_slug = ! empty( $options['bf2_login_page_slug'] ) ? $options['bf2_login_page_slug'] : 'connexion';
		$login_permalink = site_url( $login_slug ) . '/';

		$registration_slug = ! empty( $options['bf2_registration_page_slug'] ) ? $options['bf2_registration_page_slug'] : 'inscription';
		$registration_permalink = site_url( $registration_slug ) . '/';

		// Handles permalink with WPML
		if ( class_exists( 'SitePress' ) ) {
			$my_current_lang = apply_filters( 'wpml_current_language', NULL );
			$login_permalink = apply_filters( 'wpml_permalink', $login_permalink, $my_current_lang, true ); 
			
			$registration_page = get_page_by_path( $registration_slug );
			if ( !is_null( $registration_page ) ) {
				$translated_registration_page_id = apply_filters( 'wpml_object_id', $registration_page->ID, 'page', FALSE, $my_current_lang );
				$registration_permalink = get_permalink( $translated_registration_page_id );
			}
		}

		return [
			'login' => $login_permalink,
			'registration' => $registration_permalink
		];
	}

	public static function generate_link_from_url( $url ) {
		return '<a href="' . $url . '" target="_blank">' . $url . '</a>';
	}

	public static function create_attachment_folder() {
		$attachment_folder = WP_CONTENT_DIR . '/attachments/';
		if ( ! is_dir( $attachment_folder ) ) {
			mkdir( $attachment_folder );
			$myfile = fopen( $attachment_folder . "index.php", "w");
			fclose($myfile);
		}
			
	}
}
