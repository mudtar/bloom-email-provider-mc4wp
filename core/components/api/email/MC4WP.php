<?php
class ET_Core_API_Email_MC4WP extends ET_Core_API_Email_Provider {
    /**
     * @inheritDoc
     */
    public $name = 'MC4WP';

    /**
     * @inheritDoc
     */
    public $slug = 'mc4wp';

    /**
     * An instance of the mailchimp-for-wp plugin's MailChimp API v3
     * abstraction class.
     *
     * @var MC4WP_MailChimp
     */
    private $mc4wp_mailchimp;

    public function __construct( $owner, $account_name, $api_key = '' ) {
        parent::__construct( $owner, $account_name, $api_key );
        $this->mc4wp_mailchimp = new MC4WP_MailChimp();
    }

    /**
     * @inheritDoc
     */
    public function fetch_subscriber_lists() {
        $lists = $this->mc4wp_mailchimp->get_lists();
        foreach ( $lists as $list ) {
            $this->data['lists'][ $list->id ] = array(
                'list_id'           => $list->id,
                'name'              => $list->name,
                'subscribers_count' => $list->subscriber_count,
            );
        }

        return 'success';
    }

    /**
     * @inheritDoc
     */
    public function get_account_fields() {
        return array();
    }

    /**
     * @inheritDoc
     */
    public function get_data_keymap( $keymap = array(), $custom_fields_key = '' ) {
        return array(
            'api_key' => array(
                'label' => esc_html__( 'API Key', 'et_core' ),
            ),
        );
    }

    /**
     * @inheritDoc
     */
    public function subscribe( $args, $url = '' ) {
        $args = $this->transform_data_to_provider_format( $args, 'subscriber' );
        $args['ip_signup'] = et_core_get_ip_address();
        $args['status'] = empty( $args['dbl_optin'] ) ? 'pending' : 'subscribed';

        $data = $this->mc4wp_mailchimp->list_subscribe( $args['list_id'], $args['email_address'], $args );

        $result = 'success';

        if ( !is_object( $data ) || empty( $data->id ) ) {
            //$error_code = $this->mc4wp_mailchimp->get_error_code();
            $error_message = $this->mc4wp_mailchimp->get_error_message();
            $result = $error_message;
        }

        return $result;
    }
}
