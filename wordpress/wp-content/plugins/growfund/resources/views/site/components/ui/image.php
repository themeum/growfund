<?php 
/**
 * @var Growfund\Views\Components\UI\Image $image
 */

defined( 'ABSPATH' ) || exit;
?>

<div 
class="growfund-image-wrapper <?php echo $image->classname ? esc_attr(' ' . $image->classname) : ''; ?>"
<?php echo $image->id ? 'id="' . esc_attr($image->id) . '"' : ''; ?>
<?php echo $image->style ? 'style="' . esc_attr($image->style) . '"' : ''; ?>
>
<?php if ( ! empty($image->src) ) : ?>
    <img
        src="<?php echo esc_url($image->src); ?>"
        alt="<?php echo esc_attr($image->alt ?? __('Image', 'growfund')); ?>"
        loading="lazy"
        class="growfund-image"
        style="object-fit: <?php echo esc_attr($image->object_fit); ?>;"
    >
<?php else : ?>
    <div 
        class="growfund-image-fallback<?php echo $image->classname ? esc_attr(' ' . $image->classname) : ''; ?>"
    >
        <span class="growfund-image-acronym">
            <?php echo esc_html($image->acronym ?? ucfirst(substr($image->alt, 0, 1))); ?>
        </span>
    </div>
<?php endif; ?>
</div>
