<?php

namespace Growfund\Hooks\Actions;

defined( 'ABSPATH' ) || exit;

use Growfund\Hooks\BaseHook;
use Growfund\Menu\Analytics;
use Growfund\Menu\Backers;
use Growfund\Menu\Campaigns;
use Growfund\Menu\Donations;
use Growfund\Menu\Donors;
use Growfund\Menu\Fundraisers;
use Growfund\Menu\Pledges;
use Growfund\Menu\Home;
use Growfund\Menu\Root;
use Growfund\Menu\Separator;
use Growfund\Menu\Settings;
use Growfund\Supports\Option;
use Growfund\Constants\AppConfigKeys;
use Growfund\Constants\HookTypes;
use Growfund\Menu\Categories;
use Growfund\Menu\Funds;
use Growfund\Menu\Menu;
use Growfund\Menu\Tags;
use Growfund\Menu\Themes;
use Exception;
use Growfund\Menu\WithdrawalRequest;

class RegisterAdminMenu extends BaseHook
{
    public function get_name()
    {
        return 'admin_menu';
    }

    public function get_type()
    {
        return HookTypes::ACTION;
    }

    public function handle(...$args)
    {
        $menus = $this->get_menus();

        $is_donation_mode = Option::get(AppConfigKeys::IS_DONATION_MODE);

        foreach ($menus as $menu) {
            if (!class_exists($menu) || !is_subclass_of($menu, Menu::class)) {
                /* translators: %s: menu class */
                throw new Exception(sprintf(esc_html__('Menu class %s does not exist.', 'growfund'), esc_html($menu)));
            }

            $menu_instance = new $menu($is_donation_mode);

            if ($menu_instance->is_displayable()) {
                $menu_instance->add();
            }
        }
    }

    protected function get_menus()
    {
        return [
            Root::class,
            Home::class,
            Campaigns::class,
            Donations::class,
            Donors::class,
            Funds::class,
            Pledges::class,
            Backers::class,
            Fundraisers::class,
            Analytics::class,
            Separator::class,
            Categories::class,
            Tags::class,
            Separator::class,
            Themes::class,
            WithdrawalRequest::class,
            Settings::class,
        ];
    }
}
