<?php
/*
Plugin Name: Bloom Email Provider: MC4WP
*/
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

function bloom_mc4wp_initialize_component( $third_party_components, $group ) {
    include_once __DIR__ . '/core/components/api/email/MC4WP.php';

    $third_party_components['mc4wp'] = new ET_Core_API_Email_MC4WP( 'bloom', 'default' );

    return $third_party_components;
}
add_filter( 'et_core_get_third_party_components', 'bloom_mc4wp_initialize_component', 10, 2 );

function bloom_mc4wp_initially_authorize_default_account() {
    // Only run this in the administration panel to avoid unnecessary
    // frontend overhead.
    if ( is_admin() ) {
        $provider = $GLOBALS['et_bloom']->providers->get( 'mc4wp', 'default', 'bloom' );

        if ( $provider && !$provider->is_authenticated() ) {
            $provider->data['is_authorized'] = true;
            $provider->save_data();
            do_action( 'bloom_lists_auto_refresh' );
        }
    }
}
add_action( 'init', 'bloom_mc4wp_initially_authorize_default_account' );
