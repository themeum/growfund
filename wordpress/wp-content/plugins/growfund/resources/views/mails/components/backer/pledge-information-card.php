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
                            <table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%">
                                <tbody>
                                    <tr>
                                        <td style="background-color:#E5FDEE;border-left:4px solid #28A745;vertical-align:top;padding:0;">
                                            <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="" width="100%">
                                                <tbody>
                                                    <tr>
                                                        <td align="left" style="font-size:0px;padding:12px 12px 12px 20px;word-break:break-word;">
                                                            <table cellpadding="0" cellspacing="0" width="100%" border="0" style="color:#000000;font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:22px;table-layout:auto;width:100%;border:none;">
                                                                <tr>
                                                                    <td style="padding:12px;">
                                                                        <h3 style="font-size:16px;font-weight:600;color:#1C7330;margin-top:0;margin-bottom:8px;"><?php echo esc_html__('What Happens Next?', 'growfund'); ?></h3>
                                                                        <ul style="padding-left:20px; margin:0;">
                                                                            <li style="font-size:16px;color:#333333;margin-bottom:8px;"><?php echo esc_html__('We\'ll keep you updated on the campaign\'s progress', 'growfund'); ?></li>
                                                                            <?php if (!empty($campaign['campaign_end_date'])) : ?>
                                                                                <li style="font-size:16px;color:#333333;margin-bottom:8px;"><?php echo esc_html__('Your payment will only be processed if we reach our funding goal by', 'growfund'); ?> <strong><?php echo esc_html($campaign['campaign_end_date']); ?></strong></li>
                                                                                <li style="font-size:16px;color:#333333;margin-bottom:8px;"><?php echo esc_html__('Once funded, we\'ll begin production and deliver your reward by', 'growfund'); ?> <strong><?php echo esc_html($campaign['campaign_end_date']); ?></strong></li>
                                                                            <?php endif; ?>
                                                                            <li style="font-size:16px;color:#333333;margin-bottom:0;"><?php echo esc_html__('You can manage or adjust your pledge at any time before the campaign ends', 'growfund'); ?></li>
                                                                        </ul>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </tbody>
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