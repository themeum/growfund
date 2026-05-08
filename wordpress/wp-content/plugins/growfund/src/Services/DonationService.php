<?php

namespace Growfund\Services;

defined( 'ABSPATH' ) || exit;

use Growfund\App\Events\CampaignHalfMilestoneReachedEvent;
use Growfund\App\Events\DonationCreatedEvent;
use Growfund\App\Events\DonationStatusUpdateEvent;
use Growfund\App\Events\GoalReachedEvent;
use Growfund\Constants\Campaign\ReachingAction;
use Growfund\Constants\DateTimeFormats;
use Growfund\Constants\MetaKeys\Campaign;
use Growfund\Constants\Status\CampaignStatus;
use Growfund\Constants\Status\DonationStatus;
use Growfund\Constants\Status\PaymentStatus;
use Growfund\Constants\Tables;
use Growfund\Constants\WP;
use Growfund\DTO\BulkActionDTO;
use Growfund\DTO\Campaign\CampaignDTO;
use Growfund\DTO\Donation\AnnualReceiptDonationDTO;
use Growfund\DTO\Donation\AnnualReceiptDTO;
use Growfund\DTO\Donation\AnnualReceiptFilterDTO;
use Growfund\DTO\Donation\CreateDonationDTO;
use Growfund\DTO\Donation\DonationAnnualReceiptDTO;
use Growfund\DTO\Donation\DonationCampaignDTO;
use Growfund\DTO\Donation\DonationDTO;
use Growfund\DTO\Donation\DonationFilterParamsDTO;
use Growfund\DTO\Donation\DonationFundDTO;
use Growfund\DTO\PaginatedCollectionDTO;
use Growfund\Exceptions\ValidationException;
use Growfund\Http\Response;
use Growfund\QueryBuilder;
use Growfund\Supports\Arr;
use Growfund\Supports\CampaignGoal;
use Growfund\Supports\Date;
use Growfund\Supports\MediaAttachment;
use Growfund\Supports\Money;
use Growfund\Core\AppSettings;
use Growfund\Payments\DTO\PaymentMethodDTO;
use DateTime;
use Exception;
use Growfund\Constants\Campaign\FundSelectionType;
use Growfund\Constants\Campaign\TributeNotificationPreference;
use Growfund\Constants\Campaign\TributeRequirement;
use Growfund\Constants\UserTypes\Collaborator;
use Growfund\Constants\UserTypes\Donor;
use Growfund\Constants\UserTypes\Fundraiser;
use Growfund\DTO\Donation\DonationDonorDTO;
use Growfund\Supports\Payment;

class DonationService
{
    /**
     * Get paginated donations data
     */
    public function paginated(DonationFilterParamsDTO $params)
    {
        $query = $this->get_query($params);

        if (!empty($params->orderby)) {
            $query->order_by('donations.' . $params->orderby, $params->order);
        }

        $overall_query_conditions = [];

        if ($params->user_id !== growfund_user()->get_id()) {
            if (growfund_user()->has_active_role(Fundraiser::ROLE)) {
                $overall_query_conditions[] = ['campaign_id', 'IN', growfund_campaign_ids_by_fundraiser()];
            }

            if (growfund_user()->has_active_role(Collaborator::ROLE)) {
                $overall_query_conditions[] = ['campaign_id', 'IN', growfund_campaign_ids_by_collaborator()];
            }
        }

        if (!empty($params->user_id)) {
            $overall_query_conditions[] = ['user_id', '=', $params->user_id];
        }

        $data = $query->paginate(
            $params->page,
            $params->limit,
            $overall_query_conditions
        );

        foreach ($data['results'] as $key => $donation) {
            $data['results'][$key] = $this->prepare_donation_dto($donation);
        }

        $paginated_collection_dto = PaginatedCollectionDTO::from_array($data);

        return $paginated_collection_dto;
    }

    /**
     * Get the latest donations.
     *
     * Retrieves a list of the latest donations based on the provided filter parameters,
     * ordered by creation date in descending order. Limits the number of results if specified.
     *
     * @param DonationFilterParamsDTO $params_dto The parameters to filter the donations.
     * 
     * @return DonationDTO[] An array of formatted donation list items.
     */

    public function latest(DonationFilterParamsDTO $params_dto)
    {
        $query = $this->get_query($params_dto)->order_by('donations.created_at', 'DESC');

        if ($params_dto->limit) {
            $query->limit($params_dto->limit);
        }

        $donations = $query->get();

        foreach ($donations as $key => $donation) {
            $donations[$key] = $this->prepare_donation_dto($donation);
        }

        return $donations;
    }

    /**
     * Retrieve all donations based on the provided filter parameters.
     *
     * @param DonationFilterParamsDTO $params The parameters to filter the donations.
     *
     * @return DonationDTO[] An array of formatted donation list items.
     */
    public function all(DonationFilterParamsDTO $params)
    {
        $query = $this->get_query($params);

        $donations = $query->all();

        foreach ($donations as $key => $donation) {
            $donations[$key] = $this->prepare_donation_dto($donation);
        }

        return $donations;
    }

    /**
     * Prepare donation dto
     * @param object $donation
     * @return DonationDTO
     */
    protected function prepare_donation_dto($donation)
    {
        $donor_dto = null;

        if (growfund_is_valid_json($donation->user_info)) {
            $user_info = json_decode($donation->user_info, true);
            $user_info['image'] = !empty($user_info['image']) ? MediaAttachment::make($user_info['image']) : null;
            $user_info['is_verified'] = !empty($user_info['id']) && growfund_user($user_info['id'])->is_verified();
            $donor_dto = DonationDonorDTO::from_array($user_info);
            $donor_dto->id = (string) $donor_dto->id;
            $donor_dto->email = $donation->email;
            $donor_dto->phone = $donor_dto->phone;
        }

        $dto = DonationDTO::from_array((array) $donation);
        $dto->id = (string) $donation->ID;
        $dto->uid = $donation->uid;
        $dto->amount = $donation->amount;
        $dto->campaign = $this->prepare_donation_campaign_dto($donation);
        $dto->donor = $donor_dto;
        $dto->fund = DonationFundDTO::from_array([
            'id' => $donation->fund_id,
            'title' => $donation->fund_title
        ]);
        $dto->payment_method = PaymentMethodDTO::from_array(json_decode($donation->payment_method ?? '', true) ?? []);

        return $dto;
    }

