<?php
/*
Plugin Name: Book It! Transportation
Plugin URI: http://www.benmarshall.me/book-it-transportation/
Description: A complete management system for your transportation business enabling you to easily accept and manage your transportation bookings.
Version: 1.0.4
Author: Ben Marshall
Author URI: http://www.benmarshall.me
*/
include( plugin_dir_path( __FILE__ ) . 'config.php' );

function bookit_add_settings() {
  register_setting( 'bookit_options', 'bookit_reservation_received_url', 'bookit_isValidURL' );
  register_setting( 'bookit_options', 'bookit_default_reservation_status' );
  register_setting( 'bookit_options', 'bookit_confirmation_email_subject', 'bookit_emailSubject' );
  register_setting( 'bookit_options', 'bookit_reservation_email_subject', 'bookit_emailSubject' );
  register_setting( 'bookit_options', 'bookit_outsource_reservation_email_subject', 'bookit_emailSubject' );
  register_setting( 'bookit_options', 'bookit_confirmation_email_template' );
  register_setting( 'bookit_options', 'bookit_reservation_email_template' );
  register_setting( 'bookit_options', 'bookit_outsource_reservation_email_template' );
}

add_action( 'admin_init', 'bookit_admin' );
function bookit_admin() {
  global $pagenow;
  add_meta_box( 'reservation_details', 'Reservation Details', 'display_resevation_details', 'bookit_reservation', 'normal', 'core' );
  if( $pagenow == 'post.php' ) {
    add_meta_box( 'reservation_notification_options', 'Notification Options', 'display_notification_options', 'bookit_reservation', 'side', 'core' );
  }
  bookit_add_settings();
}

add_action( 'init', 'bookit_init' );
function bookit_init() {
  global $bookit_config;
  if( ! get_option( 'bookit_default_reservation_status' ) ) {
     update_option( 'bookit_default_reservation_status', 'pending-review' );
  }
  if( ! get_option( 'bookit_confirmation_email_subject' ) ) {
     update_option( 'bookit_confirmation_email_subject', 'Your reservation has beed confirmed' );
  }
  if( ! get_option( 'bookit_reservation_email_subject' ) ) {
     update_option( 'bookit_reservation_email_subject', 'We\'ve received your reservation request' );
  }

  bookit_add_post_types();
  bookit_add_categories();
  bookit_process_post();
}

function bookit_add_post_types() {
  global $bookit_config;
  foreach( $bookit_config['post_types'] as $key => $value ) {
    register_post_type( $key , $value['args'] );
  }
}

function bookit_add_categories() {
  global $bookit_config;
  foreach( $bookit_config['categories'] as $key => $value ) {
    register_taxonomy( $key, $value['post_types'], array(
      'hierarchical'      => true,
      'labels'            => $value['labels'],
      'show_ui'           => true,
      'show_admin_column' => true,
      'query_var'         => false,
      'rewrite'           => false
    ) );
  }
}

