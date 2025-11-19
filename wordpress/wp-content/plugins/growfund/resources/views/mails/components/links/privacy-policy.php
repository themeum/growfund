<?php

defined( 'ABSPATH' ) || exit;

$privacy_policy_link = growfund_privacy_policy_url();
?>

<a href="<?php echo esc_url($privacy_policy_link); ?>" style="font-size:16px;line-height:24px;color:#0055FF"><?php echo esc_html__('Privacy Policy', 'growfund'); ?></a>