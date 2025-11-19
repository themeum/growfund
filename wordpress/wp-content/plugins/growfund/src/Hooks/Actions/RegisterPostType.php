<?php

namespace Growfund\Hooks\Actions;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;
use Growfund\PostTypes\Campaign;
use Growfund\PostTypes\CampaignPost;
use Growfund\PostTypes\Reward;
use Growfund\PostTypes\RewardItem;

class RegisterPostType extends BaseHook
{
    public function get_name()
    {
        return HookNames::INIT;
    }

    public function get_type()
    {
        return HookTypes::ACTION;
    }

    public function handle(...$args)
    {
        $post_types = $this->get_post_types();

        foreach ($post_types as $post_type) {
            (new $post_type())->register();
        }
    }

    protected function get_post_types()
    {
        return [
            Campaign::class,
            Reward::class,
            RewardItem::class,
            CampaignPost::class,
        ];
    }
}
