<style>

.ndpv .pv-login-content.pv-container {
    padding: 80px 90px 70px 45px;
}

@media screen and (max-width: 992px) {
    .ndpv .pv-login-content.pv-container {
    padding: 40px;
    }
}

.ndpv .pv-login-content.pv-container ::-moz-placeholder {
    color: #4A5568;
}

.ndpv .pv-login-content.pv-container ::placeholder {
    color: #4A5568;
}

.ndpv .pv-login-content {
    width: 480px;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    position: absolute;
}
@media screen and (max-width: 540px) {
    .ndpv .pv-login-content {
    width: 100%;
    }
}

.ndpv .pv-login-content .pv-logo-content {
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    position: absolute;
}

@media screen and (max-width: 992px) {
    .ndpv .pv-login-content .pv-logo-content {
    position: inherit;
    }
}

.ndpv .pv-login-content .pv-login-title {
    font-size: 20px;
    font-weight: 600;
    color: #718096;
    margin-bottom: 30px;
}

@media screen and (max-width: 540px) {
    .ndpv .pv-login-content .pv-login-title {
    font-size: 16px;
    }
}

.ndpv .pv-login-content .pv-logo-content {
    display: inherit;
    text-align: center;
    background-color: #fff;
}

.ndpv .pv-login-content .pv-logo-content strong {
    font-size: 24px;
    display: block;
    color: #2D3748;
    margin-top: 5px;
    left: 0;
}

.ndpv .pv-login-content .pv-logo-content p {
    color: #A0AEC0;
    font-size: 16px;
    margin-top: 15px;
}

.ndpv .pv-login-content .pv-btn {
    width: 100%;
    justify-content: center;
}

.ndpv .pv-login-content input::-moz-placeholder {
    color: #4A5568 !important;
}

.ndpv .pv-login-content input::placeholder {
    color: #4A5568 !important;
}
#wp-submit {
    padding: 15px 20px;
    font-weight: 600;
    border-radius: 8px;
    color: #fff;
    cursor: pointer;
    border: none;
    font-size: 14px;
    display: inline-flex;
    align-items: center;

    background: linear-gradient(180deg, #365DFD 0%, #123DEE 100%) !important;
    transition: all 0.3s ease-out;

    width: 100%;
    justify-content: center;
}
.pv-lost-password {
    color: #718096;
}
</style>
<div class="pv-login-content pv-container pv-form-style-one" style="top: 30%;">
    <div class="" style="float:none;margin:auto;">
    <h4 class="pv-login-title"><?php esc_html_e( 'Please enter your login credentials', 'propovoice' ); ?></h4>
    <?php
        $args = [
            'redirect' => get_permalink( get_the_ID() ),
            'form_id' => 'ndpv-login-form',
            'label_username' => esc_html__( 'Username', 'propovoice' ),
            'label_password' => esc_html__( 'Password', 'propovoice' ),
            'label_remember' => esc_html__( 'Remember Me', 'propovoice' ),
            'label_log_in' => esc_html__( 'Log In', 'propovoice' ),
            'remember' => true,
        ];
        wp_login_form( $args );
        echo '<p class="pv-lost-password"><a href="' . esc_html(wp_lostpassword_url()) . '">' . esc_html__( 'Lost your password?', 'propovoice' ) . '</a></p>';
        if ( isset( $_GET['login'] ) && sanitize_text_field( $_GET['login'] ) === 'failed' ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			echo '<p style="color: red">' . esc_html__( 'You entered wrong credentials', 'propovoice' ) . '</p>';
        }
		?>
    </div>
</div>
