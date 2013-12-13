<?php
/**
 * @package Book_It
 * @version 2.0
 */

// Shortcode replacements
function bookit_shortcodes($string, $post_id = false) {
  $options = bookit_get_options();

  $string = str_replace(array(
      '[[SITE_NAME]]'
    ), array(
      get_bloginfo( 'name' )
    ), $string);

  if ( $post_id ) {
    // Get reservation status
    $terms = get_the_terms( $post_id, 'bookit_reservation_status', '', '', '' );
    $term_args = array(
      'hide_empty' => false,
      'orderby' => 'name',
      'order' => 'ASC'
    );
    $status = $terms[0]->name;

    $string = str_replace(array(
      '[[CONFIRMATION_CODE]]',
      '[[RESERVATION_DATE]]',
      '[[DATE_RESERVED]]',
      '[[STATUS]]',
      '[[PRIMARY_PASSENGER]]'
    ), array(
      get_the_title($post_id),
      date($options['full_date_format'], strtotime(get_post_meta($post_id, 'bookit_reservation_date', true) . " ". get_post_meta($post_id, 'bookit_pickup_time', true))),
      get_post_time( $options['full_date_format'], false, $post_id ),
      $status,
      get_post_meta($post_id, 'bookit_primary_passenger', true)
    ), $string);
  }

  return $string;
}

// Book It! Shortcodes
function bookit_shortcode_reservation_form( $atts ){
  ob_start();
  require_once( BOOKIT_ROOT . '/tpl/reservation_form.tpl.php' );
  $form = ob_get_contents();
  ob_end_clean();
  return $form;
}
add_shortcode( 'bookit_reservation_form', 'bookit_shortcode_reservation_form' );
