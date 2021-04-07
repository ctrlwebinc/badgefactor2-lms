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
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 * @phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
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
			case 'revoked':
				?>
					<div class="updated settings-error notice is-dismissible">
						<p><strong><?php echo __( 'Assertion revoked.', BF2_DATA['TextDomain'] ); ?></strong></p>
						<button type="button" class="notice-dismiss">
							<span class="screen-reader-text"><?php echo __( 'Dismiss this message.', BF2_DATA['TextDomain'] ); ?></span>
						</button>
					</div>
					<?php
				break;
			case 'error':
				?>
					<div class="updated settings-error error">
						<p><strong><?php echo __( 'Invalid email or user does not exist.', BF2_DATA['TextDomain'] ); ?></strong></p>
					</div>
					<?php
				break;
			case 'revocation-error':
				?>
					<div class="updated settings-error error">
						<p><strong><?php echo __( 'The revocation of this assertion has failed.', BF2_DATA['TextDomain'] ); ?></strong></p>
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
						<?php if ( ! $entity->revoked ) : ?>
						<img style="max-width: 150px" src="<?php echo $entity->image; ?>">
						<?php else : ?>
							<?php echo __( 'REVOKED!', 'Badgefactor2' ); ?>
						<?php endif; ?>
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
			<?php if ( $entity->revoked ) : ?>
				<div class="cmb-row table-layout">	
				<div class="cmb-th">
					<?php echo __( 'Revocation reason', BF2_DATA['TextDomain'] ); ?>
				</div>
				<div class="cmb-td">
					<?php echo $entity->revocationReason; ?>
				</div>
			</div>
			<?php endif; ?>
		</div>
	</div>
		<?php if ( ! $entity->revoked ) : ?>
	<p class="submit">
		<a class="button button-primary" href="<?php echo "?page=assertions&action=revoke&entity_id={$entity->entityId}"; ?>"><?php echo __( 'Revoke Assertion', BF2_DATA['TextDomain'] ); ?></a>
	</p>
	<?php endif; ?>
		<?php
	elseif ( isset( $_GET['filter_type'] )
		&& isset( $_GET['filter_value'] )
		&& ! empty( $_GET['filter_type'] )
		&& ! empty( $_GET['filter_value'] ) ) :
		?>
		<?php
		// Create.
		$filter_type  = $_GET['filter_type'];
		$filter_value = $_GET['filter_value'];

		$issuer = null;
		$badge  = null;

		switch ( $filter_type ) {
			case 'Issuers':
				$issuer = \BadgeFactor2\Models\Issuer::get( $filter_value );
				$badge  = \BadgeFactor2\Models\BadgeClass::all( -1, 1, array( 'issuer' => $filter_value ) );
				break;
			case 'Badges':
				$badge  = \BadgeFactor2\Models\BadgeClass::get( $filter_value );
				$issuer = \BadgeFactor2\Models\Issuer::get( $badge->issuer );
				break;
		}
		?>
	<form class="cmb-form" method="post" enctype="multipart/form-data">
		<div class="cmb2-wrapform-table">
			<div class="cmb2-metabox cmb-field-list">
				<div class="cmb-row table-layout">	
					<div class="cmb-th">
						<label for="issuer"><?php echo __( 'Issuer', BF2_DATA['TextDomain'] ); ?></label>
					</div>
					<div class="cmb-td">
						<input type="hidden" name="issuer" value="<?php echo $issuer->entityId; ?>">
						<input type="text" value="<?php echo $issuer->name; ?>" disabled>
					</div>
				</div>
				<?php if ( is_array( $badge ) ) : ?>
				<div class="cmb-row cmb-type-select table-layout">	
					<div class="cmb-th">
						<label for="badge"><?php echo __( 'Badge', BF2_DATA['TextDomain'] ); ?></label>
					</div>
					<div class="cmb-td">
						<select name="badge" class="cmb2-select cmb2-select-medium" required>
							<option value=""><?php echo __( 'Select a Badge', BF2_DATA['TextDomain'] ); ?></option>
							<?php foreach ( $badge as $b ) : ?>
							<option value="<?php echo $b->entityId; ?>"><?php echo $b->name; ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
				<?php else : ?>
				<div class="cmb-row table-layout">	
					<div class="cmb-th">
						<?php echo __( 'Badge', BF2_DATA['TextDomain'] ); ?>
					</div>
					<div class="cmb-td">
							<input type="hidden" name="badge" value="<?php echo $badge->entityId; ?>">
							<input type="text" value="<?php echo $badge->name; ?>" disabled>
					</div>
				</div>
				<?php endif; ?>
				<div class="cmb-row cmb-type-text table-layout">
					<div class="cmb-th">
						<?php echo __( 'Recipient Email', BF2_DATA['TextDomain'] ); ?>
					</div>
					<div class="cmb-td">
						<input type="text" name="recipient" class="cmb2-text cmb2-text-medium regular-text" required>
					</div>
				</div>
			</div>
		</div>
		<p class="submit">
			<input type="submit" class="button button-primary" value="<?php echo __( 'Create Assertion', BF2_DATA['TextDomain'] ); ?>">
		</p>
	</form>
	<?php else : ?>
		You shouldn't be here.
	<?php endif; ?>
</div>
