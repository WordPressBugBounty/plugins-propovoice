<?php

    $id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : null; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
if ( $id && get_post( $id ) ) {
	$token = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : null; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

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
		echo '<div id="ndpv-invoice"></div>';
	} else {
		ndpv()->render( 'template/partial/403' );
	}
} else {
	ndpv()->render( 'template/partial/404' );
}