    /**
     * Prepare donation campaign list item dto
     * @param object $donation
     * @return DonationCampaignDTO
     */
    protected function prepare_donation_campaign_dto($donation)
    {
        $images = !empty($donation->campaign_images) ? maybe_unserialize($donation->campaign_images) : [];

        $campaign_service = new CampaignService();

        $campaign = new DonationCampaignDTO();
        $campaign->id = $donation->campaign_id;
        $campaign->slug = $donation->campaign_slug;
        $campaign->title = $donation->campaign_name;
        $campaign->status = $donation->campaign_status;
        $campaign->fund_raised = $this->get_total_donated_amount($donation->campaign_id);
        $campaign->goal_type = $donation->campaign_goal_type;
        $campaign->goal_amount = $donation->campaign_goal_amount;
        $campaign->images =  MediaAttachment::make_many($images);
        $campaign->start_date = $donation->campaign_start_date;
        $campaign->author = $campaign_service->get_campaign_author($donation->campaign_author_id);
        $campaign->fundraiser = !empty($donation->campaign_fundraiser_id) 
            ? $campaign_service->get_campaign_fundraiser($donation->campaign_fundraiser_id) 
            : $campaign->author;
        $campaign->is_launched = !empty($donation->campaign_start_date) 
            ? Date::is_date_in_past_or_present($donation->campaign_start_date) 
            : false;

        return $campaign;
    }


    /**
     * Get a donation by ID.
     * @param int $id
     * @return DonationDTO
     * @throws \Exception
     */
    public function get_by_id(int $id)
    {
        $donation = $this->get_query(new DonationFilterParamsDTO())->find($id, 'donations.ID');

        if (!$donation) {
            throw new Exception(esc_html__('Donation not found', 'growfund'), (int) Response::NOT_FOUND);
        }

        return $this->prepare_donation_dto($donation);
    }


    /**
     * Get a donation by uid.
     * @param string $uid
     * @return DonationDTO
     * @throws \Exception
     */
    public function get_by_uid(string $uid)
    {
        $donation = $this->get_query(new DonationFilterParamsDTO())->find($uid, 'donations.uid');

        if (!$donation) {
            throw new Exception(esc_html__('Donation not found', 'growfund'), (int) Response::NOT_FOUND);
        }

        return $this->prepare_donation_dto($donation);
    }

    /**
     * Get a donation by transaction ID.
     * @param string $id
     * @return DonationDTO
     * @throws \Exception
     */
    public function get_by_transaction_id(string $id)
    {
        $donation = $this->get_query(new DonationFilterParamsDTO())->where('donations.transaction_id', $id)->first();

        if (!$donation) {
            throw new Exception(esc_html__('Donation not found', 'growfund'), (int) Response::NOT_FOUND);
        }

        return $this->prepare_donation_dto($donation);
    }

    /**
     * Create a new donation.
     *
     * @param CreateDonationDTO $dto The dto for the donation.
     * @return int The ID of the newly created donation.
     * @throws Exception If the data could not be inserted.
     */
    public function create(CreateDonationDTO $dto)
    {
        $is_manual = $dto->is_manual === true;

        if (growfund_user()->is_donor() && $is_manual) {
            throw new Exception(esc_html__('You can not create a manual donation', 'growfund'), (int) Response::FORBIDDEN);
        }

        $campaign_service = new CampaignService();
        $campaign = $campaign_service->get_by_id($dto->campaign_id);

        if (!growfund_settings(AppSettings::CAMPAIGNS)->allow_fund()) {
            $dto->fund_id = (new FundService())->get_default_fund()->id;
        }

        if (growfund_settings(AppSettings::CAMPAIGNS)->allow_fund() && $campaign->fund_selection_type === FundSelectionType::FIXED) {
            $dto->fund_id = $campaign->default_fund ?? (new FundService())->get_default_fund()->id;
        }

        if (empty($dto->status)) {
            $dto->status = DonationStatus::PENDING;
        }

        if (growfund_settings(AppSettings::CAMPAIGNS)->allow_tribute() && $campaign->tribute_notification_preference !== TributeNotificationPreference::LET_DONOR_DECIDE) {
            $dto->tribute_notification_type = $campaign->tribute_notification_preference;
        }

        
        $this->check_campaign_constraints($campaign, $dto);
        $this->check_amount_constraints((int) $dto->amount, $campaign);
        
        $dto->uid = growfund_uuid();
        $dto->is_manual = $is_manual;
        $dto->payment_engine = growfund_payment_engine();
        $dto->is_anonymous = growfund_settings(AppSettings::PERMISSIONS)->allow_anonymous_donation() && !empty($dto->is_anonymous);
        $dto->created_at = Date::current_sql_safe();
        $dto->created_by = growfund_user()->get_id();
        $dto->updated_at = Date::current_sql_safe();
        $dto->updated_by = growfund_user()->get_id();
        $dto->tribute_notification_recipient_address =  wp_json_encode($dto->tribute_notification_recipient_address);

        $dto->payment_status = $this->get_payment_status($dto->status);

        if (empty($dto->user_info)) {
            $donor_dto = (new UserService())->get_by_user_id($dto->user_id);
            $donor_dto->image = $donor_dto->image['id'] ?? null;
            $donor_dto->email = $dto->email ?? $donor_dto->email;
            $user_info = DonationDonorDTO::from_array($donor_dto->to_array());
            $dto->user_info = wp_json_encode($user_info->to_array());
            $dto->email = $user_info->email;
        }

        $dto->payment_method = empty($dto->payment_method) || is_string($dto->payment_method) 
            ? Payment::get_payment_method_by_name($dto->payment_method ?? '') 
            : wp_json_encode($dto->payment_method->to_array());

        $donation_id = QueryBuilder::query()->table(Tables::DONATIONS)->create($dto->to_array());

        if ($donation_id === false) {
            throw new Exception(esc_html__('Failed to create the donation', 'growfund'));
        }

        if ($campaign->status !== CampaignStatus::FUNDED && CampaignGoal::is_reached($campaign, $dto)) {
            $is_campaign_funded =$campaign_service->update_status($campaign->id, CampaignStatus::FUNDED);

            if ($is_campaign_funded) {
                $campaign->status = CampaignStatus::FUNDED;
                growfund_event(new GoalReachedEvent($campaign));
            }
        }

        if (!$campaign->is_half_goal_achieved_already && CampaignGoal::is_half_goal_achieved($campaign, $dto)) {
            $campaign_service->marked_as_half_goal_achieved($campaign->id);
            growfund_event(new CampaignHalfMilestoneReachedEvent($campaign->id));
        }

        growfund_event(new DonationCreatedEvent($dto, $donation_id, $campaign));

        if ($dto->status !== DonationStatus::PENDING) {
            $donation = $this->get_by_id($donation_id);
            growfund_event(new DonationStatusUpdateEvent($donation, $donation->status));
        }

        return $donation_id;
    }

