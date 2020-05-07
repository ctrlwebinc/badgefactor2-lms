<div class="cmb2-options-page">
	<form class="cmb-form" method="post" enctype="multipart/form-data">
		<div class="cmb2-wrapform-table">
			<div class="cmb2-metabox cmb-field-list">
				<div class="cmb-row cmb-type-text table-layout">	
					<div class="cmb-th">
						<label for="name"><?php echo __( 'Name', 'badgefactor2' ); ?></label>
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
						<label for="issuer_slug"><?php echo __( 'Issuer', 'badgefactor2' ); ?></label>
					</div>
					<div class="cmb-td">
						<select name="issuer_slug" class="cmb2-select cmb2-select-medium" required>
							<?php foreach ( BadgeFactor2\Issuer::all() as $issuer ) : ?>
							<option value="<?php echo $issuer->entityId; ?>"><?php echo $issuer->name; ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
				<div class="cmb-row cmb-type-text table-layout">	
					<div class="cmb-th">
						<label for="description"><?php echo __( 'Description', 'badgefactor2' ); ?></label>
					</div>
					<div class="cmb-td">
						<input type="text" name="description" class="cmb2-text cmb2-text-medium regular-text" required
						<?php
						if ( isset( $entity ) ) :
							?>
							value="<?php echo $entity->description; ?>"<?php endif; ?>>
					</div>
				</div>
				<div class="cmb-row cmb-type-file table-layout">
					<div class="cmb-th">
						<label for="image"><?php echo __( 'Image', 'badgefactor2' ); ?></label>
					</div>
					<div class="cmb-td">
						<?php
						if ( isset( $entity ) ) : ?>
							<img style="max-width: 50px" src="<?php echo $entity->image; ?>">
						<?php endif; ?>
						<br/>
						<input type="file" name="image" accept="image/png, image/jpeg, image/svg+xml" required>
					</div>
				</div>
			</div>
		</div>
		<p class="submit">
			<input type="submit" class="button button-primary" value="
			<?php if ( isset( $entity ) ) : ?>
				<?php echo __( 'Edit Badge', 'badgefactor2' ); ?>
			<?php else : ?>
				<?php echo __( 'Create Badge', 'badgefactor2' ); ?>
			<?php endif; ?>"
				>
		</p>
	</form>
</div>
