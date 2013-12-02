<?php
/*
Plugin Name: Book It! Transportation
Plugin URI: http://www.benmarshall.me/book-it-transportation/
Description: A complete management system for your transportation business enabling you to easily accept and manage your transportation bookings.
Version: 2.0
Author: Ben Marshall
Author URI: http://www.benmarshall.me
*/

/**
 * @package Book_It
 * @version 2.0
 */

// Setup Custom Posts
$custom_posts = array();

$custom_posts['bookit_reservation'] = array(
	'name' => 'Reservations',
	'singular_name' => 'Reservation',
	'show_admin_ui' => true,
	'supports' => array( 'title', 'excerpt', 'comments', 'author' ),
	'enable_archives' => false,
	'menu_icon' => plugins_url( 'assets/img/icon-sm.png', __FILE__ )
);

// Setup Custom Taxonomies
$custom_taxonomies = array();

$custom_taxonomies['bookit_event_type'] = array(
	'name' => 'Event Types',
	'singular_name' => 'Event Type',
	'type' => 'tag',
	'post_type' => 'bookit_reservation'
);

$custom_taxonomies['bookit_outsource_company'] = array(
	'name' => 'Outsource Companies',
	'singular_name' => 'Outsource Company',
	'type' => 'tag',
	'post_type' => 'bookit_reservation'
);

$custom_taxonomies['bookit_vehicle'] = array(
	'name' => 'Vehicles',
	'singular_name' => 'Vehicle',
	'type' => 'tag',
	'post_type' => 'bookit_reservation'
);

$custom_taxonomies['bookit_reservation_status'] = array(
	'name' => 'Reservation Statuses',
	'singular_name' => 'Reservation Status',
	'type' => 'tag',
	'post_type' => 'bookit_reservation'
);

$custom_taxonomies['bookit_company'] = array(
	'name' => 'Companies',
	'singular_name' => 'Company',
	'type' => 'tag',
	'post_type' => 'bookit_reservation'
);

// Setup Custom Meta Boxes

// Reservation Details Box
add_action( 'add_meta_boxes', 'bookit_reservation_details' );
function bookit_reservation_details() {
	add_meta_box( 'reservation_details', __( 'Reservation Details', 'bookit' ), 'bookit_reservation_details_box', 'bookit_reservation', 'normal', 'high' );
}
function bookit_reservation_details_box( $post ) {
	wp_nonce_field( 'bookit_reservation_details_box', 'reservation_details_nonce' );
	require_once( plugin_dir_path(__FILE__) . '/tpl/reservation_details_box.tpl.php' );
}

// Reservation Notifications Box
add_action( 'add_meta_boxes', 'bookit_notifications_box' );
function bookit_notifications_box() {
	add_meta_box( 'notifications_box', __( 'Notification Options', 'bookit' ), 'bookit_reservation_notifications_box', 'bookit_reservation', 'side', 'default' );
}
function bookit_reservation_notifications_box( $post ) {
	require_once( plugin_dir_path(__FILE__) . '/tpl/notifications_box.tpl.php' );
}

// Save Box
add_action( 'add_meta_boxes', 'bookit_save_box' );
function bookit_save_box() {
	$plugin = get_plugin_data( __FILE__ );
	add_meta_box( 'save_box', __( 'Powered by Book It! v' . $plugin['Version'], 'bookit' ), 'bookit_reservation_save_box', 'bookit_reservation', 'side', 'core' );
}
function bookit_reservation_save_box( $post ) {
	require_once( plugin_dir_path(__FILE__) . '/tpl/save_box.tpl.php' );
}

// Advertisement Box
add_action( 'add_meta_boxes', 'bookit_adver_box' );
function bookit_adver_box() {
	add_meta_box( 'adver_box', __( 'Advertisement', 'bookit' ), 'bookit_reservation_adver_box', 'bookit_reservation', 'side', 'low' );
}
function bookit_reservation_adver_box( $post ) {
	require_once( plugin_dir_path(__FILE__) . '/tpl/adver_box.tpl.php' );
}

