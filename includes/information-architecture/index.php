<?php
namespace OMGForms\Basic\IA;

function setup() {
	require_once 'types.php';
	require_once 'entries.php';
	require_once 'forms.php';
}

function get_meta_key_value( $keys, $key ) {
	return ( $key ) ? $keys[$key]['key'] : $keys;
}