    /**
     * Get payment status
     * 
     * @param string $status
     * @return string
     */
    protected function get_payment_status($status) {
        if ($status === DonationStatus::COMPLETED) {
            return PaymentStatus::PAID;
        } elseif ($status === DonationStatus::FAILED || $status === DonationStatus::CANCELLED) {
            return PaymentStatus::FAILED;
        }

        return PaymentStatus::UNPAID;
    }

    /**
     * Check campaign constraints for donation
     * 
     * @param CampaignDTO $campaign_dto
     * @param CreateDonationDTO $donation_dto
     * @param bool $throwable
     * @return bool
     * @throws ValidationException
     */
    public function check_campaign_constraints(CampaignDTO $campaign_dto, CreateDonationDTO $donation_dto, $throwable = true)
    {
        if (!$campaign_dto->is_launched) {
            if (!$throwable) {
                return false;
            }

            throw ValidationException::with_errors([
                'campaign_id' => [esc_html__('Campaign is not launched yet', 'growfund')]
            ]);
        }

        if ($campaign_dto->is_ended) {
            if (!$throwable) {
                return false;
            }

            throw ValidationException::with_errors([
                'campaign_id' => [esc_html__('Campaign has ended', 'growfund')]
            ]);
        }

        if (growfund_settings(AppSettings::CAMPAIGNS)->allow_fund() && !$donation_dto->fund_id) {
            if (!$throwable) {
                return false;
            }

            throw ValidationException::with_errors([
                'fund_id' => [esc_html__('Fund is required', 'growfund')]
            ]);
        }

        if (growfund_settings(AppSettings::CAMPAIGNS)->allow_tribute() && $campaign_dto->has_tribute && $campaign_dto->tribute_requirement === TributeRequirement::REQUIRED && empty($donation_dto->tribute_type)) {
            if (!$throwable) {
                return false;
            }

            throw ValidationException::with_errors([
                'tribute_type' => [esc_html__('Tribute is required', 'growfund')]
            ]);
        }

        if (
            $campaign_dto->reaching_action === ReachingAction::CLOSE
            && $campaign_dto->has_goal
            && CampaignGoal::is_reached($campaign_dto)
        ) {
            if (!$throwable) {
                return false;
            }

            throw ValidationException::with_errors([
                'campaign_id' => [esc_html__('Campaign gaol already reached', 'growfund')]
            ]);
        }

        return true;
    }

    /**
     * Check amount constraints for donation
     * @param int $donation_amount
     * @param CampaignDTO $campaign_dto
     * @param bool $throwable
     * 
     * @return bool
     * @throws ValidationException
     */
    public function check_amount_constraints(int $donation_amount, CampaignDTO $campaign_dto, $throwable = true) {
        if (growfund_user()->get_active_role() === Donor::ROLE) {
            $allowed_option = Arr::make($campaign_dto->suggested_options ?? [])
                ->some(function ($item) use ($donation_amount) {
                    return (int) $item['amount'] === $donation_amount;
                });

            if (!$campaign_dto->allow_custom_donation) {
                if (!$allowed_option) {
                    if (!$throwable) {
                        return false;
                    }

                    throw ValidationException::with_errors([
                        'amount' => [esc_html__('Donation amount is not allowed', 'growfund')],
                    ]);
                }
            }

            if ($campaign_dto->allow_custom_donation && !$allowed_option) {
                if ($donation_amount < (int) $campaign_dto->min_donation_amount) {
                    if (!$throwable) {
                        return false;
                    }

                    throw ValidationException::with_errors([
                        /* translators: %s: minimum donation amount */
                        'amount' => [sprintf(esc_html__('Minimum donation amount is %s', 'growfund'), esc_html(Money::prepare_for_display($campaign_dto->min_donation_amount)))],
                    ]);
                }

                if ($donation_amount > (int) $campaign_dto->max_donation_amount) {
                    if (!$throwable) {
                        return false;
                    }

                    throw ValidationException::with_errors([
                        /* translators: %s: maximum donation amount */
                        'amount' => [sprintf(esc_html__('Cannot donate more than %s', 'growfund'), esc_html(Money::prepare_for_display($campaign_dto->max_donation_amount)))],
                    ]);
                }
            }
        }

        return true;
    }

    /**
     * Update the status of a donation.
     *
     * @param int $id The ID of the donation.
     * @param string $status The action to update the status.
     * @return bool True if the status was updated, false otherwise.
     */
    public function update_status(int $id, string $status)
    {
        $donation = $this->get_by_id($id);

        $data = [
            'status' => $status,
            'updated_at' => Date::current_sql_safe(),
            'updated_by' => growfund_user()->get_id()
        ];

        if (in_array($status, [DonationStatus::PENDING, DonationStatus::CANCELLED], true)) {
            $data['payment_status'] = PaymentStatus::UNPAID;
        } elseif ($status === DonationStatus::COMPLETED) {
            $data['payment_status']  = PaymentStatus::PAID;
        } elseif ($status === DonationStatus::FAILED) {
            $data['payment_status'] = PaymentStatus::FAILED;
        }

        $is_updated = QueryBuilder::query()
            ->table(Tables::DONATIONS)
            ->where('ID', $id)
            ->update($data);

        if (!empty($is_updated)) {
            growfund_event(new DonationStatusUpdateEvent($donation, $status));
        }

        return !empty($is_updated);
    }

