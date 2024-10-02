<?php
/*
 * Template Name: Propovoice Package
 * Description: Template for Package Client View
 */

use Ndpv\Cleanup\Style;

add_action( 'wp_enqueue_scripts', [ Style::init(), 'clear_styles_and_scripts' ], 100 );
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">

    <div style="display: none;"><?php wp_head(); ?></div>
</head>

<body <?php body_class(); ?>>

    <?php

    $id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : null; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    if ( $id && get_post( $id ) ) {
        $token = isset( $_GET['token'] ) ? sanitize_text_field( $_GET['token'] ) : null; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

        //Check token that send in mail
        $check_permission = false;
        $post_token = get_post_meta( $id, 'token', true );
        if ( $token === $post_token ) {
            $check_permission = true;

            //update status if user not admin
            $update_status = false;
            if ( is_user_logged_in() ) {
                $author_id = (int) get_post_field( 'post_author', $id );
                if ( get_current_user_id() != $author_id ) {
                    $update_status = true;
                }
            } else {
                $update_status = true;
            }

            if ( $update_status ) {
                $status = get_post_meta( $id, 'status', true );
                if ( $status === 'draft' ) {
                    update_post_meta( $id, 'status', 'viewed' );
                }
            }
        }

        if ( is_user_logged_in() && apply_filters( 'ndpv_admin', current_user_can( 'manage_options' ) ) ) {
            $check_permission = true;
        }

        if ( $check_permission ) {
            echo '<div id="ndpv-package"></div>';
        } else {
            ndpv()->render( 'template/partial/403' );
        }
    } else {
        ndpv()->render( 'template/partial/404' );
    }
    ?>
    <div style="display: none;"><?php wp_footer(); ?></div>
</body>

</html>
