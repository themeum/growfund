<?php

namespace Growfund\Hooks\Filters;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;

/**
 * Filter the media query to only show the user's own media
 * If the user is not an admin, and does not have the upload_files capability,
 * the query will be filtered to only show the user's own media.
 *
 * @since 1.0.0
 */
class AjaxMedia extends BaseHook
{
    public function get_name()
    {
        return HookNames::AJAX_QUERY_ATTACHMENTS_ARGS;
    }

    public function get_type()
    {
        return HookTypes::FILTER;
    }

    public function handle(...$args)
    {
        $query = $args[0];
        $user = growfund_user();


        if ($user->is_admin()) {
            return $query;
        }

        if ($user->can_upload_files()) {
            $query['author'] = $user->get_id();
        }

        return $query;
    }
}
