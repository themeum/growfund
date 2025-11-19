<?php

namespace Growfund\Constants\Site;

defined( 'ABSPATH' ) || exit;

use Growfund\Traits\HasConstants;

/**
 * Social Media Sharing Constants
 * 
 * Contains URLs for social media sharing platforms
 * 
 * @since 1.0.0
 */
class SocialSharing
{
    use HasConstants;

    /**
     * Facebook sharing URL
     */
    const FACEBOOK_URL = 'https://www.facebook.com/sharer/sharer.php?u={url}';

    /**
     * X (Twitter) sharing URL
     */
    const X_URL = 'https://x.com/intent/tweet?url={url}&text={text}';

    /**
     * LinkedIn sharing URL
     */
    const LINKEDIN_URL = 'https://www.linkedin.com/shareArticle?url={url}&title={title}&summary={text}';

    /**
     * WhatsApp sharing URL
     */
    const WHATSAPP_URL = 'https://wa.me/?text={text}%20{url}';

    /**
     * Telegram sharing URL
     */
    const TELEGRAM_URL = 'https://t.me/share/url?url={url}&text={text}';
}
