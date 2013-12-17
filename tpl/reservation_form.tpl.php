<?php
/**
 * @package Book_It
 * @version 2.0
 */

// Get Event Types
$event_terms = get_the_terms( $ID, 'bookit_event_type', '', '', '' );
$event_term_args = array( 'hide_empty' => false, 'orderby' => 'name', 'order' => 'ASC' );
$event_types = get_terms('bookit_event_type', $event_term_args);

// Get Vehicle Types
$vehicle_terms = get_the_terms( $ID, 'bookit_vehicle', '', '', '' );
$vehicle_term_args = array( 'hide_empty' => false, 'orderby' => 'name', 'order' => 'ASC' );
$vehicles = get_terms('bookit_vehicle', $vehicle_term_args);

?>
<div class="bookit-reservation-form">
  <div class="bookit-row">
    <div class="bookit-col">
      <label for="reservation-date" class="bookit-label"><?php _e( 'Reservation Date', 'bookit' ); ?></label>
      <input type="date" name="bookit_reservation_date" id="reservation-date" value="" class="bookit-input">
    </div>
    <div class="bookit-col">
      <label for="pickup-time" class="bookit-label"><?php _e( 'Pickup Time', 'bookit' ); ?></label>
      <input type="time" name="bookit_pickup_time" id="pickup-time" value="" class="bookit-input">
    </div>
    <div class="bookit-col">
      <label for="reservation-hours" class="bookit-label"><?php _e( 'Booked For', 'bookit' ); ?> (<?php echo __('hours', 'bookit') ?>)</label>
      <input type="number" min="1" name="bookit_reservation_hours" id="reservation-hours" value="" class="bookit-input">
    </div>
    <div class="bookit-col">
      <input type="checkbox" name="bookit_is_roundtrip" value="1" id="is-roundtrip"> <label for="is-roundtrip" class="bookit-label-checkbox"><?php echo __('Roundtrip', 'bookit') ?></label>
    </div>
  </div>
  <div class="bookit-row">
    <div class="bookit-col">
      <label for="reservation-num-passengers" class="bookit-label"><?php _e( '# of Passengers', 'bookit' ); ?></label>
      <input type="number" min="1" name="bookit_num_passengers" id="reservation-num-passengers" value="" class="bookit-input">
    </div>
    <div class="bookit-col">
      <label for="event-type" class="bookit-label"><?php _e( 'Event Type', 'bookit' ); ?></label>
      <select name="tax_input[bookit_event_type][]" id="event-type" class="bookit-select">
        <?php if( count($event_types) > 0): foreach ( $event_types as $key => $obj ): ?>
        <option value="<?php echo $obj->name ?>"><?php echo $obj->name ?></option>
        <?php endforeach; endif; ?>
      </select>
    </div>
    <div class="bookit-col">
      <label for="vechicle" class="bookit-label"><?php _e( 'Preferred Vehicle', 'bookit' ); ?></label>
      <select name="tax_input[bookit_vehicle][]" id="event-type" class="bookit-select">
        <?php if( count($vehicles) > 0): foreach ( $vehicles as $key => $obj ): ?>
        <option value="<?php echo $obj->name ?>"><?php echo $obj->name ?></option>
        <?php endforeach; endif; ?>
      </select>
    </div>
  </div>

  <hr class="bookit-divider">

  <div class="bookit-row">
    <div class="bookit-col">
      <label for="primary-passenger" class="bookit-label"><?php _e( 'Contact Name', 'bookit' ); ?></label>
      <input type="text" name="bookit_primary_passenger" id="primary-passenger" value="" class="bookit-input">
    </div>
    <div class="bookit-col">
      <label for="contact-phone" class="bookit-label"><?php _e( 'Phone Number', 'bookit' ); ?></label>
      <input type="tel" name="bookit_contact_phone" id="contact-phone" value="" class="bookit-input">
    </div>
    <div class="bookit-col">
      <label for="contact-email" class="bookit-label"><?php _e( 'Email Address', 'bookit' ); ?></label>
      <input type="email" name="bookit_contact_email" id="contact-email" value="" class="bookit-input">
    </div>
  </div>

  <hr class="bookit-divider">

  <div class="bookit-row">
    <div class="bookit-col">
      <label for="pickup-location" class="bookit-label"><?php _e( 'Pickup Address', 'bookit' ); ?></label>
      <input type="text" name="bookit_pickup_location" id="pickup-location" value="" class="bookit-input">
    </div>
    <div class="bookit-col">
      <label for="destination-1" class="bookit-label"><?php _e( 'Destination Address', 'bookit' ); ?></label>
      <input type="text" name="bookit_destination[]" id="destination-1" value="" class="bookit-input">
    </div>
  </div>
</div>
