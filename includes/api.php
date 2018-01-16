<?php
namespace OMGForms\Basic\API;

use OMGForms\Basic\IA;
use OMGForms\Helpers as CoreHelpers;

function save_form_submission_as_entries( $data, $form ) {
	if ( ! CoreHelpers\is_form_type( 'basic-form', $form ) || is_wp_error( $data ) ) {
		return $data;
	}

	$form_name = \OMGForms\Helpers\get_form_name( $form['name'] );

	$entry_id = wp_insert_post( [
		'post_title' => sprintf( '%s: Temp', $form_name ),
		'post_status' => 'publish',
		'post_type' =>  IA\get_type_entries()
	], true );

	if ( is_wp_error( $entry_id ) ) {
		$data = $entry_id;
		return $data;
	}

	/**
	 * Update entry title to be a concatenation of Form Name and Entry post_id
	 */
	$post_title = apply_filters( 'omg_forms_basic_entry_title', sprintf( '%s: %d', $form_name, $entry_id ), $data, $form );
	wp_update_post( [ 'ID' => $entry_id, 'post_title' => $post_title ] );

	save_field_data( $entry_id, $data );
	set_form_relationship( $entry_id, $form );

	return $data;

}

add_action( 'omg_forms_save_data', __NAMESPACE__ .  '\save_form_submission_as_entries', 99, 2 );

/**
 * Function saves all the user submitted data as post meta.
 *
 * @param [Int]   $entry_id Entry Post ID.
 * @param [array] $data Array of user submitted data from the form.
 * @return void
 */
function save_field_data( $entry_id , $data ) {
	foreach ( $data as $key => $value ) {
		update_post_meta( $entry_id, $key, $value );
	}
}

/**
 * Function assigns the form taxonomy to the newly created form entry.
 *
 * @param [Int]   $entry_id Entry Post ID.
 * @param [array] $form Associated Array containing all the register form values.
 * @return void
 */
function set_form_relationship( $entry_id, $form ) {
	$form = get_term_by( 'slug', $form['name'], \OMGForms\Basic\IA\get_tax_forms() );

	if ( ! empty( $form ) ) {
		wp_set_object_terms( $entry_id, $form->term_id, IA\get_tax_forms() );
	}

}