// On Save
add_action( 'save_post', 'bookit_reservation_details_box_save' );
function bookit_reservation_details_box_save( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

	if ( !wp_verify_nonce( $_POST['reservation_details_nonce'], 'bookit_reservation_details_box' ) ) return;

	if ( 'bookit_reservation' == $_POST['post_type'] ) {
		if ( !current_user_can( 'edit_post', $post_id ) ) return;
	}

	foreach ( $_POST['bookit_destination'] as $key => $val ) {
		if(!$val) {
			unset($_POST['bookit_destination'][$key]);
		}
	}
	$_POST['bookit_destination'] = array_values($_POST['bookit_destination']);

	update_post_meta( $post_id, 'bookit_reservation_date', $_POST['bookit_reservation_date'] );
	update_post_meta( $post_id, 'bookit_pickup_time', $_POST['bookit_pickup_time'] );
	update_post_meta( $post_id, 'bookit_primary_passenger', $_POST['bookit_primary_passenger'] );
	update_post_meta( $post_id, 'bookit_contact_phone', $_POST['bookit_contact_phone'] );
	update_post_meta( $post_id, 'bookit_reservation_hours', $_POST['bookit_reservation_hours'] );
	update_post_meta( $post_id, 'bookit_num_passengers', $_POST['bookit_num_passengers'] );
	update_post_meta( $post_id, 'bookit_pickup_location', $_POST['bookit_pickup_location'] );
	update_post_meta( $post_id, 'bookit_destination', json_encode($_POST['bookit_destination']) );
	update_post_meta( $post_id, 'bookit_contact_email', $_POST['bookit_contact_email'] );
	update_post_meta( $post_id, 'bookit_is_outsourced', $_POST['bookit_is_outsourced'] );
	update_post_meta( $post_id, 'bookit_is_outsourced', $_POST['bookit_is_outsourced'] );
	update_post_meta( $post_id, 'bookit_is_roundtrip', $_POST['bookit_is_roundtrip'] );
}

// DO NOT EDIT BELOW THIS LINE!

add_action( 'save_post', 'bookit_set_title' );
function bookit_set_title() {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
}

add_action( 'admin_enqueue_scripts', 'bookit_scripts' );
function bookit_scripts() {
 	wp_enqueue_style( 'fontawesome', '//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css' );
  wp_enqueue_style( 'bookit', plugins_url('assets/css/styles.css', __FILE__));

  wp_enqueue_script('timeago', plugins_url('assets/js/jquery.timeago.js', __FILE__), array('jquery'));
}

add_filter( 'gettext', 'bookit_gettext', 10, 2 );
function bookit_gettext( $translation, $original ) {
	global $post_type;
	if( 'bookit_reservation' == $post_type ) {
		if ( 'Excerpt' == $original ) {
	  	return 'Client Notes';
		}else{
	    $pos = strpos($original, 'Excerpts are optional hand-crafted summaries of your');
	    if ($pos !== false) {
	      return  '';
	    }
		}
	}
	return $translation;
}

add_filter( 'enter_title_here', 'bookit_change_enter_title_text', 10, 2 );
function bookit_change_enter_title_text( $text, $post ) {
	global $post_type;
	if( 'bookit_reservation' == $post_type ) {
		return 'Reservation Confirmation Code (leave blank to auto-generate)';
	}
}

require_once( plugin_dir_path(__FILE__) . '/custom-posts/functions.php' );
require_once( plugin_dir_path(__FILE__) . '/custom-taxonomies/functions.php' );

