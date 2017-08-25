<?php
namespace OMGForms\Basic\Core;

use OMGForms\Basic\IA;
use OMGForms\Core;

function get_form_by_post_id( $post_id ) {
	$form = wp_get_object_terms( $post_id, IA\get_tax_forms() );
	return ( ! empty( $form ) ) ? $form[0] : false;
}

function get_form_values( $slug, $post, $print_values = false ) {
	$fields = Core\get_fields( $slug );
	$post_id = is_object( $post ) ? $post->ID : $post;

	if ( empty( $fields ) || ! is_array( $fields ) ) {
		return false;
	}

	return array_reduce( $fields, function( $acc, $field ) use( $post_id, $print_values ) {
		$key = sprintf( 'omg-forms-%s', $field[ 'slug' ] );
		$value = get_post_meta( $post_id, $key, true );

		if ( true === $print_values && is_array( $value ) ) {
			$value = implode( ',', $value );
		}

		$acc[ $field[ 'label' ] ] = $value;

		return $acc;
	}, [] );
}