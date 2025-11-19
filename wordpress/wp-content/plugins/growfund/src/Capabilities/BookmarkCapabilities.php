<?php

namespace Growfund\Capabilities;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Capability;
use Growfund\Services\BookmarkService;
use Growfund\Traits\HasConstants;

class BookmarkCapabilities implements Capability
{
    use HasConstants;

    const CREATE        = 'growfund_create_bookmark';
    const READ          = 'growfund_read_bookmarks';
    const EDIT          = 'growfund_edit_bookmark';
    const DELETE        = 'growfund_delete_bookmark';

    protected $bookmark_service;

    public function __construct()
    {
        $this->bookmark_service = new BookmarkService();
    }

    public function handle()
    {
        add_filter('map_meta_cap', [$this, 'filter_capability'], 10, 4);
    }

    public function get_capabilities($role = null)
    {
        return static::get_constant_values();
    }

    public function filter_capability(array $caps, string $cap, int $user_id, array $args): array
    {
        $capability_map = [
            static::READ        => [$this, 'can_read'],
            static::EDIT        => [$this, 'can_edit'],
            static::DELETE      => [$this, 'can_delete'],
        ];

        if (isset($capability_map[$cap]) && isset($args[0])) {
            return call_user_func_array($capability_map[$cap], array_merge([$user_id], $args));
        }

        return $caps;
    }

    protected function can_read(int $user_id, int $author_id): array
    {
        if ((int) $author_id === $user_id) {
            return ['exist'];
        }

        return ['do_not_allow'];
    }

    protected function can_edit(int $user_id, int $author_id): array
    {
        if ((int) $author_id === $user_id) {
            return ['exist'];
        }

        return ['do_not_allow'];
    }

    protected function can_delete(int $user_id, int $author_id): array
    {
        if (empty($author_id)) {
            return [static::DELETE];
        }

        if ((int) $author_id === $user_id) {
            return ['exist'];
        }

        return ['do_not_allow'];
    }
}
