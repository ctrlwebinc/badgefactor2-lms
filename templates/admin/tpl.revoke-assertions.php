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

?>
<div class="cmb2-options-page">
	<?php if ( isset( $_GET['notice'] ) ) : ?>
		<?php
		switch ( $_GET['notice'] ) {
			case 'updated':
				?>
					<div class="updated settings-error notice is-dismissible"> 
						<p><strong><?php echo __( 'Assertion updated.', BF2_DATA['TextDomain'] ); ?></strong></p>
						<button type="button" class="notice-dismiss">
							<span class="screen-reader-text"><?php echo __( 'Dismiss this message.', BF2_DATA['TextDomain'] ); ?></span>
						</button>
					</div>
					<?php
				break;
		}
		?>
	<?php endif; ?>
	<?php if ( isset( $entity ) ) : ?>
		<?php
		// Revoke.
		?>
	<form class="cmb-form" method="post">
		<div class="cmb2-wrapform-table">
			<div class="cmb2-metabox cmb-field-list">
				<div class="cmb-row cmb-type-file table-layout">
					<div class="cmb-th">
						<?php echo __( 'Image', BF2_DATA['TextDomain'] ); ?>
					</div>
					<div class="cmb-td">
						<?php
						if ( isset( $entity ) ) :
							?>
							<img style="max-width: 50px" src="<?php echo $entity->image; ?>">
						<?php endif; ?>
						<br/>
					</div>
				</div>
				<div class="cmb-row table-layout">	
					<div class="cmb-th">
						<?php echo __( 'Issuer', BF2_DATA['TextDomain'] ); ?>
					</div>
					<div class="cmb-td">
						<?php $issuer = \BadgeFactor2\Models\Issuer::get( $entity->issuer ); ?>
						<?php echo $issuer->name; ?>
					</div>
				</div>
				<div class="cmb-row table-layout">	
					<div class="cmb-th">
						<?php echo __( 'Badge', BF2_DATA['TextDomain'] ); ?>
					</div>
					<div class="cmb-td">
						<?php $badge = \BadgeFactor2\Models\BadgeClass::get( $entity->badgeclass ); ?>
						<?php echo $badge->name; ?>
					</div>
				</div>
				<div class="cmb-row table-layout">	
					<div class="cmb-th">
						<?php echo __( 'Recipient', BF2_DATA['TextDomain'] ); ?>
					</div>
					<div class="cmb-td">
						<?php echo $entity->recipient->plaintextIdentity; ?>
					</div>
				</div>
				<div class="cmb-row table-layout">	
					<div class="cmb-th">
						<?php echo __( 'Issued On', BF2_DATA['TextDomain'] ); ?>
					</div>
					<div class="cmb-td">
						<?php $date = strtotime( $entity->issuedOn ); ?>
						<?php echo gmdate( 'Y-m-d&\nb\s\p;H:i:s', $date ); ?>
					</div>
				</div>
				<input type="hidden" name="assertion" value="<?php echo $entity->entityId; ?>">
				<div class="cmb-row cmb-type-textarea table-layout">	
					<div class="cmb-th">
						<?php echo __( 'Reason to revoke', BF2_DATA['TextDomain'] ); ?>
					</div>
					<div class="cmb-td">
						<textarea class="bf2_tinymce" name="reason" cols="60" rows="10"></textarea>
					</div>
				</div>
			</div>
		</div>
		<p class="submit">
			<input type="submit" class="button button-secondary delete" onclick="if(!confirm( '<?php echo __('Are you sure you want to revoke this item?', BF2_DATA['TextDomain']); ?>' ) ) { event.preventDefault() }" value="<?php echo __( 'Revoke Assertion', BF2_DATA['TextDomain'] ); ?>">
		</p>
		<?php else : ?>
			You shouldn't be here.
		<?php endif; ?>
	</form>
</div>