function bookit_process_post() {
  if( $_POST ) {
    if( isset( $_POST['bookit_action'] ) ) {
      switch( $_POST['bookit_action'] ) {
        case 'send_reservation_received':
          if( isset( $_POST['ID'] ) ) {
            if( email_reservation_received( $_POST['ID'] ) ) {
              echo __( 'Email successfully sent.', 'bookit' );
            } else {
              echo __( 'There was a problem sending the email.', 'bookit' );
            }
          }
          die();
          break;
       case 'send_reservation_confirmed':
          if( isset( $_POST['ID'] ) ) {
            if( email_reservation_confirmed( $_POST['ID'] ) ) {
              echo __( 'Email successfully sent.', 'bookit' );
            } else {
              echo __( 'There was a problem sending the email.', 'bookit' );
            }
          }
          die();
          break;
        case 'send_reservation_outsource':
          if( isset( $_POST['ID'] ) ) {
            echo email_reservation_outsource( $_POST['ID'] );
          }
          die();
          break;
        case 'bookit-reservation':
          bookit_add_reservation();
          break;
      }
    }
  }
}
function bookit_add_reservation() {
  global $bookit_config;
  $page = $_POST['page'];
  $errors = array();
  foreach( $bookit_config['required_fields'] as $key => $value ) {
    if( ! isset( $_POST[$value] ) || isset( $_POST[$value] ) && ! $_POST[ $value ]) {
      $errors[] = $value;
    }
  }
  if( count( $errors ) > 0 ) {
    $_SESSION['bookit']['post']   = $_POST;
    $_SESSION['bookit']['errors'] = $errors;
    wp_redirect( $page );
    exit;
  } else {
    $post = array(
      'comment_status' => 'closed',
      'ping_status'    => 'closed',
      'post_author'    => get_current_user_id(),
      'post_status'    => $bookit_config['reservation-status'],
      'post_title'     => bookit_randString(),
      'post_type'      => 'bookit_reservation'
    );

    $post_id = wp_insert_post( $post );
    if( $post_id ) {
      wp_set_object_terms( $post_id, $_POST['vehicle'], 'vehicle', true );
      wp_set_object_terms( $post_id, $_POST['destinations'], 'destinations', true );
      wp_set_object_terms( $post_id, $_POST['pickup'], 'pickup', true );
      wp_set_object_terms( $post_id, $_POST['event_type'], 'event_type', true );

      $post = get_post( $post_id );
      $meta = get_post_custom( $post_id );
      $categories = array();
      foreach( $bookit_config['categories'] as $key => $value ) {
        $category         = wp_get_post_terms( $post_id, $key );
        $categories[$key] = $category;
      }
      $ary = array(
        'title' => $post->post_title
      );
      foreach( $bookit_config['fields'] as $key => $array ) {
        $ary[$array['key']] = $meta[ $array['key']][0];
      }
      foreach( $categories as $tag => $array ) {
        foreach( $array as $k => $v ) {
          if( isset( $ary[$tag]) ) $ary[$tag] .= ', ';
          $ary[$tag] .= $v->name;
        }
      }

      $headers[] = 'From: '.get_bloginfo('admin_name').' <'.get_bloginfo( 'admin_email' ).'>';
      $headers[] = 'Bcc: '.get_bloginfo('admin_name').' <'.get_bloginfo( 'admin_email' ).'>';
      add_filter( 'wp_mail_content_type',create_function( '', 'return "text/html";' ) );
      wp_mail( $_POST['contact_email'], $bookit_config['emails']['reservation_email_subject'], bookit_tags( $bookit_config['emails']['reservation_email_template'], $ary ), $headers );

      wp_redirect( $bookit_config['reservation-received-url'] );
      exit;
    }
  }
}
// Send the reservation received email
function email_reservation_received( $ID ) {
  global $bookit_config;
  $post       = get_post( $ID );
  $meta       = get_post_custom( $ID );
  $user_name  = $meta['contact_name'][0];
  $user_email = $meta['contact_email'][0];
  $subject    = $bookit_config['emails']['reservation_email_subject'];

  $categories = array();
  foreach( $bookit_config['categories'] as $key => $value ) {
    $category = wp_get_post_terms( $ID, $key );
    $categories[$key] = $category;
  }

  $ary = array(
    'title' => $post->post_title
  );
  foreach( $bookit_config['fields'] as $key => $array ) {
    $ary[$array['key']] = $meta[$array['key']][0];
  }
  foreach( $categories as $tag => $array ) {
    foreach( $array as $k => $v ) {
      if( isset( $ary[$tag] ))  $ary[$tag] .= ', ';
      $ary[$tag] .= $v->name;
    }
  }

  $headers[] = 'From: '.get_bloginfo( 'admin_name' ).' <' . get_bloginfo( 'admin_email' ) . '>';
  $headers[] = 'Bcc: '.get_bloginfo('admin_name').' <'.get_bloginfo('admin_email').'>';
  add_filter('wp_mail_content_type',create_function('', 'return "text/html";'));
  wp_mail( $user_email, $bookit_config['emails']['reservation_email_subject'], bookit_tags($bookit_config['emails']['reservation_email_template'],$ary), $headers );
  return true;
}
function email_reservation_outsource( $ID ) {
  global $bookit_config;
  $post = get_post($ID);
  $meta = get_post_custom($ID);
  $outsource = get_the_terms( $ID, 'outsource_companies', '', '', '' );
  if ($outsource) {
    $user_name = $outsource[0]->name;
    $user_email = get_option("_term_type_outsource_companies_".$outsource[0]->term_id);
    $subject = $bookit_config['emails']['outsource_reservation_email_subject'];
    $categories = array();
    foreach($bookit_config['categories'] as $key=>$value) {
      $category = wp_get_post_terms( $ID, $key );
      $categories[$key] = $category;
    }

    $ary = array(
      'title' => $post->post_title
    );
    foreach($bookit_config['fields'] as $key=>$array) {
      $ary[$array['key']] = $meta[$array['key']][0];
    }
    foreach($categories as $tag=>$array) {
      foreach($array as $k=>$v) {
        if(isset($ary[$tag])) $ary[$tag] .= ', ';
        $ary[$tag] = $v->name;
      }
    }

    $headers[] = 'From: '.get_bloginfo('admin_name').' <'.get_bloginfo('admin_email').'>';
    $headers[] = 'Bcc: '.get_bloginfo('admin_name').' <'.get_bloginfo('admin_email').'>';
    add_filter('wp_mail_content_type',create_function('', 'return "text/html";'));
    wp_mail( $user_name.' <'.$user_email.'>', $bookit_config['emails']['outsource_reservation_email_subject'], bookit_tags($bookit_config['emails']['outsource_reservation_email_template'],$ary), $headers );
    return __('Email successfully sent.', 'bookit');
  } else {
    return __('<b>This reservation isn\'t currently assiged to an outsource company.</b> Be sure an outsource company is selected and the reservation has been saved.', 'bookit');
  }
}
function email_reservation_confirmed($ID) {
  global $bookit_config;
  $post = get_post($ID);
  $meta = get_post_custom($ID);
  $user_name = $meta['contact_name'][0];
  $user_email = $meta['contact_email'][0];
  $subject = $bookit_config['emails']['reservation_email_subject'];

  $categories = array();
  foreach($bookit_config['categories'] as $key=>$value) {
    $category = wp_get_post_terms( $ID, $key );
    $categories[$key] = $category;
  }

  $ary = array(
    'title' => $post->post_title
  );
  foreach($bookit_config['fields'] as $key=>$array) {
    $ary[$array['key']] = $meta[$array['key']][0];
  }
  foreach($categories as $tag=>$array) {
    foreach($array as $k=>$v) {
      if(isset($ary[$tag])) $ary[$tag] .= ', ';
      $ary[$tag] = $v->name;
    }
  }

  $headers[] = 'From: '.get_bloginfo('admin_name').' <'.get_bloginfo('admin_email').'>';
  $headers[] = 'Bcc: '.get_bloginfo('admin_name').' <'.get_bloginfo('admin_email').'>';
  add_filter('wp_mail_content_type',create_function('', 'return "text/html";'));
  wp_mail( $user_email, $bookit_config['emails']['reservation_confirmation_email_subject'], bookit_tags($bookit_config['emails']['reservation_confirmation_email_confirmed_template'],$ary), $headers );
  return true;
}
// Rewrites email template tags
function bookit_tags($html,$ary) {
  $find = array();
  $replace = array();
  foreach($ary as $key=>$value) {
    $find[] = '[['.strtoupper($key).']]';
    $replace[] = $value;
  }
  if(isset($ary['month']) && isset($ary['date']) && isset($ary['year'])) {
    $find[] = '[[RESERVATION_DATE_FULLTEXT]]';
    $replace[] = date('l, F jS, Y g:ia',strtotime($ary['month'].'-'.$ary['date'].'-'.$ary['year'].' '.$ary['time']));
  }
  $html = str_replace($find,$replace,$html);
  return $html;
}

