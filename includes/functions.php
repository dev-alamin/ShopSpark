<?php
/**
 * Safely allows SVG HTML markup with limited tags and attributes.
 *
 * @param string $html The SVG HTML string to sanitize.
 * @return string Sanitized SVG HTML.
 */
function shopspark_sanitize_svg_html( $svg ) {
    $kses_defaults = wp_kses_allowed_html( 'post' );

    $svg_args = array(
        'svg'   => array(
            'class'           => true,
            'aria-hidden'     => true,
            'aria-labelledby' => true,
            'role'            => true,
            'xmlns'           => true,
            'width'           => true,
            'height'          => true,
            'viewbox'         => true // <= Must be lower case!
        ),
        'g'     => array( 'fill' => true ),
        'title' => array( 'title' => true ),
        'path'  => array( 
            'd'               => true, 
            'fill'            => true  
        )
    );

    $allowed_tags = array_merge( $kses_defaults, $svg_args );

    return wp_kses( $svg, $allowed_tags );
}

