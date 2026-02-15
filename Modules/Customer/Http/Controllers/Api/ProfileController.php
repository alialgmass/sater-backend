<?php

namespace Modules\Customer\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Customer\DTOs\UpdateProfileData;
use Modules\Customer\Http\Requests\UpdateProfileRequest;
use Modules\Customer\Services\ProfileService;
use Modules\Customer\Transformers\CustomerProfileResource;
use Modules\Customer\Services\AccountDeletionService;

class ProfileController extends ApiController
{
    public function __construct(
        protected ProfileService $profileService,
        protected AccountDeletionService $accountDeletionService
    ) {
        parent::__construct();
    }

    public function show(Request $request): JsonResponse
    {
        $customer = $request->user();
        $this->authorize('view', $customer->profile ?? new \Modules\Customer\Models\CustomerProfile(['customer_id' => $customer->id]));

        $profile = $this->profileService->getProfile($customer);

        return $this->apiBody([
            'profile' => new CustomerProfileResource($profile)
        ])->apiResponse();
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $customer = $request->user();
        $this->authorize('update', $customer->profile ?? new \Modules\Customer\Models\CustomerProfile(['customer_id' => $customer->id]));

        $data = UpdateProfileData::fromRequest($request);
        $profile = $this->profileService->updateProfile($customer, $data);

        return $this->apiMessage('Profile updated successfully.')
            ->apiBody(['profile' => new CustomerProfileResource($profile)])
            ->apiResponse();
    }

    public function deleteAccount(Request $request): JsonResponse
    {
         $customer = $request->user();
         // self authorization or dedicated policy
         if ($customer->id !== auth()->id()) {
             return $this->unauthorized('Access denied');
         }

         $this->accountDeletionService->deleteAccount($customer);

         return $this->apiMessage('Account deleted successfully.')
             ->apiResponse();
    }
}
