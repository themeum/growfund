<?php
/**
 * @var Growfund\Views\Components\Campaign\CampaignRewardContent $campaign_reward_content
 */

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Reward\QuantityType;
use Growfund\Supports\Arr;
use Growfund\Supports\Currency;
use Growfund\Supports\Location;

$growfund_countries = Arr::make(Location::get_countries());
$growfund_reward_shipping_costs = Arr::make($campaign_reward_content->reward->shipping_costs ?? []);

$growfund_reward_has_shipping_rest_of_world = !empty($growfund_reward_shipping_costs->find(function ($shipping) {
    return $shipping['location'] === Location::REST_OF_THE_WORLD;
}));

$growfund_reward_shipping_countries = $growfund_countries->filter(function ($country) use ($growfund_reward_shipping_costs)  {
    return $growfund_reward_shipping_costs->some(function($shipping) use ($country) {
        return $country['value'] === $shipping['location'];
	});
})->map(function ($country) { return $country['label'];
})->join(', ');



?>

<div class="growfund-campaign-reward-content <?php echo esc_attr($campaign_reward_content->classname); ?>">
    <div class="growfund-campaign-reward-content-header">
        <h4 class="growfund-campaign-reward-content-title"><?php echo esc_html($campaign_reward_content->reward->title); ?></h4>
        <span class="growfund-campaign-reward-content-price"><?php echo esc_html(Currency::format($campaign_reward_content->reward->amount ?? 0)); ?></span>
    </div>

    <p class="growfund-campaign-reward-content-description">
        <?php echo wp_kses_post($campaign_reward_content->reward->description ?? ''); ?>
    </p>

    <div class="growfund-campaign-reward-content-stats">
        <div class="growfund-campaign-reward-content-stat-item">
            <span class="growfund-campaign-reward-content-stat-icon">
                <?php growfund_echo_safe_html($campaign_reward_content->get_svg_icon('assets/site/icon/location.svg')); ?>
            </span>
            <div class="growfund-campaign-reward-content-stat-text">
                <span class="growfund-campaign-reward-content-stat-label"><?php esc_html_e('Ships to', 'growfund'); ?></span>
                <span class="growfund-campaign-reward-content-stat-count">
                    <?php
                    echo esc_html(
                        $growfund_reward_has_shipping_rest_of_world 
                            ? __('Anywhere in the world', 'growfund') 
                            : $growfund_reward_shipping_countries
                        ); 
					?>
                </span>
            </div>
        </div>

        <div class="growfund-campaign-reward-content-stat-item">
            <span class="growfund-campaign-reward-content-stat-icon">
                <?php growfund_echo_safe_html($campaign_reward_content->get_svg_icon('assets/site/icon/users.svg')); ?>
            </span>
            <div class="growfund-campaign-reward-content-stat-text">
                <span class="growfund-campaign-reward-content-stat-label"><?php esc_html_e('Backers', 'growfund'); ?></span>
                <span class="growfund-campaign-reward-content-stat-count"><?php echo esc_html($campaign_reward_content->reward->number_of_contributors ?? 0); ?></span>
            </div>
        </div>

        <div class="growfund-campaign-reward-content-stat-item">
            <span class="growfund-campaign-reward-content-stat-icon">
                <?php growfund_echo_safe_html($campaign_reward_content->get_svg_icon('assets/site/icon/shipping.svg')); ?>
            </span>
            <div class="growfund-campaign-reward-content-stat-text">
                <span class="growfund-campaign-reward-content-stat-label"><?php esc_html_e('Estimated Delivery', 'growfund'); ?></span>
                <span class="growfund-campaign-reward-content-stat-count" data-growfund-datetime="<?php echo esc_attr($campaign_reward_content->reward->estimated_delivery_date ?? ''); ?>"></span>
            </div>
        </div>

        <div class="growfund-campaign-reward-content-stat-item">
            <span class="growfund-campaign-reward-content-stat-icon">
                <?php growfund_echo_safe_html($campaign_reward_content->get_svg_icon('assets/site/icon/shopping.svg')); ?>
            </span>
            <div class="growfund-campaign-reward-content-stat-text">
                <span class="growfund-campaign-reward-content-stat-label"><?php esc_html_e('Limited Quantity', 'growfund'); ?></span>
                <span class="growfund-campaign-reward-content-stat-count">
                    <?php
                    echo esc_html(
                        $campaign_reward_content->reward->quantity_type === QuantityType::LIMITED 
                            ? ($campaign_reward_content->reward->quantity_limit ?? 0) 
                            : __('Unlimited', 'growfund')
                        );
					?>
                </span>
            </div>
        </div>
    </div>

    <div class="growfund-campaign-reward-content-included-section">
        <h3 class="growfund-campaign-reward-content-included-title">
            <?php 
            $growfund_reward_items_count = count($campaign_reward_content->reward->items ?? []);
            /* translators: %s: number of items */
            echo esc_html(sprintf(_n('%s item included', '%s items included', $growfund_reward_items_count, 'growfund'), $growfund_reward_items_count)); 
            ?>
        </h3>

        <?php foreach ($campaign_reward_content->reward->items as $growfund_reward_item) : ?>

            <div class="growfund-campaign-reward-content-item-pill">
                <img src="<?php echo esc_url($growfund_reward_item->image['url'] ?? growfund_placeholder_image_url()); ?>" alt="<?php echo esc_html($growfund_reward_item->title); ?>">
                <div class="growfund-campaign-reward-content-item-info">
                    <span class="growfund-campaign-reward-content-item-name"><?php echo esc_html($growfund_reward_item->title); ?></span>
                    <span class="growfund-campaign-reward-content-item-quantity">
                        <?php 
                        /* translators: %s: quantity */
                        echo esc_html(sprintf(__('Quantity: %s', 'growfund'), $growfund_reward_item->quantity ?? 0)); 
                        ?>
                    </span>
                </div>
            </div>

        <?php endforeach; ?>
    </div>
</div>
