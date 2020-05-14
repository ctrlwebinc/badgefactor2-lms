<div class="cmb2-options-page">
	<?php if ( isset( $_GET['notice'] ) ) : ?>
		<?php 
			switch ( $_GET['notice'] ) {
				case 'updated':
					?>
					<div class="updated settings-error notice is-dismissible"> 
						<p><strong><?php echo __('Assertion updated.', 'badgefactor2'); ?></strong></p>
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
			</div>
		</div>
		<p class="submit">
			<input type="submit" class="button button-primary" value="
			<?php if ( isset( $entity ) ) : ?>
				<?php echo __( 'Edit Assertion', 'badgefactor2' ); ?>
			<?php else : ?>
				<?php echo __( 'Create Assertion', 'badgefactor2' ); ?>
			<?php endif; ?>"
				>
		</p>
	</form>
</div>
