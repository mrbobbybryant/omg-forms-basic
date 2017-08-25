<?php
namespace OMGForms\Basic\PostsTable;

use OMGForms\Basic\IA;
use OMGForms\Basic\CSV;
use OMGForms\Basic\Core;

function setup() {
	add_action( 'restrict_manage_posts', __NAMESPACE__ . '\create_form_filtering_option' );
	add_filter( 'parse_query', __NAMESPACE__ . '\filter_submissions_by_form' );
	add_filter( 'bulk_actions-edit-' . IA\get_type_entries(), __NAMESPACE__ . '\register_csv_export_actions' );
	add_filter( 'handle_bulk_actions-edit-' . IA\get_type_entries(), __NAMESPACE__ . '\csv_export_action_handler', 10, 3 );
}

function create_form_filtering_option() {

	global $typenow;

	if ( $typenow !== IA\get_type_entries() ) {
		return false;
	}

	$forms = get_terms( array(
		'taxonomy' => IA\get_tax_forms(),
		'hide_empty' => true,
	) );

	if( isset( $_GET[ 'form_id' ] ) ) {
		$form_selected = $_GET[ 'form_id' ];
	} else {
		$form_selected = 0;
	}

	?>
	<select name="form_id" id="form_id">
		<option value="0"><?php esc_html_e( 'Select a form', 'omg-forms' ); ?></option>
		<?php foreach( $forms as $form ): ?>
			<option value="<?php echo esc_attr( $form->term_id ); ?>" <?php selected( $form_selected, $form->term_id ); ?>>
				<?php echo esc_html( $form->name ); ?>
			</option>
		<?php endforeach; ?>
	</select>
	<?php
}

function filter_submissions_by_form( $query ) {
	if ( ! is_admin() || ! ( IA\get_type_entries() === $query->query['post_type'] ) ) {
		return $query;
	}

	if ( empty( $_GET['form_id'] ) ) {
	    return $query;
    }

	$vars = &$query->query_vars;

	if ( ! isset ( $vars['tax_query'] ) ) {
		$vars['tax_query'] = [
			[
				'taxonomy' => IA\get_tax_forms(),
				'field'    => 'term_id',
				'terms'    => [ $_GET['form_id'] ],
			]
		];
	}

	return $query;
}

function register_csv_export_actions( $bulk_actions ) {
    if ( ! isset( $_GET[ 'form_id' ] ) ) {
        return $bulk_actions;
    }

	$bulk_actions[ 'export_omg_forms' ] = esc_html__( 'Export Entries', 'omg-forms' );

	return $bulk_actions;
}

function csv_export_action_handler( $redirect_to, $doaction, $post_ids ) {
	if ( $doaction !== 'export_omg_forms' ) {
		return $redirect_to;
	}

	if ( empty( $post_ids ) ) {
		return $redirect_to;
    }

	$form = Core\get_form_by_post_id( $post_ids[0] );
	$fields = \OMGForms\Core\get_fields( $form->slug );

	$entries = array_map( function( $post_id ) use ( $form ) {
		$data = Core\get_form_values( $form->slug, $post_id, true );
		$data[ 'publish_date' ] = get_the_date( get_option( 'date_format' ), $post_id );

		return apply_filters( 'omg-form-basic-csv-entry', $data, $post_id, $form );
    }, $post_ids );

	$entries = apply_filters( 'omg-form-basic-csv-entries', $entries, $post_ids, $form );

	CSV\generate_csv( $entries, $form, $fields );

}