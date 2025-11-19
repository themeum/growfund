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
                                        <td align="left" style="font-size:0px;padding:8px 11px;word-break:break-word;">
                                            <table cellpadding="0" cellspacing="0" width="100%" border="0" style="color:#000000;font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:22px;table-layout:auto;width:100%;border:none;">
                                                <tr>
                                                    <td width="80px" style="overflow:hidden;">
                                                        <img width="80px" src="<?php echo esc_url($campaign['image']); ?>" style="border-radius:0;" />
                                                    </td>
                                                    <td style="vertical-align:top; padding-left:10px;">
                                                        <p style="font-size:14px; font-weight:600; color:#333333; margin:0;"><?php echo esc_html($campaign['campaign_title']); ?></p>
                                                        <p style="font-size:14px; color:#636363; margin:0;">
                                                            <?php echo esc_html__('by', 'growfund'); ?>
                                                            <a href="#" style="color:#338C58; text-decoration:none;">
                                                                <?php echo esc_html($campaign['campaign_creator']); ?>
                                                            </a>
                                                            <span style="float:right;">⏰
                                                                <?php
																/* translators: %s: campaign start date */
																printf(esc_html__('starts from %s', 'growfund'), esc_html($campaign['campaign_start_date']));
                                                                ?>
                                                            </span>
                                                        </p>
                                                        <div style="background-color:#d6d6d6;height:6px;border-radius:3px;overflow:hidden; margin-top:6px;">
                                                            <div style="background-color:#338c58;height:6px; width:<?php echo esc_html($campaign['funded_percent']); ?>%;"></div>
                                                        </div>
                                                        <p style="font-size:14px;color:#636363;margin:4px 0 0 0;">
                                                            <?php
                                                            /* translators: %s: campaign goal */
															printf(esc_html__('%s goal', 'growfund'), esc_html(growfund_to_currency($campaign['campaign_goal'])));
                                                            ?>
                                                        </p>
                                                    </td>
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