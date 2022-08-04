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
					<div class="send_email_popup_action_message"></div>
					<p>Send this certificate to someone by email</p>
					<form id="send_email_form" action="" method="post">
						<input type="hidden" id="badge_page" value="<?php echo get_query_var( 'badge' ) ?>" />
						<input type="text" required placeholder="Send to email address" id="send_to_email_address" />
						<?php wp_nonce_field('send-basic-certificate', 'send-basic-certificate-nonce');?>
						<p><button type="submit" id="send_email_btn_confirm">Send</button></p>
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

		$current_user = wp_get_current_user();

		if ( $current_user->ID == 0 ) {
			header( 'Content-Type: application/json' );
			echo json_encode( array( 
				'success' => false, 
				'errors' => ['You must be logged in to send this email']
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
		
		if ( empty( $email ) || ! filter_var($email, FILTER_VALIDATE_EMAIL ) ) {
			$return['errors']['email'] = __( 'Invalide email address.', 'oshin' );
		}

		// Generates certifate pdf file
		$filename = BasicCerficateHelper::generate_and_save_certificate( $bage_page_slug );
		
		$from_email = $current_user->user_email;

		if ( empty( $return['errors'] ) ) {
			$to = $email;
			$subject = 'Look at my certificate!';
			$message = 'This is the email content';
			$headers = array ( 'Content-Type: text/html; charset=UTF-8' );
			$attachments = array( $filename );
			$send = wp_mail( $to, $subject, $message, $headers, $attachments );
		}
		$return['success'] = $send;
		if ( !$send ) {
			$return['errors']['system'] = 'There was a problem sending the email';
		}

		header( 'Content-Type: application/json' );
		echo json_encode( $return );
		die;
	}
}
