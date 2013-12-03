<?php
/**
 * @package Book_It
 * @version 2.0
 */
?>
<div id="notificationOptions">
	<div class="misc-pub-section">
		<p><?php echo __('Send reservation emails below. Be sure to save the reservation if changes are made below sending any emails.', 'bookit') ?></p>
	</div>
	<p class="text-center" id="notificationBtns">
		<a href="#" class="button" id="sendReceived"><?php echo __('Received', 'bookit') ?></a>
		<a href="#" class="button"><?php echo __('Confirmed', 'bookit') ?></a>
		<a href="#" class="button" id="sendDetails"><?php echo __('Email Details', 'bookit') ?></a>
	</p>
	<div id="emailDetails" style="display: none">
		<label for="recipient">
			<b><?php echo __('Send to', 'bookit') ?>:</b>
			<input type="text" id="recipient">
		</label>
		<p class="description"><?php echo __('Start typing an email address or outsource company to send the reservation details to.', 'bookit') ?></p>

		<?php if ( _bookit() ): $options = bookit_get_options(); ?>
		<label for="template">
			<b><?php echo __('Template', 'bookit') ?>:</b>
			<select id="template">
				<?php
				if ( !is_array($options['email_templates']) ):
					while ( !is_array($options['email_templates']) ):
						$options['email_templates'] = unserialize($options['email_templates']);
					endwhile;
					foreach ( $options['email_templates'] as $key => $val ):
					?>
					<option value="<?php echo $key ?>"><?php echo $val['name'] ?></option>
					<? endforeach;
				endif; ?>
			</select>
			<p class="description"><?php echo __('Select a email template to use.', 'bookit') ?></p>
		</label>
	<?php endif; ?>

		<a href="#" class="button" id="sendEmail"><?php echo __('Send') ?></a>
	</div>
</div>

<script>
(function($) {
	$(function() {
		$('#sendDetails').bind('click', function(e) {
			e.preventDefault();
			$('#emailDetails').slideToggle();
		});

		$('#sendReceived').bind('click', function(e) {
			e.preventDefault();
			var msg = $('<div />').attr('id', 'notificationM').addClass('updated').html('Sending, please wait&hellip;');;
			$('#notificationBtns .button').addClass('button-disabled');
			$('#notificationOptions').before(msg);

			var data = {
	      bookit_action: 'send_new_reservation_email',
	      ID: <?php echo get_the_ID(); ?>
	    };
			$.post(ajaxurl, data, function( response ) {
				var result = $.parseJSON( response );
				$.each( result, function ( index, value ) {
					$('#notificationM').html( value + '<br>' );
				});
	      setTimeout(function() {
	      	$('#notificationM').fadeOut(function() {
	      		$('#notificationBtns .button').removeClass('button-disabled');
	      		$(this).remove();
	      	});
	      }, 3000);
	    });
		});
	});
})(jQuery);
</script>
