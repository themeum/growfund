<?php

namespace Growfund\Hooks\Filters;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Constants\Site\Comment as CommentConstants;
use Growfund\Hooks\BaseHook;

/**
 * Comment Type Filter Hook
 * 
 * Adds support for filtering comments by type in WordPress admin
 * 
 * @since 1.0.0
 */
class CommentTypeFilter extends BaseHook
{
    public function get_name()
    {
        return HookNames::ADMIN_COMMENT_TYPES_DROPDOWN;
    }

    public function get_type()
    {
        return HookTypes::FILTER;
    }

    public function handle(...$args)
    {
        $comment_types = $args[0] ?? [];

        // Add growfund custom comment types to the existing dropdown
        $comment_types[CommentConstants::TYPE_CAMPAIGN_COMMENT] = __('Campaign', 'growfund');
        $comment_types[CommentConstants::TYPE_UPDATE_COMMENT] = __('Campaign Post', 'growfund');

        return $comment_types;
    }
}
