<?php

namespace Growfund\Services;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\RewardItemDTO;
use Growfund\DTO\RewardItemWithQuantityDTO;
use Growfund\Http\Response;
use Growfund\PostTypes\RewardItem;
use Growfund\Supports\Arr;
use Growfund\Supports\MediaAttachment;
use Exception;
use Growfund\Constants\OptionKeys;
use Growfund\Constants\Reward\RewardType;
use Growfund\Constants\RewardItem\AssetType;
use Growfund\Constants\RewardItem\RewardItemType;
use Growfund\Constants\Status\PaymentStatus;
use Growfund\Constants\Status\PledgeStatus;
use Growfund\Supports\Date;
use Growfund\Supports\Option;
use Growfund\Supports\PostMeta;
use Growfund\Supports\Utils;
use WP_Error;
use WP_Post;

class RewardItemService
{
    /**
     * Get all reward items by campaign id.
     * 
     * @param int $campaign_id - default 0
     * 
     * @return RewardItemDTO[]
     */
    public function get_all_by_campaign(int $campaign_id = 0, string $reward_item_type = 'all')
    {
        if (empty($campaign_id)) {
            return [];
        }

        $args = [
            'post_type' => RewardItem::NAME,
            'post_parent' => $campaign_id,
            'posts_per_page' => -1,
            'orderby' => 'ID',
            'order' => 'ASC',
        ];

        if (in_array($reward_item_type, RewardItemType::get_constant_values(), true)) {
            if ($reward_item_type === RewardItemType::PHYSICAL) {
                $args['meta_query'][] = [
					'relation' => 'OR',
					[
						'key'     => growfund_with_prefix('type'),
						'compare' => 'NOT EXISTS',
					],
					[
						'key'     => growfund_with_prefix('type'),
						'value'   => $reward_item_type,
						'compare' => '=',
					],
				];
            } else {
                $args['meta_key'] = growfund_with_prefix('type');
				$args['meta_value'] = $reward_item_type;
            }
        }

        $reward_items = get_posts($args);

        return Arr::make($reward_items)->map(function (WP_Post $reward_item) {
            $item_type = PostMeta::get($reward_item->ID, 'type');
            $thumbnail_id = get_post_thumbnail_id($reward_item->ID);
            $dto = new RewardItemDTO();
            $dto->id = (string) $reward_item->ID;
            $dto->campaign_id = (string) $reward_item->post_parent;
            $dto->type = $item_type ? $item_type : RewardItemType::PHYSICAL;
            $dto->title = $reward_item->post_title;
            $dto->image = !empty($thumbnail_id) ? MediaAttachment::make($thumbnail_id) : null;
            $dto->created_at = $reward_item->post_date_gmt;

            if ($dto->type === RewardItemType::DIGITAL) {
                $dto->asset_type = PostMeta::get($reward_item->ID, 'asset_type') ?? AssetType::FILE;

                if ($dto->asset_type === AssetType::FILE) {
                    $dto->asset = PostMeta::get($reward_item->ID, 'asset');
                    $dto->asset = !empty($dto->asset) ? MediaAttachment::make($dto->asset) : null;
                }

                if ($dto->asset_type === AssetType::URL) {
                    $dto->asset_url = PostMeta::get($reward_item->ID, 'asset_url');
                    $dto->asset_url = !empty($dto->asset_url) ? $dto->asset_url : null;
                }
            }

            return $dto;
        })->toArray();
    }

