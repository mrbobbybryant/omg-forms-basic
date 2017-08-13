<?php
namespace OMGForms\Basic\API;

use OMGForms\Basic\IA;

function save_form_submission_as_entries( $data, $form ) {
	$form_name = \OMGForms\Helpers\get_form_name( $form['name'] );
	$entry_id = wp_insert_post( [
		'post_title' => sprintf( '%s: Temp', $form_name ),
		'post_status' => 'publish',
		'post_type' =>  IA\get_type_entries()
	], true );

	if ( is_wp_error( $entry_id ) ) {
		return $entry_id;
	}

	/**
	 * Update entry title to be a concatenation of Form Name and Entry post_id
	 */
	wp_update_post( [ 'ID' => $entry_id, 'post_title' => sprintf( '%s: %d', $form_name, $entry_id ) ] );

	save_field_data( $entry_id, $data );
	set_form_relationship( $entry_id, $form );

	if ( isset( $form[ 'email' ] ) && ! empty( $form[ 'email' ] ) ) {
		send_email( $form, $entry_id );
	}
}

add_action( 'omg_forms_save_data', __NAMESPACE__ .  '\save_form_submission_as_entries', 10, 2 );

function save_field_data( $entry_id , $data ) {
	foreach( $data as $key => $value ) {
		update_post_meta( $entry_id, $key, $value );
	}
}

function set_form_relationship( $entry_id, $form ) {
	$form = get_term_by( 'slug', $form[ 'name' ], \OMGForms\Basic\IA\get_tax_forms() );

	if ( ! empty( $form ) ) {
		wp_set_object_terms( $entry_id, $form->term_id, IA\get_tax_forms() );
	}

}

function send_email( $form, $entry_id ) {
	$to      = $form['email_to'];
	$headers = array(
		'From: ' . get_bloginfo( 'admin_email' )
	);
	$subject = sprintf( '%s Submission Notification.', \OMGForms\Helpers\get_form_name( $form['name'] ) );

	$message = 'Hello' . "\r\n\r\n";
	$message .= 'We have received a new form submission on ' . site_url() . ".\r\n\r\n";
	$message .= 'Please login to view this form submission.';

	$subject = apply_filters( 'omg_form_submitted_subject', $subject, $form, $entry_id );
	$headers = apply_filters( 'omg_form_submitted_headers', $headers, $form, $entry_id );
	$message = apply_filters( 'omg_form_submitted_message', $message, $form, $entry_id );

	$sent = wp_mail( $to, $subject, $message, $headers );

	if ( false === $sent ) {
		error_log( 'Email failed to send for entry ' . $entry_id );
	}
}