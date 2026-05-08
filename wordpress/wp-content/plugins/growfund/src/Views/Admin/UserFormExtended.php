<?php

namespace Growfund\Views\Admin;

use Growfund\DTO\AddressDTO;
use Growfund\View;

defined( 'ABSPATH' ) || exit;

class UserFormExtended extends View {

    /** @var array */
    public $user_roles;

    /** @var bool */
    public $is_billing_address_same = false;

    /** @var AddressDTO|null */
    public $billing_address;

    /** @var AddressDTO|null */
    public $shipping_address;

    /** @var string */
    public $phone;

    /** @var bool */
    public $is_verified = false;

    /** @var array */
    public $growfund_roles;

    /** @var array */
    public $growfund_countries;

    /** 
     * Enable as admin script
     * @var bool 
     */
    protected $is_admin_script = true;

    protected function get_template_dir() {
        return 'admin';
    }

    protected function enqueue_styles() {
        $script = GROWFUND_RESOURCE_URL . 'assets/js/wp-user-form-extended.js';

        wp_enqueue_script(
            'wp-user-form-extended',
            $script,
            ['growfund-global-js'],
            GROWFUND_VERSION,
            true
        );
    }
}
