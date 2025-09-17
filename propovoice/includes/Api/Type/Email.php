<?php

namespace Ndpv\Api\Type;

use Ndpv\Helpers\Fns;
use Ndpv\Traits\Singleton;
use Ndpv\Models\Business;
use WP_Error;

class Email {

	use Singleton;

	private $allowed_file_types = [ 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'gif', 'txt', 'csv' ];
	private $max_file_size = 10485760; // 10MB
	private $rate_limit = 5; // emails per hour

	public function routes() {
		// Email CRUD endpoints
		register_rest_route(
            'ndpv/v1', '/emails/(?P<id>\d+)', [
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_single' ],
				'permission_callback' => [ $this, 'get_per' ],
				'args'                => [
					'id' => [
						'validate_callback' => function ( $param ) {
							return is_numeric( $param );
						},
						'sanitize_callback' => 'absint',
					],
				],
			]
        );

		register_rest_route(
            'ndpv/v1', '/emails' . ndpv()->plain_route(), [
				'methods'             => 'GET',
				'callback'            => [ $this, 'get' ],
				'permission_callback' => [ $this, 'get_per' ],
			]
        );

		register_rest_route(
            'ndpv/v1', '/emails', [
				'methods'             => 'POST',
				'callback'            => [ $this, 'create' ],
				'permission_callback' => [ $this, 'create_per' ],
			]
        );

		register_rest_route(
            'ndpv/v1', '/emails/(?P<id>[0-9,]+)', [
				'methods'             => 'DELETE',
				'callback'            => [ $this, 'delete' ],
				'permission_callback' => [ $this, 'del_per' ],
				'args'                => [
					'id' => [
						'sanitize_callback' => 'sanitize_text_field',
					],
				],
			]
        );

		// SECURE EMAIL SENDING ENDPOINT - FIXED VULNERABILITY
		register_rest_route(
            'ndpv/v1', '/send-email', [
				'methods'             => 'POST',
				'callback'            => [ $this, 'send_email' ],
				'permission_callback' => [ $this, 'send_email_permission' ],
				'args'                => [
					'to'          => [
						'required' => true,
						'type' => 'string',
						'sanitize_callback' => 'sanitize_email',
					],
					'subject'     => [
						'required' => true,
						'type' => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					],
					'message'     => [
						'required' => true,
						'type' => 'string',
						'sanitize_callback' => 'wp_kses_post',
					],
					'postId'      => [
						'required' => true,
						'type' => 'integer',
						'sanitize_callback' => 'absint',
					],
					'attachments' => [
						'required' => false,
						'type' => 'array',
						'default' => [],
					],
					'cc'          => [
						'required' => false,
						'type' => 'string',
						'sanitize_callback' => 'sanitize_email',
					],
					'bcc'         => [
						'required' => false,
						'type' => 'string',
						'sanitize_callback' => 'sanitize_email',
					],
				],
			]
        );

		// Email logs endpoints
		register_rest_route(
            'ndpv/v1', '/email-logs', [
				'methods'             => 'POST',
				'callback'            => [ $this, 'get_email_logs' ],
				'permission_callback' => [ $this, 'get_per' ],
			]
        );

		register_rest_route(
            'ndpv/v1', '/delete-email-logs', [
				'methods'             => 'POST',
				'callback'            => [ $this, 'delete_email_logs' ],
				'permission_callback' => [ $this, 'get_per' ],
			]
        );

		// Custom email template endpoints
		register_rest_route(
            'ndpv/v1', '/save-custom-email', [
				'methods'             => 'POST',
				'callback'            => [ $this, 'save_custom_email' ],
				'permission_callback' => [ $this, 'get_per' ],
			]
        );

		register_rest_route(
            'ndpv/v1', '/custom-email-templates', [
				'methods'             => 'POST',
				'callback'            => [ $this, 'get_custom_email' ],
				'permission_callback' => [ $this, 'get_per' ],
			]
        );

		register_rest_route(
            'ndpv/v1', '/delete-custom-email-template', [
				'methods'             => 'POST',
				'callback'            => [ $this, 'delete_custom_email_template' ],
				'permission_callback' => [ $this, 'get_per' ],
			]
        );
	}

