<?php

namespace App\Http\Controllers\Api;

use App\Enums\CurrencyEnum;
use Modules\Campaign\Enums\CampaignContentVariablesEnum;
use Modules\Campaign\Enums\SendingDuration;
use Modules\SallaIntegration\Enums\SallaStoreEventEnum;

class EnumController extends ApiController
{
    public function __invoke()
    {

        return self::apiBody([
            'currencies' => CurrencyEnum::toArray(),
            'salla_events' => SallaStoreEventEnum::toArray(),
            'campaign_content_variables' => CampaignContentVariablesEnum::toArray(),
            'sending_duration' => SendingDuration::toArray(),
        ])->apiResponse();
    }
}