    /**
     * Store a new reward item.
     *
     * @param RewardItemDTO $dto
     * @return int
     * @throws Exception
     */
    public function store(RewardItemDTO $dto)
    {
        $reward_item_id = wp_insert_post([
            'post_type'       => RewardItem::NAME,
            'post_title'      => $dto->title,
            'post_status'     => RewardItem::DEFAULT_POST_STATUS,
            'post_author'     => get_current_user_id(),
            'post_parent'     => $dto->campaign_id,
        ], true);

        if (is_wp_error($reward_item_id)) {
            /* translators: %s: error message */
            throw new Exception(sprintf(esc_html__('Failed to create reward item: %s', 'growfund'), esc_html($reward_item_id->get_error_message())));
        }

        if (!empty($dto->image)) {
            set_post_thumbnail($reward_item_id, $dto->image);
        }

        PostMeta::add($reward_item_id, 'type', $dto->type);

        if ($dto->type === RewardItemType::DIGITAL) {
            PostMeta::add($reward_item_id, 'asset_type', $dto->asset_type);
            
			if ($dto->asset_type === AssetType::FILE) {
				PostMeta::add($reward_item_id, 'asset', $dto->asset);
			}

			if ($dto->asset_type === AssetType::URL) {
                PostMeta::add($reward_item_id, 'asset_url', $dto->asset_url);
			}
		}

        return $reward_item_id;
    }

    /**
     * Update campaign category
     * 
     * @param int $id
     * @param RewardItemDTO $dto
     * 
     * @return bool
     * @throws Exception
     */
    public function update(int $id, RewardItemDTO $dto)
    {
        $reward_item = get_post($id);

        if (!$reward_item || $reward_item->post_parent !== $dto->campaign_id || $reward_item->post_type !== RewardItem::NAME) {
            throw new Exception(esc_html__('Reward item not found', 'growfund'), (int) Response::NOT_FOUND);
        }

        $reward_item_id = wp_update_post([
            'ID'           => $id,
            'post_title'   => $dto->title,
            'post_parent'  => $dto->campaign_id,
        ], true);

        if (is_wp_error($reward_item_id)) {
            throw new Exception(esc_html__('Failed to update the reward item', 'growfund'));
        }

        if (!empty($dto->image)) {
            set_post_thumbnail($reward_item_id, $dto->image);
        } else {
            delete_post_thumbnail($reward_item_id);
        }

        PostMeta::update($reward_item_id, 'type', $dto->type);

        if ($dto->type === RewardItemType::DIGITAL) {
            PostMeta::update($reward_item_id, 'asset_type', $dto->asset_type);
            
			if ($dto->asset_type === AssetType::FILE) {
				PostMeta::update($reward_item_id, 'asset', $dto->asset);
			}

			if ($dto->asset_type === AssetType::URL) {
                PostMeta::update($reward_item_id, 'asset_url', $dto->asset_url);
			}
		}

        return true;
    }