    /**
     * Partial update a donation.
     *
     * @param int $id The ID of the donation to update.
     * @param array $data An array of data to update.
     * @return bool True if successfully updated the donation or false.
     * @throws Exception If the data could not be updated.
     */
    public function partial_update($id, array $data)
    {
        $donation = $this->get_by_id($id);
        
        $data['updated_at'] = Date::current_sql_safe();
        $data['updated_by'] = growfund_user()->get_id();

        $result = QueryBuilder::query()->table(Tables::DONATIONS)->where('ID', $id)->update($data);

        if ($result === false) {
            throw new Exception(esc_html__('Failed to update the donation', 'growfund'));
        }

        if (isset($data['status'])) {
            growfund_event(new DonationStatusUpdateEvent($donation, $data['status']));
        }

        return true;
    }

    /**
     * Delete a donation by its ID.
     *
     * @param int $id The ID of the donation to delete.
     * @param bool $force Force delete or trash
     * @return bool True if the donation was successfully deleted, false otherwise.
     * @throws Exception If the donation could not be deleted due to its status.
     */
    public function delete(int $id, $force = false)
    {
        $donation = QueryBuilder::query()->table(Tables::DONATIONS)->find($id);

        if (!$donation) {
            return false;
        }

        if ($force) {
            return QueryBuilder::query()->table(Tables::DONATIONS)->where('ID', $id)->delete();
        }

        return QueryBuilder::query()->table(Tables::DONATIONS)->where('ID', $id)->update(['status' => DonationStatus::TRASHED]);
    }

    public function bulk_actions(BulkActionDTO $dto)
    {
        $result = [];

        switch ($dto->action) {
            case 'trash':
                $result = $this->bulk_delete($dto->ids, false);
                break;
            case 'delete':
                $result = $this->bulk_delete($dto->ids, true);
                break;
            case 'restore':
                $result = $this->bulk_restore($dto->ids);
                break;
            case 'reassign_fund':
                $result = $this->bulk_reassign_fund($dto->ids, $dto->meta['fund_id']);
                break;
        }

        return $result;
    }

    /**
     * Delete multiple existing donations by their id's with associated metadata.
     *
     * @param array $ids The ID's of the donations.
     * @param bool $force Force delete or trash
     * @return array Response array with success and failure messages.
     * @throws Exception If something went wrong.
     */
    public function bulk_delete(array $ids, bool $force = false)
    {
        $succeeded = [];
        $failed = [];

        foreach ($ids as $id) {
            try {
                $result = $this->delete($id, $force);

                if ($result === false) {
                    $failed[] = [
                        'id' => $id,
                        'message' => $force
                            ? __('Donation could not be deleted.', 'growfund')
                            : __('Donation could not be trashed.', 'growfund'),
                    ];
                } else {
                    $succeeded[] = [
                        'id' => $id,
                        'message' => $force
                            ? __('Donation has been deleted.', 'growfund')
                            : __('Donation has been trashed.', 'growfund'),
                    ];
                }
            } catch (Exception $error) {
                $failed[] = [
                    'id' => $id,
                    'message' => $error->getMessage(),
                ];
            }
        }

        return [
            'succeeded' => $succeeded,
            'failed' => $failed,
        ];
    }

    /**
     * Restore multiple trashed donations by their id's.
     * Iterates through each id and attempts to restore the corresponding donation.
     * Collects information on which donations were successfully restored and which failed.
     *
     * @param array<int> $ids The ids of the donations to be restored.
     * @return array Contains 'succeeded' and 'failed' arrays with id and message for each donation.
     * @throws Exception If an error occurs during the restoration process.
     */
    public function bulk_restore(array $ids)
    {
        $succeeded = [];
        $failed = [];

        foreach ($ids as $id) {
            try {
                $result = $this->restore($id);

                if ($result === false) {
                    $failed[] = [
                        'id' => $id,
                        'message' => __('Donation could not be restored.', 'growfund'),
                    ];
                } else {
                    $succeeded[] = [
                        'id' => $id,
                        'message' => __('Donation has been restored.', 'growfund'),
                    ];
                }
            } catch (Exception $error) {
                $failed[] = [
                    'id' => $id,
                    'message' => $error->getMessage(),
                ];
            }
        }

        return [
            'succeeded' => $succeeded,
            'failed' => $failed,
        ];
    }

    /**
     * Reassign a donation to a new fund.
     *
     * @param int $donation_id The ID of the donation.
     * @param int $fund_id The ID of the new fund.
     *
     * @return bool True if the donation was successfully reassigned.
     */
    public function reassign_fund(int $donation_id, int $fund_id)
    {
        return QueryBuilder::query()->table(Tables::DONATIONS)->where('ID', $donation_id)->update(['fund_id' => $fund_id]);
    }

    /**
     * Reassign multiple donations to a new fund.
     *
     * @param array<int> $ids The IDs of the donations.
     * @param int $fund_id The ID of the new fund.
     *
     * @return array Contains 'succeeded' and 'failed' arrays with id and message for each donation.
     * @throws Exception If an error occurs during the reassignment process.
     */
    public function bulk_reassign_fund(array $ids, int $fund_id)
    {
        $succeeded = [];
        $failed = [];

        foreach ($ids as $id) {
            try {
                $result = $this->reassign_fund($id, $fund_id);

                if ($result === false) {
                    $failed[] = [
                        'id' => $id,
                        'message' => __('Donation could not be reassigned.', 'growfund'),
                    ];
                } else {
                    $succeeded[] = [
                        'id' => $id,
                        'message' => __('Donation has been reassigned.', 'growfund'),
                    ];
                }
            } catch (Exception $error) {
                $failed[] = [
                    'id' => $id,
                    'message' => $error->getMessage(),
                ];
            }
        }

        return [
            'succeeded' => $succeeded,
            'failed' => $failed,
        ];
    }

