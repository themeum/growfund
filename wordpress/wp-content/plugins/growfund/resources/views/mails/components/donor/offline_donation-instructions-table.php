<?php defined( 'ABSPATH' ) || exit; ?>

<div style="">
    <!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:600px;" width="600" bgcolor="#ffffff" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
    <div style="background:#ffffff;background-color:#ffffff;margin:0px auto;max-width:600px;">
        <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#ffffff;background-color:#ffffff;width:100%;">
            <tbody>
                <tr>
                    <td style="direction:ltr;font-size:0px;text-align:center;">
                        <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:472px;" ><![endif]-->
                        <div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                            <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="background-color:#fcfcfc;border:1px solid #f0f0f0;border-radius:6px;vertical-align:top;" width="100%">
                                <tbody>
                                    <tr>
                                        <td align="left" style="font-size:0px;padding:0px;word-break:break-word;">
                                            <table cellpadding="0" cellspacing="0" width="100%" border="0" style="color:#000000;font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:22px;table-layout:auto;width:100%;border:none;">
                                                <tr>
                                                    <td style="padding:12px 16px; border-right:1px solid #f0f0f0; border-bottom:1px solid #f0f0f0; font-weight:500; color:#636363; width:180px;"><?php echo esc_html__('Amount', 'growfund'); ?></td>
                                                    <td style="padding:12px 16px; border-right:1px solid #f0f0f0; border-bottom:1px solid #f0f0f0; font-weight:500; color:#333333;">
                                                        <strong><?php echo esc_html(growfund_to_currency($donation['donation_amount'])); ?></strong>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding:12px 16px; border-right:1px solid #f0f0f0; border-bottom:1px solid #f0f0f0; font-weight:500; color:#636363;"><?php echo esc_html__('Donation', 'growfund'); ?></td>
                                                    <td style="padding:12px 16px; border-right:1px solid #f0f0f0; border-bottom:1px solid #f0f0f0; font-weight:500; color:#0055ff;">
                                                        <a href="<?php echo esc_url($donation['campaign_url']); ?>" style="color:#0055ff; text-decoration:none; font-weight:500;"><?php echo esc_html($donation['campaign_title']); ?></a>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding:12px 16px; border-right:1px solid #f0f0f0; font-weight:500; color:#636363; width:180px;"><?php echo esc_html__('Payment Instructions', 'growfund'); ?></td>
                                                    <td style="padding:12px 16px; border-right:1px solid #f0f0f0; font-weight:500; color:#333333;"><?php echo esc_html($donation['payment_instructions']); ?></td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <!--[if mso | IE]></td></tr></table><![endif]-->
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <!--[if mso | IE]></td></tr></table><![endif]-->
</div>