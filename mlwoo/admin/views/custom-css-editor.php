<div class="ml2-block mlwoo-css-editors-wrapper">
	<div class="ml2-header"><h2>Commerce Custom CSS</h2></div>
	<div class="ml2-body">
		<div class='ml-col-row'>
			<div class="ml-editor-controls">
				<em>Inject CSS in MobiLoud Commerce endpoint pages when viewed in the app.</em>
				<a href="#" class='button-primary mlwoo-page-css-save'>Save</a>
				<?php wp_nonce_field( 'mlwoo_save_editor', 'mlwoo_nonce_editor' ); ?>
			</div>
			<textarea
				class='ml-editor-area'
				name='mlwoo-commerce-css-textarea'><?php echo stripslashes( htmlspecialchars( Mobiloud::get_option( 'mlwoo-commerce-css-textarea', '' ) ) ); ?></textarea>
		</div>
	</div>
</div>
