<?php

namespace Growfund\Hooks\Actions;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;

class CreateDefaultPages extends BaseHook
{
    public function get_name()
    {
        return HookNames::ONBOARDING_COMPLETED;
    }

    public function get_type()
    {
        return HookTypes::ACTION;
    }

    public function handle(...$args)
    {
        $pages = [
            [
                'title'   => 'Login',
                'slug'    => 'growfund-login',
                'content' => '[growfund_login]',
            ],
            [
                'title'   => 'Register',
                'slug'    => 'growfund-register',
                'content' => '[growfund_register]',
            ],
            [
                'title'   => 'Become a Fundraiser',
                'slug'    => 'growfund-register-fundraiser',
                'content' => '[growfund_register user_type="fundraiser"]',
            ],
            [
                'title'   => 'Campaigns',
                'slug'    => 'growfund-campaigns',
                'content' => '[growfund_campaigns]',
            ],
            [
                'title'   => 'Checkout',
                'slug'    => 'growfund-checkout',
                'content' => '[growfund_checkout]',
            ],
        ];

        foreach ($pages as $page) {
            wp_insert_post(
                [
                    'post_title' => $page['title'],
                    'post_content' => $page['content'],
                    'post_status' => 'publish',
                    'post_type' => 'page',
                    'post_name' => $page['slug'],
                ]
            );
        }
    }
}