function bookit_covert_tags($string, $post_id = false) {
	$string = str_replace(array(
			'[[SITE_NAME]]'
		), array(
			get_bloginfo( 'name' )
		), $string);

	if ( $post_id ) {

	} else {
		$terms = get_the_terms( $ID, 'bookit_reservation_status', '', '', '' );
		$term_args = array(
			'hide_empty' => false,
		  'orderby' => 'name',
		  'order' => 'ASC'
		);
		$reservation_statuses = get_terms('bookit_reservation_status', $term_args);
		$statuses = false;
		if ( count($reservation_statuses) > 0 ) {
			foreach ( $reservation_statuses as $key => $obj ) {
				if ( !$statuses ) $statuses .= ', ';
				$statuses .= $obj->name;
			}
		} else {
			$statuses = __('No statuses available.', 'bookit') . '<a href="' . admin_url( 'edit-tags.php?taxonomy=bookit_reservation_status&post_type=bookit_reservation' ) . '">' . __('Manage Reservation Statuses', 'bookit') . '</a>';
		}

		$string = str_replace(array(
			'[[CONTACT_NAME]]',
			'[[CONFIRMATION_CODE]]',
			'[[DATE_RESERVED]]',
			'[[RESERVATION_DATE]]',
			'[[STATUS]]'
		), array(
			'John Doe (Demo)',
			'1A2B3C4D5E (Demo)',
			date_i18n(get_option('date_format'), time()) . ' (Demo)',
			date_i18n(get_option('date_format'), (time() + 86400)) . ' (Demo)',
			'<em>' . $statuses . '</em>'
		), $string);
	}

	return $string;
}

function bookit_render_email_templates() {
	if ( _bookit() ) {
	$options = bookit_get_options();
	?>
	<p><?php echo __('Create and manage reservation email templates below.') ?></p>
	<?php
	if ( !is_array($options['email_templates']) ) {
		while ( !is_array($options['email_templates']) ) {
			$options['email_templates'] = unserialize($options['email_templates']);
		}
	}
	foreach ( $options['email_templates'] as $key => $val ) {
		?>
		<div class="email-template">
			<p><label for="template-name-<?php echo $key ?>"><b><?php echo __('Name') ?>:</b><br>
				<input type="text" name="bookit_plugin_options[email_templates][<?php echo $key ?>][name]" id="template-name-<?php echo $key ?>" class="regular-text" value="<?php echo $val['name'] ?>">
			</label></p>
			<p><label for="template-subject-0"><b><?php echo __('Subject') ?>:</b><br>
				<input type="text" name="bookit_plugin_options[email_templates][<?php echo $key ?>][subject]" id="template-subject-<?php echo $key ?>" class="regular-text" value="<?php echo $val['subject'] ?>">
			</label></p>
			<p><label for="template-html-0"><b><?php echo __('HTML / Text') ?>:</b><br>
				<textarea name="bookit_plugin_options[email_templates][<?php echo $key ?>][html]" id="template-html-<?php echo $key ?>" class="large-text code" rows="10"><?php echo trim($val['html']) ?></textarea>
			</label></p>
			<a href="#" class="button deleteTemplate"><?php echo __('Delete Template', 'bookit') ?></a>
		</div>
		<?
	}
	?>

	<p><a href="#" id="newTemplate" class="button"><?php echo __('Create New Template', 'bookit') ?></a></p>

	<p><b><?php echo __('Template Tags', 'bookit') ?></b><br>
	<?php echo __('Use the tags below in the email templates for dynamic content.', 'bookit') ?></p>
	<table class="widefat">
		<thead>
			<tr>
				<th><?php echo __('Tag') ?></th>
				<th><?php echo __('Value') ?></th>
				<th><?php echo __('Description') ?></th>
			</tr>
		</thead>
		<thead>
			<tr>
				<td><code>[[SITE_NAME]]</code></td>
				<td><?php echo bookit_covert_tags('[[SITE_NAME]]') ?></td>
				<td><?php echo __('The name of the site specified in', 'bookit') ?> <a href="<?php echo admin_url( 'options-general.php' ) ?>"><?php echo __('General Settings', 'bookit') ?></a>.</td>
			</tr>
			<tr>
				<td><code>[[CONFIRMATION_CODE]]</code></td>
				<td><?php echo bookit_covert_tags('[[CONFIRMATION_CODE]]') ?></td>
				<td><?php echo __('The reservation confirmation code.', 'bookit') ?></td>
			</tr>
			<tr>
				<td><code>[[DATE_RESERVED]]</code></td>
				<td><?php echo bookit_covert_tags('[[DATE_RESERVED]]') ?></td>
				<td><?php echo __('The date when the reservation was submitted.', 'bookit') ?></td>
			</tr>
			<tr>
				<td><code>[[RESERVATION_DATE]]</code></td>
				<td><?php echo bookit_covert_tags('[[RESERVATION_DATE]]') ?></td>
				<td><?php echo __('The date the reservation is booked for.', 'bookit') ?></td>
			</tr>
			<tr>
				<td><code>[[STATUS]]</code></td>
				<td><?php echo bookit_covert_tags('[[STATUS]]') ?></td>
				<td><?php echo __('The') ?> <a href="<?php echo admin_url( 'edit-tags.php?taxonomy=bookit_reservation_status&post_type=bookit_reservation' ) ?>"><?php echo __('status') ?></a> <?php echo __('of the reservation.', 'bookit') ?></td>
			</tr>
			<tr>
				<td><code>[[CONTACT_NAME]]</code></td>
				<td><?php echo bookit_covert_tags('[[CONTACT_NAME]]') ?></td>
				<td><?php echo __('The reservation contact name.', 'bookit') ?></td>
			</tr>
		</thead>
	</table>

	<script>
	(function($) {
		$(function() {
			$('#newTemplate').bind('click', function(e) {
				e.preventDefault();
				var option = $('.email-template:eq(0)').clone(),
				num = $('.email-template').length;

				$('label[for="template-name-0"]', option).attr('for', 'template-name-' + num);
				$('#template-name-0', option).attr('id', 'template-name-' + num).attr('name', 'bookit_plugin_options[email_templates][' + num + '][name]').val('');

				$('label[for="template-subject-0"]', option).attr('for', 'template-subject-' + num);
				$('#template-subject-0', option).attr('id', 'template-subject-' + num).attr('name', 'bookit_plugin_options[email_templates][' + num + '][subject]').val('');

				$('label[for="template-html-0"]', option).attr('for', 'template-html-' + num);
				$('#template-html-0', option).attr('id', 'template-html-' + num).attr('name', 'bookit_plugin_options[email_templates][' + num + '][html]').val('');

				$('.email-template:eq(-1)').after(option);
			});

			$('.deleteTemplate').click(function(e) {
				e.preventDefault();
				$(this).parent().remove();
			});
		});
	})(jQuery);
	</script>
	<?php
}}

