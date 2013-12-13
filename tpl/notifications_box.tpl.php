<?php
/**
 * @package Book_It
 * @version 2.0
 */
$options = bookit_get_options();
?>
<div id="notificationOptions">
	<p><label for="bookit_send_to_name">
		<b><?php echo __('Name', 'bookit') ?>:</b>
		<input type="text" id="bookit_send_to_name" value="<?php echo esc_attr(get_post_meta($post->ID, 'bookit_primary_passenger', true)) ?>">
	</label></p>

	<p><label for="bookit_send_to_email">
		<b><?php echo __('Email', 'bookit') ?>:</b>
		<input type="text" id="bookit_send_to_email" value="<?php echo esc_attr(get_post_meta($post->ID, 'bookit_contact_email', true)) ?>">
	</label></p>

	<?php if ( is_array($options['email_templates']) ): ?>
		<label for="bookit_template_id">
			<b><?php echo __('Email Template', 'bookit') ?>:</b>
			<select id="bookit_template_id">
				<?php foreach ( $options['email_templates'] as $key => $val ): ?>
					<option value="<?php echo $key ?>"><?php echo $val['name'] ?></option>
					<? endforeach; ?>
			</select>
		</label>
	<?php endif; ?>
	<div class="bookit-msg notice">
		<?php echo __('Save changes before sending.', 'bookit') ?>
	</div>
	<a href="#" class="button" id="sendEmail"><?php echo __('Send') ?></a>
</div>

<script>
(function($) {
	$(function() {
		$('#sendEmail').bind('click', function(e) {
			e.preventDefault();
			var msg = $('<div />').attr('id', 'notificationM').addClass('updated').html('Sending, please wait&hellip;');;
			$('#notificationOptions').before(msg);

			var data = {
	      bookit_action: 'send_email',
	      bookit_template_id: $('#bookit_template_id').val(),
	      bookit_send_to_email: $('#bookit_send_to_email').val(),
	      bookit_send_to_name: $('#bookit_send_to_name').val(),
	      ID: <?php echo get_the_ID(); ?>
	    };
			$.post(ajaxurl, data, function( response ) {
				var result = $.parseJSON( response );
				$.each( result, function ( index, value ) {
					$('#notificationM').html( value + '<br>' );
				});
	      setTimeout(function() {
	      	$('#notificationM').fadeOut(function() {
	      		$(this).remove();
	      	});
	      }, 3000);
	    });
		});
	});
})(jQuery);
</script>