function bookit_emailSubject($value) {
  if(strlen($value) > 80) {
    add_settings_error(
      'bookit_confirmation_email_subject',
      'bookit_confirmation_email_subject_error',
      'To help avoid spam filters, avoid a email subject with more than 80 characters.',
      'error'
    );
  }
  return $value;
}
function bookit_isValidURL($value) {
  $response = wp_remote_get( esc_url_raw( $value ) );
  if (is_wp_error( $response ) ) {
    add_settings_error(
      'bookit_reservation_received_url',
      'bookit_reservation_received_url_error',
      'Please enter a valid, working URL.',
      'error'
    );
  }
  return $value;
}

function display_resevation_details($object) {
  global $bookit_config;
  ?>
  <?php
  wp_nonce_field( basename( __FILE__ ), 'bookit_nonce' );
  $outsource = get_the_terms( $ID, 'outsource_companies', '', '', '' );
  $term_args=array(
    'hide_empty' => false,
    'orderby' => 'name',
    'order' => 'ASC'
  );
  $terms = get_terms('outsource_companies',$term_args);
  ?>
  <table class="form-table">
    <tbody>
      <tr>
        <td valign="top"><label for="outsource_company"><?php echo __('Outsource Company', 'bookit') ?></label></td>
        <td valign="top">
          <select id="outsource_company" name="tax_input[outsource_companies][]">
            <option value=""><?php echo __('In-House (not outsourced)', 'bookit') ?></option>
            <?php foreach ($terms as $tag) { ?>
            <option value="<?php echo $tag->term_id ?>" <?php if ($outsource[0]->term_id === $tag->term_id): ?>selected="selected"<?php endif; ?>><?php echo $tag->name ?></option>
            <?php } ?>
        </select>
        </td>
      </tr>
    <? foreach($bookit_config['fields'] as $key=>$value): ?>
      <tr>
        <td valign="top"><label for="<?=$value['key'] ?>"><?php _e( $value['name'], 'bookit' ); ?></label></td>
        <td valign="top">
          <?
          if($value['type'] === 'select') {
            ?>
            <select name="<?=$value['key'] ?>" id="<?=$value['key'] ?>">
              <? foreach($value['options'] as $k=>$v): ?>
              <option value="<?=$k ?>" <? if(get_post_meta( $object->ID, $value['key'], true ) == $k): ?>selected="selected"<? endif; ?>><?=$v ?></option>
              <? endforeach; ?>
            </select>
            <?
          } elseif($value['type'] === 'text' || $value['type'] === 'number'  || $value['type'] === 'tel' || $value['type'] === 'email') {
            ?>
            <input class="<?=$value['class'] ?>" type="<?=$value['type'] ?>" name="<?=$value['key'] ?>" id="<?=$value['key'] ?>" value="<?php echo esc_attr( get_post_meta( $object->ID, $value['key'], true ) ); ?>" <? if($value['type'] === 'number'): ?>min="0"<? endif; ?>>
            <?
          } elseif($value['type'] === 'textarea') {
            ?>
            <textarea name="<?=$value['key'] ?>" id="<?=$value['key'] ?>" cols="50" rows="5" class="<?=$value['class'] ?>"><?php echo esc_attr( get_post_meta( $object->ID, $value['key'], true ) ); ?></textarea>
            <?
          }
          ?>
        </td>
      </tr>
      <? endforeach; ?>
    </tbody>
  </table>
  <?
}
function display_notification_options() {
  ?>
  <p><?=__('Use the buttons below to send notification emails regarding this reservation.') ?> <span class="description"><b><?=__('Remember to save the reservation before sending notification emails.') ?></b></span></p>
  <div id="email_status"></div>
  <div id="notification_options">
    <hr>
    <a href="#" class="button" id="send_reservation_received"><?=__('Email Reservation Received') ?></a>
    <p class="description"><?=__('Sends the user and admin an email stating the reservation has been received.') ?></p>
    <hr>
    <a href="#" class="button" id="send_reservation_confirmed"><?=__('Email Reservation Confirmed') ?></a>
    <p class="description"><?=__('Sends the user and admin an email stating the reservation has been confirmed.') ?></p>
    <hr>
    <a href="#" class="button" id="send_reservation_outsource"><?=__('Email Outsource Reservation') ?></a>
    <p class="description"><?=__('Sends the selected outsource company a email containing the reservationd details.') ?></p>
  </div>
  <script>
  jQuery(function() {
    jQuery('#send_reservation_received').live('click',function(e) {
      e.preventDefault();
      jQuery('#notification_options .button').addClass('button-disabled');
      jQuery('#email_status').html('<?=__('<div class="updated"><p>Sending, please wait&hellip;</p></div>') ?>');

      var data = {
        bookit_action: 'send_reservation_received',
        ID: <?=get_the_ID(); ?>
      };
      jQuery.post(ajaxurl, data, function(response) {
        jQuery('#email_status').html('<div class="updated"><p>' + response + '</p></div>');
        jQuery('#notification_options .button').removeClass('button-disabled');
      });
    });

    jQuery('#send_reservation_confirmed').live('click',function(e) {
      e.preventDefault();
      jQuery('#notification_options .button').addClass('button-disabled');
      jQuery('#email_status').html('<?=__('<div class="updated"><p>Sending, please wait&hellip;</p></div>') ?>');

      var data = {
        bookit_action: 'send_reservation_confirmed',
        ID: <?=get_the_ID(); ?>
      };
      jQuery.post(ajaxurl, data, function(response) {
        jQuery('#email_status').html('<div class="updated"><p>' + response + '</p></div>');
        jQuery('#notification_options .button').removeClass('button-disabled');
      });
    });

    jQuery('#send_reservation_outsource').live('click',function(e) {
      e.preventDefault();
      jQuery('#notification_options .button').addClass('button-disabled');
      jQuery('#email_status').html('<?=__( '<div class="updated"><p>Sending, please wait&hellip;</p></div>', 'bookit') ?>');

      var data = {
        bookit_action: 'send_reservation_outsource',
        ID: <?=get_the_ID(); ?>
      };
      jQuery.post(ajaxurl, data, function(response) {
        jQuery('#email_status').html('<div class="updated"><p>' + response + '</p></div>');
        jQuery('#notification_options .button').removeClass('button-disabled');
      });
    });
  });
  </script>
  <?
}
add_action( 'save_post', 'bookit_save_reservation', 10, 2 );
function bookit_save_reservation($post_id, $reservation_details) {
  global $bookit_config;
  if ( $reservation_details->post_type == 'bookit_reservation' ) {
    foreach($bookit_config['fields'] as $key=>$value) {

      $field = ( isset( $_POST[$value['key']] ) ? $_POST[$value['key']] : $value['default'] );
      $current_field = get_post_meta( $reservation_details->ID, $value['key'], true );
      if ( $field && '' == $current_field ) {
        add_post_meta( $reservation_details->ID, $value['key'], $field, true );
      } elseif ( $field && $field != $current_field ) {
        update_post_meta( $reservation_details->ID, $value['key'], $field );
      } elseif ( '' == $field && $current_field ) {
        delete_post_meta( $reservation_details->ID, $value['key'], $field );
      }
    }
  }
}

