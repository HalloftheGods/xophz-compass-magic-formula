<?php

/**
 * REST API handling for Magic Formula Forminator Proxy
 *
 * @package    Xophz_Compass_Magic_Formula
 * @subpackage Xophz_Compass_Magic_Formula/includes
 */

class Xophz_Compass_Magic_Formula_REST {

	public function register_routes() {
        register_rest_route( 'magic-formula/v1', '/forms', array(
            array(
                'methods'  => WP_REST_Server::READABLE,
                'callback' => array( $this, 'get_forms' ),
                'permission_callback' => array( $this, 'check_permission' ),
            ),
        ) );

        register_rest_route( 'magic-formula/v1', '/submissions/(?P<id>\d+)', array(
            array(
                'methods'  => WP_REST_Server::READABLE,
                'callback' => array( $this, 'get_submissions' ),
                'permission_callback' => array( $this, 'check_permission' ),
            ),
        ) );

        register_rest_route( 'magic-formula/v1', '/submit/(?P<id>\d+)', array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array( $this, 'submit_form' ),
                'permission_callback' => '__return_true',
            ),
        ) );

        register_rest_route( 'magic-formula/v1', '/conjure', array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array( $this, 'conjure_form' ),
                'permission_callback' => array( $this, 'check_permission' ),
            ),
        ) );

        register_rest_route( 'magic-formula/v1', '/mappings', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array( $this, 'get_mappings' ),
                'permission_callback' => array( $this, 'check_permission' ),
            ),
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array( $this, 'save_mappings' ),
                'permission_callback' => array( $this, 'check_permission' ),
            ),
        ) );

        register_rest_route( 'magic-formula/v1', '/forms/(?P<id>\d+)/fields', array(
            array(
                'methods'  => WP_REST_Server::READABLE,
                'callback' => array( $this, 'get_form_fields' ),
                'permission_callback' => array( $this, 'check_permission' ),
            ),
            array(
                'methods'  => WP_REST_Server::EDITABLE,
                'callback' => array( $this, 'update_form_fields' ),
                'permission_callback' => array( $this, 'check_permission' ),
            ),
        ) );

        register_rest_route( 'magic-formula/v1', '/roles', array(
            array(
                'methods'  => WP_REST_Server::READABLE,
                'callback' => array( $this, 'get_roles' ),
                'permission_callback' => array( $this, 'check_permission' ),
            ),
        ) );

        register_rest_route( 'magic-formula/v1', '/gate', array(
            array(
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => array( $this, 'render_magic_gate' ),
                'permission_callback' => '__return_true',
            ),
        ) );
	}

    public function get_roles( WP_REST_Request $request ) {
        if ( ! function_exists( 'wp_roles' ) ) {
            require_once ABSPATH . 'wp-includes/capabilities.php';
        }
        $wp_roles = wp_roles();
        $roles_list = array();
        foreach ( $wp_roles->roles as $slug => $role_data ) {
            $roles_list[] = array(
                'slug' => $slug,
                'name' => $role_data['name']
            );
        }
        return rest_ensure_response( $roles_list );
    }

    public function render_magic_gate( WP_REST_Request $request ) {
        $params = $request->get_json_params() ?: $request->get_body_params();

        $default_id = isset( $params['default_id'] ) ? sanitize_text_field( $params['default_id'] ) : '';
        $gated_id   = isset( $params['gated_id'] ) ? sanitize_text_field( $params['gated_id'] ) : '';
        $access_str = isset( $params['access'] ) ? sanitize_text_field( $params['access'] ) : '';

        $allowed_roles = array();
        if ( ! empty( $access_str ) ) {
            $access_str = trim( $access_str, '[]\'"' );
            $allowed_roles = array_filter( array_map( 'trim', explode( ',', str_replace( array( '"', '\'' ), '', $access_str ) ) ) );
        }

        $show_gated = false;
        $user_id    = get_current_user_id();

        if ( is_user_logged_in() ) {
            $user = wp_get_current_user();
            $user_roles = (array) $user->roles;

            if ( empty( $allowed_roles ) ) {
                $show_gated = true;
            } else {
                foreach ( $allowed_roles as $allowed_role ) {
                    if ( in_array( $allowed_role, $user_roles, true ) ) {
                        $show_gated = true;
                        break;
                    }
                }
            }
        }

        $atts = array( 'gated_id' => $gated_id, 'default_id' => $default_id, 'access' => $access_str );
        $show_gated = apply_filters( 'xophz_compass_magic_gate_show_gated', $show_gated, $atts, $user_id );

        $output = '<div class="magic-gate-wrapper">';

        // To ensure forminator scripts render properly over REST API which runs early:
        if ( class_exists('Forminator_CForm_Front') && class_exists('Forminator_Base_Form_Model') ) {
            $forminator_front = Forminator_CForm_Front::get_instance();
            if ( $show_gated && ! empty( $gated_id ) ) {
                $model = Forminator_Base_Form_Model::get_model( $gated_id );
                if ( $model instanceof Forminator_Form_Model ) {
                    $forminator_front->model = $model;
                    $forminator_front->enqueue_form_scripts( false );
                }
            } elseif ( ! $show_gated && ! empty( $default_id ) ) {
                $model = Forminator_Base_Form_Model::get_model( $default_id );
                if ( $model instanceof Forminator_Form_Model ) {
                    $forminator_front->model = $model;
                    $forminator_front->enqueue_form_scripts( false );
                }
            }
        }

        if ( $show_gated && ! empty( $gated_id ) ) {
            $output .= '<div class="magic-gate-gated">';
            $output .= do_shortcode( '[forminator_form id="' . esc_attr( $gated_id ) . '"]' );
            $output .= '</div>';
        } elseif ( ! $show_gated && ! empty( $default_id ) ) {
            $output .= '<div class="magic-gate-default">';
            $output .= do_shortcode( '[forminator_form id="' . esc_attr( $default_id ) . '"]' );
            $output .= '</div>';
        }

        $output .= '</div>';

        ob_start();
        wp_print_footer_scripts();
        $scripts = ob_get_clean();

        $output .= $scripts;

        $output = apply_filters( 'xophz_compass_magic_gate_output', $output, $show_gated, $atts );

        return rest_ensure_response( array( 'html' => $output ) );
    }

    public function conjure_form( WP_REST_Request $request ) {
        if ( ! class_exists( 'Forminator_API' ) ) {
            return new WP_Error( 'no_forminator', 'Forminator API not found', array( 'status' => 500 ) );
        }

        $params = $request->get_json_params() ?: $request->get_body_params();
        $fields = isset($params['fields']) ? $params['fields'] : array();
        $name   = isset($params['name']) ? sanitize_text_field($params['name']) : 'Conjured Form ' . wp_date('Y-m-d H:i');

        if ( empty($fields) || ! is_array($fields) ) {
            return new WP_Error( 'invalid_fields', 'Invalid or empty fields provided for conjuring.', array( 'status' => 400 ) );
        }

        $wrappers = array();
        $has_page_break = false;

        foreach ( $fields as $index => $field ) {
            $type = isset($field['type']) ? sanitize_text_field($field['type']) : 'text';
            $label = isset($field['label']) ? sanitize_text_field($field['label']) : 'Field ' . ($index + 1);
            $slug = isset($field['name']) ? sanitize_text_field($field['name']) : ( $type . '-' . ($index + 1) );
            
            if ( $type === 'page-break' ) {
                $has_page_break = true;
            }

            $element = array(
                'element_id'  => $slug,
                'type'        => $type,
                'field_label' => $label,
                'cols'        => 12
            );

            if ( isset($field['options']) && is_array($field['options']) ) {
                $options = array();
                foreach ( $field['options'] as $opt ) {
                    $options[] = array(
                        'label' => sanitize_text_field( $opt['label'] ?? '' ),
                        'value' => sanitize_text_field( $opt['value'] ?? '' ),
                        'limit' => '',
                        'default' => false
                    );
                }
                $element['options'] = $options;
            }

            $wrappers[] = array(
                'wrapper_id' => 'wrapper-' . uniqid(),
                'fields' => array( $element )
            );
        }

        $settings = array(
            'formName' => $name,
            'version'  => '1.0'
        );

        if ( $has_page_break || count($fields) > 8 ) {
            $settings['use_save_and_continue'] = 'true';
            $settings['sc_email_link'] = 'true';
        }

        $form_id = Forminator_API::add_form( $name, $wrappers, $settings, 'publish' );

        if ( is_wp_error( $form_id ) ) {
            return $form_id;
        }

        return rest_ensure_response( array(
            'success' => true,
            'form_id' => $form_id,
            'message' => 'Form conjured successfully!'
        ) );
    }

    public function get_mappings( WP_REST_Request $request ) {
        $mappings = get_option( 'questbook_form_mappings', array() );
        return rest_ensure_response( $mappings );
    }

    public function save_mappings( WP_REST_Request $request ) {
        $params = $request->get_json_params() ?: $request->get_body_params();
        if ( ! is_array( $params ) ) {
            return new WP_Error( 'invalid_data', 'Invalid mappings data provided.', array( 'status' => 400 ) );
        }

        // Sanitize mapping arrays
        $sanitized_mappings = array();
        foreach ( $params as $form_id => $mapping ) {
            $sanitized_mappings[ absint( $form_id ) ] = array(
                'enabled'       => isset( $mapping['enabled'] ) ? (bool) $mapping['enabled'] : false,
                'useUnverified' => isset( $mapping['useUnverified'] ) ? (bool) $mapping['useUnverified'] : true,
                'fields'        => array(),
            );
            
            if ( isset( $mapping['fields'] ) && is_array( $mapping['fields'] ) ) {
                foreach ( $mapping['fields'] as $key => $val ) {
                    $sanitized_mappings[ absint( $form_id ) ]['fields'][ sanitize_text_field( $key ) ] = sanitize_text_field( $val );
                }
            }
        }

        update_option( 'questbook_form_mappings', $sanitized_mappings );
        
        return rest_ensure_response( array( 'success' => true ) );
    }

    public function check_permission() {
        return true;
    }

    public function get_forms( WP_REST_Request $request ) {
        if ( ! class_exists( 'Forminator_API' ) ) {
            return new WP_Error( 'no_forminator', 'Forminator API not found', array( 'status' => 500 ) );
        }

        $forms = Forminator_API::get_forms( null, 1, 100 );
        $polls = Forminator_API::get_polls( null, 1, 100 );
        $quizzes = Forminator_API::get_quizzes( null, 1, 100 );

        $normalized = array();

        if ( ! is_wp_error( $forms ) && is_array( $forms ) ) {
            foreach ( $forms as $form ) {
                $normalized[] = $this->normalize_module( $form, 'form' );
            }
        }

        if ( ! is_wp_error( $polls ) && is_array( $polls ) ) {
            foreach ( $polls as $poll ) {
                $normalized[] = $this->normalize_module( $poll, 'poll' );
            }
        }

        if ( ! is_wp_error( $quizzes ) && is_array( $quizzes ) ) {
            foreach ( $quizzes as $quiz ) {
                $normalized[] = $this->normalize_module( $quiz, 'quiz' );
            }
        }

        return rest_ensure_response( $normalized );
    }

    public function get_submissions( WP_REST_Request $request ) {
        $form_id = $request->get_param( 'id' );

        if ( ! class_exists( 'Forminator_API' ) ) {
            return new WP_Error( 'no_forminator', 'Forminator API not found', array( 'status' => 500 ) );
        }

        $entries = Forminator_API::get_entries( $form_id );

        if ( is_wp_error( $entries ) ) {
            return $entries;
        }

        $formatted = array();
        foreach ( $entries as $entry ) {
            $meta = $entry->meta_data ?? array();

            $email = '';
            foreach ( $meta as $key => $val ) {
                if ( strpos( $key, 'email' ) !== false && isset( $val['value'] ) ) {
                    $email = $val['value'];
                    break;
                }
            }

            $crm_status = '';
            if ( $email && class_exists( 'Xophz_Compass_Quests_WPMUDEV' ) ) {
                $contact = $this->find_questbook_contact( $email );
                if ( $contact ) {
                    $crm_status = get_post_meta( $contact, '_qb_lead_status', true ) ?: 'New';
                }
            }

            $formatted[] = array(
                'id'        => $entry->entry_id,
                'email'     => $email,
                'date'      => $entry->date_created ?? '',
                'crmStatus' => $crm_status,
            );
        }

        return rest_ensure_response( $formatted );
    }

    public function submit_form( WP_REST_Request $request ) {
        $id = $request->get_param( 'id' );
        $params = $request->get_json_params() ?: $request->get_body_params();

        if ( ! class_exists( 'Forminator_API' ) ) {
            return new WP_Error( 'no_forminator', 'Forminator API not found', array( 'status' => 500 ) );
        }

        $entry_data = array();
        foreach ( $params as $key => $value ) {
            $entry_data[] = array(
                'name'  => sanitize_text_field( $key ),
                'value' => sanitize_text_field( $value ),
            );
        }

        $result = Forminator_API::add_form_entry( $id, $entry_data );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return rest_ensure_response( array( 'success' => true, 'entry_id' => $result ) );
    }

    private function normalize_module( $module, $type ) {
        $id = is_object( $module ) ? $module->id : ( $module['id'] ?? 0 );
        $name = is_object( $module ) ? ( $module->name ?? $module->settings['formName'] ?? '' ) : ( $module['name'] ?? '' );
        $status = is_object( $module ) ? ( $module->status ?? 'draft' ) : ( $module['status'] ?? 'draft' );

        $entries = 0;
        if ( class_exists( 'Forminator_Form_Entry_Model' ) ) {
            $entries = Forminator_Form_Entry_Model::count_entries( $id );
        }

        return array(
            'id'      => $id,
            'name'    => $name,
            'type'    => $type,
            'status'  => $status,
            'entries' => $entries,
            'views'   => 0,
        );
    }

    public function get_form_fields( WP_REST_Request $request ) {
        if ( ! class_exists( 'Forminator_API' ) ) {
            return new WP_Error( 'no_forminator', 'Forminator API not found', array( 'status' => 500 ) );
        }

        $form_id = absint( $request->get_param( 'id' ) );
        $wrappers = Forminator_API::get_form_wrappers( $form_id );

        $fields = array();

        if ( ! is_wp_error( $wrappers ) && is_array( $wrappers ) ) {
            foreach ( $wrappers as $wrapper ) {
                $wrapper_fields = isset( $wrapper['fields'] ) ? $wrapper['fields'] : array();
                foreach ( $wrapper_fields as $raw ) {
                    $element = array(
                        'type'     => $raw['type'] ?? 'text',
                        'label'    => $raw['field_label'] ?? '',
                        'name'     => $raw['element_id'] ?? '',
                        'required' => isset( $raw['required'] ) ? filter_var( $raw['required'], FILTER_VALIDATE_BOOLEAN ) : false,
                    );

                    if ( ! empty( $raw['options'] ) && is_array( $raw['options'] ) ) {
                        $element['options'] = array_map( function( $o ) {
                            return array(
                                'label' => $o['label'] ?? '',
                                'value' => $o['value'] ?? '',
                            );
                        }, $raw['options'] );
                    }

                    $fields[] = $element;
                }
            }
        }

        $form = Forminator_API::get_form( $form_id );
        $form_name = '';
        if ( ! is_wp_error( $form ) && is_object( $form ) ) {
            $form_name = $form->settings['formName'] ?? ( $form->name ?? '' );
        }

        return rest_ensure_response( array(
            'id'     => $form_id,
            'name'   => $form_name,
            'fields' => $fields,
        ) );
    }

    public function update_form_fields( WP_REST_Request $request ) {
        if ( ! class_exists( 'Forminator_API' ) ) {
            return new WP_Error( 'no_forminator', 'Forminator API not found', array( 'status' => 500 ) );
        }

        $form_id = absint( $request->get_param( 'id' ) );
        $params  = $request->get_json_params() ?: $request->get_body_params();
        $fields  = isset( $params['fields'] ) ? $params['fields'] : array();
        $name    = isset( $params['name'] ) ? sanitize_text_field( $params['name'] ) : '';

        $form = Forminator_API::get_form( $form_id );
        if ( is_wp_error( $form ) ) {
            return $form;
        }

        $settings = is_object( $form ) ? ( $form->settings ?? array() ) : array();
        if ( $name ) {
            $settings['formName'] = $name;
        }

        $wrappers = array();
        foreach ( $fields as $index => $field ) {
            $type  = sanitize_text_field( $field['type'] ?? 'text' );
            $label = sanitize_text_field( $field['label'] ?? 'Field ' . ( $index + 1 ) );
            $slug  = sanitize_text_field( $field['name'] ?? $type . '-' . ( $index + 1 ) );

            $element = array(
                'element_id'  => $slug,
                'id'          => $slug,
                'type'        => $type,
                'field_label' => $label,
                'required'    => isset( $field['required'] ) && $field['required'] ? 'true' : 'false',
                'cols'        => 12,
            );

            if ( ! empty( $field['options'] ) && is_array( $field['options'] ) ) {
                $element['options'] = array_map( function( $o ) {
                    return array(
                        'label'   => sanitize_text_field( $o['label'] ?? '' ),
                        'value'   => sanitize_text_field( $o['value'] ?? '' ),
                        'limit'   => '',
                        'default' => false,
                    );
                }, $field['options'] );
            }

            $wrappers[] = array(
                'wrapper_id' => 'wrapper-' . $slug,
                'fields'     => array( $element ),
            );
        }

        $status = is_object( $form ) ? ( $form->status ?? '' ) : '';
        $result = Forminator_API::update_form( $form_id, $wrappers, $settings, $status );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return rest_ensure_response( array(
            'success' => true,
            'message' => 'Form fields updated.',
        ) );
    }

    private function find_questbook_contact( $email ) {
        $args = array(
            'post_type'   => 'questbook_contact',
            'meta_key'    => '_qb_raw_email',
            'meta_value'  => $email,
            'fields'      => 'ids',
            'numberposts' => 1,
        );
        $posts = get_posts( $args );
        return ! empty( $posts ) ? $posts[0] : false;
    }
}
