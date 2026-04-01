<?php
/**
 * @var Growfund\Views\Pages\PledgeCheckoutPage $pledge_checkout_page
 */

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Campaign\AppreciationType;
use Growfund\Constants\PaymentEngine;
use Growfund\Constants\Pledge\DeliveryOption;
use Growfund\Constants\Pledge\PledgeOption;
use Growfund\Constants\Reward\RewardType;
use Growfund\Core\AppSettings;
use Growfund\Sanitizer;
use Growfund\Supports\Currency;
use Growfund\Supports\Location;
use Growfund\Supports\PriceCalculator;
use Growfund\Supports\Utils;
use Growfund\Views\Components\Form\Button;
use Growfund\Views\Components\Form\CheckboxField;
use Growfund\Views\Components\Form\NumberField;
use Growfund\Views\Components\Form\SelectDropdown;
use Growfund\Views\Components\Form\TextField;
use Growfund\Views\Components\PaymentMethodCard;
use Growfund\Views\Components\UI\Badge;
use Growfund\Views\Components\UI\FaqList;
use Growfund\Views\Components\UI\Image;


$growfund_checkout_has_reward = $pledge_checkout_page->campaign->appreciation_type === AppreciationType::GOODIES && !empty($pledge_checkout_page->reward);
$growfund_has_physical_goods = $growfund_checkout_has_reward && $pledge_checkout_page->reward->reward_type !== RewardType::DIGITAL_GOODS;

$growfund_user = growfund_user();
$growfund_shipping_address = $growfund_user->get_meta('shipping_address');
$growfund_billing_address = $growfund_user->get_meta('billing_address');
$growfund_is_billing_address_same = boolval($growfund_user->get_meta('is_billing_address_same') ?? false);

$growfund_checkout_consent = growfund_settings(AppSettings::GENERAL)->get_tnc_text();

$growfund_input_amount = growfund_input_get('amount', 0, Sanitizer::FLOAT);

?>

