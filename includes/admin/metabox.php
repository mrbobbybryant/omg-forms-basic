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
			<?php echo display_values( $value ); ?>
		</p>
	<?php }
}

function display_values( $value ) {
    if ( is_array( $value ) ) {
        ob_start();?>

        <ul>
            <?php foreach( $value as $item ) : ?>
                <li><?php echo esc_html( $item ); ?></li>
            <?php endforeach; ?>
        </ul>

        <?php return ob_get_clean();
    } else {
        echo esc_html( $value );
    }
}