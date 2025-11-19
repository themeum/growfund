<?php

defined( 'ABSPATH' ) || exit;

$background_color = $colors['background'] ?? '#ffffff';
$image_height = $media['height'] ?? 20;
$image_position = $media['position'] ?? 'center';
$src = $media['image'] ?? growfund_placeholder_image_url();
$aspect_ratio = 7.5;
$image_width = $image_height * $aspect_ratio;

?>
<!-- Header --><!--[if mso | IE]><tr><td class="" width="560px" ><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:560px;" width="560" bgcolor="<?php echo esc_html($background_color); ?>" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
<div
    style="background:<?php echo esc_html($background_color); ?>;background-color:<?php echo esc_html($background_color); ?>;margin:0px auto;border-radius:8px 8px 0 0;max-width:560px;">
    <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation"
        style="background:<?php echo esc_html($background_color); ?>;background-color:<?php echo esc_html($background_color); ?>;width:100%;border-radius:8px 8px 0 0;">
        <tbody>
            <tr>
                <td
                    style="border-bottom:1px solid #e6e6e6;direction:ltr;font-size:0px;padding:24px 64px;text-align:center;">
                    <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:432px;" ><![endif]-->
                    <div class="mj-column-per-100 mj-outlook-group-fix"
                        style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                        <table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%">
                            <tbody>
                                <tr>
                                    <td style="vertical-align:top;padding:0;">
                                        <table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%">
                                            <tbody>
                                                <tr>
                                                    <td align="center" style="font-size:0px;padding:0;word-break:break-word;">
                                                        <table border="0" cellpadding="0" cellspacing="0" role="presentation"
                                                            style="border-collapse:collapse;border-spacing:0px;width:100%;">
                                                            <tbody>
                                                                <tr>
                                                                    <td style="text-align:<?php echo esc_html($image_position); ?>;">
                                                                        <img alt="Banner Image" height="<?php echo esc_html($image_height); ?>"
                                                                            src="<?php echo esc_url($src); ?>"
                                                                            style="border:0;display:inline-block;outline:none;text-decoration:none;height:<?php echo esc_html($image_height); ?>px;width:auto;font-size:16px;"
                                                                            width="<?php echo esc_html($image_width); ?>">
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div><!--[if mso | IE]></td></tr></table><![endif]-->
                </td>
            </tr>
        </tbody>
    </table>
</div>
<!--[if mso | IE]></td></tr></table></td></tr><![endif]-->