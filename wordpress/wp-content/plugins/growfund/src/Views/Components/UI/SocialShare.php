<?php

namespace Growfund\Views\Components\UI;

use Growfund\Constants\SocialSharing;
use Growfund\Core\AppSettings;
use Growfund\View;
use Growfund\DTO\Campaign\CampaignDTO;

defined('ABSPATH') || exit;

class SocialShare extends View
{
    /** @var string */
    public $title;

    /** @var string */
    public $url;

    /** @var string */
    public $label;

	protected function get_template_dir()
	{
		return 'site/components/ui';
	}

	protected function enqueue_styles()
	{
		wp_enqueue_style(
		'growfund-social-share-styles',
		GROWFUND_DIR_URL . 'resources/assets/site/styles/components/ui/social-share.css',
		['growfund-main-styles'],
		GROWFUND_VERSION
		);
	}

	public function get_casts()
	{
		return [
			'campaign' => CampaignDto::class,
		];
	}

	public function get_share_platforms() {
		if (!$this->title || !$this->url) {
			return [];
		}

		$social_shares = growfund_settings(AppSettings::CAMPAIGNS)->social_shares();

		if (empty($social_shares)) {
			return [];
		}

        $share_url = $this->url;

        $platforms = [];

		foreach ($social_shares as $platform) {
			$icon = 'assets/site/icon/' . $platform . '.svg';

			switch ($platform) {
				case 'facebook':
					$aria_label = esc_html__('Share on Facebook', 'growfund');
					$share_link = str_replace(
					['{url}', '{text}'],
					[urlencode($share_url), urlencode($this->title)],
					SocialSharing::FACEBOOK_URL
					);
					break;
				case 'x':
					$aria_label = esc_html__('Share on X', 'growfund');
					$share_link = str_replace(
					['{url}', '{text}'],
					[urlencode($share_url), urlencode($this->title)],
					SocialSharing::X_URL
					);
					break;
				case 'linkedin':
					$aria_label = esc_html__('Share on LinkedIn', 'growfund');
					$share_link = str_replace(
					['{url}', '{title}', '{text}'],
					[urlencode($share_url), urlencode($this->title), urlencode($this->title)],
					SocialSharing::LINKEDIN_URL
					);
					break;
				case 'whatsapp':
					$aria_label = esc_html__('Share on WhatsApp', 'growfund');
					$share_link = str_replace(
					['{text}', '{url}'],
					[urlencode($this->title), urlencode($share_url)],
					SocialSharing::WHATSAPP_URL
					);
					break;
				case 'telegram':
					$aria_label = esc_html__('Share on Telegram', 'growfund');
					$share_link = str_replace(
					['{url}', '{text}'],
					[urlencode($share_url), urlencode($this->title)],
					SocialSharing::TELEGRAM_URL
					);
					break;

				default:
					/* translators: %s: platform name */
					$aria_label = sprintf(esc_html__('Share on %s', 'growfund'), ucfirst($platform));
					$share_link = '#';
					break;
			}

            $platforms[] = [
                'name' => ucfirst($platform),
                'key' => $platform,
                'icon' => $this->get_svg_icon($icon),
                'aria_label' => $aria_label,
                'share_link' => $share_link,
                'title' => $this->title,
                'url' => $share_url
            ];
		}

        return $platforms;
	}
}
