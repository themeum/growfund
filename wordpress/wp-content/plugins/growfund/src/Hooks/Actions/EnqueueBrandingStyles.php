<?php

namespace Growfund\Hooks\Actions;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Core\AppSettings;
use Growfund\Hooks\BaseHook;
use Growfund\Supports\Arr;
use Growfund\Supports\Colors;

/**
 * This hook is responsible for enqueueing the admin script.
 *
 * @since 1.0.0
 */
class EnqueueBrandingStyles extends BaseHook
{
    public function get_name()
    {
        return HookNames::WP_HEAD;
    }

    public function get_type()
    {
        return HookTypes::ACTION;
    }

    public function get_priority()
    {
        return 99;
    }

    public function handle(...$args)
    {
        if (is_admin() || !growfund_is_dashboard_route()) {
            return;
        }

        wp_register_style('growfund-branding-style', '', [], GROWFUND_VERSION);
        wp_enqueue_style('growfund-branding-style');

        $css = sprintf(
            ":root {\n%s\n}",
            $this->generate_branding_variables()
        );

        wp_add_inline_style('growfund-branding-style', $css);
    }

    /**
     * From the branding settings generates the :root variables.
     *
     * @return string
     */
    protected function generate_branding_variables()
    {
        $branding_variable_map = [
            'button_primary_color' => ['--growfund-brand-bg'],
            'button_hover_color' => ['--growfund-brand-bg-hover'],
            'button_text_color' => ['--growfund-brand-foreground'],
            'sidebar_background_color' => ['--growfund-sidebar-bg'],
            'sidebar_foreground_color' => ['--growfund-sidebar-fg', '--growfund-sidebar-alt'],
        ];

        $branding = growfund_settings(AppSettings::BRANDING)->get();
        $refined_colors = !empty($branding['button_primary_color'])
            ? Colors::from($branding['button_primary_color'])->get_aa_compliant_colors()
            : null;

        $colors = [
            'button_primary_color' => !empty($branding['button_primary_color'])
                ? Colors::from($branding['button_primary_color'])->to_hsl_string()
                : null,
            'button_hover_color' => !empty($branding['button_hover_color'])
                ? Colors::from($branding['button_hover_color'])->to_hsl_string()
                : null,
            'button_text_color' => !empty($branding['button_text_color'])
                ? Colors::from($branding['button_text_color'])->to_hsl_string()
                : null,
            'sidebar_background_color' => $refined_colors['background'] ?? null,
            'sidebar_foreground_color' => $refined_colors['foreground'] ?? null,
        ];



        $variables = Arr::make([]);

        foreach ($colors as $key => $color) {
            if (!empty($color)) {
                foreach ($branding_variable_map[$key] as $variable) {
                    $variables->push(
                        sprintf('%s: %s;', $variable, $color)
                    );
                }
            }
        }

        return $variables->join("\r\n");
    }
}
