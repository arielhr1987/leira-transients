<?php

use Leira_Transients\Admin\List_Table;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.

	/** @var $table List_Table */
} ?>
<form method="get">
	<table style="display: none">
		<tbody id="inlineedit">
		<tr id="inline-edit" class="inline-edit-row quick-edit-row-page inline-edit-row-page" style="display: none">
			<td colspan="<?php esc_html( $table->get_column_count() ) ?>" class="colspanchange">
				<input type="hidden" name="action" value="leira-transient-save"/>
				<input type="hidden" name="name" value=""/>
				<?php wp_nonce_field( $table->get_wpnonce_action() ); ?>
				<div class="inline-edit-wrapper" role="region">
					<fieldset class="inline-edit-col-top">
						<legend class="inline-edit-legend">
							<?php esc_html_e( 'Quick Edit', 'leira-transients' ); ?>
						</legend>
						<div class="inline-edit-col inline-edit-col-split">
							<label class="inline-edit-col-left">
								<span class="title"><?php esc_html_e( 'Name', 'leira-transients' ); ?></span>
								<span class="input-text-wrap">
									<input class="ptitle" type="text" name="name-label" value="" autocomplete="off" disabled/>
								</span>
							</label>
							<label class="inline-edit-col-left">
								<span class="title"><?php esc_html_e( 'Expiration', 'leira-transients' ); ?></span>
								<span class="input-text-wrap">
									<input class="ptitle" type="datetime-local" name="expiration" autocomplete="off"/>
								</span>
							</label>
						</div>
					</fieldset>
					<fieldset class="inline-edit-col-bottom">
						<div class="inline-edit-col">
							<label>
								<span class="title"><?php esc_html_e( 'Value', 'leira-transients' ); ?></span>
								<span class="input-text-wrap">
									<textarea class="" name="value"></textarea>
								</span>
							</label>
						</div>
					</fieldset>
					<div class="inline-edit-save submit">
						<button type="button" class="cancel button alignleft">
							<?php esc_html_e( 'Cancel', 'leira-transients' ); ?>
						</button>
						<button type="button" class="save button button-primary alignright">
							<?php esc_html_e( 'Save', 'leira-transients' ); ?>
						</button>
						<span class="spinner"></span>
						<br class="clear"/>
						<div class="notice notice-error notice-alt inline hidden">
							<p class="error"></p>
						</div>
					</div>
				</div>
			</td>
		</tr>
		</tbody>
	</table>
</form>
