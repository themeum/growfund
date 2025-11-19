<?php

namespace Growfund\Services;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\Activity\ActivityDTO;

class TimelineService
{
    /** @var ActivityService */
    private $activity_service;

    public function __construct(ActivityService $activity_service)
    {
        $this->activity_service = $activity_service;
    }

    /**
     * Create a new pledge comment
     * 
     * @param ActivityDTO $data
     * @return int|false - if failed return false otherwise return ID
     */
    public function save(ActivityDTO $activity_dto)
    {
        $activity_id = $this->activity_service->save($activity_dto);

        return $activity_id;
    }

    /**
     * Get by ID
     * @param int $id
     * @param string $activity_cause
     */
    public function get_by_id(int $id, $activity_cause)
    {
        return $this->activity_service->get_by_id($id, $activity_cause);
    }


    /**
     * Delete a pledge comment by its ID
     * @param int $id
     * @return bool
     */
    public function delete_by_id(int $id)
    {
        return $this->activity_service->delete_by_id($id);
    }
}