    /**
     * Delete reward item
     * 
     * @param int $campaign_id
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public function delete(int $campaign_id, int $id)
    {
        $reward_item = get_post($id);

        if (!$reward_item || $reward_item->post_parent !== $campaign_id || $reward_item->post_type !== RewardItem::NAME) {
            throw new Exception(esc_html__('Reward item not found', 'growfund'), (int) Response::NOT_FOUND);
        }

        $result = wp_delete_post($id, true);

        if (!$result) {
            throw new Exception(esc_html__('Failed to delete the reward item', 'growfund'));
        }

        return !empty($result);
    }

    /**
     * Delete reward item by parent id
     * 
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public function delete_by_parent_id(int $id)
    {
        $reward_items = get_posts([
            'post_type' => RewardItem::NAME,
            'post_parent' => $id,
            'numberposts' => -1,
            'post_status' => 'any',
        ]);

        if (empty($reward_items)) {
            return true;
        }

        foreach ($reward_items as $item) {
            wp_delete_post($item->ID, true);
        }

        return true;
    }

    /**
     * Get reward item by ids
     * 
     * @param int $campaign_id
     * @param array{array{id:int,quantity:int}} $reward_item_lists
     * 
     * @return RewardItemWithQuantityDTO[]
     */
    public function get_reward_items_with_quantity(int $campaign_id, string $reward_type, array $reward_item_lists)
    {
        if (empty($reward_item_lists) || empty($campaign_id)) {
            return [];
        }

        // check if the reward item list is valid
        foreach ($reward_item_lists as $item) {
            if (!array_key_exists('id', $item) || !array_key_exists('quantity', $item)) {
                return [];
            }
        }

        $ids = array_column($reward_item_lists, 'id');
        $reward_item_quantity_list = array_column($reward_item_lists, 'quantity', 'id');

        $args = [
            'post_type' => RewardItem::NAME,
            'post__in' => $ids,
            'posts_per_page' => -1,
            'post_parent' => $campaign_id,
        ];

        if ($reward_type === RewardType::PHYSICAL_GOODS) {
            $args['meta_query'][] = [
                'relation' => 'OR',
                [
                    'key'     => growfund_with_prefix('type'),
                    'compare' => 'NOT EXISTS',
                ],
                [
                    'key'     => growfund_with_prefix('type'),
                    'value'   => RewardItemType::PHYSICAL,
                    'compare' => '=',
                ],
            ];
		}

		if ($reward_type === RewardType::DIGITAL_GOODS) {
			$args['meta_key'] = growfund_with_prefix('type');
			$args['meta_value'] = RewardItemType::DIGITAL;
		}

        $result = get_posts($args);

        $reward_items = [];

        foreach ($result as $reward_item) {
            $item_type = PostMeta::get($reward_item->ID, 'type');
            $thumbnail_id = get_post_thumbnail_id($reward_item->ID);
            $dto = new RewardItemWithQuantityDTO();
            $dto->id = (string) $reward_item->ID;
            $dto->campaign_id = (string) $reward_item->post_parent;
            $dto->type = $item_type ? $item_type : RewardItemType::PHYSICAL;
            $dto->title = $reward_item->post_title;
            $dto->image = !empty($thumbnail_id) ? MediaAttachment::make($thumbnail_id) : null;
            $dto->created_at = $reward_item->post_date_gmt;
            $dto->quantity = $reward_item_quantity_list[$reward_item->ID] ?? 0;

            if ($dto->type === RewardItemType::DIGITAL) {
                $dto->asset_type = PostMeta::get($reward_item->ID, 'asset_type') ?? AssetType::FILE;

                if ($dto->asset_type === AssetType::FILE) {
                    $dto->asset = PostMeta::get($reward_item->ID, 'asset');
                    $dto->asset = !empty($dto->asset) ? MediaAttachment::make($dto->asset) : null;
                }

                if ($dto->asset_type === AssetType::URL) {
                    $dto->asset_url = PostMeta::get($reward_item->ID, 'asset_url');
                    $dto->asset_url = !empty($dto->asset_url) ? $dto->asset_url : null;
                }
            }

            $reward_items[] = $dto;
        }

        return $reward_items;
    }

    public function is_item_downloadable(RewardItemDTO $reward_item)
    {
        $is_downloadable = false;

        if ($reward_item->type === RewardItemType::DIGITAL) {
			if ($reward_item->asset_type === AssetType::FILE) {
				$is_downloadable = !empty($reward_item->asset);
			} 
                        
			if ($reward_item->asset_type === AssetType::URL) {
				$is_downloadable = !empty($reward_item->asset_url);
			}
		}

        return $is_downloadable;
    }

    public function can_user_download_reward_item(RewardItemDTO $reward_item, string $pledge_status, string $payment_status, int $backer_id, ?int $user_id = null)
    {
        if ($reward_item->type !== RewardItemType::DIGITAL) {
            return false;
        }

        $is_ready_for_download = in_array($pledge_status, [PledgeStatus::COMPLETED, PledgeStatus::BACKED], true) && $payment_status === PaymentStatus::PAID;

        if (!$is_ready_for_download) {
            return false;
        }

        if (growfund_user($user_id)->get_id() === $backer_id) {
            return true;
        }

        if (growfund_user($user_id)->is_admin()) {
            return true;
        }

        if (growfund_user($user_id)->is_fundraiser()) {
            $campaign_ids = growfund_get_all_campaign_ids_by_fundraiser();

            return in_array((int) $reward_item->campaign_id, $campaign_ids, true);
        }

        return false;
    }

