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

use Growfund\Sanitizer;

if (!function_exists('render_filters')) {
    function render_filters($args = [])
    {
        $categories = $args['categories'] ?? [];
        $filter_state = $args['filter_state'] ?? [];

        // Helper function to build hierarchical options for the dropdown
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

        // Prepare category options
        $category_options = $build_options_recursive($categories);

        // Sort options
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

        <form method="GET" action="<?php echo esc_url(strtok(Sanitizer::apply_rule(wp_unslash(growfund_input_server('REQUEST_URI')), Sanitizer::TEXT), '?')); ?>" class="growfund-filters-form">
            <div class="growfund-filters">
                <div class="growfund-filters__left">
                    <div class="growfund-filters__main-search">
                        <div class="growfund-filters__search-wrapper">
                            <div class="growfund-filters__search-form" id="growfund-filters-search-form">
                                <div class="growfund-filters__search-field">
                                    <?php
                                    growfund_renderer()
                                        ->render('site.components.search-input', [
                                            'placeholder'  => __('Search campaigns', 'growfund'),
                                            'disabled'     => false,
                                            'name'         => 'search',
                                            'id'           => 'search',
                                            'value'        => $filter_state['search'] ?? '',
                                            'attributes'   => [
                                                'autocomplete' => 'off',
                                            ],
                                        ]);
                                    ?>
                                </div>
                            </div>
                            <div class="growfund-filters__mobile-trigger" id="growfund-mobile-filter-trigger">
                                <?php
                                growfund_renderer()
                                    ->render('site.components.icon', [
                                        'name' => 'filter',
                                        'size' => '20px',
                                    ]);
                                ?>
                            </div>
                        </div>
                        <div class="growfund-filters__category">
                            <?php
                            growfund_renderer()
                                ->render('site.components.dropdown', [
                                    'name'       => 'category',
                                    'id'         => 'campaign-category',
                                    'placeholder' => esc_html__('Categories', 'growfund'),
                                    'options'    => $category_options,
                                    'value'      => $filter_state['category'] ?? '',
                                    'variant'    => 'nested',
                                ]);
                            ?>
                        </div>
                    </div>
                </div>
                <div class="growfund-filters__right">
                    <div class="growfund-filters__sort">
                        <?php
                        growfund_renderer()
                            ->render('site.components.dropdown', [
                                'name'       => 'sort',
                                'id'         => 'campaign-sort',
                                'placeholder' => esc_html__('Sort by', 'growfund'),
                                'options'    => $sort_options,
                                'value'      => $filter_state['sort'] ?? '',
                            ]);
                        ?>
                    </div>

                </div>
            </div>
        </form>
        <?php
        growfund_renderer()
            ->render('site.components.mobile-filters', get_defined_vars());
        ?>

		<?php
    }
}

// Call the render function with the passed data
render_filters(get_defined_vars());
?>