function bookit_render_license() {
	$options = bookit_get_options();
	?>
	<label for="license">
			<input type="text" name="bookit_plugin_options[license]" id="license" value="<?php echo bookit_get_options('license'); ?>" class="regular-text" > <?php if ( _bookit() ): ?><span class="bookit-green"><b><i class="icon-thumbs-up bookit-link-icon"></i> <?php echo __('Thanks for choosing Book It! to manage your reservations!', 'bookit') ?></b></span><?php else: ?><span class="bookit-red"><b><i class="icon-thumbs-down bookit-link-icon"></i> <?php echo __('Invalid license.', 'bookit') ?></b></span><?php endif; ?>
		</label>
		<p class="description"><?php echo __('If you\'ve purchased the premium version of Book It!, enter your license key here.', 'bookit') ?></p>
	<?php
}

function bookit_render_code_length() {
	$options = bookit_get_options();
	?>
	<p><label for="code-length">
			<input type="number" min="5" name="bookit_plugin_options[code_length]" id="code-length" value="<?php if( $options['code_length'] ): echo esc_attr($options['code_length']); else: echo 10; endif; ?>" class="small-text" > <?php echo __('characters') ?>
		</label></p>
	<?php
}

add_action( 'admin_init', 'bookit_options_init' );
function bookit_options_init() {
	register_setting( 'bookit_options', 'bookit_plugin_options', 'bookit_plugin_options_validate' );

	add_settings_section( 'general_settings', __('General Settings', 'bookit'), '__return_false', 'bookit_settings' );
	add_settings_section( 'email_templates', __('Email Templates', 'bookit'), '__return_false', 'bookit_settings' );

	add_settings_field( 'license', __( 'License', 'bookit' ), 'bookit_render_license', 'bookit_settings', 'general_settings' );
	add_settings_field( 'code_length', __( 'Confirmation Code Length', 'bookit' ), 'bookit_render_code_length', 'bookit_settings', 'general_settings' );

	add_settings_field( 'email_templates', __( 'Email Templates', 'bookit' ), 'bookit_render_email_templates', 'bookit_settings', 'email_templates' );
}

