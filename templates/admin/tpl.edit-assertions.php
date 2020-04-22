<div class="cmb2-options-page">
	<form class="cmb-form" method="post">
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
