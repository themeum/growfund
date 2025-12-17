<?php

defined( 'ABSPATH' ) || exit;

/**
 * Renders the filters component for campaign listing
 * 
 * @param array $args {
 *     Optional. Array of arguments.
 *     @type array  $categories     Array of category options
 *     @type array  $filter_state   Array of current filter values
 * }
 */
if (!function_exists('render_mobile_filters')) {
    function render_mobile_filters($args = [])
    {
        $categories = $args['categories'] ?? [];
        $filter_state = $args['filter_state'] ?? [];

        // Helper function to build hierarchical options for the dropdown - same as desktop filters
        $build_options_recursive = function ($categories_array, $parent_id = 0) use (&$build_options_recursive) {
            $options = [];
            foreach ($categories_array as $category) {
                if ((int) $category['parent_id'] === (int) $parent_id) {
                    $option = [
                        'value' => $category['slug'] ?? '',
                        'label' => $category['name'] ?? '',
                    ];
                    $children = $build_options_recursive($categories_array, $category['id']);

                    // Always set children, even if empty, to ensure it's treated as a category in the dropdown
                    $option['children'] = $children;

                    $options[] = $option;
                }
            }
            return $options;
        };

        // Prepare category options - same logic as desktop filters
        $category_options = $build_options_recursive($categories);

        // Sort options - only newest and end_date
        $sort_options = [
            [
				'value' => 'newest',
				'label' => __('Newest', 'growfund')
			],
            [
				'value' => 'end_date',
				'label' => __('End Date', 'growfund')
			],
        ];
		?>
        <div id="growfund-filter-modal" class="growfund-filter-modal">
            <div class="growfund-filter-modal__overlay"></div>
            <div class="growfund-filter-modal__content">
                <div class="growfund-filter-modal__header">
                    <div class="growfund-filter-modal__header-left">
                        <?php
                        growfund_renderer()->render('site.components.icon', [
							'name' => 'filter',
							'size' => 'md',
							'attributes' => ['class' => 'growfund-filter-modal__icon']
						]);
						?>
                        <h2 class="growfund-filter-modal__title"><?php esc_html_e('Filter', 'growfund'); ?></h2>
                    </div>
                    <?php
                    growfund_renderer()->render('site.components.icon', [
						'name' => 'cross',
						'size' => 'md',
						'attributes' => [
							'id' => 'growfund-filter-modal-close',
							'class' => 'growfund-filter-modal__close'
						]
					]);
					?>
                </div>
                <div class="growfund-filter-modal__body">
                    <?php
                    $category_group_classes = ['growfund-filter-group', 'growfund-mobile-filter-category--has-children'];
                    if (!empty($filter_state['category'])) {
                        $category_group_classes[] = 'growfund-mobile-filter-category--is-expanded';
                    }
                    ?>
                    <div class="<?php echo esc_html(implode(' ', $category_group_classes)); ?>">
                        <h3 class="growfund-filter-group__title">
                            <button type="button" class="growfund-mobile-filter-category__toggle">
                                <span class="growfund-mobile-filter-category__text"><?php esc_html_e('Categories', 'growfund'); ?></span>
                                <span class="growfund-mobile-filter-category__arrow">
                                    <?php
                                    growfund_renderer()->render('site.components.icon', [
										'name' => 'arrow-down',
										'size' => 'md'
									]);
									?>
                                </span>
                            </button>
                        </h3>
                        <div class="growfund-filter-group__content growfund-mobile-filter-category__children">
                            <div class="growfund-accordion-categories">
                                <?php
                                $render_categories = function ($categories, $is_child = false) use (&$render_categories, $filter_state) {
                                    $list_class = $is_child ? 'growfund-mobile-filter-category-list growfund-mobile-filter-category-list--is-child' : 'growfund-mobile-filter-category-list';
                                    echo '<ul class="' . esc_attr($list_class) . '">';

                                    foreach ($categories as $category) {
                                        $has_children = !empty($category['children']);
                                        $is_active = isset($filter_state['category']) && $filter_state['category'] === $category['value'];

                                        // Check if any descendant is the active category
                                        $is_active_parent = false;
                                        if ($has_children) {
                                            $find_active_descendant = function ($children, $active_slug) use (&$find_active_descendant) {
                                                foreach ($children as $child) {
                                                    if ($child['value'] === $active_slug) {
														return true;
                                                    }
                                                    if (!empty($child['children']) && $find_active_descendant($child['children'], $active_slug)) {
														return true;
                                                    }
                                                }
                                                return false;
                                            };
                                            if (!empty($filter_state['category'])) {
                                                $is_active_parent = $find_active_descendant($category['children'], $filter_state['category']);
                                            }
                                        }

                                        $li_classes = ['growfund-mobile-filter-category-item'];
                                        if ($has_children) {
											$li_classes[] = 'growfund-mobile-filter-category--has-children';
                                        }
                                        if ($is_active) {
											$li_classes[] = 'growfund-mobile-filter-category--is-active';
                                        }
                                        if ($is_active_parent) {
                                            $li_classes[] = 'growfund-mobile-filter-category--is-active-parent';
                                            $li_classes[] = 'growfund-mobile-filter-category--is-expanded';
                                        }

										?>
                                        <li class="<?php echo esc_html(implode(' ', $li_classes)); ?>">
                                            <div class="growfund-mobile-filter-category__header">
                                                <?php if ($has_children) : ?>
                                                    <button type="button" class="growfund-mobile-filter-category__toggle">
                                                        <span class="growfund-mobile-filter-category__text"><?php echo esc_html($category['label']); ?></span>
                                                        <span class="growfund-mobile-filter-category__arrow">
                                                            <?php
                                                            growfund_renderer()->render('site.components.icon', [
																'name' => 'arrow-down',
																'size' => 'md'
															]);
															?>
                                                        </span>
                                                    </button>
                                                <?php else : ?>
                                                    <?php if ($is_child) : ?>
                                                        <label class="growfund-mobile-filter-category__label">
                                                            <input type="radio" name="mobile_category_temp" value="<?php echo esc_attr($category['value']); ?>" <?php checked($is_active); ?> data-filter-type="category">
                                                            <?php echo esc_html($category['label']); ?>
                                                        </label>
                                                    <?php else : ?>
                                                        <label class="growfund-mobile-filter-category__label growfund-mobile-filter-category__clickable" data-value="<?php echo esc_attr($category['value']); ?>" data-filter-type="category">
                                                            <input type="radio" name="mobile_category_temp" value="<?php echo esc_attr($category['value']); ?>" <?php checked($is_active); ?> style="display: none;">
                                                            <?php echo esc_html($category['label']); ?>
                                                        </label>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($has_children) : ?>
                                                <div class="growfund-mobile-filter-category__children">
                                                    <?php $render_categories($category['children'], true); ?>
                                                </div>
                                            <?php endif; ?>
                                        </li>
										<?php
                                    }
                                    echo '</ul>';
                                };

                                $render_categories($category_options);
		?>
                            </div>
                        </div>
                    </div>

                    <?php
                    $sort_group_classes = ['growfund-filter-group', 'growfund-mobile-filter-category--has-children'];
                    if (!empty($filter_state['sort'])) {
                        $sort_group_classes[] = 'growfund-mobile-filter-category--is-expanded';
                    }
                    ?>
                    <div class="<?php echo esc_html(implode(' ', $sort_group_classes)); ?>">
                        <h3 class="growfund-filter-group__title">
                            <button type="button" class="growfund-mobile-filter-category__toggle">
                                <span class="growfund-mobile-filter-category__text"><?php esc_html_e('Sort by', 'growfund'); ?></span>
                                <span class="growfund-mobile-filter-category__arrow">
                                    <?php
                                    growfund_renderer()->render('site.components.icon', [
										'name' => 'arrow-down',
										'size' => 'md'
									]);
									?>
                                </span>
                            </button>
                        </h3>
                        <div class="growfund-filter-group__content growfund-mobile-filter-category__children">
                            <ul class="growfund-mobile-filter-category-list" data-filter-group="sort">
                                <?php foreach ($sort_options as $option) : ?>
                                    <?php
                                    $is_active = isset($filter_state['sort']) && $filter_state['sort'] === $option['value'];
                                    $li_classes = ['growfund-mobile-filter-category-item'];
                                    if ($is_active) {
                                        $li_classes[] = 'growfund-mobile-filter-category--is-active';
                                    }
                                    ?>
                                    <li class="<?php echo esc_html(implode(' ', $li_classes)); ?>" data-value="<?php echo esc_attr($option['value']); ?>">
                                        <label class="growfund-mobile-filter-category__label growfund-mobile-filter-category__clickable" data-value="<?php echo esc_attr($option['value']); ?>" data-filter-type="sort">
                                            <input type="radio" name="mobile_sort_temp" value="<?php echo esc_attr($option['value']); ?>" <?php checked($is_active); ?> style="display: none;">
                                            <?php echo esc_html($option['label']); ?>
                                        </label>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="growfund-filter-modal__footer">
                    <?php
                    growfund_renderer()->render('site.components.button', [
                        'text' => __('Clear All', 'growfund'),
                        'type' => 'button',
                        'id' => 'growfund-filter-clear',
                        'class' => 'growfund-btn--gray growfund-btn--gray-5'
                    ]);
                    ?>
                    <?php
                    growfund_renderer()->render('site.components.button', [
                        'text' => __('Apply', 'growfund'),
                        'type' => 'button',
                        'id' => 'growfund-filter-apply',
                        'class' => 'growfund-btn--primary growfund-branding-btn'
                    ]);
                    ?>
                </div>
            </div>
        </div>
		<?php
    }
}

// Call the render function with the passed data
render_mobile_filters(get_defined_vars());
?>