function bookit_plugin_options_validate( $input ) {

	if ( isset($input['email_templates']) ) $input['email_templates'] = serialize($input['email_templates']);
	if ( isset($input['license']) ) $input['license'] = htmlentities($input['license']);


	return $input;
}

function bookit_get_options($key = false) {
	$saved = (array) get_option( 'bookit_plugin_options' );
	$defaults = array();

	$defaults['code_length'] = 10;
	$defaults['license'] = '';
	$defaults['email_templates'] = array(
		0 => array(
			'name' => __('Default Template', 'bookit'),
			'subject' => __('[[[SITE_NAME]]] Reservation Details', 'bookit'),
			'html' => '<h1>Reservation Details ([[CONFIRMATION_CODE]])</h1>
<table>
	<tr>
		<td><b>Date Reserved:</b></td>
		<td>[[DATE_RESERVED]]</td>
	</tr>
	<tr>
		<td><b>Reservation Date:</b></td>
		<td>[[RESERVATION_DATE]]</td>
	</tr>
	<tr>
		<td><b>Status:</b></td>
		<td>[[STATUS]]</td>
	</tr>
	<tr>
		<td><b>Contact Name:</b></td>
		<td>[[CONTACT_NAME]]</td>
	</tr>
</table>
			'
		)
	);

	$defaults = apply_filters( 'bookit_default_theme_options', $defaults );

	$options = wp_parse_args( $saved, $defaults );
	$options = array_intersect_key( $options, $defaults );

	if ( $key ) {
		if ( $key == 'license' ) {
			$options[$key] = html_entity_decode($options[$key]);
		}

		return $options[$key];
	} else {
		return $options;
	}
}

add_action( 'admin_menu', 'bookit_plugin_options_add_page' );
function bookit_plugin_options_add_page() {
	add_plugins_page( __( 'Book It! Options', 'bookit' ), __( 'Book It! Options', 'bookit' ), 'edit_theme_options', 'bookit_options', 'bookit_plugin_options_render_page' );

  remove_meta_box('tagsdiv-bookit_event_type', 'bookit_reservation', 'side');
  remove_meta_box('tagsdiv-bookit_outsource_company', 'bookit_reservation', 'side');
  remove_meta_box('tagsdiv-bookit_vehicle', 'bookit_reservation', 'side');
  remove_meta_box('submitdiv', 'bookit_reservation', 'side');
  remove_meta_box('commentstatusdiv', 'bookit_reservation', 'normal');
  remove_meta_box('tagsdiv-bookit_reservation_status', 'bookit_reservation', 'side');
  remove_meta_box('tagsdiv-bookit_company', 'bookit_reservation', 'side');
}

function bookit_plugin_options_render_page() {
	?>
	<div class="wrap">
		<?php screen_icon(); ?>
		<h2><?php echo __('Book It! Transportation Options', 'bookit'); ?></h2>
		<?php settings_errors(); ?>

		<form method="post" action="options.php">
			<?php
				settings_fields( 'bookit_options' );
				do_settings_sections( 'bookit_settings' );
				submit_button();
			?>
		</form>
	</div>
	<?php
}

