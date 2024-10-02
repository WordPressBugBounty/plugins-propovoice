<?php

namespace Ndpv\Ctrl\Install\Type;

class TaxonomyService
{
    public function __construct()
    {
        $this->create_custom_taxonomy();
    }

    public function create_custom_taxonomy()
    {

        $service_taxonomies = [
            'package_status' => [
                [
                    'label' => 'Draft',
                    'bg_color' => '#CBD5E0',
                    'color' => '#2D3748',
                    'type' => 'draft'
                ],
                [
                    'label' => 'Inactive',
                    'bg_color' => '#E0F0EC',
                    'color' => '#4BB99E',
                    'type' => 'inactive'
                ],
                [
                    'label' => 'Active',
                    'bg_color' => '#DDFFDE',
                    'color' => '#0BA24B',
                    'type' => 'active'
                ]
            ],
            'order_status' => [
                [
                    'label' => 'New',
                    'bg_color' => '#FFDEEB',
                    'color' => '#FF267F',
                    'type' => 'new'
                ],
                [
                    'label' => 'Submitted Request',
                    'bg_color' => '#FFF5E9',
                    'color' => '#F68A0B',
                    'type' => 'request'
                ],
                [
                    'label' => 'Processing',
                    'bg_color' => '#E7ECFE',
                    'color' => '#4C6FFF',
                    'type' => 'processing'
                ],
                [
                    'label' => 'Completed',
                    'bg_color' => '#DDFFDE',
                    'color' => '#16B21D',
                    'type' => 'completed'
                ],
                [
                    'label' => 'Cancelled',
                    'bg_color' => '#FFF0F1 ',
                    'color' => '#F16063',
                    'type' => 'cancelled'
                ]
            ],
            'request_status' => [
                [
                    'label' => 'New',
                    'bg_color' => '#FFDEEB',
                    'color' => '#FF267F',
                    'type' => 'new'
                ],
                [
                    'label' => 'Evaluation',
                    'bg_color' => '#FFF5E9',
                    'color' => '#F68A0B',
                    'type' => 'evaluation'
                ],
                [
                    'label' => 'Accepted',
                    'bg_color' => '#E7ECFE',
                    'color' => '#4C6FFF',
                    'type' => 'accepted'
                ],
                [
                    'label' => 'Cancelled',
                    'bg_color' => '#FFF0F1 ',
                    'color' => '#F16063',
                    'type' => 'cancelled'
                ]
            ]
        ];

        foreach ($service_taxonomies as $key => $taxonomies) {
            foreach ($taxonomies as $taxonomy) {
                $term_id = wp_insert_term(
                    $taxonomy['label'], // the term
                    'ndpv_' . $key // the taxonomy
                );

                if ( !is_wp_error($term_id) ) {
                    update_term_meta($term_id['term_id'], 'tax_pos', $term_id['term_id']);

                    if (isset($taxonomy['bg_color']) && $taxonomy['bg_color']) {
                        update_term_meta($term_id['term_id'], 'bg_color', $taxonomy['bg_color']);
                    }
                    if (isset($taxonomy['color']) && $taxonomy['color']) {
                        update_term_meta($term_id['term_id'], 'color', $taxonomy['color']);
                    }
                    if (isset($taxonomy['type']) && $taxonomy['type']) {
                        update_term_meta($term_id['term_id'], 'type', $taxonomy['type']);
                    }

                }
            }
        }
    }
}
