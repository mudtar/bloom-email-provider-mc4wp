<?php
/*
Plugin Name: Bloom Email Provider: MC4WP
*/
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

function bloom_mc4wp_initialize_component( $third_party_components, $group ) {
    include_once __DIR__ . '/core/components/api/email/MC4WP.php';

    $third_party_components['mc4wp'] = new ET_Core_API_Email_MC4WP;

    return $third_party_components;
}
add_filter( 'et_core_get_third_party_components', 'bloom_mc4wp_initialize_component', 10, 2 );
