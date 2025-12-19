<?php

defined( 'ABSPATH' ) || exit;

use Growfund\Supports\FlashMessage;
use Growfund\Supports\Location;
use Growfund\Supports\Utils;

$form_errors = [];

if (FlashMessage::has('validation_errors')) {
    $validation_errors = FlashMessage::get('validation_errors');
    if (is_array($validation_errors)) {
        $form_errors = $validation_errors;
    }
}

if (!$is_shortcode) {
    growfund_get_header();
}

$default_amount = null;
$default_index = 0;
?>

<div class="growfund-donation-checkout growfund-page-container">
    <div class="growfund-donation-checkout__header">
        <div class="growfund-donation-checkout__campaign-info">
            <?php
            if (!empty($campaign_image)) {
                growfund_renderer()
                    ->render('site.components.image', [
                        'src' => $campaign_image,
                        'alt' => $campaign_title,
                        'attributes' => ['class' => 'growfund-donation-checkout__campaign-image']
                    ]);
            }
            ?>
            <div class="growfund-donation-checkout__campaign-text">
                <p><?php esc_html_e('Choose your donation amount for', 'growfund'); ?></p>
                <div class="growfund-donation-checkout__campaign-title"><?php echo esc_html($campaign_title); ?></div>
            </div>
        </div>

        <div class="growfund-donation-checkout__donation-section">
            <div class="growfund-donation-checkout__section-title"><?php esc_html_e('Select a donation amount', 'growfund'); ?></div>
            <?php if (!empty($suggested_options) && is_array($suggested_options)) : ?>
                <div class="growfund-donation-checkout__amount-buttons">
                <?php
                $amount_buttons = [];

                foreach ($suggested_options as $option) {
                    $amount = $option['amount'] ?? 0;
                    
                    if ($amount > 0) {
                        $amount_buttons[] = [
                            'amount' => $amount,
                            'text' => growfund_to_currency($amount),
                            'formatted' => growfund_to_currency($amount),
                            'is_default' => $option['is_default'] ?? false
                        ];
                    }
                }


                foreach ($amount_buttons as $index => $button) {
                    if (isset($button['is_default']) && $button['is_default']) {
                        $default_amount = $button['amount'];
                        $default_index = $index;
                        break;
                    }
                }


                if ($default_amount === null && !empty($amount_buttons)) {
                    $default_amount = $amount_buttons[0]['amount'];
                    $default_index = 0;
                }

                foreach ($amount_buttons as $index => $button) {

                    $is_selected = ($index === $default_index);

                    growfund_renderer()->render('site.components.button', [
                        'text' => $button['text'],
                        'type' => 'button',
                        'variant' => 'amount-selector',
                        'selected' => $is_selected,
                        'attributes' => [
                            'data-amount' => $button['amount'],
                            'data-formatted' => $button['formatted'],
                        ],
                    ]);
                }
                ?>
            </div>
            <?php endif; ?>

            <?php if ($allow_custom_donation) : ?>
                <div class="growfund-donation-checkout__amount-input">
                    <div class="growfund-donation-checkout__amount-input-content" style="cursor: pointer;">
                        <span class="growfund-donation-checkout__amount-label"><?php esc_html('Enter your custom amount'); ?></span>
                        <span class="growfund-donation-checkout__amount-value" id="selectedAmount"><?php echo esc_html(growfund_to_currency($default_amount)); ?></span>
                    </div>
                    <input type="number" id="customAmount" placeholder="<?php esc_attr_e('Enter your custom amount', 'growfund'); ?>" value="<?php echo esc_html($default_amount); ?>"
                        min="<?php echo esc_attr($min_donation_amount ? $min_donation_amount : 1); ?>"
                        max="<?php echo esc_attr($max_donation_amount ? $max_donation_amount : 1000000); ?>"
                        step="0.01" style="display: none;"
                        data-min-amount="<?php echo esc_attr($min_donation_amount ? $min_donation_amount : 1); ?>"
                        data-max-amount="<?php echo esc_attr($max_donation_amount ? $max_donation_amount : 1000000); ?>">

                    <?php if ($min_donation_amount || $max_donation_amount) : ?>
                        <div class="growfund-donation-checkout__amount-range-hint">
                            <small class="growfund-text-muted">
                                <?php 
								if ($min_donation_amount && $max_donation_amount) {
                                    /* translators: 1: min donation amount, 2: max donation amount */
									printf(esc_html__('Amount must be between %1$s and %2$s', 'growfund'), esc_html(growfund_to_currency($min_donation_amount)), esc_html(growfund_to_currency($max_donation_amount)));
								} elseif ($min_donation_amount) {
                                    /* translators: %s: min donation amount */
                                    printf(esc_html__('Minimum amount: %s', 'growfund'), esc_html(growfund_to_currency($min_donation_amount)));
                                } elseif ($max_donation_amount) {
                                    /* translators: %s: max donation amount */
                                    printf(esc_html__('Maximum amount: %s', 'growfund'), esc_html(growfund_to_currency($max_donation_amount)));
                                }
                                ?>
                            </small>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php $final_error = $form_errors['general'] ?? ''; ?>
    <?php if (!empty($final_error)) : ?>
        <div class="growfund-card" style="display:flex; align-items: center; gap: 8px; font-size: 14px; background-color: #fee2e2; border: 1px solid #fca5a5; color: #D40000; padding: 1rem; margin-bottom: 1rem; border-radius: 0.375rem;">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M8.00094 5.99951V8.66618M8.00094 11.3328H8.0076M14.4876 11.9995L9.15426 2.66617C9.03797 2.46097 8.86933 2.29029 8.66555 2.17154C8.46176 2.0528 8.23012 1.99023 7.99426 1.99023C7.7584 1.99023 7.52677 2.0528 7.32298 2.17154C7.11919 2.29029 6.95055 2.46097 6.83426 2.66617L1.50093 11.9995C1.38338 12.2031 1.32175 12.4341 1.32227 12.6692C1.32279 12.9042 1.38545 13.135 1.50389 13.338C1.62234 13.5411 1.79236 13.7092 1.99673 13.8254C2.20109 13.9415 2.43253 14.0016 2.6676 13.9995H13.3343C13.5682 13.9993 13.7979 13.9375 14.0005 13.8204C14.203 13.7032 14.3711 13.5349 14.4879 13.3322C14.6048 13.1296 14.6663 12.8998 14.6662 12.6658C14.6662 12.4319 14.6046 12.2021 14.4876 11.9995Z" stroke="#D40000" stroke-linecap="round" stroke-linejoin="round" />
            </svg>

            <div><?php echo esc_html(is_array($final_error) ? implode(', ', $final_error) : $final_error); ?></div>
        </div>
		<?php
    endif;
    ?>

    <div class="growfund-error-message-container" style="display: none; align-items: center; gap: 8px; font-size: 14px; background-color: #fee2e2; border: 1px solid #fca5a5; color: #D40000; padding: 1rem; margin-bottom: 1rem; border-radius: 0.375rem;">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M8.00094 5.99951V8.66618M8.00094 11.3328H8.0076M14.4876 11.9995L9.15426 2.66617C9.03797 2.46097 8.86933 2.29029 8.66555 2.17154C8.46176 2.0528 8.23012 1.99023 7.99426 1.99023C7.7584 1.99023 7.52677 2.0528 7.32298 2.17154C7.11919 2.29029 6.95055 2.46097 6.83426 2.66617L1.50093 11.9995C1.38338 12.2031 1.32175 12.4341 1.32227 12.6692C1.32279 12.9042 1.38545 13.135 1.50389 13.338C1.62234 13.5411 1.79236 13.7092 1.99673 13.8254C2.20109 13.9415 2.43253 14.0016 2.6676 13.9995H13.3343C13.5682 13.9993 13.7979 13.9375 14.0005 13.8204C14.203 13.7032 14.3711 13.5349 14.4879 13.3322C14.6048 13.1296 14.6663 12.8998 14.6662 12.6658C14.6662 12.4319 14.6046 12.2021 14.4876 11.9995Z" stroke="#D40000" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <div class="growfund-error-message-element"></div>
    </div>

    <?php

    $show_separator = false;

    if (isset($fund_selection_type) && $fund_selection_type === 'donor-decide' && !empty($funds)) {
        $show_separator = true;
    }

    if ($show_separator) :
		?>
        <hr class="growfund-donation-checkout__separator">
    <?php endif; ?>

    <?php
    $tribute_fields = [];
    $notification_fields = [];
    $hidden_fields = [];

    if ($is_tribute_enabled) {

        $notification_type = 'send-ecard';

        if (($tribute_notification_preference ?? 'donor-decide') !== 'donor-decide') {
            $notification_type_map = [
                'send-ecard' => 'send-ecard',
                'send-post-mail' => 'send-post-mail',
                'send-ecard-and-post-mail' => 'send-ecard-and-post-mail'
            ];
            $notification_type = $notification_type_map[$tribute_notification_preference] ?? 'send-ecard';
        }


        $notification_fields = [];


        if (($tribute_notification_preference ?? 'donor-decide') === 'donor-decide') {
            $notification_fields[] = [
                'type' => 'dropdown',
                'label' => esc_html__('How would you like to send the notification?', 'growfund'),
                'data' => [
                    'id' => 'tributeNotificationType',
                    'name' => 'tribute_notification_type',
                    'required' => ($tribute_requirement === 'required'),
                    'variant' => 'gray',
                    'options' => [
                        [
							'value' => 'send-ecard',
							'label' => esc_html__('Send eCard', 'growfund')
						],
                        [
							'value' => 'send-post-mail',
							'label' => esc_html__('Send Post Mail', 'growfund')
						],
                        [
							'value' => 'send-ecard-and-post-mail',
							'label' => esc_html__('Send eCard and Post Mail', 'growfund')
						]
                    ]
                ]
            ];
        }


        if (($tribute_notification_preference ?? 'donor-decide') === 'donor-decide' ||
            in_array($tribute_notification_preference, ['send-post-mail', 'send-ecard-and-post-mail'], true)
        ) {
            $notification_fields[] = [
                'type' => 'div',
                'wrapper_class' => 'growfund-notification-field growfund-postmail-fields',
                'fields' => [
                    [
                        'type' => 'row',
                        'fields' => [
                            [
                                'type' => 'input',
                                'label' => esc_html__('Address*', 'growfund'),
                                'data' => [
                                    'id' => 'tributeNotificationRecipientAddress',
                                    'name' => 'tribute_notification_recipient_address[address]',
                                    'type' => 'text',
                                    'placeholder' => 'e.g. 123 Main St',
                                    'required' => ($tribute_requirement === 'required')
                                ]
                            ],
                            [
                                'type' => 'input',
                                'label' => esc_html__('Address 2', 'growfund'),
                                'data' => [
                                    'id' => 'tributeNotificationRecipientAddress2',
                                    'name' => 'tribute_notification_recipient_address[address2]',
                                    'type' => 'text',
                                    'placeholder' => 'e.g. Apt 4B'
                                ]
                            ]
                        ]
                    ],
                    [
                        'type' => 'row',
                        'fields' => [
                            [
                                'type' => 'dropdown',
                                'label' => esc_html__('Country*', 'growfund'),
                                'data' => [
                                    'options' => Location::get_countries_for_dropdown(true, esc_html__('Select Country', 'growfund')),
                                    'name' => 'tribute_notification_recipient_address[country]',
                                    'id' => 'tributeNotificationRecipientCountry',
                                    'placeholder' => esc_html__('Select Country', 'growfund'),
                                    'required' => ($tribute_requirement === 'required'),
                                    'variant' => 'gray',
                                    'searchable' => true,
                                    'searchPlaceholder' => esc_html__('Search countries...', 'growfund'),
                                    'data-location-data' => wp_json_encode(Location::get_countries())
                                ]
                            ],
                            [
                                'type' => 'dropdown',
                                'label' => esc_html__('State*', 'growfund'),
                                'data' => [
                                    'options' => [
										[
											'value' => '',
											'label' => esc_html__('Select State', 'growfund')
										]
                                    ],
                                    'name' => 'tribute_notification_recipient_address[state]',
                                    'id' => 'tributeNotificationRecipientState',
                                    'placeholder' => esc_html__('Select State', 'growfund'),
                                    'required' => ($tribute_requirement === 'required'),
                                    'variant' => 'gray',
                                    'searchable' => true,
                                    'searchPlaceholder' => esc_html__('Search states...', 'growfund'),
                                    'data-country-dependent' => 'true'
                                ]
                            ]
                        ]
                    ],
                    [
                        'type' => 'row',
                        'fields' => [
                            [
                                'type' => 'input',
                                'label' => esc_html__('City*', 'growfund'),
                                'data' => [
                                    'id' => 'tributeNotificationRecipientCity',
                                    'name' => 'tribute_notification_recipient_address[city]',
                                    'type' => 'text',
                                    'placeholder' => 'e.g. New York',
                                    'required' => ($tribute_requirement === 'required'),
                                    'data-country-dependent' => 'true'
                                ]
                            ],
                            [
                                'type' => 'input',
                                'label' => esc_html__('ZIP Code*', 'growfund'),
                                'data' => [
                                    'id' => 'tributeNotificationRecipientZipCode',
                                    'name' => 'tribute_notification_recipient_address[zip_code]',
                                    'type' => 'text',
                                    'placeholder' => 'e.g. 10001',
                                    'required' => ($tribute_requirement === 'required')
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        }

        if (!empty($tribute_options) && is_array($tribute_options)) {
            foreach ($tribute_options as $index => $option) {
                $tribute_fields[] = [
                    'type' => 'input',
                    'label' => esc_html($option['message']),
                    'data' => [
                        'type' => 'radio',
                        'name' => 'tribute_type',
                        'value' => $option['message'],
                        'id' => 'tribute_type_' . $index,
                        'checked' => !empty($option['is_default'])
                    ]
                ];
            }
        } else {

            $tribute_fields = [
                [
                    'type' => 'input',
                    'label' => esc_html__('In Honor of', 'growfund'),
                    'data' => [
                        'type' => 'radio',
                        'name' => 'tribute_type',
                        'value' => 'in-honor-of',
                        'id' => 'tribute_type_honor',
                        'checked' => true
                    ]
                ],
                [
                    'type' => 'input',
                    'label' => esc_html__('In Memory of', 'growfund'),
                    'data' => [
                        'type' => 'radio',
                        'name' => 'tribute_type',
                        'value' => 'in-memory-of',
                        'id' => 'tribute_type_memory'
                    ]
                ]
            ];
        }

        $hidden_fields = [];

        if (($tribute_notification_preference ?? 'donor-decide') !== 'donor-decide') {
            $hidden_fields[] = [
                'type' => 'input',
                'data' => [
                    'type' => 'hidden',
                    'name' => 'tribute_notification_type',
                    'value' => esc_attr($notification_type)
                ]
            ];
        }
    }

    $form_fields = [];

    if (isset($fund_selection_type) && $fund_selection_type === 'donor-decide' && !empty($funds)) {

        $form_fields[] = [
            'type' => 'dropdown',
            'label' => __('Which fund would you like to support?', 'growfund'),
            'label_class' => 'growfund-form-label growfund-library-support-label',
            'data' => [
                'id' => 'fund',
                'name' => 'fund_id',
                'required' => true,
                'variant' => 'gray',
                'options' => array_map(function ($fund, $index) {
                    return [
                        'value' => $fund['id'],
                        'label' => $fund['title'],
                        'selected' => $index === 0
                    ];
                }, $funds ?? [], array_keys($funds ?? []))
            ]
        ];
    } elseif (isset($default_fund) && !empty($default_fund)) {
            $form_fields[] = [
                'type' => 'input',
                'data' => [
                    'type' => 'hidden',
                    'name' => 'fund_id',
                    'value' => esc_attr($default_fund)
                ]
            ];
    }

    if ($is_tribute_enabled) {
        $form_fields[] = [
            'type' => 'html',
            'content' => '<hr class="growfund-donation-checkout__separator">'
        ];

        $form_fields = array_merge($form_fields, [
            [
                'type' => 'div',
                'wrapper_class' => 'growfund-dedicate-checkbox-wrapper',
                'wrapper_attributes' => [
                    'data-requirement' => $tribute_requirement ?? 'optional'
                ],
                'fields' => [
                    [
                        'type' => 'checkbox',
                        'label' => $tribute_title ?? esc_html__('Dedicate this donation to a loved one', 'growfund'),
                        'data' => [
                            'id' => 'dedicateDonation',
                            'name' => 'dedicate_donation',
                            'type' => 'checkbox',
                            'class' => 'growfund-tribute-toggle',
                            'checked' => false
                        ]
                    ],
                    [
                        'type' => 'div',
                        'wrapper_class' => ($tribute_requirement === 'required') ? 'growfund-tribute-content' : 'growfund-tribute-content growfund-tribute-hidden',
                        'wrapper_attributes' => [],
                        'fields' => [
                            [
                                'type' => 'group',
                                'title' => esc_html__('Dedication type', 'growfund'),
                                'wrapper_class' => 'growfund-form-section growfund-tribute-group',
                                'fields' => [
                                    [
                                        'type' => 'row',
                                        'wrapper_class' => 'growfund-form-row-radio',
                                        'fields' => $tribute_fields
                                    ]
                                ]
                            ],
                            [
                                'type' => 'group',
                                'title' => esc_html__('Details', 'growfund'),
                                'wrapper_class' => 'growfund-form-section growfund-tribute-group',
                                'fields' => [
                                    [
                                        'type' => 'row',
                                        'fields' => [
                                            [
                                                'type' => 'input',
                                                'label' => esc_html__('First name*', 'growfund'),
                                                'data' => [
                                                    'id' => 'tributeFirstName',
                                                    'name' => 'tribute_salutation',
                                                    'type' => 'text',
                                                    'placeholder' => 'e.g. John',
                                                    'required' => ($tribute_requirement === 'required'),
                                                    'data-tribute-required' => 'true'
                                                ]
                                            ],
                                            [
                                                'type' => 'input',
                                                'label' => esc_html__('Last name*', 'growfund'),
                                                'data' => [
                                                    'id' => 'tributeLastName',
                                                    'name' => 'tribute_to',
                                                    'type' => 'text',
                                                    'placeholder' => 'e.g. Smith',
                                                    'required' => ($tribute_requirement === 'required'),
                                                    'data-tribute-required' => 'true'
                                                ]
                                            ]
                                        ]
                                    ],
                                    [
                                        'type' => 'row',
                                        'fields' => [
                                            [
                                                'type' => 'input',
                                                'label' => esc_html__('Recipient name*', 'growfund'),
                                                'data' => [
                                                    'id' => 'tributeNotificationRecipientName',
                                                    'name' => 'tribute_notification_recipient_name',
                                                    'type' => 'text',
                                                    'placeholder' => 'e.g. Jane Smith',
                                                    'required' => ($tribute_requirement === 'required'),
                                                    'data-tribute-required' => 'true'
                                                ]
                                            ]
                                        ]
                                    ],
                                    [
                                        'type' => 'row',
                                        'fields' => [
                                            [
                                                'type' => 'input',
                                                'label' => esc_html__('Email*', 'growfund'),
                                                'data' => [
                                                    'id' => 'tributeNotificationRecipientEmail',
                                                    'name' => 'tribute_notification_recipient_email',
                                                    'type' => 'email',
                                                    'placeholder' => 'e.g. jane.smith@example.com',
                                                    'required' => ($tribute_requirement === 'required'),
                                                    'data-tribute-required' => 'true'
                                                ]
                                            ],
                                            [
                                                'type' => 'input',
                                                'label' => esc_html__('Phone*', 'growfund'),
                                                'data' => [
                                                    'id' => 'tributeNotificationRecipientPhone',
                                                    'name' => 'tribute_notification_recipient_phone',
                                                    'type' => 'tel',
                                                    'placeholder' => 'e.g. +1 (555) 123-4567',
                                                    'required' => ($tribute_requirement === 'required'),
                                                    'data-tribute-required' => 'true'
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            [
                                'type' => 'group',
                                'title' => __('Notification Details', 'growfund'),
                                'wrapper_class' => 'growfund-form-section growfund-tribute-group',
                                'fields' => array_merge($notification_fields, [
                                    [
                                        'type' => 'textarea',
                                        'label' => __('Personalized message', 'growfund'),
                                        'data' => [
                                            'id' => 'tributeMessage',
                                            'name' => 'notes',
                                            'placeholder' => esc_html__('Share a personal message about your loved one...', 'growfund'),
                                            'required' => false,
                                            'rows' => 4
                                        ]
                                    ]
                                ])
                            ]
                        ]
                    ]
                ]
            ]
        ]);
    }

    if (!$is_user_logged_in) {

        $form_fields[] = [
            'type' => 'group',
            'title' => esc_html__('Contact Information', 'growfund'),
            'title_class' => 'growfund-form-title growfund-contact-info-title',
            'fields' => [
                [
                    'type' => 'row',
                    'fields' => [
                        [
                            'type' => 'input',
                            'label' => esc_html__('First name*', 'growfund'),
                            'data' => [
                                'id' => 'firstName',
                                'name' => 'donor_first_name',
                                'type' => 'text',
                                'placeholder' => 'e.g. John',
                                'required' => true
                            ]
                        ],
                        [
                            'type' => 'input',
                            'label' => esc_html__('Last name*', 'growfund'),
                            'data' => [
                                'id' => 'lastName',
                                'name' => 'donor_last_name',
                                'type' => 'text',
                                'placeholder' => 'e.g. Smith',
                                'required' => true
                            ]
                        ]
                    ]
                ],
                [
                    'type' => 'row',
                    'fields' => [
                        [
                            'type' => 'input',
                            'label' => esc_html__('Email address*', 'growfund'),
                            'data' => [
                                'id' => 'email',
                                'name' => 'donor_email',
                                'type' => 'email',
                                'placeholder' => 'e.g. john.smith@example.com',
                                'required' => true
                            ]
                        ],
                        [
                            'type' => 'input',
                            'label' => esc_html__('Phone number*', 'growfund'),
                            'data' => [
                                'id' => 'phone',
                                'name' => 'donor_phone',
                                'type' => 'tel',
                                'placeholder' => 'e.g. +1 (555) 123-4567',
                                'required' => true
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }


    $form_fields[] = [
        'type' => 'html',
        'content' => '<hr class="growfund-donation-checkout__separator">'
    ];

    $form_fields[] = [
        'type' => 'group',
        'title' => esc_html__('Payment Information', 'growfund'),
        'fields' => [
            [
                'type' => 'payment-method-selector',
                'methods' => $payment_methods,
                'selected' => $payment_methods[0]['value'] ?? '',
                'name' => 'payment_method'
            ]
        ]
    ];


    $form_fields[] = [
        'type' => 'html',
        'content' => '<div class="growfund-donation-checkout__summary">
            <h3 class="growfund-donation-summary__title">' . esc_html__('Donation', 'growfund') . '</h3>
            <div class="growfund-donation-summary">
                <div class="growfund-donation-summary__item growfund-donation-summary__donation">
                    <span class="growfund-donation-summary__label">' . esc_html__('Your donation', 'growfund') . '</span>
                    <span class="growfund-donation-summary__amount" id="summaryAmount">' . esc_html(growfund_to_currency($default_amount)) . '</span>
                </div>
                <hr class="growfund-donation-summary__separator">
                <div class="growfund-donation-summary__item growfund-donation-summary__total">
                    <span class="growfund-donation-summary__label">' . esc_html__('Total due today', 'growfund') . '</span>
                    <span class="growfund-donation-summary__amount" id="summaryTotal">' . esc_html(growfund_to_currency($default_amount)) . '</span>
                </div>
                <hr class="growfund-donation-checkout__separator">
            </div>
        </div>'
    ];

    // Add anonymous donation checkbox
    if ($allow_anonymous_donation) {
        $form_fields[] = [
            'type' => 'checkbox',
            'label' => esc_html__('Don\'t display my name publicly on the fundraiser.', 'growfund'),
            'data' => [
                'id' => 'anonymousDonation',
                'name' => 'is_anonymous',
                'type' => 'checkbox',
                'value' => '1'
            ]
        ];
    }

    $form_fields[] = [
        'type' => 'input',
        'data' => [
            'type' => 'hidden',
            'name' => 'amount',
            'id' => 'donationAmount',
            'value' => $default_amount
        ]
    ];

    $form_fields[] = [
        'type' => 'input',
        'data' => [
            'type' => 'hidden',
            'name' => 'campaign_id',
            'value' => esc_attr($campaign_id)
        ]
    ];


    if ($is_user_logged_in && $current_user) {
        $form_fields[] = [
            'type' => 'input',
            'data' => [
                'type' => 'hidden',
                'name' => 'donor_first_name',
                'value' => esc_attr($current_user->first_name ? $current_user->first_name : $current_user->display_name)
            ]
        ];

        $form_fields[] = [
            'type' => 'input',
            'data' => [
                'type' => 'hidden',
                'name' => 'donor_last_name',
                'value' => esc_attr($current_user->last_name ? $current_user->last_name : '')
            ]
        ];

        $form_fields[] = [
            'type' => 'input',
            'data' => [
                'type' => 'hidden',
                'name' => 'donor_email',
                'value' => esc_attr($current_user->user_email)
            ]
        ];

        $form_fields[] = [
            'type' => 'input',
            'data' => [
                'type' => 'hidden',
                'name' => 'donor_phone',
                'value' => esc_attr(get_user_meta($current_user->ID, 'phone', true))
            ]
        ];
    }


    foreach ($hidden_fields as $hidden_field) {
        $form_fields[] = $hidden_field;
    }


    growfund_renderer()->render('site.components.form-builder', [
        'fields' => $form_fields,
        'form_attributes' => [
            'id' => 'donationForm',
            'method' => 'POST',
            'action' => esc_url(Utils::get_checkout_submit_url())
        ],
        'submit_button_text' => !empty($payment_methods) ? esc_html__('Donate now', 'growfund') : null,
        'submit_button_attributes' => [
            'class' => 'growfund-btn--primary growfund-branding-btn'
        ],
        'errors' => $form_errors
    ]);
    ?>
</div>

<?php
if (!$is_shortcode) {
    growfund_get_footer();
}
?>