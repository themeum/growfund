<?php

defined( 'ABSPATH' ) || exit;

use Growfund\Supports\Location;
use Growfund\Supports\FlashMessage;
use Growfund\Supports\Utils;

$checkout = $checkout ?? null;
$reward_data = $checkout->reward_data ?? null;
$order_summary = $checkout->order_summary ?? null;
$reward_id = $checkout->reward_id ?? null;
$campaign_id = $checkout->campaign_id ?? null;

$campaign_slug = null;
if (isset($campaign_data) && !empty($campaign_data->slug)) {
    $campaign_slug = $campaign_data->slug;
}

$url_amount = 0;

$amount = absint($amount ?? 0);

if (isset($amount) && is_numeric($amount)) {
    $url_amount = floatval(absint($amount));
    if (!$reward_id) {
        $order_summary->reward_price = $url_amount;
        $order_summary->total = $url_amount + $order_summary->bonus_support + $order_summary->shipping;
    }
}

// Get user shipping address from checkout DTO (already processed by controller)
$user_shipping_address = $checkout->user_shipping_address ?? null;

// Get consent text from checkout DTO
$consent_text = !empty($checkout) && !empty($checkout->consent_text) ? $checkout->consent_text : esc_html__('I have read and agree to the terms and conditions above.', 'growfund');

$base_fields = [
    [
        'type' => 'input',
        'data' => [
            'type' => 'hidden',
            'name' => 'checkout_action',
            'value' => 'submit'
        ]
    ],
    [
        'type' => 'input',
        'data' => [
            'type' => 'hidden',
            'name' => 'campaign_id',
            'value' => $campaign_id ?? ''
        ]
    ],
    [
        'type' => 'input',
        'data' => [
            'type' => 'hidden',
            'name' => 'reward_id',
            'value' => $reward_id ?? ''
        ]
    ],
    [
        'type' => 'input',
        'data' => [
            'type' => 'hidden',
            'name' => 'pledge_option',
            'value' => !empty($reward_id) ? 'with-rewards' : 'without-rewards'
        ]
    ],
    [
        'type' => 'input',
        'data' => [
            'type' => 'hidden',
            'name' => 'total_amount',
            'value' => !empty($reward_id) ? ($order_summary->total ?? 0) : ($order_summary->reward_price ?? 0),
            'id' => 'total_amount_input'
        ]
    ],
    [
        'type' => 'input',
        'data' => [
            'type' => 'hidden',
            'name' => 'bonus_support_amount',
            'value' => !empty($reward_id) ? ($order_summary->bonus_support ?? 0) : 0,
            'id' => 'bonus_support_amount_input'
        ]
    ],
    [
        'type' => 'input',
        'data' => [
            'type' => 'hidden',
            'name' => 'shipping_cost',
            'value' => !empty($reward_id) ? ($order_summary->shipping ?? 0) : 0,
            'id' => 'shipping_cost_input'
        ]
    ],
    [
        'type' => 'input',
        'data' => [
            'type' => 'hidden',
            'name' => 'amount',
            'value' => !empty($reward_id) ? ($order_summary->reward_price ?? 0) : $url_amount,
            'id' => 'reward_amount_input'
        ]
    ]
];

