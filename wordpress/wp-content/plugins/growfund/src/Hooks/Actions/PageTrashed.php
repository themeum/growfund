<?php

namespace Growfund\Hooks\Actions;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\AppConfigKeys;
use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Core\AppSettings;
use Growfund\Hooks\BaseHook;
use Growfund\Supports\Option;
use Growfund\Supports\PostMeta;

class PageTrashed extends BaseHook
{
    public function get_name()
    {
        return HookNames::WP_TRASH_POST;
    }

    public function get_type()
    {
        return HookTypes::ACTION;
    }


    public function handle(...$args)
	{
		if (empty($args)) {
			return;
		}

		$post_id = $args[0];

		if (get_post_type($post_id) !== 'page') {
			return;
		}

		if (!PostMeta::get($post_id, 'is_growfund_page')) {
			return;
		}

        $page_settings = growfund_settings(AppSettings::PAGES)->get();

		if (empty($page_settings)) {
			return;
		}
		$updated = false;

		foreach ($page_settings as $page_key => $page_id) {
			if ((int) $page_id === (int) $post_id) {
				$page_settings[$page_key] = null;
				$updated = true;
			}
		}
        if ($updated) {
            Option::update(AppConfigKeys::PAGE, $page_settings);
		}
	}
}
