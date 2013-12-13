<?php
/**
 * @package Book_It
 * @version 2.0
 */

function bookit_send_email( $ID, $email_template_id, $recipient_ary ) {
  $errors = array();
  $post   = get_post( $ID );
  $email_templates = bookit_get_options('email_templates');
  $email_template = isset($email_templates[$email_template_id]) ? $email_templates[$email_template_id] : false;

  if( $post ) {
    if( $email_template ) {
      $recipient_email = isset( $recipient_ary['email'] ) ? $recipient_ary['email'] : false;
      $recipient_name = isset( $recipient_ary['name'] ) ? $recipient_ary['name'] : false;

      if ( $recipient_email && is_email( $recipient_email ) ) {
        if ( $recipient_name ) {

          // Setup email fields
          $to = $recipient_name . ' <' . $recipient_email . '>';
          $subject = bookit_shortcodes($email_template['subject'], $post->ID);
          $html = bookit_shortcodes($email_template['html'], $post->ID);
          $headers = array();
          $headers[] = 'From: ' . get_bloginfo( 'admin_name' ) . ' <' . get_bloginfo( 'admin_email' ) . '>';
          $headers[] = 'Bcc: ' . get_bloginfo( 'admin_name' ) . ' <' . get_bloginfo( 'admin_email' ) . '>';

          // Send the email
          add_filter( 'wp_mail_content_type', 'bookit_set_html_content_type' );
          if ( ! wp_mail( $to, $subject, $html, $headers ) ) {
            $errors[] = __( 'There was a problem sending the email.', 'bookit' );
          }
          remove_filter( 'wp_mail_content_type', 'set_html_content_type' );
        } else {
          $errors[] = __( 'Missing the recipient\'s name.', 'bookit' );
        }
      } else {
         $errors[] = __( 'The recipient\'s email is invalid.', 'bookit' );
      }
    } else {
      $errors[] = __( 'Unable to load the email template.', 'bookit' );
    }
  } else {
    $errors[] = __( 'Unable to load the post.', 'bookit' );
  }

  if (count($errors) > 0 ) {
    return $errors;
  } else {
    return array('The email has been successfully sent.');
  }
}

function bookit_set_html_content_type() {
  return 'text/html';
}