$shipping_fields = empty($reward_id) ? [] : [
    [
        'type' => 'group',
        'title' => __('Shipping Address', 'growfund'),
        'wrapper_class' => 'growfund-form-section growfund-shipping-address-section',
        'fields' => [
            [
                'type' => 'dropdown',
                'data' => [
                    'options' => Location::get_countries_for_dropdown(true, __('Select Country', 'growfund')),
                    'name' => 'country',
                    'id' => 'growfund_country',
                    'placeholder' => esc_html__('Select Country', 'growfund'),
                    'required' => false,
                    'variant' => 'gray',
                    'searchable' => true,
                    'searchPlaceholder' => esc_html__('Search countries...', 'growfund'),
                    'data-location-data' => wp_json_encode(Location::get_countries()),
                    'value' => $user_shipping_address['country'] ?? ''
                ]
            ],
            [
                'type' => 'input',
                'data' => [
                    'type' => 'text',
                    'name' => 'address',
                    'placeholder' => esc_html__('Address line 1', 'growfund'),
                    'required' => true,
                    'value' => $user_shipping_address['address'] ?? ''
                ]
            ],
            [
                'type' => 'input',
                'data' => [
                    'type' => 'text',
                    'name' => 'address_2',
                    'placeholder' => esc_html__('Address line 2 (Optional)', 'growfund'),
                    'value' => $user_shipping_address['address_2'] ?? ''
                ]
            ],
            [
                'type' => 'group',
                'wrapper_class' => 'growfund-form-row-half',
                'fields' => [
                    [
                        'type' => 'dropdown',
                        'data' => [
                            'options' => $user_shipping_address && !empty($user_shipping_address['country'])
                                ? Location::get_states_for_dropdown($user_shipping_address['country'], true, __('Select State', 'growfund'))
                                : [
                                    [
                                        'value' => '',
                                        'label' => esc_html__('Select State', 'growfund')
                                    ]
                                ],
                            'name' => 'state',
                            'id' => 'growfund_state',
                            'placeholder' => esc_html__('State', 'growfund'),
                            'required' => false,
                            'variant' => 'gray',
                            'searchable' => true,
                            'searchPlaceholder' => esc_html__('Search states...', 'growfund'),
                            'data-country-dependent' => 'true',
                            'value' => $user_shipping_address['state'] ?? ''
                        ]
                    ],
                    [
                        'type' => 'input',
                        'data' => [
                            'type' => 'text',
                            'name' => 'city',
                            'id' => 'growfund_city',
                            'placeholder' => esc_html__('City', 'growfund'),
                            'required' => true,
                            'variant' => 'gray',
                            'value' => $user_shipping_address['city'] ?? '',
                            'data-user-city' => $user_shipping_address['city'] ?? ''
                        ]
                    ]
                ]
            ],
            [
                'type' => 'input',
                'data' => [
                    'type' => 'text',
                    'name' => 'zip_code',
                    'placeholder' => esc_html__('Zip/Postal Code', 'growfund'),
                    'required' => true,
                    'value' => $user_shipping_address['zip_code'] ?? ''
                ]
            ]
        ]
    ]
];

$payment_fields = [
    [
        'type' => 'group',
        'title' => __('Payment method', 'growfund'),
        'fields' => [
            [
                'type' => 'payment-method-selector',
                'methods' => $payment_methods,
                'selected' => $payment_methods[0]['value'] ?? '',
                'name' => 'payment_method',
                'default_icon' => 'cash-on-delivery'
            ]
        ]
    ],
];

if (!empty($payment_methods)) {
    $payment_fields[] = [
        'type' => 'group',
        'fields' => [
            [
                'type' => 'checkbox',
                'label' => $consent_text,
                'data' => [
                    'type' => 'checkbox',
                    'name' => 'terms_agreement',
                    'value' => '1',
                    'id' => 'terms_agreement_checkbox',
                    'required' => true
                ]
            ]
        ]
    ];
}

$form_fields = array_merge($base_fields, $shipping_fields, $payment_fields);

// FAQ Items - Use campaign FAQs from controller data
$faq_items = [];
if (isset($campaign_data) && !empty($campaign_data->faqs) && is_array($campaign_data->faqs)) {
    $faq_items = $campaign_data->faqs;
}

if (!$is_shortcode) {
    growfund_get_header();
}
?>



