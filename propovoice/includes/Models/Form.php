<?php

namespace Ndpv\Models;

class Form {

    protected function get_value( $fields, $tag ) {
        $value = '';
        if ( $fields ) {
            foreach ( $fields as $svalue ) {
                if ( $svalue['id'] === $tag ) {
                    $value = ( $svalue['value'] === null ) ? '' : $svalue['value'];
                    break;
                }
            }
        }
        return $value;
    }

    public function contact_form_7() {
        $forms = [];

        $args = [
            'post_type' => 'wpcf7_contact_form',
            'posts_per_page' => -1,
        ];

        $cf7_query = new \WP_Query( $args );

        if ( ! $cf7_query->have_posts() ) {
            return $forms;
        } else {
            while ( $cf7_query->have_posts() ) {
                $cf7_query->the_post();

                global $post;

                $cf7 = \WPCF7_ContactForm::get_instance( $post->ID );

                $get_data = get_option( "ndpv_contact_form_7_{$post->ID}" );
                $form = [
                    'id'     => $post->ID,
                    'active'  => isset( $get_data['active'] ) ? $get_data['active'] : false,
                    'title'  => $post->post_title,
                    'fields' => [],
                ];

                foreach ( $cf7->collect_mail_tags() as $tag ) {
                    $value = $this->get_value( $get_data['fields'], $tag );

                    $form['fields'][] = [
                        'id'    => $tag,
                        'label' => "[{$tag}]",
                        'value' => $value,
                    ];
                }

                $forms[] = $form;
            }
        }
        return $forms;
    }

    public function wpforms() {
        $forms = [];

        $wpforms = wpforms()->form->get();

        foreach ( $wpforms as $wpform ) {
            $get_data = get_option( "ndpv_wpforms_{$wpform->ID}" );
            $form = [
                'id'     => $wpform->ID,
                'active'  => isset( $get_data['active'] ) ? $get_data['active'] : false,
                'title'  => $wpform->post_title,
                'fields' => [],
            ];

            $wpform_fields = wpforms_get_form_fields( $wpform );

            foreach ( $wpform_fields as $wpform_field ) {
                $value = $this->get_value( $get_data['fields'], $wpform_field['id'] );

                $form['fields'][] = [
                    'id'    => $wpform_field['id'],
                    'label' => $wpform_field['label'],
                    'value' => $value,
                ];
            }

            $forms[] = $form;
        }
        return $forms;
    }

    public function ninja_forms() {
        $forms = [];

        $nf = Ninja_Forms();
        $nf_forms = $nf->form()->get_forms();

        foreach ( $nf_forms as $nform ) {
            $form_id = absint( $nform->get_id() );
            $form_settings = $nform->get_settings();
            $fields = $nf->form( $form_id )->get_fields();

            $get_data = get_option( "ndpv_ninja_forms_{$form_id}" );
            $form = [
                'id'     => $form_id,
                'active'  => isset( $get_data['active'] ) ? $get_data['active'] : false,
                'title'  => $form_settings['title'],
                'fields' => [],
            ];

            foreach ( $fields as $field ) {
                $field_id = $field->get_id();
                $field_settings = $field->get_settings();

                $value = $this->get_value( $get_data['fields'], $field_id );

                $form['fields'][] = [
                    'id'    => $field_id,
                    'label' => $field_settings['label'],
                    'value' => $value,
                ];
            }

            $forms[] = $form;
        }
        return $forms;
    }