    /**
     * Move all donations with a given fund with a new fund.
     *
     * @param int $current_fund_id The ID of the fund to replace.
     * @param int $new_fund_id The ID of the new fund.
     *
     * @return bool True if the replacement was successful, false otherwise.
     */
    public function change_fund(int $current_fund_id, int $new_fund_id): bool
    {
        return QueryBuilder::query()->table(Tables::DONATIONS)->where('fund_id', $current_fund_id)->update(['fund_id' => $new_fund_id]);
    }

    /**
     * Move all donations with a given fund with the default fund.
     *
     * @param int $current_fund_id The ID of the fund to replace.
     *
     * @return bool True if the replacement was successful, false otherwise.
     */
    public function change_fund_to_default(int $current_fund_id): bool
    {
        $default_fund_id = (new FundService())->get_default_fund()->id;

        return $this->change_fund($current_fund_id, (int) $default_fund_id);
    }

    /**
     * Restore a trashed donation.
     *
     * @param int $id The ID of the donation to restore.
     * @return bool True if the donation was successfully restored, false otherwise.
     */
    public function restore(int $id): bool
    {
        return QueryBuilder::query()->table(Tables::DONATIONS)->where('ID', $id)->update(['status' => DonationStatus::PENDING]);
    }

    /**
     * Empty the trash of donations.
     *
     * This function deletes all donations with the status "trashed".
     *
     * @param int|null $user_id The ID of the user to empty the trash for.
     * @return bool True if the trash was successfully emptied, false otherwise.
     */
    public function empty_trash($user_id = null)
    {
        if (!empty($user_id)) {
            return QueryBuilder::query()
                ->table(Tables::DONATIONS . ' as donations')
                ->left_join(WP::POSTS_TABLE . ' as campaigns', 'donations.campaign_id', 'campaigns.ID')
                ->where('donations.status', DonationStatus::TRASHED)
                ->where('campaigns.post_author', $user_id)
                ->delete('donations');
        }

        return QueryBuilder::query()->table(Tables::DONATIONS)->where('status', DonationStatus::TRASHED)->delete();
    }

    /**
     * Build a query with all the conditions applied from the given parameters.
     *
     * @param DonationFilterParamsDTO $params The parameters to filter the query with.
     *
     * @return QueryBuilder The query builder with all the conditions applied.
     */
    protected function get_query(DonationFilterParamsDTO $params)
    {
        $postmeta_table = WP::POST_META_TABLE;

        $query = QueryBuilder::query()
            ->select([
                'donations.*',
                'campaigns.post_title as campaign_name',
                'campaigns.post_name as campaign_slug',
                'campaign_status_meta.meta_value as campaign_status',
                'campaign_start_date_meta.meta_value as campaign_start_date',
                'campaign_images_meta.meta_value as campaign_images',
                'campaign_goal_amount_meta.meta_value as campaign_goal_amount',
                'campaign_goal_type_meta.meta_value as campaign_goal_type',
                'campaign_fundraiser_id_meta.meta_value as campaign_fundraiser_id',
                'funds.title as fund_title',
                'campaigns.post_author as campaign_author_id',
            ])
            ->table(Tables::DONATIONS . ' as donations')
            ->left_join(
                'posts as campaigns',
                'donations.campaign_id',
                'campaigns.ID'
            )
            ->join_raw(
                "{$postmeta_table} as campaign_status_meta",
                "LEFT",
                "campaigns.ID = campaign_status_meta.post_id AND campaign_status_meta.meta_key = :campaign_status_meta_key",
                [
                    'campaign_status_meta_key' => growfund_with_prefix(Campaign::STATUS)
                ]
            )
            ->join_raw(
                "{$postmeta_table} as campaign_start_date_meta",
                "LEFT",
                "donations.campaign_id = campaign_start_date_meta.post_id AND campaign_start_date_meta.meta_key = :campaign_start_date_meta_key",
                [
                    'campaign_start_date_meta_key' => growfund_with_prefix(Campaign::START_DATE)
                ]
            )
            ->join_raw(
                "{$postmeta_table} as campaign_images_meta",
                "LEFT",
                "campaigns.ID = campaign_images_meta.post_id AND campaign_images_meta.meta_key = :campaign_images_meta_key",
                [
                    'campaign_images_meta_key' => growfund_with_prefix(Campaign::IMAGES)
                ]
            )
            ->join_raw(
                "{$postmeta_table} as campaign_goal_amount_meta",
                "LEFT",
                "campaigns.ID = campaign_goal_amount_meta.post_id AND campaign_goal_amount_meta.meta_key = :campaign_goal_amount_meta_key",
                [
                    'campaign_goal_amount_meta_key' => growfund_with_prefix(Campaign::GOAL_AMOUNT)
                ]
            )
            ->join_raw(
                "{$postmeta_table} as campaign_goal_type_meta",
                "LEFT",
                "campaigns.ID = campaign_goal_type_meta.post_id AND campaign_goal_type_meta.meta_key = :campaign_goal_type_meta_key",
                [
                    'campaign_goal_type_meta_key' => growfund_with_prefix(Campaign::GOAL_TYPE)
                ]
            )
            ->join_raw(
                "{$postmeta_table} as campaign_fundraiser_id_meta",
                "LEFT",
                "donations.campaign_id = campaign_fundraiser_id_meta.post_id AND campaign_fundraiser_id_meta.meta_key = :campaign_fundraiser_id_meta_key",
                [
                    'campaign_fundraiser_id_meta_key' => growfund_with_prefix(Campaign::FUNDRAISER_ID)
                ]
            )
            ->left_join(
                Tables::FUNDS . ' as funds',
                'donations.fund_id',
                'funds.ID'
            );

        if (!empty($params->user_id) && $params->user_id !== growfund_user()->get_id()) {
            if (growfund_user()->has_active_role(Fundraiser::ROLE)) {
                $query->where_in('donations.campaign_id', growfund_campaign_ids_by_fundraiser());
            }

            if (growfund_user()->has_active_role(Collaborator::ROLE)) {
                $query->where_in('donations.campaign_id', growfund_campaign_ids_by_collaborator());
            }
        }

        if ($params->user_id) {
            $query->where('donations.user_id', $params->user_id);
        }
        if ($params->campaign_id) {
            $query->where('donations.campaign_id', $params->campaign_id);
        }
        if ($params->fund_id) {
            $query->where('donations.fund_id', $params->fund_id);
        }
        if ($params->status) {
            $query->where('donations.status', $params->status);
        }
        if (empty($params->status)) {
            $query->where('donations.status', '!=', DonationStatus::TRASHED);
        }
        if ($params->start_date) {
            $query->where('DATE(donations.created_at)', '>=', $params->start_date);
        }
        if ($params->end_date) {
            $query->where('DATE(donations.created_at)', '<=', $params->end_date);
        }
        if ($params->search) {
            $query->where('campaigns.post_title', 'LIKE', "%{$params->search}%");
        }

        return $query;
    }

