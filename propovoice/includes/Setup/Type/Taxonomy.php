<?php

namespace Ndpv\Setup\Type;

class Taxonomy {

    public function __construct() {
        $this->create_custom_taxonomy();
    }

    public function create_custom_taxonomy() {

        //Workspace pro
        if ( ! get_page_by_path( 'workspace' ) ) {
            $data = [
                'post_type'     => 'ndpv_workspace',
                'post_title'    => 'Workspace',
                'post_name'     => 'workspace',
                'post_status'   => 'publish',
                'post_author'   => get_current_user_id(),
            ];
            $post_id = wp_insert_post( $data );

            if ( ! is_wp_error( $post_id ) ) {
                update_option( 'ndpv_workspace_default', $post_id );
            }
        }

        //deal
        $temp_pipeline = [
            'Deal Pipeline',
        ];
        foreach ( $temp_pipeline as $pipeline ) {
            $pipeline_add = wp_insert_term(
                $pipeline, // the term
                'ndpv_deal_pipeline', // the taxonomy
            );

            if ( ! is_wp_error( $pipeline_add ) ) {
                update_term_meta( $pipeline_add['term_id'], 'tax_pos', $pipeline_add['term_id'] );

                $pipeline_id = isset( $pipeline_add['term_id'] ) ? $pipeline_add['term_id'] : 0;

                $temp_stage = [
                    [
                        'label' => 'Opportunity',
                        'bg_color' => '#FFF0F1',
                        'color' => '#EBA45D',
                        'type' => '',
                    ],
                    [
                        'label' => 'Contacting',
                        'bg_color' => '#E0F0EC',
                        'color' => '#4BB99E',
                        'type' => '',
                    ],
                    [
                        'label' => 'Engaging',
                        'bg_color' => '#F4F2FE',
                        'color' => '#8775EC',
                        'type' => '',
                    ],
                    [
                        'label' => 'Proposing',
                        'bg_color' => '#ECF9FC',
                        'color' => '#33C3E2',
                        'type' => '',
                    ],
                    [
                        'label' => 'Closing Won',
                        'bg_color' => '#DDFFDE',
                        'color' => '#0BA24B',
                        'type' => 'won',
                    ],
                    [
                        'label' => 'Lost',
                        'bg_color' => '#FFF0F1',
                        'color' => '#FF6771',
                        'type' => 'lost',
                    ],
                ];

                foreach ( $temp_stage as $stage ) {
                    $stage_add = wp_insert_term(
                        $stage['label'], //the term
                        'ndpv_deal_stage' //the taxonomy
                    );

                    if ( ! is_wp_error( $stage_add ) ) {
                        $stage_id = isset( $stage_add['term_id'] ) ? $stage_add['term_id'] : 0;
                        add_term_meta( $stage_id, 'deal_pipeline_id', $pipeline_id );
                        update_term_meta( $stage_id, 'tax_pos', $stage_id );

                        if ( $stage['bg_color'] ) {
                            update_term_meta( $stage_id, 'bg_color', $stage['bg_color'] );
                        }
                        if ( $stage['color'] ) {
                            update_term_meta( $stage_id, 'color', $stage['color'] );
                        }
                        if ( $stage['type'] ) {
                            update_term_meta( $stage_id, 'type', $stage['type'] );
                        }
                    }
                }
            }
        }

        $all_taxonomies = [
            'lead_level' => [
                [
                    'label' => 'Hot',
                    'bg_color' => '#FFE8F1',
                    'color' => '#EE0D69',
                    'type' => '',
                ],
                [
                    'label' => 'Warm',
                    'bg_color' => '#FFEED9',
                    'color' => '#FF6B00',
                    'type' => '',
                ],
                [
                    'label' => 'Cold',
                    'bg_color' => '#E7ECFE',
                    'color' => '#4B6EFE',
                    'type' => '',
                ],
            ],
            'lead_source' => [
                [
                    'label' => 'Upwork',
                    'bg_color' => '#6fda44',
                    'color' => '#fff',
                    'type' => '',
                ],
                [
                    'label' => 'Behance',
                    'bg_color' => '#0057ff',
                    'color' => '#fff',
                    'type' => '',
                ],
                [
                    'label' => 'Dribble',
                    'bg_color' => '#ea4c89',
                    'color' => '#fff',
                    'type' => '',
                ],
                [
                    'label' => 'Facebook',
                    'bg_color' => '#4267B2',
                    'color' => '#fff',
                    'type' => '',
                ],
            ],
            'tag' => [
                [
                    'label' => 'Design',
                ],
                [
                    'label' => 'Development',
                ],
                [
                    'label' => 'Assist',
                ],
            ],
            'task_status' => [
                [
                    'label' => 'Todo',
                    'bg_color' => '#FFF0F1',
                    'color' => '#FF6771',
                    'type' => '',
                ],
                [
                    'label' => 'In Progress',
                    'bg_color' => '#ECF9FC',
                    'color' => '#33C3E2',
                    'type' => '',
                ],
                [
                    'label' => 'Done',
                    'bg_color' => '#E0F0EC',
                    'color' => '#4BB99E',
                    'type' => 'done',
                ],
            ],
            'task_type' => [
                [
                    'label' => 'Task',
                    'icon' => 'task',
                ],
                [
                    'label' => 'Email',
                    'icon' => 'mail',
                ],
                [
                    'label' => 'Call',
                    'icon' => 'call',
                ],
                [
                    'label' => 'Meeting',
                    'icon' => 'meeting',
                ],
                [
                    'label' => 'Presentation',
                    'icon' => 'presentation',
                ],
            ],
            'task_priority' => [
                [
                    'label' => 'Low',
                    'bg_color' => '#CBD5E0',
                    'color' => '#2D3748',
                    'type' => '',
                ],
                [
                    'label' => 'Medium',
                    'bg_color' => '#ECF9FC',
                    'color' => '#33C3E2',
                    'type' => '',
                ],
                [
                    'label' => 'High',
                    'bg_color' => '#FFF0F1',
                    'color' => '#FF6771',
                    'type' => '',
                ],
            ],
            'project_status' => [
                [
                    'label' => 'New',
                    'bg_color' => '#F4F2FE',
                    'color' => '#8775EC',
                    'type' => '',
                ],
                [
                    'label' => 'In Progress',
                    'bg_color' => '#ECF9FC',
                    'color' => '#33C3E2',
                    'type' => '',
                ],
                [
                    'label' => 'Done',
                    'bg_color' => '#E0F0EC',
                    'color' => '#4BB99E',
                    'type' => '',
                ],
                [
                    'label' => 'Completed',
                    'bg_color' => '#DDFFDE',
                    'color' => '#0BA24B',
                    'type' => 'completed',
                ],
            ],
            'contact_status' => [
                [
                    'label' => 'Active',
                    'bg_color' => '#E0F0EC',
                    'color' => '#4BB99E',
                    'type' => 'active',
                ],
                [
                    'label' => 'Inactive',
                    'bg_color' => '#EFE7DF',
                    'color' => '#A49485',
                    'type' => 'inactive',
                ],
                [
                    'label' => 'Block',
                    'bg_color' => '#FFF0F1',
                    'color' => '#FF6771',
                    'type' => 'block',
                ],
            ],
            'extra_amount' => [
                [
                    'label' => 'Tax',
                    'extra_amount_type' => 'tax',
                    'val_type' => 'fixed',
                    'show' => true,
                ],
                [
                    'label' => 'Fee',
                    'extra_amount_type' => 'fee',
                    'val_type' => 'fixed',
                    'show' => true,
                ],
                [
                    'label' => 'Discount',
                    'extra_amount_type' => 'discount',
                    'val_type' => 'fixed',
                    'show' => true,
                ],
            ],
        ];

        foreach ( $all_taxonomies as $key => $taxonomies ) {
            foreach ( $taxonomies as $taxonomy ) {
                $term_id = wp_insert_term(
                    $taxonomy['label'], // the term
                    'ndpv_' . $key, // the taxonomy
                );
                if ( ! is_wp_error( $term_id ) ) {
                    update_term_meta( $term_id['term_id'], 'tax_pos', $term_id['term_id'] );

                    if ( isset( $taxonomy['bg_color'] ) && $taxonomy['bg_color'] ) {
                        update_term_meta( $term_id['term_id'], 'bg_color', $taxonomy['bg_color'] );
                    }
                    if ( isset( $taxonomy['color'] ) && $taxonomy['color'] ) {
                        update_term_meta( $term_id['term_id'], 'color', $taxonomy['color'] );
                    }
                    if ( isset( $taxonomy['type'] ) && $taxonomy['type'] ) {
                        update_term_meta( $term_id['term_id'], 'type', $taxonomy['type'] );
                    }

                    //this is for extra amount
                    if ( isset( $taxonomy['extra_amount_type'] ) && $taxonomy['extra_amount_type'] ) {
                        update_term_meta( $term_id['term_id'], 'extra_amount_type', $taxonomy['extra_amount_type'] );
                    }
                    if ( isset( $taxonomy['val_type'] ) && $taxonomy['val_type'] ) {
                        update_term_meta( $term_id['term_id'], 'val_type', $taxonomy['val_type'] );
                    }
                    if ( isset( $taxonomy['show'] ) && $taxonomy['show'] ) {
                        update_term_meta( $term_id['term_id'], 'show', $taxonomy['show'] );
                    }

                    //set icon
                    if ( isset( $taxonomy['icon'] ) && $taxonomy['icon'] ) {
                        $url = ndpv()->get_asset_uri( 'img/task-type/' ) . $taxonomy['icon'] . '.png';
                        $icon_id = $this->custom_taxonomy_image( $url );
                        if ( ! is_wp_error( $icon_id ) ) {
                            update_term_meta( $term_id['term_id'], 'icon', $icon_id );
                        }
                    }
                }
            }
        }
    }

