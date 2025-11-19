<?php

namespace Growfund\Hooks\Actions;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;
use Growfund\Taxonomies\Category;
use Growfund\Taxonomies\Tag;

class RegisterTaxonomy extends BaseHook
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
        $taxonomies = $this->get_taxonomies();

        foreach ($taxonomies as $taxonomy) {
            (new $taxonomy())->register();
        }
    }

    protected function get_taxonomies()
    {
        return [
            Category::class,
            Tag::class,
        ];
    }
}