// Add filters
add_filter( 'enter_title_here', 'bookit_change_enter_title_text', 10, 2 );
function bookit_change_enter_title_text( $text, $post ) {
  if( $post->post_type == 'bookit_reservation') {
    return __( 'Enter the reservation confirmation code', 'bookit');
  } else {
    return $text;
  }
}
add_filter( 'gettext', 'change_publish_button', 10, 2 );
function change_publish_button( $translation, $text ) {
  if( 'bookit_reservation' == get_post_type())
    if ( $text == 'Publish' ) {
      return 'Save Reservation';
    }
  return $translation;
}
add_shortcode( 'bookit_reservation_form', 'bookit_shortcode_reservation_form' );
function bookit_shortcode_reservation_form( $atts ) {
  global $bookit_config, $post;
  $current_user = wp_get_current_user();
  extract( shortcode_atts( array(
  ), $atts ) );
  $html = '<form id="bookit-reservation" name="bookit-reservation" method="post" action="'.get_permalink().'"><input type="hidden" name="bookit_action" value="bookit-reservation">'.wp_nonce_field( 'bookit_nonce' );
  $events = get_terms( 'event_type', array(
    'orderby'    => 'count',
    'hide_empty' => 0
  ) );
  $vehicles = get_terms( 'vehicle', array(
    'orderby'    => 'count',
    'hide_empty' => 0
  ) );
  ?>
  <? if(isset($_SESSION['bookit']['errors'])): ?>
  <div class="message-box-wrapper red">
    <div class="message-box-title"><?=__('Sorry, there was a problem processing your reservation, see below.') ?></div>
    <div class="message-box-content"><ul><?
    foreach($_SESSION['bookit']['errors'] as $key=>$value):
      if($value === 'contact_name'):
        echo '<li>'.__('Please enter your <strong>name</strong>.');
      elseif($value === 'contact_phone'):
        echo '<li>'.__('Please enter your <strong>phone number</strong>.');
      elseif($value === 'contact_email'):
        echo '<li>'.__('Please enter your <strong>email address</strong>.');
      elseif($value === 'num_passengers'):
        echo '<li>'.__('Please enter the <strong>number of passengers</strong>.');
      endif;
    endforeach;
    ?></ul></div>
  </div>
  <?  unset($_SESSION['bookit']['errors']); endif; ?>
  <?
  if(isset($_SESSION['bookit']['post'])) $bookit_config['post'] = $_SESSION['bookit']['post'];
  foreach($bookit_config['fields'] as $key=>$value) {
    ob_start();
    ?>
    <input type="hidden" name="page" value="<?=get_permalink() ?>">
    <div class="bookit-field <?=$value['key'] ?>">
      <div class="bookit-label"><label for="<?=$value['key'] ?>"><?=$value['name'] ?></label></div>
      <div class="bookit-input">
        <?
        if($value['type'] === 'text' || $value['type'] === 'number'  || $value['type'] === 'tel' || $value['type'] === 'email'): ?>
          <input type="<?=$value['type'] ?>" name="<?=$value['key'] ?>" id="<?=$value['key'] ?>" value="<?=stripslashes($bookit_config['post'][$value['key']]) ?>" placeholder="<?=$value['placeholder'] ?>" <? if($value['type'] === 'number'): ?>min="0"<? endif; ?>>
        <? elseif($value['type'] === 'textarea'): ?>
          <textarea name="<?=$value['key'] ?>" id="<?=$value['key'] ?>" cols="50" rows="5"><?=stripslashes($bookit_config['post'][$value['key']]) ?></textarea>
        <? elseif($value['type'] === 'select'): ?>
          <select name="<?=$value['key'] ?>" id="<?=$value['key'] ?>">
            <? foreach($value['options'] as $k=>$v): ?>
            <option value="<?=$k ?>" <? if(stripslashes($bookit_config['post'][$value['key']]) == $k): ?>selected="selected"<? endif; ?>><?=$v ?></option>
            <? endforeach; ?>
          </select>
        <? endif; ?>
      </div>
    </div>
    <?
    $html .= ob_get_contents();
    ob_end_clean();
  }
  ob_start();

  ?>
  <div class="bookit-field pickup">
    <div class="bookit-label"><label for="pickup"><?=_e('Where would you like to be picked up at?','bookit') ?></label></div>
    <div class="bookit-input">
      <input type="text" name="pickup[]" id="pickup" value="<?=stripslashes($bookit_config['post']['pickup'][0]) ?>" class="bookit-pickup" placeholder="<?=_e('Enter a address, business name or landmark','bookit') ?>">
    </div>
  </div>
  <div class="bookit-field destinations">
    <div class="bookit-label"><label for="destinations"><?=_e('Tell us where you\'d like to be taken:','bookit') ?></label></div>
    <div class="bookit-input" id="cell-destinations">
      <input type="text" name="destinations[]" class="bookit-destinations" value="<?=stripslashes($bookit_config['post']['destinations'][0]) ?>" placeholder="<?=_e('Enter a address, business name or landmark','bookit') ?>"> <a href="#" id="bookit_add">Add another destination &raquo;</a>
    </div>
    <script>
    jQuery(function() {
      jQuery('#bookit_add').live('click',function(e) {
        e.preventDefault();
        jQuery('#bookit_add').before(jQuery("#cell-destinations input").first().clone());
      });
    });
    </script>
  </div>
  <div class="bookit-field vehicle">
    <div class="bookit-label"><label for="vehicle"><?=_e('Select your perferred vehicle:','bookit') ?></label></div>
    <div class="bookit-input">
      <select name="vehicle" id="vehicle">
        <? foreach($vehicles as $key=>$value): ?>
        <option value="<?=$value->slug ?>" <? if(stripslashes($bookit_config['post']['vehicle']) == $value->slug): ?>selected="selected"<? endif; ?>><?=$value->name ?></option>
        <? endforeach; ?>
      </select>
    </div>
  </div>
  <div class="bookit-field event-type">
    <div class="bookit-label"><label for="event-type"><?=_e('Select a service type:','bookit') ?></label></div>
    <div class="bookit-input">
      <select name="event_type" id="event_type">
        <? foreach($events as $key=>$value): ?>
        <option value="<?=$value->slug ?>" <? if(stripslashes($bookit_config['post']['event_type']) == $value->slug): ?>selected="selected"<? endif; ?>><?=$value->name ?></option>
        <? endforeach; ?>
      </select>
    </div>
  </div>
  <?
  $html .= ob_get_contents();
  ob_end_clean();


  $html .= '<input type="submit" value="Submit Reservation" id="submit" name="submit"></form>';
  unset($_SESSION['bookit']['post']);
  return $html;
}
add_action('wp_enqueue_scripts', 'bookit_add_scipts');
function bookit_add_scipts() {
  wp_enqueue_script(
    'jquery_ui',
    plugins_url('/assets/js/jquery-ui-1.10.0.custom.min.js', __FILE__),
    array('jquery')
  );
  wp_enqueue_script(
    'bookit',
    plugins_url('/assets/js/script.js', __FILE__),
    array('jquery_ui')
  );
  wp_register_style( 'jquery_ui_benmarshall', plugins_url('/assets/css/benmarshall/jquery-ui-1.10.0.custom.min.css', __FILE__) );
  wp_enqueue_style( 'jquery_ui_benmarshall' );

  $categories = get_terms( 'destinations', array(
    'orderby'    => 'count',
    'hide_empty' => 0
   ) );
  wp_localize_script( 'bookit', 'bookit', $categories );
}

