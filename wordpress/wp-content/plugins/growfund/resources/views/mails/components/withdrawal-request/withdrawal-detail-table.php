<?php 
defined( 'ABSPATH' ) || exit;
?>
<div style="">
        <!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->

        <div style="margin:0px auto;max-width:600px;">
        <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
            <tbody>
            <tr>
                <td style="direction:ltr;font-size:0px;padding:0px;text-align:center;">
                <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:600px;" ><![endif]-->

                <div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                    <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
                    <tbody>
                        <tr>
                        <td align="left" style="font-size:0px;padding:10px;word-break:break-word;">
                            <table
                            cellpadding="0"
                            cellspacing="0"
                            width="100%"
                            border="0"
                            style="color:#000000;font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:22px;table-layout:auto;width:100%;border:1px solid #F0F0F0;"
                            >
                            <tr style="border-bottom: 1px solid #F0F0F0;">
                                <td style="padding: 12px 16px; font-weight: 400; color: #636363; width: 40%; font-family: Arial, sans-serif; font-size: 14px;border-left: 1px solid #F0F0F0;"><?php esc_html_e('Requested amount', 'growfund'); ?></td>
                                <td style="padding: 12px 16px; font-weight: 700; color: #333333; font-family: Arial, sans-serif; font-size: 14px; text-align: left; border-left: 1px solid #F0F0F0;"><?php echo esc_html(growfund_to_currency($withdrawal_request['amount'] ?? 0)); ?></td>
                            </tr>
                            <tr>
                                <td style="padding: 12px 16px; font-weight: 400; color: #636363; width: 40%; font-family: Arial, sans-serif; font-size: 14px;"><?php esc_html_e('Fundraiser', 'growfund'); ?></td>
                                <td style="padding: 12px 16px; font-weight: 500; color: #0055FF; font-family: Arial, sans-serif; font-size: 14px; text-align: left; border-left: 1px solid #F0F0F0;"><?php echo esc_html($withdrawal_request['fundraiser']['display_name'] ?? ''); ?></td>
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