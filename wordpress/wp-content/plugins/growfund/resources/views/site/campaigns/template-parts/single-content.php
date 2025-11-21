<?php

defined( 'ABSPATH' ) || exit;

if ($data) : 
    $total_media_count = 0;
	?>
    <div class="growfund-container">
        <?php
        if (growfund_app()->is_donation_mode() && !empty($data->contribution)) {
            growfund_renderer()->render(
                'site.components.donation-success-modal',
                [
                    'contribution' => $data->contribution
                ]
            );
        } elseif (!growfund_app()->is_donation_mode() && !empty($data->contribution)) {
            growfund_renderer()->render(
                'site.components.pledge-success-modal',
                [
                    'contribution' => $data->contribution
                ]
            );
        } elseif (isset($_GET['failed'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only check, no action performed.
            growfund_renderer()->render('site.components.contribution-failed-modal');
        }
        ?>
        <div class="growfund-project">
            <div class="growfund-project__header">
                <div class="growfund-project__main">
                    <h1 class="growfund-project__title"><?php echo esc_html($data->title ?? ''); ?></h1>
                    <p class="growfund-project__description">
					<?php echo esc_html($data->description ?? ''); ?>
                    </p>

                    <div class="growfund-media growfund-media-slider">
					<?php
					if (empty($data->images) && empty($data->video) && empty($data->video['url'])) :
						?>
                            <div class="growfund-media__container">
							<?php 
								growfund_renderer()
								->render('site.components.image', [
									'src' => growfund_site_placeholder_image_url(false),
									'alt' => 'Campaign Placeholder Image',
									'attributes' => [
										'class' => 'growfund-media__image growfund-media__placeholder'
									]
								]);
							?>
                            </div>
                        <?php else : ?>
                            <div class="growfund-media__container__wrapper">
                                <div class="growfund-media__container">
                                    <?php
                                    if (!empty($data->video) && !empty($data->video['url'])) : 
                                        ++$total_media_count;
                                        ?>
                                        <div class="growfund-media__item">
                                        <?php 
                                            growfund_renderer()
                                                ->render('site.components.video', [
                                                    'src' => $data->video['url'],
                                                    'poster' => '',
                                                    'title' => '',
                                                    'attributes' => [
                                                        'class' => 'growfund-media__video growfund-video-hidden'
                                                    ],
                                                    'controls' => false,
                                                    'muted' => false,
                                                    'autoplay' => false,
                                                    'thumbnail' => $data->video['poster']['url'] ?? ''
                                                ]);
                                        ?>
                                            </div>
                                            <?php 
                                        endif;
                                    ?>
                                    <?php 
                                    if (!empty($data->images) && is_array($data->images)) :
                                        ?>
                                        <?php
                                        foreach ($data->images as $image) {
                                            ++$total_media_count;
                                            ?>
                                                <div class="growfund-media__item">
                                                    <?php
                                                        growfund_renderer()
                                                        ->render('site.components.image', [
                                                            'src' => !empty($image['url']) ? $image['url'] : growfund_placeholder_image_url(),
                                                            'alt' => esc_attr($image['alt'] ?? 'Campaign Image'),
                                                            'attributes' => [
                                                                'class' => 'growfund-media__image'
                                                            ]
                                                        ]);
                                                    ?>
                                                </div>
                                            <?php
                                        }
                                    endif;
                                    ?>
                                </div>
                                <?php if ($total_media_count > 1) : ?>
                                    <button class="growfund-slider-btn prev" disabled>
                                        <svg width="18" height="15" viewBox="0 0 18 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M7.76568 0.234324C8.07812 0.54674 8.07812 1.05327 7.76568 1.36568L2.73138 6.4H16.8C17.2418 6.4 17.6 6.75818 17.6 7.2C17.6 7.64183 17.2418 8 16.8 8H2.73138L7.76568 13.0342C8.07812 13.3467 8.07812 13.8533 7.76568 14.1658C7.45327 14.4781 6.94674 14.4781 6.63432 14.1658L0.234324 7.76568C-0.078108 7.45327 -0.078108 6.94674 0.234324 6.63432L6.63432 0.234324C6.94674 -0.078108 7.45327 -0.078108 7.76568 0.234324Z" fill="#F5F5F5"/>
                                        </svg>

                                    </button>
                                    <button class="growfund-slider-btn next">
                                        <svg width="18" height="15" viewBox="0 0 18 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M9.83432 0.234324C10.1467 -0.078108 10.6533 -0.078108 10.9657 0.234324L17.3658 6.63432C17.6781 6.94674 17.6781 7.45327 17.3658 7.76568L10.9657 14.1658C10.6533 14.4781 10.1467 14.4781 9.83432 14.1658C9.52189 13.8533 9.52189 13.3467 9.83432 13.0342L14.8686 8H0.8C0.358176 8 0 7.64183 0 7.2C0 6.75818 0.358176 6.4 0.8 6.4H14.8686L9.83432 1.36568C9.52189 1.05327 9.52189 0.54674 9.83432 0.234324Z" fill="#F5F5F5"/>
                                        </svg>
                                    </button>
                                <?php endif; ?>
                            </div>
                        
							<?php if ($total_media_count > 1) : ?>
                                <div class="growfund-media__thumbnails">
                                    <?php if (!empty($data->video) && !empty($data->video['url'])) : ?>
                                        <div class="growfund-thumb">
                                            <img src="<?php echo !empty($data->video['poster']) && !empty($data->video['poster']['url']) ? esc_url($data->video['poster']['url']) : esc_url(growfund_site_placeholder_image_url()); ?>" alt="thumb" />
                                        </div>
                                    <?php endif; ?>
                                    <?php 
                                    if (!empty($data->images) && is_array($data->images)) :
                                        ?>
                                        <?php
                                        foreach ($data->images as $image) {
                                            ?>
                                            <div class="growfund-thumb"><img src="<?php echo !empty($image['url']) ? esc_url($image['url']) : esc_url(growfund_site_placeholder_image_url()); ?>" alt="thumb" /></div>
                                            <?php
                                        }
                                    endif;
                                    ?>
                                </div>
                            <?php endif; ?>
                    <?php endif; ?>
                    </div>

                    <?php if (!empty($data->tags)) : ?>
                        <div class="growfund-tags-section">
                            <div class="growfund-tags">
                                <div class="growfund-tags__icon">
                                    <?php
                                    growfund_renderer()
                                        ->render('site.components.icon', [
                                            'name' => 'tags',
                                            'size' => 'sm'
                                        ]);
									?>
                                </div>
                                <span>
                                    <?php
                                    $tagCount = count($data->tags);
                                    foreach ($data->tags as $index => $tag) { // phpcs:ignore
                                        echo esc_html($tag->name);
                                        if ($index < $tagCount - 1) {
                                            echo ', ';
                                        }
                                    }
                                    ?>
                                </span>
                            </div>
                            <div class="growfund-tags__button">
                                <?php
                                // Prepare sharing data
                                $social_shares = growfund_social_sharing_options();
                                $share_url = growfund_campaign_url($data->post_id ?? get_the_ID());
                                $share_title = $data->update_title ?? $data->title ?? '';
                                /* translators: %s: campaign title */
                                $share_text = sprintf(__('Check out this campaign: %s', 'growfund'), $share_title);

                                if (!empty($social_shares)) {
                                    growfund_renderer()
                                        ->render('site.components.social-share', [
                                            'social_shares' => $social_shares,
                                            'share_url' => $share_url,
                                            'share_title' => $share_title,
                                            'share_text' => $share_text
                                        ]);
                                }
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <?php
                growfund_renderer()
				->render('site.components.campaign-sidebar', [
					'campaign' => $data
				]);
				?>
            </div>

            <?php
            $totalUpdatesCount = 0;
            if (is_object($data) && isset($data->total_campaign_updates_count)) {
                $totalUpdatesCount = $data->total_campaign_updates_count;
            }

            $totalCommentsCount = 0;
            if (is_object($data) && isset($data->total_comments_count)) {
                $totalCommentsCount = $data->total_comments_count;
            }
            ?>
            <?php
            $actions = [];
            if (!growfund_app()->is_donation_mode() && !$data->is_ended) {
                $actions = [
                    [
						'label' => __('Back this campaign', 'growfund'),
						'variant' => 'growfund-btn--primary'
					]
                ];
            }
            ?>
            <?php
            $campaignTabLabel = growfund_app()->is_donation_mode() ? 'Info' : 'Campaign';
            $tabs = [ // phpcs:ignore
			[
				'label' => $campaignTabLabel,
				'active' => true
			]
            ];

            if (!growfund_app()->is_donation_mode()) {
                array_splice($tabs, 1, 0, [
                    [
						'label' => __('Rewards', 'growfund'),
						'active' => false
					]
                ]);
            }

            if (isset($data->can_see_campaign_updates) && $data->can_see_campaign_updates) {
                $tabs[] = [ // phpcs:ignore
					'label' => __('Updates', 'growfund'),
					'active' => false,
					'badge' => $totalUpdatesCount
				];
            }

            if (!empty($data->comments) || (isset($data->comment_form_data) && $data->comment_form_data !== null && !empty($data->comment_form_data))) {
                $tabs[] = [ // phpcs:ignore
					'label' => __('Comments', 'growfund'),
					'active' => false,
					'badge' => $totalCommentsCount
				];
            }

            $insertPosition = growfund_app()->is_donation_mode() ? 1 : 2;
            array_splice($tabs, $insertPosition, 0, [
				[
					'label' => __('FAQ', 'growfund'),
					'active' => false
				]
            ]);
			?>
		<?php
		growfund_renderer()
			->render('site.components.tabs', [
				'tabs' => $tabs,
				'actions' => $actions,
				'is_ended' => $data->is_ended
			]);
		?>

            <div class="growfund-tab-container">
			<?php
			growfund_renderer()
				->render('site.components.tab-content-campaign', [
					'campaign' => $data
				]);
			?>
			<?php if (!growfund_app()->is_donation_mode()) : ?>
                    <?php
                    growfund_renderer()
                        ->render('site.components.tab-content-rewards', [
                            'rewards' => $data->rewards ?? [],
                            'campaign' => $data
                        ]);
					?>
                <?php endif; ?>
                <?php
                growfund_renderer()
				->render('site.components.tab-content-faq', [
					'faqs' => $data->faqs ?? []
				]);
				?>
			<?php if (isset($data->can_see_campaign_updates) && $data->can_see_campaign_updates) : ?>
                    <?php
                    growfund_renderer()
                        ->render('site.components.tab-content-updates', [
                            'updates' => !empty($data->campaign_updates) ? $data->campaign_updates : [],
                            'campaign' => $data,
                            'campaign_id' => $campaign_id
                        ]);
					?>
                <?php endif; ?>
                <?php if (!empty($data->comments) || (isset($data->comment_form_data) && $data->comment_form_data !== null && !empty($data->comment_form_data))) : ?>
                    <?php
                    growfund_renderer()
                        ->render('site.components.tab-content-comments', [
                            'comments' => $data->comments ?? [],
                            'comment_form_data' => $data->comment_form_data ?? null,
                            'campaign_id' => $campaign_id
                        ]);
					?>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <?php if (!empty($data->related_campaigns)) : ?>
        <?php
        growfund_renderer()
            ->render('site.components.recommendations', [
                'campaigns' => $data->related_campaigns,
                'title' => __('We also recommend', 'growfund'),
                'exploreText' => __('Explore more', 'growfund')
            ]);
		?>
    <?php endif; ?>

    <?php if ($data) : ?>
        <?php
        growfund_renderer()
            ->render('site.components.pledge-modal', [
                'campaign' => $data,
                'rewards' => $data->rewards ?? [],
                'min_pledge_amount' => $data->min_pledge_amount ?? 1,
                'max_pledge_amount' => $data->max_pledge_amount ?? null
            ]);
		?>
    <?php endif; ?>

<?php else : ?>
    <div class="growfund-container">
        <p><?php esc_html_e('Campaign not found.', 'growfund'); ?></p>
    </div>
<?php endif; ?>