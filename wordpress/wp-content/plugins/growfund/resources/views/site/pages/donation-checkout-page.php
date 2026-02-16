<?php
/**
 * @var Growfund\Views\Pages\DonationCheckoutPage $donation_checkout_page
 */

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Campaign\FundSelectionType;
use Growfund\Constants\Campaign\SuggestedOptionType;
use Growfund\Constants\Campaign\TributeNotificationPreference;
use Growfund\Constants\Campaign\TributeNotificationType;
use Growfund\Constants\Campaign\TributeRequirement;
use Growfund\Constants\PaymentEngine;
use Growfund\Core\AppSettings;
use Growfund\Supports\Arr;
use Growfund\Supports\Currency;
use Growfund\Supports\Location;
use Growfund\Supports\Utils;
use Growfund\Views\Components\Form\Button;
use Growfund\Views\Components\Form\CheckboxField;
use Growfund\Views\Components\Form\NumberField;
use Growfund\Views\Components\Form\RadioField;
use Growfund\Views\Components\Form\SelectDropdown;
use Growfund\Views\Components\Form\TextareaField;
use Growfund\Views\Components\Form\TextField;
use Growfund\Views\Components\PaymentMethodCard;
use Growfund\Views\Components\UI\Badge;
use Growfund\Views\Components\UI\Image;

$growfund_default_donation_amount = 0;

$growfund_user = growfund_user();
$growfund_billing_address = $growfund_user->get_meta('billing_address');

$growfund_checkout_consent = growfund_settings(AppSettings::GENERAL)->get_tnc_text();
?>


