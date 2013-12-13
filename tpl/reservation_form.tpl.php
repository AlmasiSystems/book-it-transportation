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
      <label for="reservation-date"><?php _e( 'Reservation Date', 'bookit' ); ?></label>
      <input type="date" name="bookit_reservation_date" id="reservation-date" value="">
    </div>
    <div class="bookit-col">
      <label for="pickup-time"><?php _e( 'Pickup Time', 'bookit' ); ?></label>
      <input type="time" name="bookit_pickup_time" id="pickup-time" value="">
    </div>
    <div class="bookit-col">
      <label for="reservation-hours"><?php _e( 'Booked For', 'bookit' ); ?> (<?php echo __('hours', 'bookit') ?>)</label>
      <input type="number" min="1" name="bookit_reservation_hours" id="reservation-hours" value="" >
    </div>
    <div class="bookit-col">
      <input type="checkbox" name="bookit_is_roundtrip" value="1" id="is-roundtrip"> <label for="is-roundtrip"><?php echo __('Roundtrip', 'bookit') ?></label>
    </div>
  </div>
  <div class="bookit-row">
    <div class="bookit-col">
      <label for="event-type"><?php _e( 'Event Type', 'bookit' ); ?></label>
      <select name="tax_input[bookit_event_type][]" id="event-type">
        <?php if( count($event_types) > 0): foreach ( $event_types as $key => $obj ): ?>
        <option value="<?php echo $obj->name ?>"><?php echo $obj->name ?></option>
        <?php endforeach; endif; ?>
      </select>
    </div>
    <div class="bookit-col">
      <label for="vechicle"><?php _e( 'Preferred Vehicle', 'bookit' ); ?></label>
      <select name="tax_input[bookit_vehicle][]" id="event-type">
        <?php if( count($vehicles) > 0): foreach ( $vehicles as $key => $obj ): ?>
        <option value="<?php echo $obj->name ?>"><?php echo $obj->name ?></option>
        <?php endforeach; endif; ?>
      </select>
    </div>
  </div>
</div>
