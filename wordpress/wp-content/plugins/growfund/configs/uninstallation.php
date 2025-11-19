<?php

defined( 'ABSPATH' ) || exit;

use Growfund\Hooks\DeactivationAction\FlushRewriteRules;
use Growfund\Hooks\UninstallActions\EraseApplicationData;
use Growfund\Hooks\UninstallActions\RemoveCapabilities;

return [
    RemoveCapabilities::class,
    FlushRewriteRules::class,
    EraseApplicationData::class,
];
