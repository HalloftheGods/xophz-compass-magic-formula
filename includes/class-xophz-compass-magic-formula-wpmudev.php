<?php

/**
 * WPMU DEV Integrations for Magic Formulas Proxy
 *
 * Hooks into Forminator to intercept/handle specific behaviors if needed.
 *
 * @package    Xophz_Compass_Magic_Formula
 * @subpackage Xophz_Compass_Magic_Formula/includes
 */

class Xophz_Compass_Magic_Formula_WPMUDEV {

	public function init_hooks() {
        // Example: Hook before Forminator processes fields
        // add_action( 'forminator_custom_form_submit_before_set_fields', array( $this, 'intercept_submission' ), 10, 3 );
        
        // Auto-populate forms based on previous submissions
        add_filter( 'forminator_cform_render_fields', array( $this, 'autopopulate_form_fields' ), 10, 2 );
	}

    /**
     * Intercept Forminator submissions (if needed for the backend)
     */
    public function intercept_submission( $entry, $module_id, $field_data_array ) {
        // Future COMPASS backend logic for Magic Formulas proxy
    }

    /**
     * Automatically populate form fields with the user's previous submission data
     *
     * @param array $wrappers Form field wrappers and structure
     * @param int $form_id The form ID
     * @return array Modified wrappers with prefilled default values
     */
    public function autopopulate_form_fields( $wrappers, $form_id ) {
        if ( ! is_user_logged_in() ) {
            return $wrappers;
        }

        $user_id = get_current_user_id();

        global $wpdb;
        $meta_table = Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY_META );
        $entry_table = Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY );

        $sql = "
            SELECT e.entry_id 
            FROM {$entry_table} e
            JOIN {$meta_table} m ON e.entry_id = m.entry_id
            WHERE e.form_id = %d 
              AND e.status = 'active'
              AND m.meta_key = '_user_id' 
              AND m.meta_value = %s
            ORDER BY e.date_created DESC
            LIMIT 1
        ";
        
        $entry_id = $wpdb->get_var( $wpdb->prepare( $sql, $form_id, $user_id ) );

        if ( ! $entry_id ) {
            return $wrappers;
        }

        $latest_entry = new Forminator_Form_Entry_Model( $entry_id );
        $entry_meta = $latest_entry->meta_data;
        $meta_data = array();
        
        foreach ( $entry_meta as $key => $meta ) {
            if ( isset( $meta['value'] ) ) {
                $meta_data[$key] = $meta['value'];
            }
        }

        $has_autofilled = false;

        foreach ( $wrappers as &$wrapper ) {
            if ( ! isset( $wrapper['fields'] ) || ! is_array( $wrapper['fields'] ) ) {
                continue;
            }

            foreach ( $wrapper['fields'] as &$field ) {
                $element_id = isset( $field['element_id'] ) ? $field['element_id'] : '';
                
                if ( empty( $element_id ) ) {
                    continue;
                }

                if ( isset( $meta_data[ $element_id ] ) && $meta_data[ $element_id ] !== '' && $meta_data[ $element_id ] !== null ) {
                    $value = $meta_data[ $element_id ];
                    $has_autofilled = true;
                    
                    // Different field types might need different keys for their value
                    $field_type = isset( $field['type'] ) ? $field['type'] : '';
                    
                    switch ( $field_type ) {
                        case 'text':
                        case 'email':
                        case 'phone':
                        case 'number':
                        case 'url':
                        case 'textarea':
                        case 'hidden':
                        case 'address':
                        case 'name':
                            $field['default'] = is_array($value) ? implode(', ', $value) : $value;
                            break;
                            
                        case 'radio':
                        case 'select':
                            if ( isset( $field['options'] ) && is_array( $field['options'] ) ) {
                                foreach ( $field['options'] as &$option ) {
                                    $option_value = isset( $option['value'] ) ? $option['value'] : '';
                                    $val = is_array($value) ? $value[0] : $value;
                                    if ( $option_value == $val ) {
                                        $option['default'] = true;
                                    } else {
                                        $option['default'] = false;
                                    }
                                }
                            }
                            break;
                            
                        case 'checkbox':
                            if ( isset( $field['options'] ) && is_array( $field['options'] ) ) {
                                $vals = is_array( $value ) ? $value : array( $value );
                                foreach ( $field['options'] as &$option ) {
                                    $option_value = isset( $option['value'] ) ? $option['value'] : '';
                                    if ( in_array( $option_value, $vals ) ) {
                                        $option['default'] = true;
                                    } else {
                                        $option['default'] = false;
                                    }
                                }
                            }
                            break;
                    }
                }
            }
        }

        if ( $has_autofilled ) {
            array_unshift( $wrappers, array(
                'fields' => array(
                    array(
                        'type' => 'html',
                        'element_id' => 'autofill-notice-1',
                        'variations' => '<div style="margin-bottom: 20px; padding: 15px; border-radius: 8px; background: rgba(98, 201, 255, 0.1); border-left: 4px solid var(--x-cyan, #62c9ff); color: var(--x-cyan, #62c9ff); font-size: 0.9em;"><i class="fal fa-magic" style="margin-right: 8px;"></i> Form autopopulated from your previous submission.</div>'
                    )
                )
            ) );
        }

        return $wrappers;
    }
}
