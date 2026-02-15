<?php

namespace Modules\Banner\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Modules\Banner\Http\Resources\BannerResource;
use Modules\Banner\Services\BannerService;

class BannerController extends ApiController
{
    public function __construct(
        protected BannerService $bannerService
    ) {
        parent::__construct();
    }

    /**
     * Display a listing of all banners.
     */
    public function index(): JsonResponse
    {
        $banners = $this->bannerService->getAll();

        return $this->apiBody([
            'banners' => BannerResource::collection($banners)
        ])->apiResponse();
    }

    /**
     * Display only currently active and running banners.
     */
    public function active(): JsonResponse
    {
        $banners = $this->bannerService->getActive();

        return $this->apiBody([
            'banners' => BannerResource::collection($banners)
        ])->apiResponse();
    }
}