<form method="POST" action="<?php echo esc_url(Utils::get_checkout_submit_url()); ?>">
    <?php growfund_nonce_field(); ?>
    <input type="hidden" name="campaign_id" value="<?php echo esc_attr($pledge_checkout_page->campaign->id); ?>" />
   
    <input type="hidden" name="pledge_option" value="<?php echo esc_attr($growfund_checkout_has_reward ? PledgeOption::WITH_REWARDS : PledgeOption::WITHOUT_REWARDS); ?>" />
   
    <?php if ($growfund_checkout_has_reward) : ?>
        <input 
            type="hidden" 
            name="reward_id" 
            value="<?php echo esc_attr($pledge_checkout_page->reward->id); ?>" 
            data-pledge-amount="<?php echo esc_attr($pledge_checkout_page->reward->amount); ?>"
            id="growfund_reward_id"
        />
    <?php endif; ?>

    <div class="growfund-pledge-checkout-page">
        <?php 

        $growfund_error_badge = new Badge();
        $growfund_error_badge->variant = 'error';
        $growfund_error_badge->message =  growfund_flash_get_message('checkout_error') 
            ?? growfund_flash_get_message('checkout_form_errors')['campaign_id'][0] 
            ?? growfund_flash_get_message('checkout_form_errors')['reward_id'][0]
            ?? growfund_flash_get_message('checkout_form_errors')['pledge_option'][0] 
            ?? '';

        growfund_render($growfund_error_badge);

        ?>

        <div class="growfund-pledge-checkout-page-left-section">
            <div class="growfund-pledge-checkout-page-left-section-title-wrapper">
                <?php
                $growfund_arrow_button = new Button();
                $growfund_arrow_button->classname = 'growfund-pledge-checkout-page-left-section-arrow-button';
                $growfund_arrow_button->id = "growfund-pledge-checkout-page-left-section-arrow-button";
                $growfund_arrow_button->svg_icon = 'assets/site/icon/arrow-left.svg';
                $growfund_arrow_button->href = growfund_campaign_url($pledge_checkout_page->campaign->slug);
                $growfund_arrow_button->has_link = true;

                growfund_render($growfund_arrow_button);
                ?>
                <span class="growfund-pledge-checkout-page-left-section-title"><?php esc_html_e('Checkout', 'growfund'); ?></span>
            </div>

            <?php if ($growfund_checkout_has_reward) : ?>
                <div class="growfund-pledge-checkout-page-left-section-rewards">
                    <div class="growfund-pledge-checkout-page-left-section-rewards-title">
                        <span class="growfund-pledge-checkout-page-left-section-reward-gift-icon">
                            <?php growfund_echo_safe_html($pledge_checkout_page->get_svg_icon('assets/site/icon/gift.svg')); ?>
                        </span>
                        <span class="growfund-pledge-checkout-page-left-section-reward-title"><?php esc_html_e('Rewards', 'growfund'); ?></span>
                    </div>

                    <div class="growfund-pledge-checkout-page-left-section-reward-wrapper">
                        <div class="growfund-pledge-checkout-page-left-section-reward-image">
                            <?php 
                    
                            $growfund_reward_image = new Image([
                                'src'   => $pledge_checkout_page->reward->image['url'] ?? growfund_placeholder_image_url(),
                                'alt'   => $pledge_checkout_page->reward->title,
                            ]);

                            growfund_render($growfund_reward_image); 
                            ?>
                        </div>

                        <div class="growfund-pledge-checkout-page-left-section-reward-name-wrapper">
                            <span class="growfund-pledge-checkout-page-left-section-reward-name"><?php echo esc_html($pledge_checkout_page->reward->title); ?></span>
                            <span class="growfund-pledge-checkout-page-left-section-reward-amount">
                                <?php echo esc_html(Currency::format($pledge_checkout_page->reward->amount)); ?>
                            </span>
                        </div>
                    </div>

                    <div class="growfund-pledge-checkout-page-left-section-reward-content-included-section">
                        <h3 class="growfund-pledge-checkout-page-left-section-reward-content-included-title">
                            <?php 
                            $growfund_reward_items_count = count($pledge_checkout_page->reward->items ?? []);
                            /* translators: %s: number of items */
                            echo esc_html(sprintf(_n('%s item included', '%s items included', $growfund_reward_items_count, 'growfund'), $growfund_reward_items_count)); 
                            ?>
                        </h3>
                        <div class="growfund-pledge-checkout-page-left-section-reward-content-item-pill-wrapper">
                            <?php foreach ($pledge_checkout_page->reward->items as $growfund_item) : ?>
                                <div class="growfund-pledge-checkout-page-left-section-reward-content-item-pill">
                                    <img src="<?php echo esc_url($growfund_item->image['url'] ?? growfund_placeholder_image_url()); ?>" alt="<?php echo esc_html($growfund_item->title); ?>">
                                    <div class="growfund-pledge-checkout-page-left-section-reward-content-item-info">
                                        <span class="growfund-pledge-checkout-page-left-section-reward-content-item-name"><?php echo esc_html($growfund_item->title); ?></span>
                                        <span class="growfund-pledge-checkout-page-left-section-reward-content-item-quantity">
                                            <?php 
                                            /* translators: %s: quantity */
                                            echo esc_html(sprintf(__('Quantity: %s', 'growfund'), $growfund_item->quantity ?? 0)); 
                                            ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="growfund-pledge-checkout-page-left-section-bonus-support-shipping-fee">
                <div class="growfund-pledge-checkout-page-left-section-bonus-support">
                    <span class="growfund-pledge-checkout-page-left-section-bonus-support-text">
                        <?php $growfund_checkout_has_reward ? esc_html_e('Bonus Support', 'growfund') : esc_html_e('Pledge without Amount', 'growfund'); ?>
                    </span>
                    <span class="growfund-pledge-checkout-page-left-section-bonus-support-amount">
                        <?php
                        $growfund_field = new NumberField();
                        $growfund_field->classname   = 'growfund-pledge-checkout-page-left-section-bonus-support-input';
                        $growfund_field->placeholder = Currency::format(0);
                        $growfund_field->name        = $growfund_checkout_has_reward ? 'bonus_support_amount' : 'amount';
                        $growfund_field->id          = 'growfund_pledge_amount_input';
                        $growfund_field->value       = $growfund_checkout_has_reward ? 0 : $growfund_input_amount;
                        $growfund_field->show_currency = true;
                        $growfund_field->error_msg = growfund_flash_get_message('checkout_form_errors')['amount'][0] ?? '';

                        growfund_render($growfund_field);
                        ?>
                    </span>
                </div>
                <?php if ($growfund_has_physical_goods) : ?>
                    <div 
                        class="growfund-pledge-checkout-page-left-section-bonus-support" 
                        id="checkout_shipping_section" 
                        data-shipping-costs="<?php echo esc_attr(wp_json_encode($pledge_checkout_page->reward->shipping_costs ?? [])); ?>"
                    >
                        <span class="growfund-pledge-checkout-page-left-section-bonus-support-text">
                            <?php esc_html_e('Shipping', 'growfund'); ?>
                        </span>
                        <?php $growfund_shipping_amount = PriceCalculator::calculate_shipping_cost($growfund_shipping_address, $pledge_checkout_page->reward); ?>
                        <span class="growfund-pledge-checkout-page-left-section-bonus-support-amount" id="checkout_shipping_amount" data-shipping-amount="<?php echo esc_attr($growfund_shipping_amount); ?>" >
                            <?php echo esc_html(Currency::format($growfund_shipping_amount)); ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="growfund-pledge-checkout-page-left-section-total-amount-wrapper">
                <span class="growfund-pledge-checkout-page-left-section-total-amount-text"><?php esc_html_e('Total Amount', 'growfund'); ?></span>
                <span class="growfund-pledge-checkout-page-left-section-total-amount" id="checkout_total_amount">
                    <?php 
                        $growfund_pledge_amount = $growfund_checkout_has_reward 
                            ? $pledge_checkout_page->reward->amount
                            : $growfund_input_amount;
                        echo esc_html(Currency::format(PriceCalculator::calculate_pledge_total_amount($growfund_pledge_amount, 0, $growfund_shipping_amount ?? 0))); 
					?>
                </span>
            </div>
        </div>

        <div class="growfund-pledge-checkout-page-right-section">
            <div class="growfund-pledge-checkout-page-right-section-shipping-payment-wrapper">
                <?php if ($growfund_has_physical_goods) : ?>
                    <?php if ($pledge_checkout_page->reward->allow_local_pickup) : ?>
                    <div class="growfund-pledge-checkout-page-right-section-shipping-address-wrapper">
                        <span class="growfund-pledge-checkout-page-right-section-shipping-title"><?php esc_html_e('Delivery Option', 'growfund'); ?></span>
                        <div class="growfund-pledge-checkout-page-right-section-shipping-address">
                            <?php
                            $growfund_select_field = new SelectDropdown();
                            $growfund_select_field->name          = 'delivery_option';
                            $growfund_select_field->id            = 'delivery_option';
                            $growfund_select_field->classname     = 'growfund-pledge-checkout-page-right-section-shipping-country';
                            $growfund_select_field->placeholder   = __('Select option', 'growfund');
                            $growfund_select_field->default_value = DeliveryOption::HOME_DELIVERY;
                            $growfund_select_field->allow_clear   = false;
                            $growfund_select_field->is_filterable = false;
                            $growfund_select_field->options       = [
                                [
                                    'label' => __('Home Delivery', 'growfund'),
                                    'value' => DeliveryOption::HOME_DELIVERY
                                ],
                                [
                                    'label' => __('Local Pickup', 'growfund'),
                                    'value' => DeliveryOption::LOCAL_PICKUP
                                ],
                            ];
                            $growfund_select_field->error_msg = growfund_flash_get_message('checkout_form_errors')['delivery_option'][0] ?? '';

                            growfund_render($growfund_select_field);
                            ?>
                            <div id="local_pickup_instructions" class="growfund-pledge-checkout-page-local-pickup-instruction growfund-rich-text-content growfund-hidden">
                                <?php growfund_echo_safe_html($pledge_checkout_page->reward->local_pickup_instructions); ?>
                            </div>
                        </div>
                    </div>
                    <?php else : ?>
                        <input type="hidden" id="delivery_option" name="delivery_option" value="<?php echo esc_attr(DeliveryOption::HOME_DELIVERY); ?>" />
                    <?php endif; ?>
                    <div class="growfund-pledge-checkout-page-right-section-shipping-address-wrapper" id="shipping_address_section">
                        <span class="growfund-pledge-checkout-page-right-section-shipping-title"><?php esc_html_e('Shipping Address', 'growfund'); ?></span>
                        <div class="growfund-pledge-checkout-page-right-section-shipping-address">
                            <?php
                            $growfund_select_field = new SelectDropdown();
                            $growfund_select_field->name          = 'shipping_address[country]';
                            $growfund_select_field->id            = 'shipping_country';
                            $growfund_select_field->classname     = 'growfund-pledge-checkout-page-right-section-shipping-country';
                            $growfund_select_field->placeholder   = __('Select country', 'growfund');
                            $growfund_select_field->default_value = $growfund_shipping_address['country'] ?? null;
                            $growfund_select_field->allow_clear   = false;
                            $growfund_select_field->options       = Location::get_countries_for_dropdown(false);
                            $growfund_select_field->error_msg = growfund_flash_get_message('checkout_form_errors')['shipping_address.country'][0] ?? '';

                            growfund_render($growfund_select_field);

                            $growfund_address_1 = new TextField();
                            $growfund_address_1->name        = 'shipping_address[address]';
                            $growfund_address_1->value       = $growfund_shipping_address['address'] ?? null;
                            $growfund_address_1->id          = 'shipping_address_1';
                            $growfund_address_1->classname   = 'growfund-pledge-checkout-page-right-section-shipping-address-1';
                            $growfund_address_1->placeholder = __('Address Line 1', 'growfund');
                            $growfund_address_1->error_msg = growfund_flash_get_message('checkout_form_errors')['shipping_address.address'][0] ?? '';

                            growfund_render($growfund_address_1);

                            $growfund_address_2 = new TextField();
                            $growfund_address_2->name        = 'shipping_address[address_2]';
                            $growfund_address_2->value       = $growfund_shipping_address['address_2'] ?? null;
                            $growfund_address_2->id          = 'shipping_address_2';
                            $growfund_address_2->classname   = 'growfund-pledge-checkout-page-right-section-shipping-address-2';
                            $growfund_address_2->placeholder = __('Address Line 2 (optional)', 'growfund');

                            growfund_render($growfund_address_2);
                            ?>

                            <div class="growfund-pledge-checkout-page-right-section-state-city-wrapper">
                                <?php
                                $growfund_state_field = new SelectDropdown();
                                $growfund_state_field->name        = 'shipping_address[state]';
                                $growfund_state_field->default_value = $growfund_shipping_address['state'] ?? null;
                                $growfund_state_field->id          = 'shipping_state';
                                $growfund_state_field->classname       = 'growfund-pledge-checkout-page-right-section-shipping-state';
                                $growfund_state_field->placeholder = __('Select state', 'growfund');
                                $growfund_state_field->allow_clear   = false;
                                $growfund_state_field->options     = Location::get_states_for_dropdown($growfund_shipping_address['country'] ?? '', false);
                                $growfund_state_field->error_msg = growfund_flash_get_message('checkout_form_errors')['shipping_address.state'][0] ?? '';

                                growfund_render($growfund_state_field);

                                $growfund_city_field = new TextField();
                                $growfund_city_field->name        = 'shipping_address[city]';
                                $growfund_city_field->value       = $growfund_shipping_address['city'] ?? null;
                                $growfund_city_field->id          = 'shipping_city';
                                $growfund_city_field->classname       = 'growfund-pledge-checkout-page-right-section-shipping-city';
                                $growfund_city_field->placeholder = __('Select city', 'growfund');
                                $growfund_city_field->error_msg = growfund_flash_get_message('checkout_form_errors')['shipping_address.city'][0] ?? '';

                                growfund_render($growfund_city_field);
                                ?>
                            </div>

                            <?php
                            $growfund_zip_code = new TextField();
                            $growfund_zip_code->name        = 'shipping_address[zip_code]';
                            $growfund_zip_code->value       = $growfund_shipping_address['zip_code'] ?? null;
                            $growfund_zip_code->id          = 'shipping_postal_code';
                            $growfund_zip_code->classname       = 'growfund-pledge-checkout-page-right-section-shipping-postal-code';
                            $growfund_zip_code->placeholder = __('ZIP / Postal Code', 'growfund');
                            $growfund_zip_code->error_msg = growfund_flash_get_message('checkout_form_errors')['shipping_address.zip_code'][0] ?? '';

                            growfund_render($growfund_zip_code);
                            ?>
                        </div>
                        <?php 
                        $growfund_same_as_shipping_checkbox = new CheckboxField();
                        $growfund_same_as_shipping_checkbox->name = 'is_billing_address_same';
                        $growfund_same_as_shipping_checkbox->checked = $growfund_is_billing_address_same;
                        $growfund_same_as_shipping_checkbox->label = __('Same as shipping address', 'growfund');
                        $growfund_same_as_shipping_checkbox->id = 'is_billing_address_same';
                        $growfund_same_as_shipping_checkbox->style = 'margin-top: 16px;';
                        $growfund_same_as_shipping_checkbox->error_msg = growfund_flash_get_message('checkout_form_errors')['is_billing_address_same'][0] ?? '';

                        growfund_render($growfund_same_as_shipping_checkbox);
                        ?>
                    </div>
                <?php endif; ?>
                <div id="checkout_billing_address_section" class="growfund-pledge-checkout-page-right-section-shipping-address-wrapper <?php echo $growfund_has_physical_goods && $growfund_is_billing_address_same ? esc_attr('growfund-hidden') : ''; ?>">
                    <span class="growfund-pledge-checkout-page-right-section-shipping-title"><?php esc_html_e('Billing Address', 'growfund'); ?></span>
                    <div class="growfund-pledge-checkout-page-right-section-shipping-address">
                        <?php
                        $growfund_select_field = new SelectDropdown();
                        $growfund_select_field->name          = 'billing_address[country]';
                        $growfund_select_field->id            = 'billing_country';
                        $growfund_select_field->classname     = 'growfund-pledge-checkout-page-right-section-shipping-country';
                        $growfund_select_field->placeholder   = __('Select country', 'growfund');
                        $growfund_select_field->default_value = $growfund_billing_address['country'] ?? null;
                        $growfund_select_field->allow_clear   = false;
                        $growfund_select_field->options       = Location::get_countries_for_dropdown(false);
                        $growfund_select_field->error_msg = growfund_flash_get_message('checkout_form_errors')['billing_address.country'][0] ?? '';

                        growfund_render($growfund_select_field);

                        $growfund_address_1 = new TextField();
                        $growfund_address_1->name        = 'billing_address[address]';
                        $growfund_address_1->value       = $growfund_billing_address['address'] ?? null;
                        $growfund_address_1->id          = 'billing_address_1';
                        $growfund_address_1->classname   = 'growfund-pledge-checkout-page-right-section-shipping-address-1';
                        $growfund_address_1->placeholder = __('Address Line 1', 'growfund');
                        $growfund_address_1->error_msg = growfund_flash_get_message('checkout_form_errors')['billing_address.address'][0] ?? '';

                        growfund_render($growfund_address_1);

                        $growfund_address_2 = new TextField();
                        $growfund_address_2->name        = 'billing_address[address_2]';
                        $growfund_address_2->value       = $growfund_billing_address['address_2'] ?? null;
                        $growfund_address_2->id          = 'billing_address_2';
                        $growfund_address_2->classname   = 'growfund-pledge-checkout-page-right-section-shipping-address-2';
                        $growfund_address_2->placeholder = __('Address Line 2 (optional)', 'growfund');

                        growfund_render($growfund_address_2);
                        ?>

                        <div class="growfund-pledge-checkout-page-right-section-state-city-wrapper">
                            <?php
                            $growfund_state_field = new SelectDropdown();
                            $growfund_state_field->name        = 'billing_address[state]';
                            $growfund_state_field->default_value = $growfund_billing_address['state'] ?? null;
                            $growfund_state_field->id          = 'billing_state';
                            $growfund_state_field->classname   = 'growfund-pledge-checkout-page-right-section-shipping-state';
                            $growfund_state_field->placeholder = __('Select state', 'growfund');
                            $growfund_state_field->options     = Location::get_states_for_dropdown($growfund_billing_address['country'] ?? null);
                            $growfund_state_field->allow_clear   = false;
                            $growfund_state_field->error_msg = growfund_flash_get_message('checkout_form_errors')['billing_address.state'][0] ?? '';

                            growfund_render($growfund_state_field);

                            $growfund_city_field = new TextField();
                            $growfund_city_field->name        = 'billing_address[city]';
                            $growfund_city_field->value       = $growfund_billing_address['city'] ?? null;
                            $growfund_city_field->id          = 'billing_city';
                            $growfund_city_field->classname   = 'growfund-pledge-checkout-page-right-section-shipping-city';
                            $growfund_city_field->placeholder = __('Select city', 'growfund');
                            $growfund_city_field->error_msg = growfund_flash_get_message('checkout_form_errors')['billing_address.city'][0] ?? '';

                            growfund_render($growfund_city_field);
                            ?>
                        </div>

                        <?php
                        $growfund_zip_code = new TextField();
                        $growfund_zip_code->name        = 'billing_address[zip_code]';
                        $growfund_zip_code->value       = $growfund_billing_address['zip_code'] ?? null;
                        $growfund_zip_code->id          = 'billing_postal_code';
                        $growfund_zip_code->classname       = 'growfund-pledge-checkout-page-right-section-shipping-postal-code';
                        $growfund_zip_code->placeholder = __('ZIP / Postal Code', 'growfund');
                        $growfund_zip_code->error_msg = growfund_flash_get_message('checkout_form_errors')['billing_address.zip_code'][0] ?? '';

                        growfund_render($growfund_zip_code);
                        ?>
                    </div>
                </div>

                <?php
                if (growfund_settings(AppSettings::PAYMENT)->get_payment_engine() === PaymentEngine::NATIVE) {
					$growfund_payment_methods = new PaymentMethodCard();
					$growfund_payment_methods->payment_methods = $pledge_checkout_page->payment_methods;
					$growfund_payment_methods->error_msg = growfund_flash_get_message('checkout_form_errors')['payment_method'][0] ?? '';

					growfund_render($growfund_payment_methods);
                }
                ?>

                <div class="growfund-pledge-checkout-page-right-section-payment-method-agreement">
                    <?php
                    $growfund_terms_agreement = new CheckboxField();
                    $growfund_terms_agreement->name  = 'terms_agreement';
                    $growfund_terms_agreement->id    = 'terms_agreement_checkbox';
                    $growfund_terms_agreement->label = $growfund_checkout_consent ? $growfund_checkout_consent : __('I have read and agree to the terms and conditions above.', 'growfund');
                    $growfund_terms_agreement->checked = false;

                    growfund_render($growfund_terms_agreement);
                    ?>

                    <div class="growfund-pledge-checkout-page-right-section-pledge-button-container">
                        <?php
                        $growfund_pledge_button        = new Button();
                        $growfund_pledge_button->id    = "growfund_checkout_page_pledge_button";
                        $growfund_pledge_button->classname = 'growfund-pledge-checkout-page-right-section-pledge-button growfund-branding-btn';
                        $growfund_pledge_button->label = __('Pledge Now', 'growfund');
                        $growfund_pledge_button->type  = 'submit';
                        $growfund_pledge_button->disabled = true;

                        growfund_render($growfund_pledge_button);
                        ?>
                    </div>

                    <div class="growfund-pledge-checkout-page-right-section-pledge-button-info">
                        <?php 
                        growfund_echo_safe_html(sprintf(
                                /* translators: 1: Terms of Use link, 2: Privacy Policy link */
                                __('By submitting your pledge, you agree to our %1$s and %2$s.', 'growfund'), 
                                '<a href="' . esc_url(growfund_terms_and_conditions_url()) . '">' . __('Terms of Use', 'growfund') . '</a>',
                                '<a href="' . esc_url(growfund_privacy_policy_url()) . '">' . __('Privacy Policy', 'growfund') . '</a>'
                        )); 
                        ?>
                    </div>
                </div>
            </div>
            

            <div class="growfund-pledge-checkout-page-right-section-reward-wrapper">
                <span class="growfund-pledge-checkout-page-left-section-reward-icon">
                    <?php growfund_echo_safe_html($pledge_checkout_page->get_svg_icon('assets/site/icon/reward.svg')); ?>
                </span>
                <div class="growfund-pledge-checkout-page-right-section-reward-section">
                    <span class="growfund-pledge-checkout-page-right-section-reward-section-title">
                        <?php esc_html_e("Rewards aren't guaranteed.", 'growfund'); ?>
                    </span>
                    <p class="growfund-pledge-checkout-page-right-section-reward-section-desc">
                        <?php
                        /* translators: 1: Site name */
                        echo esc_html(sprintf(__("Your pledge will support an ambitious creative project that has yet to be developed. There's a risk that, despite a creator's efforts, your reward may not be fulfilled. We urge you to consider this risk prior to pledging. %s is not responsible for reward fulfillment or refunds.", "growfund"), esc_html(growfund_site_name())));
                        ?>
                        <br/>
                        <br/>
                        <a href="<?php echo esc_url(growfund_terms_and_conditions_url()); ?>">
                            <?php esc_html_e('Learn more about accountability', 'growfund'); ?>
                        </a>
                    </p>
                </div>
            </div>

            <div class="growfund-pledge-checkout-page-right-section-faq-wrapper">
                <span class="growfund-pledge-checkout-page-right-section-faq-section-title"><?php esc_html_e('Frequently Asked Questions', 'growfund'); ?></span>
                <div class="growfund-pledge-checkout-page-right-section-faq-section-list">
                    <?php 
                    $growfund_faq_list = new FaqList();
                    $growfund_faq_list->faqs = $pledge_checkout_page->campaign->faqs ?? [];
                    
                    growfund_render($growfund_faq_list);
                    ?>
                </div>
            </div>
        </div>
    </div>
</form>