<?php
namespace OMGForms\Basic\Metabox;

use OMGForms\Basic\IA;
use OMGForms\Basic\Core;

function setup() {
	add_action( 'add_meta_boxes', __NAMESPACE__ . '\register_entries_metabox' );
}

function register_entries_metabox() {
	add_meta_box(
		'entries-meta-box',
		esc_html__( 'Entry Info', 'omg-forms' ),
		__NAMESPACE__ . '\entries_meta_box_display',
		IA\get_type_entries()
	);
}

function entries_meta_box_display( $post ) {
	$form  = Core\get_form_by_post_id( $post->ID );
	$values = Core\get_form_values( $form->slug, $post );

	if ( empty( $values ) ) {
		return false;
	}

	foreach( $values as $key => $value ) { ?>
		<p>
			<strong><?php echo esc_html( sprintf( '%s: ', $key ) ); ?></strong>
			<?php echo esc_html( $value ); ?>
		</p>
	<?php }
}