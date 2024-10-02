<?php
/*
 * Template Name: Propovoice Invoice
 * Description: Template for Invoice Client View
 */

use Ndpv\Cleanup\Style;

add_action( 'wp_enqueue_scripts', [ Style::init(), 'clear_styles_and_scripts' ], 9999 );
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <style type="text/css">
        @media print {
            @page {
                margin: 0;
            }
        }
    </style>
    <div style="display: none;"><?php wp_head(); ?></div>
</head>

<body <?php body_class(); ?>>
    <?php ndpv()->render( 'template/estvoice-template' ); ?>
    <div style="display: none;"><?php wp_footer(); ?></div>
</body>

</html>
