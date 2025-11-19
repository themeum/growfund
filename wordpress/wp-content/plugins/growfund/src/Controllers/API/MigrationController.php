<?php

namespace Growfund\Controllers\API;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Request;
use Growfund\Http\Response;
use Growfund\Services\Migration\MigrationService;
use Growfund\Validation\Validator;

class MigrationController
{
    protected $migration_service;

    public function __construct()
    {
        $this->migration_service = new MigrationService();
    }

    public function migrate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'step' => 'required|in:migrate-campaigns,migrate-contributions,final',
        ]);

        if ($validator->is_failed()) {
            return growfund_response()->json([
                'data' => $validator->get_errors(),
                'message' => '',
            ], Response::UNPROCESSABLE_ENTITY);
        }

        $migration_info = $this->migration_service->migrate($request->get_string('step'));
        $total = $migration_info->total ?? 0;
        $completed = $migration_info->completed ?? 0;

        return growfund_response()->json([
            'data' => [
                'is_running' => $request->get_string('step') === 'final' 
                    ? false 
                    : $migration_info !== false && $total > $completed,
                'step' => $request->get_string('step'),
                'total' => $total,
                'completed' => $completed,
            ],
            'message' => '',
        ], Response::OK);
    }
}