add_action( 'init', 'bookit_init' );
function bookit_init() {
	if ( !term_exists('N/A', 'bookit_company') ) wp_insert_term( 'N/A', 'bookit_company', array( 'slug' => 'no-company' ) );
	if ( !term_exists('N/A', 'bookit_event_type') ) wp_insert_term( 'N/A', 'bookit_event_type', array( 'slug' => 'no-event' ) );
	if ( !term_exists('N/A', 'bookit_vehicle') ) wp_insert_term( 'N/A', 'bookit_vehicle', array( 'slug' => 'no-vehicle' ) );
	if ( !term_exists('N/A', 'bookit_reservation_status') ) wp_insert_term( 'Pending Review', 'bookit_reservation_status', array( 'slug' => 'pending-review' ) );
	if ( !term_exists('N/A', 'bookit_reservation_status') ) wp_insert_term( 'Completed', 'bookit_reservation_status', array( 'slug' => 'completed' ) );

	bookit_listen();
}

function bookit_listen() {
	if( $_POST ) {
		if( isset( $_POST['bookit_action'] ) ) {
			switch( $_POST['bookit_action'] ) {
      	case 'send_new_reservation_email':
				if( isset( $_POST['ID'] ) ) {
					$result = bookit_send_email( $_POST['ID'], 'new_reservation' );
				}
				return;
				break;
      }
		}
	}
}

function bookit_send_email( $ID, $type ) {
  $errors = array();
  $post   = get_post( $ID );
  if ( $post ) {
    $meta   = get_post_custom( $post->ID );
    if ( isset($meta['bookit_contact_email'][0]) && is_email( $meta['bookit_contact_email'][0] ) ) {
    	$contact_name  = isset($meta['bookit_primary_passenger'][0]) ? $meta['bookit_primary_passenger'][0] : '';
      $user_email = $meta['contact_email'][0];

      /*if ( $type == 'new_reservation' ) {
      	$to = $contact_name . '<' . $user_email . '>';
      	$email  = isset($bookit_config['emails'][$type]) ? $bookit_config['emails'][$type] : false;
      }

      
      
      if ( $email ) {
        $subject = isset($email['subject']) ? $email['subject'] : get_option('bookit_emails_default_subject');
        $template = isset($email['template']) ? $email['template'] : false;
        if ( $template ) {
          $categories = array();
          foreach( $bookit_config['categories'] as $key => $value ) {
            $category = wp_get_post_terms( $post->ID, $key );
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
          $headers[] = 'From: ' . get_bloginfo( 'admin_name' ) . ' <' . get_bloginfo( 'admin_email' ) . '>';
          $headers[] = 'Bcc: ' . get_bloginfo( 'admin_name' ) . ' <' . get_bloginfo( 'admin_email' ) . '>';
          add_filter( 'wp_mail_content_type', create_function( '', 'return "text/html";' ) );
          if ( ! wp_mail( $to, bookit_tags( $subject, $ary ), bookit_tags( $template, $ary ), $headers ) ) {
            $errors[] = __( 'There was a problem sending the email.', 'bookit' );
          }
        } else {
          $errors[] = __( 'You must first create a <a href="wp-admin/options-general.php?page=bookit">email template</a>.', 'bookit' );
        }
      } else {
        $errors[] = __( 'Unable to locate the \'' . $type . '\' email type.', 'bookit' );
      }*/
    } else {
      $errors[] = __( 'The user\'s email is invaild.', 'bookit' );
    }
  } else {
    $errors[] = __( 'Unable to load the post.', 'bookit' );
  }

  if (count($errors) > 0 ) {
    return $errors;
  } else {
    return 'success';
  }
}

function bookit_randString($length=10, $charset='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789') {
	if(!$length) $length = 10;
  $str = '';
  $count = strlen($charset);
  while ($length--) {
    $str .= $charset[mt_rand(0, $count-1)];
  }
  return $str;
}