<main class="growfund-page-container">
    <div class="growfund-main-container">
        <div class="growfund-reward-wrapper">
            <div class="growfund-checkout-header">
                <div class="growfund-back-button-wrapper">
                    <?php
                    growfund_renderer()->render('site.components.button', [
                        'variant' => 'back',
                        'icon' => 'arrow-left',
                        'ariaLabel' => __('Go back', 'growfund'),
                        'href' => esc_url(growfund_campaign_url($campaign_id))
                    ]);
                    ?>
                </div>
                <h1 class="growfund-section-title">Checkout</h1>
            </div>
            <div class="growfund-reward-column">
                <!-- Reward Section -->
                <?php if ($reward_id && $reward_data) : ?>
                    <?php
                    growfund_renderer()->render('site.components.campaign-reward', [
                        'rewards' => [$reward_data]
                    ]);
                    ?>
                <?php else : ?>
                    <!-- Show empty state when no reward is selected (with or without pledge amount) -->
                    <?php
                    growfund_renderer()->render('site.components.empty-state', [
                        'campaign_slug' => $campaign_slug
                    ]);
                    ?>
                <?php endif; ?>
                <!-- Order Summary -->
                <?php
                growfund_renderer()->render('site.components.order-summary', [
                    'summary' => $order_summary,
                    'show_bonus' => !empty($reward_id),
                    'show_shipping' => !empty($reward_id)
                ]);
                ?>
            </div>
        </div>

        <div class="growfund-sidebar">
            <!-- Status Messages -->
            <?php
            // Handle validation errors from FlashMessage
            $form_errors = [];

            if (FlashMessage::has('validation_errors')) {
                $validation_errors = growfund_flash_get_message('validation_errors');

                if (is_array($validation_errors)) {
                    $form_errors = $validation_errors;
                }
            }
            ?>

            <!-- Form Builder Usage -->
            <div class="growfund-card growfund-form-builder-card">
                <?php $final_error = $form_errors['general'] ?? $form_errors['amount'] ?? $form_errors['reward_id'] ?? $form_errors['campaign_id'] ?? ''; ?>
                <?php if (!empty($final_error)) : ?>
                    <div class="growfund-card"
                        style="display:flex; align-items: center; gap: 8px; font-size: 14px; background-color: #fee2e2; border: 1px solid #fca5a5; color: #D40000; padding: 1rem; margin-bottom: 1rem; border-radius: 0.375rem;">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M8.00094 5.99951V8.66618M8.00094 11.3328H8.0076M14.4876 11.9995L9.15426 2.66617C9.03797 2.46097 8.86933 2.29029 8.66555 2.17154C8.46176 2.0528 8.23012 1.99023 7.99426 1.99023C7.7584 1.99023 7.52677 2.0528 7.32298 2.17154C7.11919 2.29029 6.95055 2.46097 6.83426 2.66617L1.50093 11.9995C1.38338 12.2031 1.32175 12.4341 1.32227 12.6692C1.32279 12.9042 1.38545 13.135 1.50389 13.338C1.62234 13.5411 1.79236 13.7092 1.99673 13.8254C2.20109 13.9415 2.43253 14.0016 2.6676 13.9995H13.3343C13.5682 13.9993 13.7979 13.9375 14.0005 13.8204C14.203 13.7032 14.3711 13.5349 14.4879 13.3322C14.6048 13.1296 14.6663 12.8998 14.6662 12.6658C14.6662 12.4319 14.6046 12.2021 14.4876 11.9995Z"
                                stroke="#D40000" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>

                        <div><?php echo esc_html(is_array($final_error) ? implode(', ', $final_error) : $final_error); ?></div>
                    </div>
                    <?php
                endif;
                ?>

                <?php
                growfund_renderer()->render('site.components.form-builder', [
                    'fields' => $form_fields,
                    'form_attributes' => [
                        'method' => 'POST',
                        'action' => esc_url(Utils::get_checkout_submit_url()),
                        'id' => 'growfund_payment_form'
                    ],
                    'submit_button_text' => !empty($payment_methods) ? __('Pledge', 'growfund') : null,
                    'submit_button_attributes' => [
                        'class' => 'growfund-btn--primary growfund-branding-btn',
                        'id' => 'growfund_pledge_button',
                        'disabled' => true
                    ],
                    'errors' => $form_errors
                ]);
                ?>

                <?php if (!empty($payment_methods)) : ?>
                    <div class="growfund-form-builder-footer-text">
                        <?php
                        /* translators: %s: Site name */
                        printf(esc_html__('By submitting your pledge, you agree to %s\'s Terms of Use, and Privacy Policy, and for our payment processor, Stripe, to charge your payment method.', 'growfund'), esc_html(growfund_site_name()));
                        ?>
                    </div>
                <?php endif; ?>
            </div>
            <!-- Guarantee Section -->
            <div class="growfund-card growfund-guarantee-section">
                <div class="growfund-guarantee-header">
                    <?php
                    growfund_renderer()->render('site.components.icon', [
                        'name' => 'reward',
                        'size' => 'lg'
                    ]);
                    ?>
                    <span
                        class="growfund-guarantee-title"><?php echo esc_html__('Rewards aren\'t guaranteed.', 'growfund'); ?></span>
                </div>
                <div class="growfund-guarantee-text">
                    <?php
                    /* translators: %s: Site name */
                    printf(esc_html__('Your pledge will support an ambitious creative project that has yet to be developed. There\'s a risk that, despite a creator\'s efforts, your reward may not be fulfilled. We urge you to consider this risk prior to pledging. %s is not responsible for reward fulfillment or refunds.', 'growfund'), esc_html(growfund_site_name()));
                    ?>
                </div>
                <a href="#"
                    class="growfund-guarantee-link"><?php echo esc_html__('Learn more about accountability', 'growfund'); ?></a>
            </div>
            <!-- FAQ Section -->
            <div class="growfund-faq-section">
                <h2 class="growfund-faq-section-title"><?php echo esc_html__('Frequently Asked Questions', 'growfund'); ?>
                </h2>
                <?php
                growfund_renderer()->render('site.components.faq', [
                    'faqs' => $faq_items
                ]);
                ?>
            </div>
        </div>
    </div>
</main>

<?php
if (!$is_shortcode) {
    growfund_get_footer();
}
?>