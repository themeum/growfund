<?php

defined( 'ABSPATH' ) || exit;

?>
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
                                        <td style="background-color:#fcfcfc;border:1px solid #f0f0f0;border-radius:10px;vertical-align:top;padding:16px;">
                                            <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="" width="100%">
                                                <tbody>
                                                    <tr>
                                                        <td align="left" style="font-size:0px;padding:0 0 15px 0;word-break:break-word;">
                                                            <div style="font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:12px;font-weight:500;line-height:1;text-align:left;color:#636363;">
                                                                <span style="font-weight:700; color:#333333; font-size:14px"> <?php echo esc_html($campaign['campaign_start_date']); ?> </span>
                                                                <?php
                                                                /* translators: %s: campaign creator */
                                                                printf(esc_html__('by %s', 'growfund'), esc_html($campaign['campaign_creator']))
                                                                ?>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td align="center" style="font-size:0px;padding:0;word-break:break-word;">
                                                            <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;">
                                                                <tbody>
                                                                    <tr>
                                                                        <td style="width:438px;">
                                                                            <img alt="Campaign image" height="auto" src="<?php echo esc_url($campaign['image']); ?>" style="border:0;border-radius:8px;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px;" width="438" />
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td align="left" style="font-size:0px;padding:15px 0 0 0;word-break:break-word;">
                                                            <div style="font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:16px;font-weight:400;line-height:24px;text-align:left;color:#000000;"><?php growfund_echo_safe_html(sprintf('%s%s', substr($campaign['description'] ?? '', 0, 100), strlen($campaign['description'] ?? '') > 100 ? '...' : '')); ?></div>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td align="center" vertical-align="middle" style="font-size:0px;padding:20px 0 0 0;word-break:break-word;">
                                                            <?php growfund_echo_safe_html($button); ?>
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