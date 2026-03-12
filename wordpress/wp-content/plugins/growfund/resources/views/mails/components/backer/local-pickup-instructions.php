<?php 
defined( 'ABSPATH' ) || exit; 

$growfund_rich_text_css_file = GROWFUND_RESOURCE_PATH . 'assets/site/styles/rich-text-editor.css';

if (file_exists($growfund_rich_text_css_file)) {
    $growfund_rich_text_content = file_get_contents($growfund_rich_text_css_file);
    
    if ($growfund_rich_text_content) {
        $growfund_rich_text_css = '<style type="text/css">' . $growfund_rich_text_content . '</style>';
        echo wp_kses($growfund_rich_text_css, ['style' => ['type' => true]]);
    }
}

?>

<div style="" >
    <!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
    <div style="margin:0px auto;max-width:600px;">
        <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
            <tbody>
                <tr>
                <td style="direction:ltr;font-size:0px;padding:20px 0;text-align:center;">
                    <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:600px;" ><![endif]-->
                    <div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                        <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
                            <tbody>
                                <tr>
                                    <td align="left" style="background:#f7f7f7;font-size:0px;padding:16px;word-break:break-word;">
                                        <div class="growfund-rich-text-content" style="font-size:14px;line-height:1.6;text-align:left;color:#636363;">
                                            <?php growfund_echo_safe_html($local_pickup_instructions); ?>
                                        </div>
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