    /**
     * Gets the total fund raised by a campaign in a given date range.
     *
     * @param int $campaign_id The campaign ID.
     * @param string|null $start_date The start date in 'Y-m-d' format. Optional.
     * @param string|null $end_date The end date in 'Y-m-d' format. Optional.
     *
     * @return int The total fund raised in cents.
     */
    public function get_total_donated_amount($campaign_id = null, $start_date = null, $end_date = null)
    {
        $start_date = !empty($start_date) ? new DateTime($start_date) : null;
        $end_date = !empty($end_date) ? new DateTime($end_date) : null;

        $query = QueryBuilder::query()->table(Tables::DONATIONS)->where('status', '=', DonationStatus::COMPLETED);

        if (growfund_user()->has_active_role(Fundraiser::ROLE)) {
            $campaign_ids = growfund_campaign_ids_by_fundraiser();
            $query->where_in('campaign_id', $campaign_ids);
        }

        if (growfund_user()->has_active_role(Collaborator::ROLE)) {
            $campaign_ids = growfund_campaign_ids_by_collaborator();
            $query->where_in('campaign_id', $campaign_ids);
        }

        if (!empty($campaign_id)) {
            $query->where('campaign_id', $campaign_id);
        }

        if ($start_date !== null) {
            $query->where_date('created_at', '>=', $start_date->format(DateTimeFormats::DB_DATE));
        }

        if ($end_date !== null) {
            $query->where_date('created_at', '<=', $end_date->format(DateTimeFormats::DB_DATE));
        }

        return $query->sum('amount');
    }

    /**
     * Gets the total net amount by a campaign in a given date range.
     *
     * @param int $campaign_id The campaign ID.
     * @param string|null $start_date The start date in 'Y-m-d' format. Optional.
     * @param string|null $end_date The end date in 'Y-m-d' format. Optional.
     *
     * @return int The total net amount in cents.
     */
    public function get_total_net_amount($campaign_id = null, $start_date = null, $end_date = null)
    {
        $start_date = !empty($start_date) ? new DateTime($start_date) : null;
        $end_date = !empty($end_date) ? new DateTime($end_date) : null;

        $query = QueryBuilder::query()->table(Tables::DONATIONS)->where('status', '=', DonationStatus::COMPLETED);

        if (growfund_user()->has_active_role(Fundraiser::ROLE)) {
            $campaign_ids = growfund_campaign_ids_by_fundraiser();
            $query->where_in('campaign_id', $campaign_ids);
        }

        if (growfund_user()->has_active_role(Collaborator::ROLE)) {
            $campaign_ids = growfund_campaign_ids_by_collaborator();
            $query->where_in('campaign_id', $campaign_ids);
        }

        if (!empty($campaign_id)) {
            $query->where('campaign_id', $campaign_id);
        }

        if ($start_date !== null) {
            $query->where_date('created_at', '>=', $start_date->format(DateTimeFormats::DB_DATE));
        }

        if ($end_date !== null) {
            $query->where_date('created_at', '<=', $end_date->format(DateTimeFormats::DB_DATE));
        }

        return $query->sum('amount - processing_fee');
    }

    /**
     * Gets the total average amount by a campaign in a given date range.
     *
     * @param int $campaign_id The campaign ID.
     * @param string|null $start_date The start date in 'Y-m-d' format. Optional.
     * @param string|null $end_date The end date in 'Y-m-d' format. Optional.
     *
     * @return float|int The total average amount in cents.
     */
    public function get_total_average_amount($campaign_id = null, $start_date = null, $end_date = null)
    {
        $start_date = !empty($start_date) ? new DateTime($start_date) : null;
        $end_date = !empty($end_date) ? new DateTime($end_date) : null;

        $query = QueryBuilder::query()->table(Tables::DONATIONS)->where('status', '=', DonationStatus::COMPLETED);

        if (growfund_user()->has_active_role(Fundraiser::ROLE)) {
            $campaign_ids = growfund_campaign_ids_by_fundraiser();
            $query->where_in('campaign_id', $campaign_ids);
        }

        if (growfund_user()->has_active_role(Collaborator::ROLE)) {
            $campaign_ids = growfund_campaign_ids_by_collaborator();
            $query->where_in('campaign_id', $campaign_ids);
        }

        if (!empty($campaign_id)) {
            $query->where('campaign_id', $campaign_id);
        }

        if ($start_date !== null) {
            $query->where_date('created_at', '>=', $start_date->format(DateTimeFormats::DB_DATE));
        }

        if ($end_date !== null) {
            $query->where_date('created_at', '<=', $end_date->format(DateTimeFormats::DB_DATE));
        }

        return $query->avg('amount');
    }

