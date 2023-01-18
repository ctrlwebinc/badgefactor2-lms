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
 * @phpcs:disable WordPress.WP.I18n.NonSingularStringLiteralDomain
 */

namespace BadgeFactor2\Admin\CMB2_Fields;

/**
 * CMB2 Badge Request Content Field.
 */
class Badge_Request_Status {

	/**
	 * Init Hooks.
	 *
	 * @return void
	 */
	public static function init_hooks() {
		add_filter( 'cmb2_render_badge_request_status', array( self::class, 'render_badge_request_status' ), 10, 5 );
	}


	/**
	 * Render Badge Request Status.
	 *
	 * @param CMB2_Field $field Field.
	 * @param string     $field_escaped_value Field escaped value.
	 * @param string     $field_object_id Field object id.
	 * @param string     $field_object_type Field object type.
	 * @param CMB2_Types $field_type_object Field Type Object.
	 * @return void
	 */
	public static function render_badge_request_status( $field, $field_escaped_value, $field_object_id, $field_object_type, $field_type_object ) {
		$badge_request_status = $field_escaped_value;

		$options = array(
			'requested' => __( 'Requested', BF2_DATA['TextDomain'] ),
			'granted'   => __( 'Granted', BF2_DATA['TextDomain'] ),
			'rejected'  => __( 'Rejected', BF2_DATA['TextDomain'] ),
			'revision'  => __( 'Revision requested', BF2_DATA['TextDomain'] ),
			'revoked'   => __( 'Revoked', BF2_DATA['TextDomain'] ),
			'revision_cancelled'   => __( 'Revision cancelled', BF2_DATA['TextDomain'] ),
			'rejection_cancelled'   => __( 'Rejection cancelled', BF2_DATA['TextDomain'] ),
		);

		echo sprintf( '<div style="margin-top: 6px">%s</div>', $options[ $badge_request_status ]);
		echo sprintf( '<input type="hidden" name="status" value="%s">', $badge_request_status );
		if ( 'requested' === $badge_request_status ) {
			echo '<span class="button-group" style="margin-top: 1rem">';
			echo sprintf( '<button data-confirm="%s" class="button button-secondary" id="approve-badge">%s</button>', __( 'Approve this badge request?', BF2_DATA['TextDomain'] ), __( 'Approve', BF2_DATA['TextDomain'] ) );
			echo sprintf( '<button class="button button-secondary" id="start-badge-revision">%s</button>', __( 'Request Revision', BF2_DATA['TextDomain'] ) );
			echo sprintf( '<button class="button button-secondary" id="start-badge-rejection">%s</button>', __( 'Reject', BF2_DATA['TextDomain'] ) );
			echo '</span>';
		}

		if ( 'revision' === $badge_request_status ) {
			echo '<span class="button-group" style="margin-top: 1rem">';
			echo sprintf( '<button data-confirm="%s" class="button button-secondary" id="cancel-revise-badge">%s</button>', __( 'Cancel this badge request revision?', BF2_DATA['TextDomain'] ), __( 'Cancel revision', BF2_DATA['TextDomain'] ) );
			echo '</span>';
		}

		if ( 'rejected' === $badge_request_status ) {
			echo '<span class="button-group" style="margin-top: 1rem">';
			echo sprintf( '<button data-confirm="%s" class="button button-secondary" id="cancel-reject-badge">%s</button>', __( 'Cancel this badge request rejection?', BF2_DATA['TextDomain'] ), __( 'Cancel rejection', BF2_DATA['TextDomain'] ) );
			echo '</span>';
		}

	}
}