    public function gravity_forms() {
        $forms = [];

        $gf_forms = \GFFormsModel::get_forms( true );

        foreach ( $gf_forms as $gf_form ) {
            $form_id = absint( $gf_form->id );
            $get_data = get_option( "ndpv_gravity_forms_{$form_id}" );
            $form = [
                'id'     => $form_id,
                'active'  => isset( $get_data['active'] ) ? $get_data['active'] : false,
                'title'  => $gf_form->title,
                'fields' => [],
            ];

            $form_meta = \GFFormsModel::get_form_meta( $form_id );

            foreach ( $form_meta['fields'] as $field ) {
                $field = \GF_Fields::create( $field );

                if ( empty( $field['inputs'] ) ) {
                    $value = $this->get_value( $get_data['fields'], $field->id );

                    $form['fields'][] = [
                        'id'    => $field->id,
                        'label' => $field->label,
                        'value' => $value,
                    ];
                } else {
                    foreach ( $field['inputs'] as $i => $group_field ) {
                        if ( empty( $group_field['isHidden'] ) ) {
                            $value = '';
                            if ( $get_data['fields'] ) {
                                foreach ( $get_data['fields'] as $svalue ) {
                                    if ( $svalue['id'] === $group_field['id'] ) {
                                        $value = $svalue['value'];
                                        break;
                                    }
                                }
                            }
                            $form['fields'][] = [
                                'id'    => $group_field['id'],
                                'label' => $group_field['label'],
                                'value' => $value,
                            ];
                        }
                    }
                }
            }

            $forms[] = $form;
        }

        return $forms;
    }

    public function fluent_forms() {
        $forms = [];

        $forms = wpFluent()->table( 'fluentform_forms' )->get();

        return array_map(
            function ( $form ) {

                $get_data = get_option( "ndpv_fluent_forms_{$form->id}" );
                return [
                    'id'     => absint( $form->id ),
                    'active' => isset( $get_data['active'] ) ? $get_data['active'] : false,
                    'title'  => $form->title,
                    'fields' => $this->transform_form_fields( json_decode( $form->form_fields, true ), $get_data['fields'] ),
                ];
            },
            $forms
        );

        return $forms;
    }

    public function metform() {

        $results = [];
        $args = [
            'post_type'   => 'metform-form',
            'post_status' => 'publish',
            'numberposts' => -1,
        ];

        $forms = get_posts( $args );
        foreach ( $forms as $form ) {
            $get_data = get_option( "ndpv_metform_{$form->ID}" );
            $fields = [];

            if ( class_exists( '\MetForm\Core\Entries\Action' ) ) {
                $metform_fields = ( new \MetForm\Core\Entries\Action() )->get_fields( $form->ID );
                foreach ( $metform_fields as $mf_key => $mf_value ) {
                    $value = $this->get_value( $get_data['fields'], $mf_key );
                    array_push(
                        $fields, [
							'id' => $mf_key,
							'label' => $mf_value->mf_input_label,
							'value' => $value,
						]
                    );
                }
            }

            $data = [
                'id'     => $form->ID,
                'active' => isset( $get_data['active'] ) ? $get_data['active'] : false,
                'title'  => $form->post_title,
                'fields' => $fields,
            ];
            array_push( $results, $data );
        }
        return $results;
    }

    protected function transform_form_fields( $fields, $setting ) {
        $data = [];

        foreach ( $fields['fields'] as $field ) {
            if ( ! array_key_exists( 'name', $field['attributes'] ) ) {
                continue;
            }

            if ( $this->has_sub_fields( $field ) ) {
                $data = array_merge( $data, $this->get_sub_fields( $field, $setting ) );
                continue;
            }

            $value = $this->get_value( $setting, $field['attributes']['name'] );

            $data[] = [
                'id'    => $field['attributes']['name'],
                'label' => $this->get_label( $field['attributes']['name'] ),
                'value' => $value,
            ];
        }

        return $data;
    }

    /**
     * Check has sub fields
     *
     * @param $field
     *
     * @return bool
     */
    protected function has_sub_fields( $field ) {
        return array_key_exists( 'fields', $field );
    }

    /**
     * Get sub fields
     *
     * @param $field
     *
     * @return array
     */
    protected function get_sub_fields( $field, $setting ) {
        $data = [];

        foreach ( $field['fields'] as $sub_field ) {
            if ( ! array_key_exists( 'name', $sub_field['attributes'] ) ) {
                continue;
            }

            $value = $this->get_value( $setting, $sub_field['attributes']['name'] );

            $data[] = [
                'id' => $sub_field['attributes']['name'],
                'label' => $this->get_label( $sub_field['attributes']['name'] ),
                'value' => $value,
            ];
        }

        return $data;
    }

    /**
     * Format for label
     *
     * @param $label
     *
     * @return string
     */
    protected function get_label( $label ) {
        return ucwords( str_replace( [ '-', '_' ], [ ' ', ' ' ], $label ) );
    }
}
