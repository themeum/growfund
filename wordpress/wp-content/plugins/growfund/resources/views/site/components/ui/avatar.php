<?php
/**
 * @var Growfund\Views\Components\UI\Avatar $avatar
 */

defined('ABSPATH') || exit;

$growfund_avatar_src = ! empty( $avatar->src )
    ? $avatar->src
    : growfund_user_avatar();
$growfund_acronym = ucfirst(substr($avatar->avatar_name ?? '', 0, 1));
?>

<div class="growfund-avatar-image-wrapper <?php echo esc_attr($avatar->classname ?? ''); ?>" >
    <?php if (!empty($avatar->src) && !$avatar->use_acronym) : ?>
    <img
        src="<?php echo esc_url($growfund_avatar_src); ?>"
        alt="<?php echo esc_attr($avatar->avatar_name ?? __('Avatar', 'growfund')); ?>"
        loading="lazy"
        class="growfund-avatar-image"
        <?php echo $avatar->id ? 'id="' . esc_attr($avatar->id) . '"' : ''; ?>
    >
    <?php else : ?>
        <div class="growfund-avatar-acronym"><?php echo esc_html($growfund_acronym); ?></div>
    <?php endif; ?>
</div>