<form method="POST" action="<?php echo esc_url(Utils::get_checkout_submit_url()); ?>">
    <?php growfund_nonce_field(); ?>
    <input type="hidden" name="campaign_id" value="<?php echo esc_attr($donation_checkout_page->campaign->id); ?>" />

    <div class="growfund-donation-checkout-page">
        <?php 

        $growfund_error_badge = new Badge();
        $growfund_error_badge->variant = 'error';
        $growfund_error_badge->message =  growfund_flash_get_message('checkout_error') ?? growfund_flash_get_message('checkout_form_errors')['campaign_id'][0] ?? '';

        growfund_render($growfund_error_badge);

        ?>
        <div class="growfund-donation-checkout-page-donation">
            <div class="growfund-donation-checkout-page-donation-image">
            <?php
            $growfund_donation_image = new Image([
                'src' => $donation_checkout_page->campaign->images[0]['url'] ?? growfund_placeholder_image_url(),
                'alt' => $donation_checkout_page->campaign->title,
            ]);
            growfund_render($growfund_donation_image);
            ?>
            </div>
            <div class="growfund-donation-checkout-page-donation-tittle-wrapper">
                <span class="growfund-donation-checkout-page-donation-text"><?php esc_html_e('Choose your donation amount for', 'growfund'); ?></span>
                <span class="growfund-donation-checkout-page-donation-tittle"><?php echo esc_html($donation_checkout_page->campaign->title); ?></span>
            </div>
        </div>

        <div class="growfund-donation-checkout-page-donation-wrapper">
            <span class="growfund-donation-checkout-page-donation-amount-text"><?php esc_html_e('Enter your donation', 'growfund'); ?></span>
            <div class="growfund-donation-checkout-page-donation-amount-wrapper">
                <?php
                foreach ($donation_checkout_page->campaign->suggested_options as $growfund_suggestion) {
                    $growfund_is_default = (bool) ($growfund_suggestion['is_default'] ?? false);

                    if ($growfund_is_default) {
                        $growfund_default_donation_amount = $growfund_suggestion['amount'] ?? 0;
                    }

                    $growfund_radio = new RadioField();
                    $growfund_radio->name          = 'amount';
                    $growfund_radio->value         = $growfund_suggestion['amount'] ?? 0;
                    $growfund_radio->label         = Currency::format($growfund_suggestion['amount'] ?? 0);
                    $growfund_radio->title         = $donation_checkout_page->campaign->suggested_option_type === SuggestedOptionType::AMOUNT_DESCRIPTION ? ($growfund_suggestion['description'] ?? '') : '';
                    $growfund_radio->checked       = $growfund_is_default;
                    $growfund_radio->classname     = 'growfund-donation-checkout-page-donation-amount';
                    $growfund_radio->wrapper_class = 'growfund-donation-checkout-page-donation-radio-wrapper';

                    growfund_render($growfund_radio);
                }
                ?>
            </div>
            <?php if ($donation_checkout_page->campaign->allow_custom_donation) : ?>
                <span class="growfund-donation-checkout-page-donation-input-wrapper">
                    <?php
                    $growfund_field = new NumberField();
                    $growfund_field->classname   = 'growfund-donation-checkout-page-donation-input';
                    $growfund_field->placeholder = __('Enter your custom amount', 'growfund');
                    $growfund_field->name        = 'amount';
                    $growfund_field->id          = 'growfund_donation_custom_amount';
                    $growfund_field->value       = $growfund_default_donation_amount;
                    $growfund_field->show_currency = true;
                    growfund_render($growfund_field);
                    ?>
                </span>
            <?php endif; ?>
            <p class="growfund-form-error"><?php echo esc_html(growfund_flash_get_message('checkout_form_errors')['amount'][0] ?? ''); ?></p>
        </div>

        <?php if ($donation_checkout_page->campaign->fund_selection_type === FundSelectionType::DONOR_DECIDE && growfund_settings(AppSettings::CAMPAIGNS)->allow_fund()) : ?>
            <div class="growfund-donation-checkout-page-support-wrapper">
                <span class="growfund-donation-checkout-page-support-title"><?php esc_html_e('Which fund would you like to support?', 'growfund'); ?></span>
                <?php
                $growfund_select_field = new SelectDropdown();
                $growfund_select_field->name        = 'fund_id';
                $growfund_select_field->classname   = 'growfund-donation-checkout-page-support-dropdown';
                $growfund_select_field->placeholder = __('Select Fund', 'growfund');
                $growfund_select_field->allow_clear = false;
                $growfund_select_field->options     = Arr::make($donation_checkout_page->funds ?? [])->map(function ($fund) {
                    return [
                        'label' => $fund['title'],
                        'value' => $fund['id'],
                    ];
                })->toArray();
                $growfund_select_field->error_msg = growfund_flash_get_message('checkout_form_errors')['fund_id'][0] ?? '';
                
                growfund_render($growfund_select_field);
                ?>
            </div>
        <?php else : ?>
            <input type="hidden" name="fund_id" value="<?php echo esc_attr($donation_checkout_page->campaign->default_fund); ?>">
        <?php endif; ?>

        <?php if (growfund_settings(AppSettings::CAMPAIGNS)->allow_tribute() && $donation_checkout_page->campaign->has_tribute) : ?>
            <div class="growfund-donation-checkout-page-tribute-wrapper">
                <?php
                if ($donation_checkout_page->campaign->tribute_requirement === TributeRequirement::OPTIONAL) {
                    $growfund_checkbox = new CheckboxField();
                    $growfund_checkbox->name        = 'dedicate_donation';
                    $growfund_checkbox->id          = 'growfund_tribute_checkbox';
                    $growfund_checkbox->label       = $donation_checkout_page->campaign->tribute_title ?? __('Dedicate this donation to a loved one', 'growfund');
                    $growfund_checkbox->checked     = false;
                    $growfund_checkbox->label_class = "growfund-donation-checkout-page-tribute-checkbox";
                    $growfund_checkbox->error_msg   = growfund_flash_get_message('checkout_form_errors')['dedicate_donation'][0] ?? '';
                    
                    growfund_render($growfund_checkbox);
                }
                
                ?>
                <div class="growfund-donation-checkout-page-tribute-content <?php echo esc_attr($donation_checkout_page->campaign->tribute_requirement === TributeRequirement::OPTIONAL ? 'growfund-hidden' : ''); ?>" id="growfund_tribute_content">
                    <div class="growfund-donation-checkout-page-tribute-dedication-type-section">
                        <div class="growfund-donation-checkout-page-tribute-dedication-type-title">
                            <?php esc_html_e('Dedication type', 'growfund'); ?>
                        </div>
                        <div class="growfund-donation-checkout-page-tribute-dedication-type-options">
                            <?php 
                            foreach ($donation_checkout_page->campaign->tribute_options as $growfund_tribute_option) {
                                $growfund_dedication_option = new RadioField();
                                $growfund_dedication_option->name = 'tribute_type';
                                $growfund_dedication_option->value = $growfund_tribute_option['message'];
                                $growfund_dedication_option->label = $growfund_tribute_option['message'];
                                $growfund_dedication_option->checked = (bool) ($growfund_tribute_option['is_default'] ?? false);

                                growfund_render($growfund_dedication_option);
                            }
                            ?>
                        </div>
                        <p class="growfund-form-error"><?php echo esc_html(growfund_flash_get_message('checkout_form_errors')['tribute_type'][0] ?? ''); ?></p>
                    </div>
                    <div class="growfund-donation-checkout-page-tribute-details">
                        <div class="growfund-donation-checkout-page-tribute-details-title">
                            <?php esc_html_e('Details', 'growfund'); ?>
                        </div>
                        <div class="growfund-donation-checkout-page-tribute-details-content">
                            <?php 
                                $growfund_prepended_label = new TextField();
                                $growfund_prepended_label->label = __('Tribute Prepended Label', 'growfund');
                                $growfund_prepended_label->name = 'tribute_salutation';
                                $growfund_prepended_label->placeholder = __('e.g. Mr, Mrs', 'growfund');
                                $growfund_prepended_label->error_msg = growfund_flash_get_message('checkout_form_errors')['tribute_salutation'][0] ?? '';

                                growfund_render($growfund_prepended_label);
                            ?>

                            <?php 
                                $growfund_tribute_to = new TextField();
                                $growfund_tribute_to->label = __('Tribute to', 'growfund');
                                $growfund_tribute_to->name = 'tribute_to';
                                $growfund_tribute_to->placeholder = __('e.g. John Doe', 'growfund');
                                $growfund_tribute_to->error_msg = growfund_flash_get_message('checkout_form_errors')['tribute_to'][0] ?? '';

                                growfund_render($growfund_tribute_to);
                            ?>

                            <?php 
                                $growfund_recipient_name = new TextField();
                                $growfund_recipient_name->label = __('Recipient Name', 'growfund');
                                $growfund_recipient_name->name = 'tribute_notification_recipient_name';
                                $growfund_recipient_name->placeholder = __('e.g. Jane Smith', 'growfund');
                                $growfund_recipient_name->error_msg = growfund_flash_get_message('checkout_form_errors')['tribute_notification_recipient_name'][0] ?? '';

                                growfund_render($growfund_recipient_name);
                            ?>

                            <?php 
                                $growfund_recipient_email = new TextField();
                                $growfund_recipient_email->label = __('Recipient Email', 'growfund');
                                $growfund_recipient_email->name = 'tribute_notification_recipient_email';
                                $growfund_recipient_email->placeholder = __('e.g. janesmith@example.com', 'growfund');
                                $growfund_recipient_email->error_msg = growfund_flash_get_message('checkout_form_errors')['tribute_notification_recipient_email'][0] ?? '';

                                growfund_render($growfund_recipient_email);
                            ?>

                            <?php 
                                $growfund_recipient_phone = new TextField();
                                $growfund_recipient_phone->label = __('Recipient Phone', 'growfund');
                                $growfund_recipient_phone->name = 'tribute_notification_recipient_phone';
                                $growfund_recipient_phone->placeholder = __('e.g. +1 (555) 123-4567', 'growfund');
                                $growfund_recipient_phone->error_msg = growfund_flash_get_message('checkout_form_errors')['tribute_notification_recipient_phone'][0] ?? '';

                                growfund_render($growfund_recipient_phone);
                            ?>
                        </div>
                    </div>
                    <div class="growfund-donation-checkout-page-tribute-notification">
                        <div class="growfund-donation-checkout-page-tribute-notification-title">
                            <?php esc_html_e('Notification Details', 'growfund'); ?>
                        </div>
                        <div class="growfund-donation-checkout-page-tribute-notification-content">
                            <?php
                            if (
                                empty($donation_checkout_page->campaign->tribute_notification_preference) 
                                || $donation_checkout_page->campaign->tribute_notification_preference === TributeNotificationPreference::LET_DONOR_DECIDE
                                ) :
                                ?>
                                <span class="growfund-donation-checkout-page-tribute-notification-type-label">
                                    <?php esc_html_e('How would you like to send the notification?', 'growfund'); ?>
                                </span>
								<?php
                                    $growfund_tribute_notification_type = new SelectDropdown();
                                    $growfund_tribute_notification_type->placeholder = __('Select an option', 'growfund');
                                    $growfund_tribute_notification_type->name = 'tribute_notification_type';
                                    $growfund_tribute_notification_type->id = 'tribute_notification_type';
                                    $growfund_tribute_notification_type->options = [
                                        [
                                            'value' => TributeNotificationType::ECARD,
                                            'label' => esc_html__('Send eCard', 'growfund')
                                        ],
                                        [
                                            'value' => TributeNotificationType::POST_MAIL,
                                            'label' => esc_html__('Send Post Mail', 'growfund')
                                        ],
                                        [
                                            'value' => TributeNotificationType::BOTH,
                                            'label' => esc_html__('Send eCard and Post Mail', 'growfund')
                                        ]
                                    ];
                                    $growfund_tribute_notification_type->error_msg = growfund_flash_get_message('checkout_form_errors')['tribute_notification_type'][0] ?? '';
                                    $growfund_tribute_notification_type->allow_clear = false;
                                    $growfund_tribute_notification_type->is_filterable = false;

                                    growfund_render($growfund_tribute_notification_type);
									?>
                            <?php else : ?>
                                <input type="hidden" name="tribute_notification_type" value="<?php echo esc_attr($donation_checkout_page->campaign->tribute_notification_preference); ?>">
                            <?php endif; ?>

                            <?php if ($donation_checkout_page->campaign->tribute_notification_preference !== TributeNotificationPreference::ECARD) : ?>

                            <div class="growfund-donation-checkout-page-tribute-recipient-address growfund-hidden">
                                <?php 
                                $growfund_select_field = new SelectDropdown();
                                $growfund_select_field->name          = 'tribute_notification_recipient_address[country]';
                                $growfund_select_field->id            = 'recipient_country';
                                $growfund_select_field->placeholder   = __('Select country', 'growfund');
                                $growfund_select_field->allow_clear   = false;
                                $growfund_select_field->options       = Location::get_countries_for_dropdown(false);
                                $growfund_select_field->error_msg = growfund_flash_get_message('checkout_form_errors')['tribute_notification_recipient_address.country'][0] ?? '';

                                growfund_render($growfund_select_field);

                                $growfund_address_1 = new TextField();
                                $growfund_address_1->name        = 'tribute_notification_recipient_address[address]';
                                $growfund_address_1->id          = 'recipient_address_1';
                                $growfund_address_1->placeholder = __('Address Line 1', 'growfund');
                                $growfund_address_1->error_msg = growfund_flash_get_message('checkout_form_errors')['tribute_notification_recipient_address.address'][0] ?? '';

                                growfund_render($growfund_address_1);

                                $growfund_address_2 = new TextField();
                                $growfund_address_2->name        = 'tribute_notification_recipient_address[address_2]';
                                $growfund_address_2->id          = 'recipient_address_2';
                                $growfund_address_2->placeholder = __('Address Line 2 (optional)', 'growfund');

                                growfund_render($growfund_address_2);

                                $growfund_state_field = new SelectDropdown();
                                $growfund_state_field->name        = 'tribute_notification_recipient_address[state]';
                                $growfund_state_field->id          = 'recipient_state';
                                $growfund_state_field->placeholder = __('Select state', 'growfund');
                                $growfund_state_field->allow_clear   = false;
                                $growfund_state_field->options     = [];
                                $growfund_state_field->error_msg = growfund_flash_get_message('checkout_form_errors')['tribute_notification_recipient_address.state'][0] ?? '';

                                growfund_render($growfund_state_field);

                                $growfund_city_field = new TextField();
                                $growfund_city_field->name        = 'tribute_notification_recipient_address[city]';
                                $growfund_city_field->id          = 'recipient_city';
                                $growfund_city_field->placeholder = __('Select city', 'growfund');
                                $growfund_city_field->error_msg = growfund_flash_get_message('checkout_form_errors')['tribute_notification_recipient_address.city'][0] ?? '';

                                growfund_render($growfund_city_field);

                                $growfund_zip_code = new TextField();
                                $growfund_zip_code->name        = 'tribute_notification_recipient_address[zip_code]';
                                $growfund_zip_code->id          = 'recipient_postal_code';
                                $growfund_zip_code->placeholder = __('ZIP / Postal Code', 'growfund');
                                $growfund_zip_code->error_msg = growfund_flash_get_message('checkout_form_errors')['tribute_notification_recipient_address.zip_code'][0] ?? '';

                                growfund_render($growfund_zip_code);
                                ?>
                            </div>

                            <?php endif; ?>

                            <div class="growfund-donation-checkout-page-tribute-message">
                            <?php 
                                $growfund_personalized_message = new TextareaField();
                                $growfund_personalized_message->name = 'notes';
                                $growfund_personalized_message->label = __('Personalized message', 'growfund');
                                $growfund_personalized_message->placeholder = __('Share a personal message about your loved one...', 'growfund');
                                $growfund_personalized_message->error_msg = growfund_flash_get_message('checkout_form_errors')['notes'][0] ?? '';
                                
                                growfund_render($growfund_personalized_message);
                            ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="growfund-donation-checkout-page-contact-info">
            <span class="growfund-donation-checkout-page-contact-info-title"><?php esc_html_e('Contact Information', 'growfund'); ?></span>
            <div class="growfund-donation-checkout-page-contact-info-fname-lname">
                <?php
                $growfund_first_name = new TextField();
                $growfund_first_name->label       = 'First name*';
                $growfund_first_name->name        = 'contact_info[first_name]';
                $growfund_first_name->classname   = 'growfund-donation-checkout-page-contact-info-fname';
                $growfund_first_name->placeholder = __('e.g. John', 'growfund');
                $growfund_first_name->error_msg = growfund_flash_get_message('checkout_form_errors')['contact_info.first_name'][0] ?? '';

                growfund_render($growfund_first_name);

                $growfund_last_name = new TextField();
                $growfund_last_name->label       = 'Last name*';
                $growfund_last_name->name        = 'contact_info[last_name]';
                $growfund_last_name->classname   = 'growfund-donation-checkout-page-contact-info-lname';
                $growfund_last_name->placeholder = __('e.g. Steve', 'growfund');
                $growfund_last_name->error_msg = growfund_flash_get_message('checkout_form_errors')['contact_info.last_name'][0] ?? '';

                growfund_render($growfund_last_name);
                ?>
            </div>
            <div class="growfund-donation-checkout-page-contact-info-email-wrapper">
                <?php
                $growfund_email_address = new TextField();
                $growfund_email_address->label       = "Email address*";
                $growfund_email_address->name        = 'contact_info[email]';
                $growfund_email_address->classname       = 'growfund-donation-checkout-page-contact-info-email';
                $growfund_email_address->placeholder = __('e.g. johnsmith@yourmail.com', 'growfund');
                $growfund_email_address->error_msg = growfund_flash_get_message('checkout_form_errors')['contact_info.email'][0] ?? '';

                growfund_render($growfund_email_address);
                ?>
            </div>
            <div class="growfund-donation-checkout-page-billing-country">
                <span class="growfund-donation-checkout-page-billing-country-label"><?php esc_html_e('Country*', 'growfund'); ?></span>
            <?php
                $growfund_country = new SelectDropdown();
                $growfund_country->name        = 'billing_address[country]';
                $growfund_country->id          = 'billing_country';
                $growfund_country->placeholder = __('Select country', 'growfund');
                $growfund_country->default_value = $growfund_billing_address['country'] ?? null;
                $growfund_country->options = Location::get_countries_for_dropdown(false);
                $growfund_country->error_msg = growfund_flash_get_message('checkout_form_errors')['billing_address.country'][0] ?? '';
                $growfund_country->allow_clear = false;

                growfund_render($growfund_country);
            ?>
            </div>

            <?php

                $growfund_address = new TextField();
                $growfund_address->label       = 'Address Line 1*';
                $growfund_address->name        = 'billing_address[address]';
                $growfund_address->id          = 'billing_address_1';
                $growfund_address->placeholder = __('Address Line 1', 'growfund');
                $growfund_address->value       = $growfund_billing_address['address'] ?? null;
                $growfund_address->error_msg = growfund_flash_get_message('checkout_form_errors')['billing_address.address'][0] ?? '';

                growfund_render($growfund_address);

                $growfund_address_2 = new TextField();
                $growfund_address_2->name        = 'billing_address[address_2]';
                $growfund_address_2->value       = $growfund_billing_address['address_2'] ?? null;
                $growfund_address_2->label         = 'Address Line 2';
                $growfund_address_2->id          = 'billing_address_2';
                $growfund_address_2->placeholder = __('Address Line 2 (optional)', 'growfund');

                growfund_render($growfund_address_2);
            ?>
            <div class="growfund-donation-checkout-page-right-section-state-city-wrapper">
                <?php
                    $growfund_state_field = new SelectDropdown();
                    $growfund_state_field->name        = 'billing_address[state]';
                    $growfund_state_field->default_value = $growfund_billing_address['state'] ?? null;
                    $growfund_state_field->id          = 'billing_state';
                    $growfund_state_field->classname   = 'growfund-donation-checkout-page-right-section-shipping-state';
                    $growfund_state_field->placeholder = __('Select state', 'growfund');
                    $growfund_state_field->options     = Location::get_states_for_dropdown($growfund_billing_address['country'] ?? null);
                    $growfund_state_field->allow_clear = false;
                    $growfund_state_field->error_msg = growfund_flash_get_message('checkout_form_errors')['billing_address.state'][0] ?? '';

                    growfund_render($growfund_state_field);

                    $growfund_city_field = new TextField();
                    $growfund_city_field->name        = 'billing_address[city]';
                    $growfund_city_field->value       = $growfund_billing_address['city'] ?? null;
                    $growfund_city_field->id          = 'billing_city';
                    $growfund_city_field->classname   = 'growfund-donation-checkout-page-right-section-shipping-city';
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
                $growfund_zip_code->classname   = 'growfund-donation-checkout-page-right-section-shipping-postal-code';
                $growfund_zip_code->placeholder = __('ZIP / Postal Code', 'growfund');
                $growfund_zip_code->error_msg = growfund_flash_get_message('checkout_form_errors')['billing_address.zip_code'][0] ?? '';

                growfund_render($growfund_zip_code);
            ?>
        </div>

        <?php
		if (growfund_settings(AppSettings::PAYMENT)->get_payment_engine() === PaymentEngine::NATIVE) {
			$growfund_payment_methods = new PaymentMethodCard();
			$growfund_payment_methods->payment_methods = $donation_checkout_page->payment_methods;
			$growfund_payment_methods->error_msg = growfund_flash_get_message('checkout_form_errors')['payment_method'][0] ?? '';

			growfund_render($growfund_payment_methods);
		}
        ?>

        <div class="growfund-donation-checkout-page-donate-main-wrapper">
            <span class="growfund-donation-checkout-page-donate-title"><?php esc_html_e('Donation Summary', 'growfund'); ?></span>
            <div class="growfund-donation-checkout-page-donate-wrapper">
                <span class="growfund-donation-checkout-page-donate-text"><?php esc_html_e('Your donation', 'growfund'); ?></span>
                <span class="growfund-donation-checkout-page-donate-amount" id="total_donation_amount"><?php echo esc_html(Currency::format($growfund_default_donation_amount)); ?></span>
            </div>
            <div class="growfund-donation-checkout-page-total-due-wrapper">
                <span class="growfund-donation-checkout-page-total-due-text"><?php esc_html_e('Total due today', 'growfund'); ?></span>
                <span class="growfund-donation-checkout-page-total-due-amount" id="total_due_amount"><?php echo esc_html(Currency::format($growfund_default_donation_amount)); ?></span>
            </div>

            <div class="growfund-donation-checkout-page-privacy-wrapper">
                <?php
                if (growfund_settings(AppSettings::PERMISSIONS)->allow_anonymous_donation()) {
                    $growfund_is_anonymous_checkbox = new CheckboxField();
					$growfund_is_anonymous_checkbox->name        = 'is_anonymous';
					$growfund_is_anonymous_checkbox->classname   = '1';
					$growfund_is_anonymous_checkbox->id          = 'is_anonymous';
					$growfund_is_anonymous_checkbox->label       = __('Don\'t display my name publicly on the fundraiser', 'growfund');
					$growfund_is_anonymous_checkbox->label_class = 'growfund-donation-checkout-page-is-anonymous-checkbox';
					$growfund_is_anonymous_checkbox->checked     = false;

					growfund_render($growfund_is_anonymous_checkbox);
                }
                
                $growfund_terms_agreement = new CheckboxField();
                $growfund_terms_agreement->name  = 'terms_agreement';
                $growfund_terms_agreement->id    = 'terms_agreement_checkbox';
                $growfund_terms_agreement->checked     = false;
                $growfund_terms_agreement->label = $growfund_checkout_consent ? $growfund_checkout_consent : __('I have read and agree to the terms and conditions above.', 'growfund');
                $growfund_terms_agreement->checked = false;

                growfund_render($growfund_terms_agreement);
                ?>
            </div>

            <div class="growfund-donation-checkout-page-donate-button-container">
                <?php
                $growfund_donate_button = new Button();
                $growfund_donate_button->classname = 'growfund-donation-checkout-page-donate-button growfund-branding-btn';
                $growfund_donate_button->label = __('Donate Now', 'growfund');
                $growfund_donate_button->type  = 'submit';
                $growfund_donate_button->id   = "growfund_checkout_page_donation_button";
                $growfund_donate_button->disabled = true;

                growfund_render($growfund_donate_button);
                ?>
            </div>
        </div>
    </div>
</form>

