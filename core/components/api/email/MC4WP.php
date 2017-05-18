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
        $this->mc4wp_mailchimp = new MC4WP_MailChimp();
        parent::__construct( $owner, $account_name, $api_key );
    }

    /**
     * @inheritDoc
     */
    public function fetch_subscriber_lists() {
        $lists = $this->mc4wp_mailchimp->get_lists();

        if ( $lists ) {
            foreach ( $lists as $list ) {
                $this->data['lists'][ $list->id ] = array(
                    'list_id'           => $list->id,
                    'name'              => $list->name,
                    'subscribers_count' => $list->subscriber_count,
                );
            }

            $this->data['is_authorized'] = true;
            $this->save_data();
        }

        return 'success';
    }

    /**
     * @inheritDoc
     */
    public function get_account_fields() {
        return array(
            'interest_ids' => array(
                'label' => 'Interest IDs',
            ),
        );
    }

    /**
     * @inheritDoc
     */
    public function get_data_keymap( $keymap = array(), $custom_fields_key = '' ) {
        $custom_fields_key = 'merge_fields';

        $keymap = array(
            'list' => array(
                'list_id'           => 'id',
                'name'              => 'name',
                'subscribers_count' => 'stats.member_count',
            ),
            'subscriber' => array(
                'email'     => 'email_address',
                'name'      => 'merge_fields.FNAME',
                'last_name' => 'merge_fields.LNAME',
            ),
            'subscriber_group' => array(
                'group_id'          => 'id',
                'name'              => 'name',
                'subscribers_count' => 'member_count'
            ),
            'error' => array(
                'error_message' => 'detail',
            ),
        );

        return parent::get_data_keymap( $keymap, $custom_fields_key );
    }

    /**
     * Transforms the comma separated list of interest IDs from the
     * account fields into the format accepted by the MailChimp v3 API
     * and returns it.
     *
     * @return array
     */
    private function _transform_interest_ids_to_provider_format() {
        $interests = array();

        if ( isset( $this->data['interest_ids'] ) ) {
            $interest_ids =
                explode( ',', $this->data['interest_ids'] );

            foreach ( $interest_ids as $id ) {
               if ( !empty( $id ) && ctype_xdigit( $id ) ) {
                   $interests[ $id ] = true;
               }
            }
        }

        return $interests;
    }

    /**
     * @inheritDoc
     */
    public function subscribe( $args, $url = '' ) {
        $list_id = $args['list_id'];

        $args = $this->transform_data_to_provider_format( $args, 'subscriber' );
        $args['ip_signup'] = et_core_get_ip_address();
        $args['status'] = empty( $args['dbl_optin'] ) ? 'pending' : 'subscribed';

        $interests = $this->_transform_interest_ids_to_provider_format();
        if ( !empty( $interests ) ) {
            $args['interests'] = $interests;
        }

        $data = $this->mc4wp_mailchimp->list_subscribe( $list_id, $args['email_address'], $args );

        $result = 'success';

        if ( !is_object( $data ) || empty( $data->id ) ) {
            //$error_code = $this->mc4wp_mailchimp->get_error_code();
            $error_message = $this->mc4wp_mailchimp->get_error_message();
            $result = $error_message;
        }

        return $result;
    }
}