add_action('wp_logout', 'bookit_end_session');
add_action('wp_login', 'bookit_end_session');
function bookit_end_session() {
  unset($_SESSION['bookit']);
}

function bookit_randString($length=10, $charset='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789') {
  $str = '';
  $count = strlen($charset);
  while ($length--) {
    $str .= $charset[mt_rand(0, $count-1)];
  }
  return $str;
}


add_action( 'admin_menu', 'bookit_admin_menu' );
function bookit_admin_menu() {
  add_options_page( 'Book It! Transportation Settings', 'Book It! Transportation', 'manage_options', 'bookit', 'bookit_options' );
  remove_meta_box('outsource_companiesdiv', 'bookit_reservation', 'side');
}
function bookit_options() {
  if ( !current_user_can( 'manage_options' ) )  {
    wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
  }
  include( plugin_dir_path( __FILE__ ) . 'inc/options.php');
}

function get_taxonomy_term_type($taxonomy,$term_id) {
  return get_option("_term_type_{$taxonomy}_{$term->term_id}");
}
function update_taxonomy_term_type($taxonomy,$term_id,$value) {
  update_option("_term_type_{$taxonomy}_{$term_id}",$value);
}

TaxonomyTermTypes::on_load();
TaxonomyTermTypes::register_taxonomy( array( 'outsource_companies' ) );
class TaxonomyTermTypes {
  static function on_load() {
    add_action( 'created_term', array( __CLASS__, 'term_type_update' ), 10, 3 );
    add_action( 'edit_term', array( __CLASS__, 'term_type_update' ), 10, 3 );
  }
  static function register_taxonomy($taxonomy) {
    if (!is_array($taxonomy))
      $taxonomy = array($taxonomy);
    foreach($taxonomy as $tax_name) {
      add_action("{$tax_name}_add_form_fields",array(__CLASS__,"add_form_fields"));
      add_action("{$tax_name}_edit_form_fields",array(__CLASS__,"edit_form_fields"),10,2);
    }
  }
  // This displays the selections. Edit it to retrieve
  static function add_form_fields($taxonomy) {
    echo __('Company Email', 'bookit') . self::get_select_html();
  }
  // This displays the selections. Edit it to retrieve your own terms however you retrieve them.
  static function get_select_html($selected='') {
    $html =<<<HTML
<input type="email" name="company_email" id="company_email" value="$selected">
HTML;
    return $html;
  }
    // This a table row with the drop down for an edit screen
    static function edit_form_fields($term, $taxonomy) {
    $selected = get_option("_term_type_{$taxonomy}_{$term->term_id}");
    $select = self::get_select_html($selected);
    $html =<<<HTML
      <tr class="form-field form-required">
        <th scope="row" valign="top"><label for="company_email">Company Email</label></th>
        <td>$select</td>
      </tr>
HTML;
    echo $html;
  }
  // These hooks are called after adding and editing to save $_POST['tag-term']
  static function term_type_update($term_id, $tt_id, $taxonomy) {
    if (isset($_POST['company_email'])) {
      update_taxonomy_term_type($taxonomy,$term_id,$_POST['company_email']);
    }
  }
}