add_filter( 'wp_insert_post_data' , 'bookit_auto_generate_title' , '99', 2 );
function bookit_auto_generate_title( $data , $postarr ) {
	global $wpdb;
	if ( $data['post_type'] == 'bookit_reservation' && strlen($data['post_title']) < 1 ) {
		$unique = false;
		while(!$unique) {
			$rand = bookit_randString(get_option( 'bookit_code_length', 10 ));
			$query = $wpdb->prepare('SELECT ID FROM ' . $wpdb->posts . ' WHERE post_title = %s AND post_type = \'bookit_reservation\'', $rand);
			$wpdb->query( $query );
			if ( !$wpdb->num_rows ) {
				$unique = true;
			}
		}
		$data['post_title'] = $rand;
	}
	return $data;
}

function _bookit(){$result=json_decode(file_get_contents('http://www.codehab.com/api/?license='.urlencode(bookit_get_options('license'))));
	if ( isset( $result->status) ) {
		if($result->status=='invalid')return false;
		if($result->status=='valid') return true;
		return false;
	} else {
		return false;
	}
}

// Force reservation post status to be private, don't want to public seeing your client's records!
function bookit_force_type_private ( $post ) {
	if ( $post['post_type'] == 'bookit_reservation' ) $post['post_status'] = 'private';
	
	return $post;
}
add_filter('wp_insert_post_data', 'bookit_force_type_private');


// Manage columns on Reservations admin page

add_action( 'manage_bookit_reservation_posts_custom_column', 'bookit_column_content', 10, 2 );
function bookit_column_content ( $column_name, $post_id ) {
	switch ( $column_name ) {
		case 'bookit-reservation-date':
			if ( get_post_meta($post_id, 'bookit_reservation_date', true) ) {
				echo date('l, F j, Y' ,strtotime(get_post_meta($post_id, 'bookit_reservation_date', true)));
			} else {
				echo '<em>' . __('Not set. ', 'bookit') . '</em>';
			}
		break;
		case 'bookit-primary-passenger':
			if ( get_post_meta($post_id, 'bookit_primary_passenger', true) ) {
				echo get_post_meta($post_id, 'bookit_primary_passenger', true);
			} else {
				echo '<em>' . __('Not set. ', 'bookit') . '</em>';
			}
		break;
		default:
			return;
	}
}

add_action( 'pre_get_posts', 'bookit_column_orderby' );
function bookit_column_orderby( $query ) {
	if( ! is_admin() )
		return;

	$orderby = $query->get( 'orderby');
	switch ( $orderby ) {
		case 'bookit-reservation-date':
			$query->set('meta_key','bookit_reservation_date');
			$query->set('orderby','meta_value');
		break;
		case 'bookit-primary-passenger':
			$query->set('meta_key','bookit_primary_passenger');
			$query->set('orderby','meta_value');
		break;
	}
}

// Reservation Date column
add_filter('manage_bookit_reservation_posts_columns', 'bookit_reservation_date_column');
function bookit_reservation_date_column($columns) {
	$columns['bookit-reservation-date'] =__('Reservation Date','bookit');
	return $columns;
}
add_filter( 'manage_edit-bookit_reservation_sortable_columns', 'bookit_reservation_date_sortable_column' );
function bookit_reservation_date_sortable_column( $columns ) {
	$columns['bookit-reservation-date'] = 'bookit-reservation-date';
	return $columns;
}

// Confirmation Code column
add_filter('manage_bookit_reservation_posts_columns' , 'bookit_column_name');
function bookit_column_name( $columns ) {
	$columns['title'] = __('Confirmation Code', 'bookit');
	return $columns;
}

// Primary Passenger column
add_filter('manage_bookit_reservation_posts_columns', 'bookit_primary_passenger_column');
function bookit_primary_passenger_column($columns) {
	$columns['bookit-primary-passenger'] =__('Primary Passenger','bookit');
	return $columns;
}
add_filter( 'manage_edit-bookit_reservation_sortable_columns', 'bookit_primary_passenger_sortable_column' );
function bookit_primary_passenger_sortable_column( $columns ) {
	$columns['bookit-primary-passenger'] = 'bookit-primary-passenger';
	return $columns;
}