    /**
     * Get the total number of donors for a campaign in a given date range.
     *
     * @param int|null $campaign_id The campaign ID.
     * @param string|null $start_date The start date in 'Y-m-d' format. Optional.
     * @param string|null $end_date The end date in 'Y-m-d' format. Optional.
     * @param int|null $exclude_user_id The user ID to exclude. Optional.
     *
     * @return int The total number of donors.
     */
    public function get_total_donors_count_for_campaign($campaign_id = null, $start_date = null, $end_date = null, $exclude_user_id = null)
    {
        $start_date = !empty($start_date) ? new DateTime($start_date) : null;
        $end_date = !empty($end_date) ? new DateTime($end_date) : null;

        $query = QueryBuilder::query()->table(Tables::DONATIONS)->where('status', DonationStatus::COMPLETED);

        if (growfund_user()->has_active_role(Fundraiser::ROLE)) {
            $campaign_ids = growfund_campaign_ids_by_fundraiser();
            $query->where_in('campaign_id', $campaign_ids);
        }

        if (growfund_user()->has_active_role(Collaborator::ROLE)) {
            $campaign_ids = growfund_campaign_ids_by_collaborator();
            $query->where_in('campaign_id', $campaign_ids);
        }

        if (!empty($exclude_user_id)) {
            $query->where('user_id', '!=', $exclude_user_id);
        }


        if (!empty($campaign_id)) {
            $query->where('campaign_id', $campaign_id);
        }

        if ($start_date !== null) {
            $query->where_date('created_at', '>=', $start_date->format(DateTimeFormats::DB_DATE));
        }

        if ($end_date !== null) {
            $query->where_date('created_at', '<=', $end_date->format(DateTimeFormats::DB_DATE));
        }

        $result = $query
            ->select([
                'COUNT(DISTINCT user_id) + COUNT(DISTINCT CASE WHEN user_id IS NULL THEN email END) + SUM(user_id IS NULL AND email IS NULL) as total_count'
			])
            ->get();

        return (int) ($result[0]->total_count ?? 0);
    }

    /**
     * Retrieves the total refunded amount for a specific campaign within a date range.
     *
     * @param int|null $campaign_id The ID of the campaign. Optional.
     * @param string|null $start_date The start date in 'Y-m-d' format. Optional.
     * @param string|null $end_date The end date in 'Y-m-d' format. Optional.
     *
     * @return int|float The total refunded amount in cents. Returns 0 if no refunds are found.
     */

    public function get_total_refund_amount($campaign_id = null, $start_date = null, $end_date = null)
    {
        $start_date = !empty($start_date) ? new DateTime($start_date) : null;
        $end_date = !empty($end_date) ? new DateTime($end_date) : null;

        $query = QueryBuilder::query()->table(Tables::DONATIONS)->where('status', '=', DonationStatus::REFUNDED);

        if (growfund_user()->has_active_role(Fundraiser::ROLE)) {
            $campaign_ids = growfund_campaign_ids_by_fundraiser();
            $query->where_in('campaign_id', $campaign_ids);
        }

        if (growfund_user()->has_active_role(Collaborator::ROLE)) {
            $campaign_ids = growfund_campaign_ids_by_collaborator();
            $query->where_in('campaign_id', $campaign_ids);
        }

        if (!empty($campaign_id)) {
            $query->where('campaign_id', $campaign_id);
        }

        if ($start_date !== null) {
            $query->where_date('created_at', '>=', $start_date->format(DateTimeFormats::DB_DATE));
        }

        if ($end_date !== null) {
            $query->where_date('created_at', '<=', $end_date->format(DateTimeFormats::DB_DATE));
        }

        return $query->sum('amount');
    }

    /**
     * Get the total number of donations for a campaign.
     *
     * @param int|string $campaign_id
     * @return int
     */
    public function get_total_number_of_donations_for_campaign($campaign_id)
    {
        $donations = QueryBuilder::query()
            ->table(Tables::DONATIONS)
            ->where('campaign_id', $campaign_id)
            ->count();

        return $donations;
    }

    /**
     * Get the latest contribution of a donor
     *
     * @param integer $donor_id
     * @return DonationDTO|null
     */
    public function get_latest_contribution(int $donor_id)
    {
        $donation = $this->get_query(new DonationFilterParamsDTO())
            ->where('donations.user_id', $donor_id)
            ->where_in(
                'donations.status',
                [DonationStatus::COMPLETED, DonationStatus::PENDING]
            )
            ->order_by('donations.created_at', 'DESC')
            ->first();

        if (empty($donation)) {
            return null;
        }

        return $this->prepare_donation_dto($donation);
    }

    /**
     * Get the latest contribution date of a donor
     * @since 1.0.3
     * @param int $donor_id
     * @return string|null
     */
    public function get_latest_donation_date(int $donor_id) {
		$donation = QueryBuilder::query()
            ->select([
                'donations.id',
                'donations.created_at',
            ])
            ->table(Tables::DONATIONS . ' as donations')
            ->where('donations.user_id', $donor_id)
            ->where_in(
                'donations.status',
                [DonationStatus::COMPLETED, DonationStatus::PENDING]
            )
            ->order_by('donations.created_at', 'DESC')
            ->first();

        return $donation->created_at ?? null;
    }

    /**
     * Get the total number of donations by a donor.
     *
     * @param integer $donor_id
     * @return int
     */
    public function get_total_number_of_donations(int $donor_id)
    {
        $donations = QueryBuilder::query()
            ->table(Tables::DONATIONS)
            ->where('user_id', $donor_id)
            ->where_in('status', [DonationStatus::COMPLETED, DonationStatus::PENDING])
            ->count();

        return $donations;
    }

    /**
     * Get the total number of donations for a campaign.
     *
     * @param integer $campaign_id
     * @return int
     */
    public function get_total_count_of_donations_for_campaign(int $campaign_id)
    {
        $donations = QueryBuilder::query()
            ->table(Tables::DONATIONS)
            ->where('campaign_id', $campaign_id)
            ->where_in('status', [DonationStatus::COMPLETED, DonationStatus::PENDING])
            ->count();

        return $donations;
    }

    /**
     * Get the number of successfully donated campaign by a donor.
     *
     * @param int $donor_id
     * @return int
     */
    public function get_successfully_donated_campaigns_by_donor(int $donor_id)
    {
        $campaigns = QueryBuilder::query()->table(Tables::DONATIONS)
            ->where('status', DonationStatus::COMPLETED)
            ->where('user_id', $donor_id)
            ->where('payment_status', PaymentStatus::PAID)
            ->group_by('campaign_id')
            ->count('DISTINCT campaign_id');

        return $campaigns;
    }

    /**
     * Get the total contribution amount by a donor.
     * @param int $donor_id
     * 
     * @return int
     */
    public function get_total_contribution_amount_by_donor(int $donor_id)
    {
        $donations = QueryBuilder::query()
            ->table(Tables::DONATIONS)
            ->where('user_id', $donor_id)
            ->where('status', DonationStatus::COMPLETED)
            ->where('payment_status', PaymentStatus::PAID)
            ->sum('amount');

        return $donations;
    }

