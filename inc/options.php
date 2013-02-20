<?php
/*
 * Book It! Transportation 1.0.3
 * http://www.benmarshall.me/book-it-transportation/
 */
$plugin = get_plugin_data( str_replace('inc/','',plugin_dir_path( __FILE__)).'bookitportation.php');
$changelog = trim(str_replace('== Changelog ==','',file_get_contents(str_replace('inc/','',plugin_dir_path( __FILE__)).'readme.txt')));
?>
<div class="wrap">
  <?php screen_icon(); ?>
  <form action="options.php" method="post" id="bookit_options_form" name="bookit_options_form">
  <?php settings_fields('bookit_options'); ?>
  <div style="float: right;margin-top:10px"><em><?php echo __('Something not work right? Have a feature request?') ?></em> <a href="http://www.benmarshall.me/bugs/" target="_blank" class="button button-primary"><?php echo __('Report a Bug', 'bookit') ?></a></div>
  <h2><?php echo __('Book It! Transportation') ?> &raquo; Settings</h2>
  <hr>
  <h3 class="title"><?php echo __('Reservation Settings') ?></h3>
  <table class="form-table">
    <tr valign="top">
      <th scope="row">
        <label for="bookit_reservation_received_url"><?php echo __('Reservation Received URL') ?></label>
      </th>
      <td>
        <input name="bookit_reservation_received_url" type="text" id="bookit_reservation_received_url" value="<?php echo get_option('bookit_reservation_received_url'); ?>" class="regular-text">
        <p class="description"><?php echo __('The URL the user is directed to after they\'ve submitted the reservation form (e.g. thank you page, confirmation pending page, conversion page, etc.)') ?></p>
      </td>
    </tr>
    <tr valign="top">
      <th scope="row">
        <label for="bookit_default_reservation_status"><?php echo __('Default Reservation Status') ?></label>
      </th>
      <td>
        <select name="bookit_default_reservation_status" id="bookit_default_reservation_status">
          <option value="confirmed"<? if(get_option('bookit_default_reservation_status') == 'confirmed'): ?>selected="selected"<? endif; ?>><?php echo __('Confirmed') ?></option>
          <option value="pending-review"<? if(get_option('bookit_default_reservation_status') == 'pending-review'): ?>selected="selected"<? endif; ?>><?php echo __('Pending Review') ?></option>
        </select>
        <p class="description"><?php echo __('Select the default status for new reservations.') ?></p>
      </td>
    </tr>
  </table>
  <hr>
  <h3 class="title"><?php echo __('Email Settings') ?></h3>
  <div style="float:left;width:60%;">
    <table class="form-table">
      <tr valign="top">
        <th scope="row">
          <label for="bookit_reservation_email_subject"><?php echo __('New Reservation Subject') ?></label>
        </th>
        <td>
          <input name="bookit_reservation_email_subject" type="text" id="bookit_reservation_email_subject" value="<?php echo get_option('bookit_reservation_email_subject'); ?>" class="regular-text">
          <p class="description"><?php echo __('The subject of the email that get\'s sent for new reservation bookings.') ?></p>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row">
          <label for="bookit_reservation_email_template"><?php echo __('Reservation Confirmed Body') ?></label>
        </th>
        <td>
          <textarea name="bookit_reservation_email_template" id="bookit_reservation_email_template" rows="10" class="large-text code"><?php echo get_option('bookit_reservation_email_template'); ?></textarea>
          <p class="description"><?php echo __('This is what will appear in the email that get\'s sent for new reservations.') ?></p>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row">
          <label for="bookit_confirmation_email_subject"><?php echo __('Reservation Confirmed Subject') ?></label>
        </th>
        <td>
          <input name="bookit_confirmation_email_subject" type="text" id="bookit_confirmation_email_subject" value="<?php echo get_option('bookit_confirmation_email_subject'); ?>" class="regular-text">
          <p class="description"><?php echo __('The subject of the email that get\'s sent for reservation confirmations.') ?></p>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row">
          <label for="bookit_confirmation_email_template"><?php echo __('Reservation Confirmed Body') ?></label>
        </th>
        <td>
          <textarea name="bookit_confirmation_email_template" id="bookit_confirmation_email_template" rows="10" class="large-text code"><?php echo get_option('bookit_confirmation_email_template'); ?></textarea>
          <p class="description"><?php echo __('This is what will appear in the email that get\'s sent for confirmations.') ?></p>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row">
          <label for="bookit_confirmation_email_subject"><?php echo __('Reservation Outsource Subject') ?></label>
        </th>
        <td>
          <input name="bookit_outsource_reservation_email_subject" type="text" id="bookit_outsource_reservation_email_subject" value="<?php echo get_option('bookit_outsource_reservation_email_subject'); ?>" class="regular-text">
          <p class="description"><?php echo __('The subject of the email that get\'s sent for reservations sent to outsource companies.') ?></p>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row">
          <label for="bookit_outsource_reservation_email_template"><?php echo __('Reservation Outsource Body') ?></label>
        </th>
        <td>
          <textarea name="bookit_outsource_reservation_email_template" id="bookit_outsource_reservation_email_template" rows="10" class="large-text code"><?php echo get_option('bookit_outsource_reservation_email_template'); ?></textarea>
          <p class="description"><?php echo __('This is what will appear in the email that get\'s sent to the reservation\'s booked outsource company.', 'bookit') ?></p>
        </td>
      </tr>
    </table>
  </div>
  <div style="float:right; width:40%;">
    <div class="metabox-holder">
      <div class="postbox">
        <h3 class="hndle"><?php echo __('Available Shortcodes', 'bookit') ?></h3>
        <div class="inside">
          <table>
            <tbody>
              <tr>
                <td><code>[[TITLE]]</code></td><td><em><?php echo __('the reservation confirmation code', 'bookit') ?></em></td>
              </tr>
              <tr>
                <td><code>[[MONTH]]</code></td><td><em><?php echo __('the month (number) the reservation is booked for', 'bookit') ?></em></td>
              </tr>
              <tr>
                <td><code>[[DATE]]</code></td><td><em><?php echo __('the date the reservation is booked for', 'bookit') ?></em></td>
              </tr>
              <tr>
                <td><code>[[YEAR]]</code></td><td><em><?php echo __('the year the reservation is booked for', 'bookit') ?></em></td>
              </tr>
              <tr>
                <td><code>[[TIME]]</code></td><td><em><?php echo __('the time the reservation is booked for', 'bookit') ?></em></td>
              </tr>
              <tr>
                <td><code>[[CONTACT_NAME]]</code></td><td><em><?php echo __('the reservation\'s contact name', 'bookit') ?></em></td>
              </tr>
              <tr>
                <td><code>[[CONTACT_PHONE]]</code></td><td><em><?php echo __('the reservation\'s contact phone number', 'bookit') ?></em></td>
              </tr>
              <tr>
                <td><code>[[CONTACT_EMAIL]]</code></td><td><em><?php echo __('the reservation\'s contact email address', 'bookit') ?></em></td>
              </tr>
              <tr>
                <td><code>[[PICKUP]]</code></td><td><em><?php echo __('the pickup location for the reservation', 'bookit') ?></em></td>
              </tr>
              <tr>
                <td><code>[[DESTINATIONS]]</code></td><td><em><?php echo __('the destination(s) location for the reservation', 'bookit') ?></em></td>
              </tr>
              <tr>
                <td><code>[[VEHICLE]]</code></td><td><em><?php echo __('the vehicle booked for the reservation', 'bookit') ?></em></td>
              </tr>
              <tr>
                <td><code>[[EVENT_TYPE]]</code></td><td><em><?php echo __('the event type for the reservation', 'bookit') ?></em></td>
              </tr>
              <tr>
                <td><code>[[NUM_PASSENGERS]]</code></td><td><em><?php echo __('the number of passengers booked for the reservation', 'bookit') ?></em></td>
              </tr>
              <tr>
                <td><code>[[NUM_HOURS]]</code></td><td><em><?php echo __('the number of hours booked for the reservation', 'bookit') ?></em></td>
              </tr>
              <tr>
                <td><code>[[INSTRUCTIONS]]</code></td><td><em><?php echo __('client instructions for the reservation', 'bookit') ?></em></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="clear"></div>
  <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo __('Save Changes') ?>"></p>
  </form>
  <div class="metabox-holder">
    <div class="postbox">
      <h3 class="hndle"><?php echo __('Book It! Transportation Details') ?></h3>
      <div class="inside">
        <div style="float: right;">
          <script type="text/javascript"><!--
          google_ad_client = "ca-pub-6102402008946964";
          /* Book It! Transportation Medium Rectangle */
          google_ad_slot = "3498616466";
          google_ad_width = 300;
          google_ad_height = 250;
          //-->
          </script>
          <script type="text/javascript"
          src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
          </script>
        </div>
        <b><?php echo __('Author:') ?></b> <a href="http://www.benmarshall.me/book-it-transportation/" target="_blank">Ben Marshall</a><br>
        <b><?php echo __('Version:') ?></b> <?php echo $plugin['Version'] ?><br>
        <b><?php echo __('Last Updated:') ?></b> <?php echo date("F d, Y g:i:sa", filemtime(__FILE__)) ?>
        <h4><?php echo __('Change Log') ?></h4>
        <?php echo nl2br($changelog) ?>
      </div>
    </div>
  </div>
</div>
