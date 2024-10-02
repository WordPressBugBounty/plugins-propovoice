<?php

namespace Ndpv\Setup\Type;

class Page {


    public function __construct() {
        $this->create_custom_page();
    }

    public function create_custom_page() {
        //Workspace pro
        //Estimate
        //Invoice
        //Proposal

        if ( ! get_page_by_path( 'estimate' ) ) {
            $args = [
                'post_title'    => 'Propovoice Estimate',
                'post_name'     => 'estimate',
                'post_status'   => 'publish',
                'post_author'   => get_current_user_id(),
                'post_type'     => 'page',
            ];
            $id = wp_insert_post( $args );
            add_post_meta( $id, '_wp_page_template', 'estimate-template.php' );
        }

        if ( ! get_page_by_path( 'invoice' ) ) {
            $args = [
                'post_title'    => 'Propovoice Invoice',
                'post_name'     => 'invoice',
                'post_status'   => 'publish',
                'post_author'   => get_current_user_id(),
                'post_type'     => 'page',
            ];
            $id = wp_insert_post( $args );
            add_post_meta( $id, '_wp_page_template', 'invoice-template.php' );
        }

        if ( ! get_page_by_path( 'propovoice-form' ) ) {
            $args = [
                'post_title'    => 'Propovoice Form',
                'post_name'     => 'propovoice-form',
                'post_status'   => 'publish',
                'post_author'   => get_current_user_id(),
                'post_type'     => 'page',
            ];
            $id = wp_insert_post( $args );
            add_post_meta( $id, '_wp_page_template', 'form-template.php' );
        }
    }
}