add_filter( 'manage_edit-bookit_reservation_columns', 'add_new_bookit_reservation_columns' );
function add_new_bookit_reservation_columns( $bookit_reservation_columns ) {
  $bookit_reservation_columns['cb']             = '<input type="checkbox">';
  $bookit_reservation_columns['title']          = _x( 'Confirmation Code', 'column name');
  $bookit_reservation_columns['date_reserved']  = __( 'Date Reserved', 'bookit' );
  return $bookit_reservation_columns;
}

add_action( 'manage_bookit_reservation_posts_custom_column', 'manage_bookit_reservation_columns', 10, 2 );
function manage_bookit_reservation_columns( $column_name, $id ) {
  global $post;
  switch ( $column_name ) {
    case 'date_reserved':
      $month  = get_post_meta( $post->ID , 'month' , true );
      $date   = get_post_meta( $post->ID , 'date' , true );
      $year   = get_post_meta( $post->ID , 'year' , true );
      $time   = get_post_meta( $post->ID , 'time' , true );
      echo date( 'M. j, Y g:ia', strtotime($month . '-' . $date . '-' . $year . ' ' . $time) );
      break;
    default:
      break;
  }
}

add_filter( 'wp_insert_post_data' , 'bookit_dont_publish' , '99', 2 );
function bookit_dont_publish( $data , $postarr ) {
  if ($data['post_type'] == 'bookit_reservation' ){
    $data['post_status'] = 'draft';
  }
  return $data;
}

if( $bookit_config['premium'] ) {
  add_action( 'post_submitbox_misc_actions', 'bookit_publish_box' );
  function bookit_publish_box( $post ) {
    ?>
    <div class="misc-pub-section misc-pub-section">
      <h4><?php echo __('The Money Box', 'bookit')?></h4>
      <label for="bookit_quoted_price"><?php echo __('Quoted Price:', 'bookit')?></label>
      <input type="number" name="bookit_quoted_price" id="bookit_quoted_price" step=".1" min="0" value="<?php echo get_post_meta( $post->ID, 'bookit_quoted_price', true )?>">
    </div>
    <?
  }
}