<?php

defined( 'ABSPATH' ) || exit;

$growfund_button_text_color = $colors['button'] ?? '#000000';
$growfund_button_background_color = $colors['button_background'] ?? '#338C58';
?>
<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:separate;width:100%;line-height:100%;">
    <tr>
        <td align="center" bgcolor="<?php echo esc_html($growfund_button_background_color); ?>" role="presentation" style="border:none;border-radius:8px;cursor:auto;height:36px;mso-padding-alt:10px 25px;background:<?php echo esc_html($growfund_button_background_color); ?>;" valign="middle">
            <a href="<?php echo esc_url($link); ?>" style="display:inline-block;background:<?php echo esc_html($growfund_button_background_color); ?>;color:<?php echo esc_html($growfund_button_text_color); ?>;font-family:Inter, Arial, sans-serif;font-size:13px;font-weight:normal;line-height:120%;margin:0;text-decoration:none;text-transform:none;padding:10px 25px;mso-padding-alt:0px;border-radius:8px;" target="_blank"><?php echo esc_html($text); ?></a>
        </td>
    </tr>
</table>