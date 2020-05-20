<div class="cmb2-options-page">
	<?php if ( isset( $_GET['notice'] ) ) : ?>
		<?php 
			switch ( $_GET['notice'] ) {
				case 'updated':
					?>
					<div class="updated settings-error notice is-dismissible"> 
						<p><strong><?php echo __('Issuer updated.', 'badgefactor2'); ?></strong></p>
						<button type="button" class="notice-dismiss">
							<span class="screen-reader-text"><?php echo __('Dismiss this message.', 'badgefactor2'); ?></span>
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
				<div class="cmb-row cmb-type-text table-layout">	
					<div class="cmb-th">
						<label for="email"><?php echo __( 'Email', 'badgefactor2' ); ?></label>
					</div>
					<div class="cmb-td">
						<input type="email" name="email" class="cmb2-text cmb2-text-medium regular-text" required
						<?php
						if ( isset( $entity ) ) :
							?>
							value="<?php echo $entity->email; ?>"<?php endif; ?>>
					</div>
				</div>
				<div class="cmb-row cmb-type-text table-layout">	
					<div class="cmb-th">
						<label for="url"><?php echo __( 'URL', 'badgefactor2' ); ?></label>
					</div>
					<div class="cmb-td">
						<input type="text" name="url" class="cmb2-text-url cmb2-text-medium regular-text" required
						<?php
						if ( isset( $entity ) ) :
							?>
							value="<?php echo $entity->url; ?>"<?php endif; ?>>
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
			</div>
		</div>
		<p class="submit">
			<input type="submit" class="button button-primary" value="
			<?php if ( isset( $entity ) ) : ?>
				<?php echo __( 'Edit Issuer', 'badgefactor2' ); ?>
			<?php else : ?>
				<?php echo __( 'Create Issuer', 'badgefactor2' ); ?>
			<?php endif; ?>"
				>
		</p>
	</form>
</div>
