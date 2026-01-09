<?php

defined( 'ABSPATH' ) || exit;

$background_color = $colors['background'] ?? '#ffffff';
$text_color = $colors['text'] ?? '#333333';
?>
<!-- Footer --><!--[if mso | IE]><tr><td class="" width="560px" ><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:560px;" width="560" bgcolor="<?php echo esc_attr($background_color); ?>" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
<div
    style="background:<?php echo esc_attr($background_color); ?>;background-color:<?php echo esc_attr($background_color); ?>;margin:0px auto;border-radius:0 0 8px 8px;max-width:560px;">
    <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation"
        style="background:<?php echo esc_attr($background_color); ?>;background-color:<?php echo esc_attr($background_color); ?>;width:100%;border-radius:0 0 8px 8px;">
        <tbody>
            <tr>
                <td
                    style="border-top:1px solid #e6e6e6;direction:ltr;font-size:0px;padding:24px 64px;text-align:center;">
                    <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:432px;" ><![endif]-->
                    <div class="mj-column-per-100 mj-outlook-group-fix"
                        style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                        <table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%">
                            <tbody>
                                <tr>
                                    <td style="vertical-align:top;padding:0;">
                                        <?php if (!empty($content['footer'])) : ?>
                                            <table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%">
                                                <tbody>
                                                    <tr>
                                                        <td align="center" style="font-size:0px;padding:0;word-break:break-word;">
                                                            <div
                                                                style="font-family:Inter, sans-serif;font-size:16px;font-weight:normal;letter-spacing:0%;line-height:1.5;text-align:center;text-decoration:none;color:<?php echo esc_attr($text_color); ?>;">
                                                                <?php echo wp_kses_post($content['footer']); ?>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div><!--[if mso | IE]></td></tr></table><![endif]-->
                </td>
            </tr>
        </tbody>
    </table>
</div><!--[if mso | IE]></td></tr></table></td></tr></table><![endif]-->