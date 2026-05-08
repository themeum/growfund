<?php 
/** @var \Growfund\Views\Admin\UserFormExtended $user_form_extended */
?>
<h1><?php esc_html_e('Growfund User Information', 'growfund'); ?></h1>
<div style="padding: 12px 20px; border: 1px solid #ccc; margin: 20px 0px; border-radius: 5px;">
    <table class="form-table">
        <?php if (!defined('IS_PROFILE_PAGE') || !IS_PROFILE_PAGE) : ?>
        <tr>
            <th><label for="additional_roles"><?php esc_html_e('Select Roles', 'growfund'); ?></label></th>
            <td>
                <select name="additional_roles[]" multiple style="max-height: 120px;" class="regular-text">
                    <?php foreach ($user_form_extended->growfund_roles as $growfund_role => $growfund_role_name) : ?>
                        <option value="<?php echo esc_attr($growfund_role); ?>"
                            <?php selected(!empty($user_form_extended->user_roles) && in_array($growfund_role, $user_form_extended->user_roles, true)); ?>>
                            <?php echo esc_html($growfund_role_name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <?php endif; ?>
        <tr>
            <th><label for="phone_number"><?php esc_html_e('Phone Number', 'growfund'); ?></label></th>
            <td>
                <input type="text" class="regular-text" name="phone" value="<?php echo esc_html($user_form_extended->phone ?? ''); ?>" />
            </td>
        </tr>
        <tr>
            <th><label for="is_verified"><?php esc_html_e('Is Verified', 'growfund'); ?></label></th>
            <td>
                <label>
                <input type="checkbox" class="regular-text" name="is_verified" value="true" <?php checked($user_form_extended->is_verified); ?>  />
                    <?php esc_html_e('Is user verified by email or admin?', 'growfund'); ?>						
                </label>
            </td>
        </tr>
    </table>

    <?php if (!growfund_app()->is_donation_mode()) : ?>
    <h3><?php esc_html_e('Growfund Shipping Address', 'growfund'); ?></h3>
    <table class="form-table">
        <tr>
            <th><label for="shipping_address"><?php esc_html_e('Address Line 1', 'growfund'); ?></label></th>
            <td>
                <input type="text" class="regular-text" name="shipping_address[address]" value="<?php echo esc_html($user_form_extended->shipping_address->address ?? ''); ?>" />
            </td>
        </tr>
        <tr>
            <th><label for="shipping_address_2"><?php esc_html_e('Address Line 2', 'growfund'); ?></label></th>
            <td>
                <input type="text" class="regular-text" name="shipping_address[address_2]" value="<?php echo esc_html($user_form_extended->shipping_address->address_2 ?? ''); ?>" />
            </td>
        </tr>
        <tr>
            <th><label for="shipping_city"><?php esc_html_e('City', 'growfund'); ?></label></th>
            <td>
                <input type="text" class="regular-text" name="shipping_address[city]" value="<?php echo esc_html($user_form_extended->shipping_address->city ?? ''); ?>" />
            </td>
        </tr>
        <tr>
            <th><label for="shipping_zip_code"><?php esc_html_e('Zip/Postal Code', 'growfund'); ?></label></th>
            <td>
                <input type="text" class="regular-text" name="shipping_address[zip_code]" value="<?php echo esc_html($user_form_extended->shipping_address->zip_code ?? ''); ?>" />
            </td>
        </tr>
        <tr>
            <th><label for="shipping_country"><?php esc_html_e('Country', 'growfund'); ?></label></th>
            <td>
                <select name="shipping_address[country]" class="regular-text">
                    <?php foreach ($user_form_extended->growfund_countries as $country) : ?>
                        <option value="<?php echo esc_attr($country['value']); ?>"
                            <?php selected($country['value'] === $user_form_extended->shipping_address->country); ?>>
                            <?php echo esc_html($country['label']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
    </table>
    <?php endif; ?>

    <h3><?php esc_html_e('Growfund Billing Address', 'growfund'); ?></h3>
    <?php if (!growfund_app()->is_donation_mode()) : ?>
        <table class="form-table">
            <tr>
                <th><label for="is_billing_address_same"><?php esc_html_e('Same as shipping address', 'growfund'); ?></label></th>
                <td>
                    <label>
                    <input type="checkbox" id="is_billing_address_same" class="regular-text" name="is_billing_address_same" value="true" <?php checked($user_form_extended->is_billing_address_same); ?>  />
                        <?php esc_html_e('Is billing address same as shipping address?', 'growfund'); ?>						
                    </label>
                </td>
            </tr>
        </table>
        <?php endif; ?>

    <table id="billing_address_section" class="form-table" style="<?php echo $user_form_extended->is_billing_address_same ? 'display: none;' : ''; ?>">
        <tr>
            <th><label for="billing_address"><?php esc_html_e('Address Line 1', 'growfund'); ?></label></th>
            <td>
                <input type="text" class="regular-text" name="billing_address[address]" value="<?php echo esc_html($user_form_extended->billing_address->address ?? ''); ?>" />
            </td>
        </tr>
        <tr>
            <th><label for="billing_address_2"><?php esc_html_e('Address Line 2', 'growfund'); ?></label></th>
            <td>
                <input type="text" class="regular-text" name="billing_address[address_2]" value="<?php echo esc_html($user_form_extended->billing_address->address_2 ?? ''); ?>" />
            </td>
        </tr>
        <tr>
            <th><label for="billing_city"><?php esc_html_e('City', 'growfund'); ?></label></th>
            <td>
                <input type="text" class="regular-text" name="billing_address[city]" value="<?php echo esc_html($user_form_extended->billing_address->city ?? ''); ?>" />
            </td>
        </tr>
        <tr>
            <th><label for="billing_zip_code"><?php esc_html_e('Zip/Postal Code', 'growfund'); ?></label></th>
            <td>
                <input type="text" class="regular-text" name="billing_address[zip_code]" value="<?php echo esc_html($user_form_extended->billing_address->zip_code ?? ''); ?>" />
            </td>
        </tr>
        <tr>
            <th><label for="billing_country"><?php esc_html_e('Country', 'growfund'); ?></label></th>
            <td>
                <select name="billing_address[country]" class="regular-text">
                    <?php foreach ($user_form_extended->growfund_countries as $country) : ?>
                        <option value="<?php echo esc_attr($country['value']); ?>"
                            <?php selected($country['value'] === $user_form_extended->billing_address->country); ?>>
                            <?php echo esc_html($country['label']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
    </table>
</div>