	/**
	 * SECURE EMAIL SENDING METHOD - FIXES CVE-2025-8422
	 */
	public function send_email( $request ) {
		// Authentication & Authorization
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( [ 'message' => 'Authentication required' ], 401 );
		}

		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'ndpv_core' ) ) {
			wp_send_json_error( [ 'message' => 'Insufficient permissions' ], 403 );
		}

		// CSRF Protection
		$nonce = $request->get_header( 'X-WP-Nonce' );
		if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			wp_send_json_error( [ 'message' => 'Security check failed' ], 403 );
		}

		// Rate Limiting
		if ( ! $this->check_rate_limit() ) {
			wp_send_json_error( [ 'message' => 'Rate limit exceeded' ], 429 );
		}

		// Input Validation
		$params = $this->validate_email_params( $request->get_params() );
		if ( is_wp_error( $params ) ) {
			wp_send_json_error( [ 'message' => $params->get_error_message() ], 400 );
		}

		// Process Attachments Securely
		$attachments = $this->process_attachments( $params['attachments'] );
		if ( is_wp_error( $attachments ) ) {
			wp_send_json_error( [ 'message' => $attachments->get_error_message() ], 400 );
		}

		// Send Email
		$result = $this->send_wp_mail( $params, $attachments );

		if ( $result ) {
			$this->save_sent_message_to_db(
                $params['postId'], [
					'id'          => time(),
					'subject'     => $params['subject'],
					'content'     => $params['message'],
					'attachments' => array_map( 'basename', $attachments ),
					'sent_at'     => current_time( 'mysql' ),
					'sent_by'     => get_current_user_id(),
				]
            );
			wp_send_json_success( [ 'message' => 'Email sent successfully' ] );
		} else {
			wp_send_json_error( [ 'message' => 'Failed to send email' ], 500 );
		}
	}

	/**
	 * Permission callback for send_email endpoint
	 */
	public function send_email_permission( $request ) {
		return is_user_logged_in() &&
				( current_user_can( 'manage_options' ) || current_user_can( 'ndpv_core' ) );
	}

	/**
	 * Validate and sanitize email parameters
	 */
	private function validate_email_params( $params ) {
		$errors = new WP_Error();

		// Validate recipient
		$to = sanitize_email( $params['to'] ?? '' );
		if ( ! is_email( $to ) ) {
			$errors->add( 'invalid_email', 'Invalid recipient email address' );
		}

		// Validate post ID
		$post_id = absint( $params['postId'] ?? 0 );
		if ( ! $post_id || ! get_post_status( $post_id ) ) {
			$errors->add( 'invalid_post', 'Invalid post ID' );
		}

		// Check post permissions
		if ( $post_id && ! current_user_can( 'read_post', $post_id ) ) {
			$errors->add( 'no_post_access', 'No permission to access this post' );
		}

		// Validate optional CC/BCC
		$cc = $bcc = null;
		if ( ! empty( $params['cc'] ) ) {
			$cc = sanitize_email( $params['cc'] );
			if ( ! is_email( $cc ) ) {
				$errors->add( 'invalid_cc', 'Invalid CC email address' );
			}
		}

		if ( ! empty( $params['bcc'] ) ) {
			$bcc = sanitize_email( $params['bcc'] );
			if ( ! is_email( $bcc ) ) {
				$errors->add( 'invalid_bcc', 'Invalid BCC email address' );
			}
		}

		if ( $errors->has_errors() ) {
			return $errors;
		}

		return [
			'to'          => $to,
			'subject'     => sanitize_text_field( $params['subject'] ?? '' ),
			'message'     => wp_kses_post( $params['message'] ?? '' ),
			'postId'      => $post_id,
			'cc'          => $cc,
			'bcc'         => $bcc,
			'attachments' => $params['attachments'] ?? [],
		];
	}

	/**
	 * Process and validate attachments securely - PREVENTS ARBITRARY FILE READ
	 */
	private function process_attachments( $attachments ) {
		if ( empty( $attachments ) || ! is_array( $attachments ) ) {
			return [];
		}

		$upload_dir = wp_upload_dir();
		$allowed_dir = realpath( $upload_dir['basedir'] );
		$processed_attachments = [];

		foreach ( $attachments as $attachment ) {
			$file_path = $this->resolve_attachment_path( $attachment, $allowed_dir );

			if ( is_wp_error( $file_path ) ) {
				return $file_path;
			}

			// Security validations
			if ( ! file_exists( $file_path ) || ! is_readable( $file_path ) ) {
				return new WP_Error( 'file_not_found', 'Attachment file not found or not readable' );
			}

			if ( filesize( $file_path ) > $this->max_file_size ) {
				return new WP_Error( 'file_too_large', 'Attachment file too large (max 10MB)' );
			}

			$extension = strtolower( pathinfo( $file_path, PATHINFO_EXTENSION ) );
			if ( ! in_array( $extension, $this->allowed_file_types, true ) ) {
				return new WP_Error( 'invalid_file_type', 'File type not allowed: ' . $extension );
			}

			$processed_attachments[] = $file_path;
		}

		return $processed_attachments;
	}

	/**
	 * Resolve attachment path securely - CRITICAL SECURITY FIX
	 */
	private function resolve_attachment_path( $attachment, $allowed_dir ) {
		$attachment = sanitize_text_field( urldecode( $attachment ) );

		// CRITICAL: Block path traversal attacks
		if ( strpos( $attachment, '/' ) === 0 || strpos( $attachment, '../' ) !== false ) {
			return new WP_Error( 'invalid_path', 'Invalid attachment path detected' );
		}

		// Handle URL attachments
		if ( filter_var( $attachment, FILTER_VALIDATE_URL ) ) {
			$site_url = get_site_url();
			if ( strpos( $attachment, $site_url ) !== 0 ) {
				return new WP_Error( 'external_url', 'External URLs not allowed' );
			}

			$relative_path = str_replace( $site_url, '', $attachment );
			$relative_path = ltrim( $relative_path, '/' );

			// CRITICAL: Only allow uploads directory
			if ( strpos( $relative_path, 'wp-content/uploads/' ) !== 0 ) {
				return new WP_Error( 'invalid_directory', 'Files must be in uploads directory' );
			}

			$file_path = ABSPATH . $relative_path;
		} else {
			$file_path = $allowed_dir . '/' . sanitize_file_name( basename( $attachment ) );
		}

		// CRITICAL: Validate real path to prevent directory traversal
		$real_path = realpath( $file_path );
		if ( ! $real_path || strpos( $real_path, $allowed_dir ) !== 0 ) {
			return new WP_Error( 'file_access_denied', 'File access denied' );
		}

		return $real_path;
	}

	/**
	 * Send email using wp_mail
	 */
	private function send_wp_mail( $params, $attachments ) {
		$headers = [ 'Content-Type: text/html; charset=UTF-8' ];

		// Add CC/BCC headers
		if ( $params['cc'] ) {
			$headers[] = 'Cc: ' . $params['cc'];
		}
		if ( $params['bcc'] ) {
			$headers[] = 'Bcc: ' . $params['bcc'];
		}

		// Get sender info
		$business = new Business();
		$business_info = $business->info();
		$company_name = sanitize_text_field( $business_info['name'] ?? get_bloginfo( 'name' ) );
		$mail_from = sanitize_email( $business_info['email'] ?? get_option( 'admin_email' ) );

		if ( is_email( $mail_from ) ) {
			$headers[] = 'From: ' . $company_name . ' <' . $mail_from . '>';
		}

		return wp_mail( $params['to'], $params['subject'], $params['message'], $headers, $attachments );
	}

	/**
	 * Check rate limiting
	 */
	private function check_rate_limit() {
		$user_id = get_current_user_id();
		$transient_key = 'ndpv_email_rate_' . $user_id;
		$count = (int) get_transient( $transient_key );

		if ( $count >= $this->rate_limit ) {
			return false;
		}

		set_transient( $transient_key, $count + 1, HOUR_IN_SECONDS );
		return true;
	}


	/**
	 * Get custom email templates
	 */
	public function get_custom_email( $request ) {
		$templates = get_option( 'pv_custom_email_templates', [] );
		return is_array( $templates ) ? array_values( $templates ) : [];
	}

	/**
	 * Save custom email template with validation
	 */
	public function save_custom_email( $request ) {
		$param = $request->get_params();

		// Validate input
		$name = sanitize_text_field( $param['name'] ?? '' );
		$subject = sanitize_text_field( $param['subject'] ?? '' );
		$message = wp_kses_post( $param['message'] ?? '' );

		if ( empty( $name ) || empty( $subject ) || empty( $message ) ) {
			wp_send_json_error( [ 'message' => 'Name, subject, and message are required' ], 400 );
		}

		$new_item = [
			'id'         => time(),
			'name'       => $name,
			'subject'    => $subject,
			'message'    => $message,
			'created_at' => current_time( 'mysql' ),
			'created_by' => get_current_user_id(),
		];

		$existing_array = get_option( 'pv_custom_email_templates', [] );
		if ( ! is_array( $existing_array ) ) {
			$existing_array = [];
		}

		$existing_array[] = $new_item;
		update_option( 'pv_custom_email_templates', $existing_array );

		wp_send_json_success(
            [
				'message' => 'Template saved successfully',
				'id' => $new_item['id'],
			]
        );
	}

	/**
	 * Delete custom email template with validation
	 */
	public function delete_custom_email_template( $request ) {
		$param = $request->get_params();
		$id_to_remove = absint( $param['id'] ?? 0 );

		if ( ! $id_to_remove ) {
			wp_send_json_error( [ 'message' => 'Invalid template ID' ], 400 );
		}

		$existing_array = get_option( 'pv_custom_email_templates', [] );
		if ( ! is_array( $existing_array ) ) {
			wp_send_json_error( [ 'message' => 'No templates found' ], 404 );
		}

		$found = false;
		foreach ( $existing_array as $key => $item ) {
			if ( isset( $item['id'] ) && $item['id'] === $id_to_remove ) {
				unset( $existing_array[ $key ] );
				$found = true;
				break;
			}
		}

		if ( ! $found ) {
			wp_send_json_error( [ 'message' => 'Template not found' ], 404 );
		}

		update_option( 'pv_custom_email_templates', array_values( $existing_array ) );
		wp_send_json_success( [ 'message' => 'Template deleted successfully' ] );
	}

	/**
	 * Enhanced email logs deletion with security checks
	 */
	public function delete_email_logs( $request ) {
		$param = $request->get_params();
		$post_id = absint( $param['postId'] ?? 0 );
		$id_to_remove = absint( $param['id'] ?? 0 );

		if ( ! $post_id || ! $id_to_remove ) {
			wp_send_json_error( [ 'message' => 'Invalid parameters' ], 400 );
		}

		// Check permissions
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error( [ 'message' => 'Insufficient permissions' ], 403 );
		}

		$existing_array = get_post_meta( $post_id, 'email_logs', true );
		if ( ! is_array( $existing_array ) ) {
			wp_send_json_error( [ 'message' => 'No email logs found' ], 404 );
		}

		$found = false;
		foreach ( $existing_array as $key => $item ) {
			if ( isset( $item['id'] ) && $item['id'] === $id_to_remove ) {
				// Clean up attachments if they exist
				if ( isset( $item['attachments']['id'] ) ) {
					wp_delete_attachment( $item['attachments']['id'], true );
				}
				unset( $existing_array[ $key ] );
				$found = true;
				break;
			}
		}

		if ( ! $found ) {
			wp_send_json_error( [ 'message' => 'Email log not found' ], 404 );
		}

		update_post_meta( $post_id, 'email_logs', array_values( $existing_array ) );
		wp_send_json_success( [ 'message' => 'Email log deleted successfully' ] );
	}

	/**
	 * Get email logs with permission check
	 */
	public function get_email_logs( $request ) {
		$param = $request->get_params();
		$post_id = absint( $param['postId'] ?? 0 );

		if ( ! $post_id ) {
			wp_send_json_error( [ 'message' => 'Invalid post ID' ], 400 );
		}

		// Check permissions
		if ( ! current_user_can( 'read_post', $post_id ) ) {
			wp_send_json_error( [ 'message' => 'Insufficient permissions' ], 403 );
		}

		$post_meta = get_post_meta( $post_id, 'email_logs', true );
		return is_array( $post_meta ) ? array_values( $post_meta ) : [];
	}

	/**
	 * Enhanced save sent message with better data structure
	 */
	private function save_sent_message_to_db( $post_id, $new_item ) {
		$existing_array = get_post_meta( $post_id, 'email_logs', true );
		if ( ! is_array( $existing_array ) ) {
			$existing_array = [];
		}

		$existing_array[] = $new_item;
		update_post_meta( $post_id, 'email_logs', $existing_array );
	}

	// Existing methods with minor optimizations...

	public function get( $req ) {
		// Add proper implementation
		wp_send_json_success( [ 'message' => 'Get emails endpoint' ] );
	}

	public function get_single( $req ) {
		$url_params = $req->get_url_params();
		$id = absint( $url_params['id'] ?? 0 );

		if ( ! $id ) {
			wp_send_json_error( [ 'message' => 'Invalid ID' ], 400 );
		}

		wp_send_json_success(
            [
				'id' => $id,
				'message' => 'Get single email',
			]
        );
	}

	public function create( $req ) {
		$param = $req->get_params();
		$type = sanitize_text_field( $param['type'] ?? '' );

		switch ( $type ) {
			case 'sent':
				$this->sent( $param );
				break;
			case 'feedback':
				$this->feedback( $param );
				break;
			case 'dashboard':
				$this->dashboard( $param );
				break;
			default:
				wp_send_json_error( [ 'message' => 'Invalid email type' ], 400 );
		}
	}
	/**
	 * Optimized sent method with enhanced validation and security
	 */
	public function sent( $param ) {
		// Input validation and sanitization
		$validated_data = $this->validate_sent_params( $param );
		if ( is_wp_error( $validated_data ) ) {
			wp_send_json_error( [ 'message' => $validated_data->get_error_message() ], 400 );
		}

		// Extract validated data
		extract( $validated_data );

		// Build organization info
		$org_info = $this->build_organization_info( $org_id );
		if ( is_wp_error( $org_info ) ) {
			wp_send_json_error( [ 'message' => $org_info->get_error_message() ], 400 );
		}

		// Generate secure URL with token validation
		$secure_url = $this->generate_secure_url( $invoice_id, $path );
		if ( is_wp_error( $secure_url ) ) {
			wp_send_json_error( [ 'message' => $secure_url->get_error_message() ], 400 );
		}

		// Build and send email
		$email_result = $this->build_and_send_invoice_email(
            [
				'to' => $mail_to,
				'from' => $mail_from,
				'company_name' => $org_name,
				'subject' => $subject,
				'message' => $message,
				'url' => $secure_url,
				'path' => $path,
				'title' => $title,
				'org_info' => $org_info,
            ]
        );

		if ( $email_result ) {
			// Update invoice status if it's still draft
			$this->update_invoice_status( $invoice_id );

			wp_send_json_success( [ 'message' => 'Invoice email sent successfully' ] );
		} else {
			wp_send_json_error( [ 'message' => 'Failed to send invoice email' ], 500 );
		}
	}

	/**
	 * Optimized feedback method with enhanced security
	 */
	public function feedback( $param ) {
		// Input validation and sanitization
		$validated_data = $this->validate_feedback_params( $param );
		if ( is_wp_error( $validated_data ) ) {
			wp_send_json_error( [ 'message' => $validated_data->get_error_message() ], 400 );
		}

		// Extract validated data
		extract( $validated_data );

		// Verify token security
		if ( ! $this->verify_invoice_token( $invoice_id, $token ) ) {
			wp_send_json_error( [ 'message' => 'Invalid or expired token' ], 403 );
		}

		// Process feedback with validation
		$feedback_result = $this->process_invoice_feedback(
            [
				'invoice_id' => $invoice_id,
				'feedback_type' => $feedback_type,
				'note' => $note,
				'attachment' => $attachment,
            ]
        );

		if ( is_wp_error( $feedback_result ) ) {
			wp_send_json_error( [ 'message' => $feedback_result->get_error_message() ], 400 );
		}

		// Trigger webhooks for feedback events
		$this->trigger_feedback_webhooks( $invoice_id, $feedback_type, $param );

		wp_send_json_success(
            [
				'message' => 'Feedback processed successfully',
				'type' => $feedback_type,
			]
        );
	}

	/**
	 * Optimized dashboard method with enhanced validation
	 */
	public function dashboard( $param ) {
		// Input validation and sanitization
		$validated_data = $this->validate_dashboard_params( $param );
		if ( is_wp_error( $validated_data ) ) {
			wp_send_json_error( [ 'message' => $validated_data->get_error_message() ], 400 );
		}

		// Extract validated data
		extract( $validated_data );

		// Rate limiting for dashboard feedback
		if ( ! $this->check_dashboard_rate_limit() ) {
			wp_send_json_error( [ 'message' => 'Too many feedback submissions. Please try again later.' ], 429 );
		}

		// Build email content
		$email_data = $this->build_dashboard_email_content(
            [
				'feedback_type' => $feedback_type,
				'name' => $name,
				'from' => $from,
				'subject' => $subject,
				'details' => $details,
            ]
        );

		// Send feedback email to PropoVoice support
		$result = $this->send_dashboard_feedback_email( $email_data );

		if ( $result ) {
			wp_send_json_success( [ 'message' => 'Feedback sent successfully' ] );
		} else {
			wp_send_json_error( [ 'message' => 'Failed to send feedback' ], 500 );
		}
	}

	/**
	 * Validate sent method parameters
	 */
	private function validate_sent_params( $param ) {
		$errors = new WP_Error();

		// Validate required fields
		$required_fields = [ 'fromData', 'toData', 'invoice_id', 'subject' ];
		foreach ( $required_fields as $field ) {
			if ( empty( $param[ $field ] ) ) {
				$errors->add( 'missing_field', "Missing required field: {$field}" );
			}
		}

		if ( $errors->has_errors() ) {
			return $errors;
		}

		// Sanitize and validate data
		$org_id = absint( $param['fromData']['id'] ?? 0 );
		$org_name = sanitize_text_field( $param['fromData']['name'] ?? '' );
		$mail_from = sanitize_email( $param['fromData']['email'] ?? '' );
		$mail_to = sanitize_email( $param['toData']['email'] ?? '' );
		$invoice_id = absint( $param['invoice_id'] ?? 0 );
		$path = sanitize_text_field( $param['path'] ?? '' );
		$title = sanitize_text_field( $param['title'] ?? '' );
		$subject = sanitize_text_field( $param['subject'] ?? '' );
		$message = wp_kses_post( $param['msg'] ?? '' );

		// Validate email addresses
		if ( ! is_email( $mail_from ) ) {
			$errors->add( 'invalid_from_email', 'Invalid sender email address' );
		}
		if ( ! is_email( $mail_to ) ) {
			$errors->add( 'invalid_to_email', 'Invalid recipient email address' );
		}

		// Validate invoice exists and user has permission
		if ( ! $invoice_id || ! get_post_status( $invoice_id ) ) {
			$errors->add( 'invalid_invoice', 'Invalid invoice ID' );
		} elseif ( ! current_user_can( 'read_post', $invoice_id ) ) {
			$errors->add( 'no_permission', 'No permission to access this invoice' );
		}

		if ( $errors->has_errors() ) {
			return $errors;
		}

		return compact( 'org_id', 'org_name', 'mail_from', 'mail_to', 'invoice_id', 'path', 'title', 'subject', 'message' );
	}

	/**
	 * Validate feedback method parameters
	 */
	private function validate_feedback_params( $param ) {
		$errors = new WP_Error();

		// Required fields validation
		$invoice_id = absint( $param['invoice_id'] ?? 0 );
		$token = sanitize_text_field( $param['token'] ?? '' );
		$feedback_type = sanitize_text_field( $param['feedback_type'] ?? '' );

		if ( ! $invoice_id ) {
			$errors->add( 'invalid_invoice', 'Invalid invoice ID' );
		}
		if ( ! $token ) {
			$errors->add( 'missing_token', 'Security token is required' );
		}
		if ( ! in_array( $feedback_type, [ 'accept', 'decline' ], true ) ) {
			$errors->add( 'invalid_feedback_type', 'Invalid feedback type' );
		}

		// Optional fields
		$note = wp_kses_post( $param['note'] ?? '' );
		$attachment = sanitize_text_field( $param['attachment'] ?? '' );

		// Validate attachment if provided
		if ( $attachment ) {
			$attachment_validation = $this->validate_feedback_attachment( $attachment );
			if ( is_wp_error( $attachment_validation ) ) {
				return $attachment_validation;
			}
		}

		if ( $errors->has_errors() ) {
			return $errors;
		}

		return compact( 'invoice_id', 'token', 'feedback_type', 'note', 'attachment' );
	}

	/**
	 * Validate dashboard method parameters
	 */
	private function validate_dashboard_params( $param ) {
		$errors = new WP_Error();

		// Get current user for defaults
		$current_user = wp_get_current_user();

		// Validate and sanitize input
		$feedback_type = sanitize_text_field( $param['feedback_type'] ?? '' );
		$name = sanitize_text_field( $param['name'] ?? $current_user->display_name );
		$from = sanitize_email( $param['from'] ?? $current_user->user_email );
		$subject = sanitize_text_field( $param['subject'] ?? '' );
		$details = sanitize_textarea_field( $param['details'] ?? '' );

		// Required field validation
		if ( ! in_array( $feedback_type, [ 'features', 'bug' ], true ) ) {
			$errors->add( 'invalid_feedback_type', 'Invalid feedback type. Must be "features" or "bug"' );
		}
		if ( ! is_email( $from ) ) {
			$errors->add( 'invalid_email', 'Invalid email address' );
		}
		if ( empty( $subject ) ) {
			$errors->add( 'missing_subject', 'Subject is required' );
		}
		if ( empty( $details ) ) {
			$errors->add( 'missing_details', 'Details are required' );
		}

		// Content validation
		if ( strlen( $subject ) > 200 ) {
			$errors->add( 'subject_too_long', 'Subject must be less than 200 characters' );
		}
		if ( strlen( $details ) > 5000 ) {
			$errors->add( 'details_too_long', 'Details must be less than 5000 characters' );
		}

		if ( $errors->has_errors() ) {
			return $errors;
		}

		return compact( 'feedback_type', 'name', 'from', 'subject', 'details' );
	}

	/**
	 * Build organization information securely
	 */
	private function build_organization_info( $org_id ) {
		if ( ! $org_id || ! get_post_status( $org_id ) ) {
			return new WP_Error( 'invalid_org', 'Invalid organization ID' );
		}

		$org_meta = get_post_meta( $org_id );
		$logo_id = absint( $org_meta['logo'][0] ?? 0 );
		$address = sanitize_textarea_field( $org_meta['address'][0] ?? '' );
		$email = sanitize_email( $org_meta['email'][0] ?? '' );
		$mobile = sanitize_text_field( $org_meta['mobile'][0] ?? '' );

		$org_img = '';
		if ( $logo_id ) {
			$logo_src = wp_get_attachment_image_src( $logo_id, 'thumbnail' );
			if ( $logo_src ) {
				$org_img = sprintf(
                    "<img src='%s' alt='%s' style='max-width: 200px !important;max-height: 90px !important;'/>",
                    esc_url( $logo_src[0] ),
                    esc_attr( get_post_field( 'post_title', $org_id ) )
				);
			}
		}

		$org_address = '';
		if ( $address ) {
			$org_address .= esc_html( $address ) . '<br />';
		}
		if ( $email ) {
			$org_address .= esc_html( $email );
		}
		if ( $mobile ) {
			$org_address .= ',<br />' . esc_html( $mobile );
		}

		return [
			'img' => $org_img,
			'address' => $org_address,
		];
	}

	/**
	 * Generate secure URL with token validation
	 */
	private function generate_secure_url( $invoice_id, $path ) {
		if ( ! $invoice_id || ! get_post_status( $invoice_id ) ) {
			return new WP_Error( 'invalid_invoice', 'Invalid invoice for URL generation' );
		}

		$token = get_post_meta( $invoice_id, 'token', true );
		if ( ! $token ) {
			// Generate new token if none exists
			$token = wp_generate_password( 32, false );
			update_post_meta( $invoice_id, 'token', $token );
		}

		return sprintf(
            '%s?id=%d&token=%s',
            esc_url( Fns::client_page_url( $path ) ),
            $invoice_id,
            $token
		);
	}

	/**
	 * Build and send invoice email
	 */
	private function build_and_send_invoice_email( $args ) {
		$template = ndpv()->render( 'email/invoice', [], true );
		if ( ! $template ) {
			return false;
		}

		$subject = Fns::templateVariable( $args['subject'], [] );
		$body = Fns::templateVariable(
            $template, [
				'msg' => $args['message'],
				'url' => $args['url'],
				'path' => $args['path'],
				'title' => $args['title'],
				'org_name' => $args['company_name'],
				'org_img' => $args['org_info']['img'],
				'org_address' => $args['org_info']['address'],
            ]
        );

		$headers = [
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . $args['company_name'] . ' <' . $args['from'] . '>',
		];

		return wp_mail( $args['to'], $subject, $body, $headers, [] );
	}

	/**
	 * Update invoice status from draft to sent
	 */
	private function update_invoice_status( $invoice_id ) {
		$current_status = get_post_meta( $invoice_id, 'status', true );
		if ( $current_status === 'draft' ) {
			update_post_meta( $invoice_id, 'status', 'sent' );
			update_post_meta( $invoice_id, 'sent_at', current_time( 'mysql' ) );
		}
	}

	/**
	 * Verify invoice token securely
	 */
	private function verify_invoice_token( $invoice_id, $given_token ) {
		if ( ! $invoice_id || ! $given_token ) {
			return false;
		}

		$saved_token = get_post_meta( $invoice_id, 'token', true );
		return hash_equals( $saved_token, $given_token );
	}

	/**
	 * Process invoice feedback securely
	 */
	private function process_invoice_feedback( $args ) {
		$invoice_id = $args['invoice_id'];
		$feedback_type = $args['feedback_type'];

		// Update invoice status
		update_post_meta( $invoice_id, 'status', $feedback_type );

// Get remote IP safely
$remote_ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';

// Save feedback data
$feedback_data = [
    'type'       => $feedback_type,
    'note'       => $args['note'],
    'attachment' => $args['attachment'],
    'time'       => current_time( 'timestamp' ),
    'ip'         => $remote_ip,
];

		update_post_meta( $invoice_id, 'feedback', $feedback_data );
		update_post_meta( $invoice_id, 'feedback_at', current_time( 'mysql' ) );

		return true;
	}

	/**
	 * Trigger feedback webhooks
	 */
	private function trigger_feedback_webhooks( $invoice_id, $feedback_type, $param ) {
		$webhook_param = array_merge(
            $param, [
				'post_id' => $invoice_id,
				'id' => $invoice_id,
            ]
        );

		if ( $feedback_type === 'accept' ) {
			do_action( 'ndpvp_webhook', 'est_accept', $webhook_param );
		} elseif ( $feedback_type === 'decline' ) {
			do_action( 'ndpvp_webhook', 'est_reject', $webhook_param );
		}
	}

	/**
	 * Build dashboard email content
	 */
	private function build_dashboard_email_content( $args ) {
		$feedback_title = ( $args['feedback_type'] === 'features' )
		? 'Feature Request: '
		: 'Bug Report: ';

		return [
			'to' => 'support@propovoice.com',
			'subject' => $feedback_title . $args['subject'],
			'body' => $this->format_dashboard_feedback_body( $args ),
			'headers' => [
				'Content-Type: text/html; charset=UTF-8',
				'From: ' . $args['name'] . ' <' . $args['from'] . '>',
			],
		];
	}

	/**
	 * Format dashboard feedback body
	 */
	private function format_dashboard_feedback_body( $args ) {
		$body = '<h3>PropoVoice Dashboard Feedback</h3>';
		$body .= '<p><strong>Type:</strong> ' . ucfirst( $args['feedback_type'] ) . '</p>';
		$body .= '<p><strong>From:</strong> ' . esc_html( $args['name'] ) . ' (' . esc_html( $args['from'] ) . ')</p>';
		$body .= '<p><strong>Subject:</strong> ' . esc_html( $args['subject'] ) . '</p>';
		$body .= '<p><strong>Details:</strong></p>';
		$body .= '<div>' . nl2br( esc_html( $args['details'] ) ) . '</div>';
		$body .= '<hr>';
		$body .= '<p><small>Sent from PropoVoice Dashboard on ' . current_time( 'Y-m-d H:i:s' ) . '</small></p>';

		return $body;
	}

	/**
	 * Send dashboard feedback email
	 */
	private function send_dashboard_feedback_email( $email_data ) {
		return wp_mail(
            $email_data['to'],
            $email_data['subject'],
            $email_data['body'],
            $email_data['headers'],
            []
		);
	}

	/**
	 * Check dashboard rate limiting
	 */
	private function check_dashboard_rate_limit() {
		$user_id = get_current_user_id();
		$transient_key = 'ndpv_dashboard_feedback_' . $user_id;
		$count = (int) get_transient( $transient_key );

		// Allow max 3 dashboard feedback per hour
		if ( $count >= 3 ) {
			return false;
		}

		set_transient( $transient_key, $count + 1, HOUR_IN_SECONDS );
		return true;
	}

	/**
	 * Validate feedback attachment
	 */
	private function validate_feedback_attachment( $attachment ) {
		if ( empty( $attachment ) ) {
			return true;
		}

		// Basic URL validation
		if ( ! filter_var( $attachment, FILTER_VALIDATE_URL ) ) {
			return new WP_Error( 'invalid_attachment', 'Invalid attachment URL' );
		}

		// Check if it's from our site
		$site_url = get_site_url();
		if ( strpos( $attachment, $site_url ) !== 0 ) {
			return new WP_Error( 'external_attachment', 'External attachment URLs not allowed' );
		}

		return true;
	}

	public function delete( $req ) {
		$url_params = $req->get_url_params();
		$ids_string = sanitize_text_field( $url_params['id'] ?? '' );
		$ids = array_map( 'absint', explode( ',', $ids_string ) );

		$deleted = [];
		foreach ( $ids as $id ) {
			if ( $id > 0 && current_user_can( 'delete_post', $id ) ) {
				wp_delete_post( $id );
				$deleted[] = $id;
			}
		}

		wp_send_json_success(
            [
				'deleted' => $deleted,
				'count' => count( $deleted ),
			]
        );
	}

	// Permission methods
	public function get_per() {
		return current_user_can( 'ndpv_email' );
	}

	public function create_per() {
		return current_user_can( 'ndpv_core' );
	}

	public function update_per() {
		return current_user_can( 'ndpv_email' );
	}

	public function del_per() {
		return current_user_can( 'ndpv_email' );
	}
}
