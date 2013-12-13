<?php
/**
 * @package Book_It
 * @version 2.0
 */

function bookit_listen() {
  if( $_POST ) {
    if( isset( $_POST['bookit_action'] ) ) {
      switch( $_POST['bookit_action'] ) {
        case 'send_email':
          if(
            isset( $_POST['ID'] ) &&
            isset( $_POST['bookit_template_id'] ) &&
            isset( $_POST['bookit_send_to_email'] ) &&
            isset( $_POST['bookit_send_to_name'] )
          ) {
            $result = json_encode(bookit_send_email( $_POST['ID'], $_POST['bookit_template_id'], array(
              'email' => $_POST['bookit_send_to_email'],
              'name' => $_POST['bookit_send_to_name']
            )));
          }
        break;
      }
      echo $result;
      die(); // This is required to return a proper result
    }
  }
}