    /**
     * Get the average contribution amount by a donor.
     * @param int $donor_id
     * 
     * @return float|int
     */
    public function get_average_contribution_amount_by_donor(int $donor_id)
    {
        $donations = QueryBuilder::query()
            ->table(Tables::DONATIONS)
            ->where('user_id', $donor_id)
            ->where('status', DonationStatus::COMPLETED)
            ->where('payment_status', PaymentStatus::PAID)
            ->avg('amount');

        return $donations;
    }

    /**
     * Get the total received amount by a fundraiser
     * 
     * @param int $fundraiser_id
     * 
     * @return int
     */
    public function get_total_received_amount_by_fundraiser(int $fundraiser_id)
    {
        $campaign_collaborators_table = QueryBuilder::query()->table(Tables::CAMPAIGN_COLLABORATORS)->get_table_name();

        $received_amount = QueryBuilder::query()
            ->table(Tables::DONATIONS . ' as donations')
            ->inner_join(WP::POSTS_TABLE . ' as campaigns', 'campaigns.ID', 'donations.campaign_id')
            ->join_raw(
                WP::POST_META_TABLE . ' as campaign_fundraiser_meta', 
                'LEFT', 
                sprintf(
                    "campaigns.ID = campaign_fundraiser_meta.post_id AND campaign_fundraiser_meta.meta_key = '%s'",
                    growfund_with_prefix('fundraiser_id')
                )
            )
            ->where('donations.status', DonationStatus::COMPLETED)
            ->where_raw(sprintf(
                "(campaigns.post_author = :post_author OR campaign_fundraiser_meta.meta_value = :fundraiser_id OR EXISTS ( SELECT 1 FROM `%s` AS collaborators WHERE collaborators.campaign_id = donations.campaign_id AND collaborators.collaborator_id = :collaborator_id))",
                $campaign_collaborators_table,
            ), [
                'post_author' => $fundraiser_id,
                'fundraiser_id' => (string) $fundraiser_id,
                'collaborator_id' => $fundraiser_id
            ])
            ->sum('amount');

        return $received_amount;
    }

    /**
     * Get the total number of donations by a donor.
     *
     * @param integer $donor_id
     * @return int
     */
    public function get_total_count_of_donations(int $donor_id)
    {
        $donations = QueryBuilder::query()
            ->table(Tables::DONATIONS)
            ->where('user_id', $donor_id)
            ->where_in('status', [DonationStatus::COMPLETED, DonationStatus::PENDING])
            ->count();

        return $donations ?? 0;
    }

    /**
     * Get the total number of completed donations by a donor.
     *
     * @param integer $backer_id
     * @return int
     */
    public function get_total_count_of_completed_donations(int $backer_id)
    {
        $donations = QueryBuilder::query()
            ->table(Tables::DONATIONS)
            ->where('user_id', $backer_id)
            ->where('status', DonationStatus::COMPLETED)
            ->count();

        return $donations ?? 0;
    }

    public function get_annual_receipts_for_donor(AnnualReceiptFilterDTO $dto)
    {
        $receipts = QueryBuilder::query()->table(Tables::DONATIONS)
            ->select(['YEAR(created_at) as year', 'SUM(amount) as total_donations', 'COUNT(*) as number_of_donations'])
            ->where('user_id', growfund_user()->get_id())
            ->where('status', DonationStatus::COMPLETED)
            ->group_by('YEAR(created_at)')
            ->order_by('YEAR(created_at)', 'DESC')
            ->paginate(
                $dto->page,
                $dto->limit,
                [
                    ['user_id', growfund_user()->get_id()]
                ]
            );

        $current_page_index = ($receipts['current_page'] - 1) * $receipts['per_page'];

        $receipts['results'] = Arr::make($receipts['results'])->map(function ($receipt) use ($current_page_index) {
            $dto = new DonationAnnualReceiptDTO();
            $dto->id = sprintf('#CF00%d', ++$current_page_index);
            $dto->year = $receipt->year;
            $dto->total_donations = $receipt->total_donations;
            $dto->number_of_donations = (int) $receipt->number_of_donations;

            return $dto;
        })->toArray();

        return $receipts;
    }

    /**
     * Get the details of an annual receipt.
     * 
     * @param int $donor_id
     * @param int $year
     * @return AnnualReceiptDTO
     */
    public function get_annual_receipt_detail(int $donor_id, int $year)
    {
        $donations = QueryBuilder::query()->table(Tables::DONATIONS)
            ->where('user_id', $donor_id)
            ->where('status', DonationStatus::COMPLETED)
            ->where('YEAR(created_at)', $year)
            ->get();

        $dto = new AnnualReceiptDTO();
        $dto->donor = (new DonorService())->get_by_id($donor_id);
        $dto->donations = Arr::make($donations)->map(function ($donation) {
            $receipt_donation_dto = AnnualReceiptDonationDTO::from_array((array) $donation);
            $receipt_donation_dto->id = (string) $donation->ID;
            $receipt_donation_dto->payment_method = PaymentMethodDTO::from_array(json_decode($donation->payment_method ?? '', true) ?? []);

            return $receipt_donation_dto;
        })->toArray();

        return $dto;
    }

    /**
     * Check if a user has contributed to a campaign.
     *
     * @param int $campaign_id
     * @param int $user_id
     * @return bool
     */
    public function has_contributions(int $campaign_id, int $user_id)
    {
        return QueryBuilder::query()
            ->table(Tables::DONATIONS)
            ->where('user_id', $user_id)
            ->where('campaign_id', $campaign_id)
            ->where('status', DonationStatus::COMPLETED)
            ->where('payment_status', PaymentStatus::PAID)
            ->count() > 0;
    }

    /**
     * Get the total received amount by a campaign.
     *
     * @param int $campaign_id
     * @return int
     */
    public function get_total_received_amount_by_campaign(int $campaign_id)
    {
        $received_amount = QueryBuilder::query()
            ->table(Tables::DONATIONS . ' as donations')
            ->where('donations.campaign_id', $campaign_id)
            ->where('donations.status', DonationStatus::COMPLETED)
            ->sum('amount');

        return (int) $received_amount;
    }
}