    public function get_downloadable_link_for_digital_goods(string $uid, int $reward_item_id)
    {
        $pledge = (new PledgeService())->get_by_uid($uid);

        $reward_item = Arr::make($pledge->reward->items ?? [])->find(function ($item) use ($reward_item_id) {
            return (int) $item->id === $reward_item_id;
        });

        if (empty($reward_item)) {
            throw new Exception(esc_html__('Reward item not found', 'growfund'), (int) Response::NOT_FOUND);
        }

        if (!$reward_item->can_download) {
            throw new Exception(esc_html__('You cannot download this reward', 'growfund'), (int) Response::FORBIDDEN);
        }

        return Utils::generate_digital_reward_download_url($uid, $reward_item_id);
    }

    public function handle_reward_item_secure_download(string $pledge_uid, int $reward_item_id, string $signature, int $expires, int $user_id) {
        if (empty($pledge_uid) || empty($reward_item_id) || empty($signature) || empty($expires) || empty($user_id)) {
            throw new Exception(esc_html__('Invalid request', 'growfund'), (int) Response::BAD_REQUEST);
		}

        $current_time = (int) strtotime(Date::current_sql_safe());

        if ($current_time > (int) $expires) {
            throw new Exception(esc_html__('Download link expired', 'growfund'), (int) Response::FORBIDDEN);
		}

        $data = $user_id . '|' . $pledge_uid . '|' . $reward_item_id . '|' . $expires;

        $has_key = Option::get(OptionKeys::DOWNLOAD_HASH_KEY);

        $expected_signature = hash_hmac(
            'sha256',
            $data,
            $has_key
		);

        if (!hash_equals($expected_signature, $signature)) {
            throw new Exception(esc_html__('Invalid signature', 'growfund'), (int) Response::FORBIDDEN);
		}

        $pledge = (new PledgeService())->get_by_uid($pledge_uid);

        /** @var RewardItemDTO */
        $reward_item = Arr::make($pledge->reward->items ?? [])->find(function ($item) use ($reward_item_id) {
            return (int) $item->id === $reward_item_id;
        });

        if (empty($reward_item)) {
            throw new Exception(esc_html__('Reward item not found', 'growfund'), (int) Response::NOT_FOUND);
        }

        if (
            !$this->is_item_downloadable($reward_item) 
            || !$this->can_user_download_reward_item($reward_item, $pledge->status, $pledge->payment->payment_status, (int) $pledge->backer->id, $user_id)
        ) {
            throw new Exception(esc_html__('Reward item type is not downloadable', 'growfund'), (int) Response::FORBIDDEN);
        }

        if ($reward_item->asset_type === AssetType::URL) {
            growfund_redirect($reward_item->asset_url);
            exit;
        }

        $attachment_id = 0;

        if (!empty($reward_item->asset)) {
            $attachment_id = is_numeric($reward_item->asset) ? (int) $reward_item->asset : (int) ($reward_item->asset['id'] ?? 0);
        }

        $file_path = get_attached_file($attachment_id);

        if (!$file_path || !file_exists($file_path)) {
            throw new Exception(esc_html__('File not found', 'growfund'), (int) Response::NOT_FOUND);
		}

        $file_name = basename($file_path);
		$mime_type = get_post_mime_type($attachment_id);

        if (ob_get_length()) {
			ob_end_clean();
		}

        header('Content-Description: File Transfer');
		header('Content-Type: ' . $mime_type);
		header('Content-Disposition: attachment; filename="' . $file_name . '"');
		header('Content-Length: ' . filesize($file_path));
		header('Cache-Control: private, no-store, no-cache');
		header('Expires: 0');

		readfile($file_path); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile -- read binary bites from file
		exit;
    }
}