    public function custom_taxonomy_image( $image_url = '', $post_id = false ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';

        // Download the image from the URL
        $tmp = download_url( $image_url );

        // Check if there was an error during download
        if ( is_wp_error( $tmp ) ) {
            return $tmp; // Return the WP_Error object
        }

        // Set variables for storage
        preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $image_url, $matches );
        $file_array['name'] = basename( $matches[0] );
        $file_array['tmp_name'] = $tmp;

        // Handle the sideloading of the image
        $time = current_time( 'mysql' );
        $file = wp_handle_sideload( $file_array, [ 'test_form' => false ], $time );

        // Check if there was an error during sideloading
        if ( isset( $file['error'] ) ) {
            return new \WP_Error( 'upload_error', $file['error'] );
        }

        // Insert the attachment into the media library
        $url = $file['url'];
        $type = $file['type'];
        $file = $file['file'];
        $title = preg_replace( '/\.[^.]+$/', '', basename( $file ) );
        $parent = (int) absint( $post_id ) > 0 ? absint( $post_id ) : 0;

        $attachment = [
            'post_mime_type' => $type,
            'guid' => $url,
            'post_parent' => $parent,
            'post_title' => $title,
            'post_content' => '',
        ];

        $id = wp_insert_attachment( $attachment, $file, $parent );

        // Update attachment metadata
        if ( ! is_wp_error( $id ) ) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
            $data = wp_generate_attachment_metadata( $id, $file );
            wp_update_attachment_metadata( $id, $data );
        }

        return $id;
    }
}
