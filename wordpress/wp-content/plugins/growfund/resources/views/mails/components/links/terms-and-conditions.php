<?php

defined( 'ABSPATH' ) || exit;

$terms_and_conditions = growfund_terms_and_conditions_url();
?>

<a href="<?php echo esc_url($terms_and_conditions); ?>" style="font-size:16px;line-height:24px;color:#0055FF"><?php echo esc_html__('Terms & Conditions', 'growfund'); ?></a>