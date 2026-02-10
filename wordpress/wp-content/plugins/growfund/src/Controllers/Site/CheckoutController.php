<?php

namespace Growfund\Controllers\Site;

defined( 'ABSPATH' ) || exit;

use Exception;
use Growfund\Contracts\Request;
use Growfund\Core\AppSettings;
use Growfund\DTO\Donation\CreateDonationDTO;
use Growfund\DTO\Pledge\CreatePledgeDTO;
use Growfund\Exceptions\ValidationException;
use Growfund\Services\CampaignService;
use Growfund\Services\CheckoutService;
use Growfund\Supports\Url;
use Growfund\Validation\Validator;

class CheckoutController
{
    /**
     * @var CampaignService
     */
    private $campaign_service;

    /**
     * @var CheckoutService
     */
    private $service;

    public function __construct() {
        $this->campaign_service = new CampaignService();
        $this->service = new CheckoutService();
    }

    public function show(Request $request) {
        $campaign_id = $request->get_int('campaign_id');

        if (empty($campaign_id)) {
            return Url::redirect_back();
        }

        $campaign = $this->campaign_service->get_by_id($campaign_id);

        if (growfund_app()->is_donation_mode()) {
            return $this->service->get_donation_checkout_page($campaign);
        }

        if (!growfund_user()->is_logged_in()) {
            return growfund_redirect(growfund_login_url(Url::get_previous_url()));
        }

        return $this->service->get_pledge_checkout_page($campaign, $request->get_int('reward_id'));
    }


    public function store(Request $request) {
        if (!$request->get_bool('terms_agreement', false)) {
			growfund_flash_set_message('checkout_error', 'You should accept the terms and conditions.');
            Url::redirect_back();
        }
        
        if (growfund_app()->is_donation_mode()) {
			
            if (!growfund_user()->is_logged_in() && !growfund_settings(AppSettings::PAYMENT)->allow_guest_checkout()) {
                growfund_flash_set_message('checkout_error', 'You must be logged in to make a donation.');
                growfund_redirect(growfund_login_url(Url::get_previous_url()));
            }

			$validator = Validator::make($request, CreateDonationDTO::checkout_validation_rules());

			if ($validator->is_failed()) {
				growfund_flash_set_message('checkout_form_errors', $validator->get_errors());
				Url::redirect_back();
			}

			try {
				$redirect_url = $this->service->create_donation($request);
			} catch (ValidationException $error) {
				growfund_flash_set_message('checkout_form_errors', $error->get_errors());
				Url::redirect_back();
			} catch (Exception $error) {
				growfund_flash_set_message('checkout_error', $error->getMessage());
				Url::redirect_back();
			}

			return growfund_redirect($redirect_url);
        }


        $validator = Validator::make($request, CreatePledgeDTO::checkout_validation_rules());

        if ($validator->is_failed()) {
            growfund_flash_set_message('checkout_form_errors', $validator->get_errors());
            Url::redirect_back();
        }

        try {
			$redirect_url = $this->service->create_pledge($request);
        } catch (ValidationException $error) {
            growfund_flash_set_message('checkout_form_errors', $error->get_errors());
            Url::redirect_back();
        } catch (Exception $error) {
            growfund_flash_set_message('checkout_error', $error->getMessage());
            Url::redirect_back();
        }

        return growfund_redirect($redirect_url);
    }
}
