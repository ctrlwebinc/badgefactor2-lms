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
						<p><strong><?php echo __( 'Badge updated.', BF2_DATA['TextDomain'] ); ?></strong></p>
						<button type="button" class="notice-dismiss">
							<span class="screen-reader-text"><?php echo __( 'Dismiss this message.', BF2_DATA['TextDomain'] ); ?></span>
						</button>
					</div>
					<?php
				break;
		}
		?>
	<?php endif; ?>
	<form class="cmb-form" method="post" enctype="multipart/form-data">
		<div class="cmb2-wrapform-table">
			<div class="cmb2-metabox cmb-field-list">
				<div class="cmb-row cmb-type-text table-layout">	
					<div class="cmb-th">
						<label for="name"><?php echo __( 'Name', BF2_DATA['TextDomain'] ); ?></label>
					</div>
					<div class="cmb-td">
						<input type="text" name="name" class="cmb2-text cmb2-text-medium regular-text" required 
						<?php
						if ( isset( $entity ) ) :
							?>
							value="<?php echo $entity->name; ?>"<?php endif; ?>>
					</div>
				</div>
				<div class="cmb-row cmb-type-select table-layout">	
					<div class="cmb-th">
						<label for="issuer_slug"><?php echo __( 'Issuer', BF2_DATA['TextDomain'] ); ?></label>
					</div>
					<div class="cmb-td">
						<select name="issuer_slug" class="cmb2-select cmb2-select-medium" required>
							<?php foreach ( BadgeFactor2\Models\Issuer::all() as $issuer ) : ?>
							<option value="<?php echo $issuer->entityId; ?>"><?php echo $issuer->name; ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
				<div class="cmb-row cmb-type-textarea table-layout">	
					<div class="cmb-th">
						<label for="description"><?php echo __( 'Description', BF2_DATA['TextDomain'] ); ?></label>
					</div>
					<div class="cmb-td">
						<textarea class="bf2_tinymce" name="description" cols="60" rows="10">
							<?php if ( isset( $entity ) ) : ?>
								<?php echo $entity->description; ?>
							<?php endif; ?>
						</textarea>
					</div>
				</div>
				<div class="cmb-row cmb-type-file table-layout">
					<div class="cmb-th">
						<label for="image"><?php echo __( 'Image', BF2_DATA['TextDomain'] ); ?></label>
					</div>
					<div class="cmb-td">
						<?php
						if ( isset( $entity ) ) :
							?>
							<img style="max-width: 50px" src="<?php echo $entity->image; ?>">
						<?php endif; ?>
						<br/>
						<input type="file" name="image" accept="image/png, image/jpeg, image/svg+xml" 
						<?php
						if ( ! isset( $entity ) ) :
							?>
							required<?php endif; ?>>
					</div>
				</div>
			</div>
		</div>
		<p class="submit">
			<input type="submit" class="button button-primary" value="
			<?php if ( isset( $entity ) ) : ?>
				<?php echo __( 'Edit Badge', BF2_DATA['TextDomain'] ); ?>
			<?php else : ?>
				<?php echo __( 'Create Badge', BF2_DATA['TextDomain'] ); ?>
			<?php endif; ?>"
				>
		</p>
	</form>
</div>
