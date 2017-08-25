<?php
namespace OMGForms\Basic\CSV;

function generate_csv( $entries, $form, $fields ) {

	$headings = generate_csv_file_headings( $form, $fields );

	header('Content-Type: text/csv; charset=utf-8');

	$disposition = sprintf( 'Content-Disposition: attachment; filename=%s.csv', generate_filename( $form->name ) );

	header( $disposition );

	$output = fopen( 'php://output', 'w' );

	fputcsv( $output, $headings );

	foreach( $entries as $entry ) {
		fputcsv( $output, $entry );
	}

}

function generate_filename( $form_name ) {
	return sprintf( '%s-%s', $form_name, time() );
}

function generate_csv_file_headings( $form, $fields ) {
	$headings = array_map( function( $field ) {
		return $field[ 'label' ];
	}, $fields );

	$headings[] = 'Publish Date';

	return apply_filters( 'omg-form-basic-csv-headings', $headings, $form, $fields );
}