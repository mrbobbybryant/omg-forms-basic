<?php

if ( !defined( 'OMG_FORMS_BASICS_DIR' ) ) {
	define( 'OMG_FORMS_BASICS_DIR', dirname( __FILE__ ) );
}

if ( !defined( 'OMG_FORMS_BASIC_FILE' ) ) {
	define( 'OMG_FORMS_BASIC_FILE', __FILE__ );
}

if ( !defined( 'OMG_FORMS_BASIC_VERSION' ) ) {
	define( 'OMG_FORMS_BASCI_VERSION', '0.3.0' );
}

require_once OMG_FORMS_BASICS_DIR . '/includes/core.php';
require_once OMG_FORMS_BASICS_DIR . '/includes/api.php';
require_once OMG_FORMS_BASICS_DIR . '/includes/information-architecture/index.php';
require_once OMG_FORMS_BASICS_DIR . '/includes/admin/metabox.php';
require_once OMG_FORMS_BASICS_DIR . '/includes/admin/posts-table.php';
require_once OMG_FORMS_BASICS_DIR . '/includes/form-functions.php';
require_once OMG_FORMS_BASICS_DIR . '/includes/csv.php';

\OMGForms\Basic\IA\setup();
\OMGForms\Basic\Core\setup();
\OMGForms\Basic\Metabox\setup();
\OMGForms\Basic\PostsTable\setup();

function install() {
	\OMGForms\Basic\IA\register_entries_cpt();
	flush_rewrite_rules();
}

/**
 * Bootstrap Initial Forms Setup
 */
$version = get_option( 'omg_forms_version', OMG_FORMS_VERSION );

if ( empty( $version ) ) {
	install();
	update_option( 'omg_forms_version', OMG_FORMS_VERSION );
}

function create_associated_tax_term( $slug, $args ) {
	$form = get_term_by( 'slug', $slug, \OMGForms\Basic\IA\get_tax_forms() );

	if ( ! empty( $form ) ) {
		return;
	}

	wp_insert_term( $args[ 'name' ], \OMGForms\Basic\IA\get_tax_forms(), [ 'slug' => $slug ] );
}
add_action( 'omg_forms_create_form', 'create_associated_tax_term', 10, 2 );

function forms_create_rest_submission( $args ) {
	$args[ 'rest_api' ] = true;
	return $args;
}
add_filter( 'omg_form_filter_register_args', 'forms_create_rest_submission' );