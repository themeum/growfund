<?php defined( 'ABSPATH' ) || exit; ?>

<div style="margin:16px auto;background:#fcfcfc;background-color:#fcfcfc;border-radius:8px;">
    <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
        <tbody>
            <tr>
                <td style="direction:ltr;font-size:0px;padding:0;text-align:center;">
                    <div class="growfund-column-per-100 growfund-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                        <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
                            <tbody>
                                <tr>
                                    <td align="left" style="font-size:0px;padding:0;word-break:break-word;">
                                        <table cellpadding="0" cellspacing="0" width="100%" border="0" style="color:#333333;font-family:Inter, Arial, sans-serif;font-size:13px;line-height:22px;table-layout:auto;width:100%;border:none;border:1px solid #f0f0f0;">
                                            <tr style="border-bottom:1px solid #f0f0f0;">
                                                <td style="padding: 8px 16px; color: inherit; font-weight: 500; width: 30%;"><?php echo esc_html__('Campaigns', 'growfund'); ?></td>
                                                <td style="padding: 8px 16px; font-weight: bold; color: inherit;border-left:1px solid #f0f0f0;"><?php echo esc_html($campaign['title']); ?></td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 16px; color: inherit; font-weight: 500;"><?php echo esc_html__('Backers', 'growfund'); ?></td>
                                                <td style="padding: 8px 16px; font-weight: bold; color: inherit;border-left:1px solid #f0f0f0;"><?php echo esc_html($campaign['backers']); ?></td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 16px; color: inherit; font-weight: 500;"><?php echo esc_html__('Fund Raised', 'growfund'); ?></td>
                                                <td style="padding: 8px 16px; font-weight: bold; color: inherit;border-left:1px solid #f0f0f0;"><?php echo esc_html($campaign['fund_raised']); ?></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>