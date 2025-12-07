<?php

namespace Modules\Vendor\Services;

use DB;
use Exception;
use Modules\Vendor\DTOs\VendorDTO;
use Modules\Vendor\Enums\VendorStatus;
use Modules\Vendor\Models\Vendor;
use Modules\Vendor\Repositories\Contracts\VendorRepositoryInterface;
use Storage;

class VendorService
{

    public function __construct(
        private VendorRepositoryInterface $vendorRepository
    ) {
    }

    /**
     * Register a new vendor.
     */
    public function register(VendorDTO $dto): Vendor
    {
        try {
            DB::beginTransaction();

            // Validate unique phone
            if ($this->vendorRepository->phoneExists($dto->phone)) {
                throw new Exception('Phone number already registered.');
            }

            // Validate unique slug
            if ($this->vendorRepository->slugExists($dto->shop_slug)) {
                throw new Exception('Shop slug already taken.');
            }

            // Create vendor data array
            $vendorData = $dto->toArray();

            // Handle logo upload
            if ($dto->hasLogo()) {
                $vendorData['logo'] = $this->uploadImage($dto->getLogo(), 'vendors/logos');
            }

            // Handle cover upload
            if ($dto->hasCover()) {
                $vendorData['cover'] = $this->uploadImage($dto->getCover(), 'vendors/covers');
            }

            // Create vendor using DTO
            $vendorDTO = VendorDTO::fromArray($vendorData);
            $vendor = $this->vendorRepository->create($vendorDTO);

            DB::commit();

            return $vendor;
        } catch (Exception $e) {
            DB::rollBack();

            // Clean up uploaded files on error
            if (isset($vendorData['logo'])) {
                $this->deleteImage($vendorData['logo']);
            }
            if (isset($vendorData['cover'])) {
                $this->deleteImage($vendorData['cover']);
            }

            throw $e;
        }
    }

    /**
     * Update vendor profile.
     */
    public function updateProfile(Vendor $vendor, array $data): Vendor
    {
        try {
            DB::beginTransaction();

            // Handle logo upload
            if (isset($data['logo']) && $data['logo']) {
                // Delete old logo
                if ($vendor->logo) {
                    $this->deleteImage($vendor->logo);
                }
                $data['logo'] = $this->uploadImage($data['logo'], 'vendors/logos');
            }

            // Handle cover upload
            if (isset($data['cover']) && $data['cover']) {
                // Delete old cover
                if ($vendor->cover) {
                    $this->deleteImage($vendor->cover);
                }
                $data['cover'] = $this->uploadImage($data['cover'], 'vendors/covers');
            }

            // Update vendor
            $vendor = $this->vendorRepository->update($vendor, $data);

            DB::commit();

            return $vendor;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Activate vendor.
     */
    public function activate(int $vendorId): Vendor
    {
        return $this->vendorRepository->updateStatus($vendorId, VendorStatus::ACTIVE->value);
    }

    /**
     * Suspend vendor.
     */
    public function suspend(int $vendorId): Vendor
    {
        return $this->vendorRepository->updateStatus($vendorId, VendorStatus::SUSPENDED->value);
    }

    /**
     * Get vendor by slug.
     */
    public function getBySlug(string $slug): ?Vendor
    {
        return $this->vendorRepository->findBySlug($slug);
    }

    /**
     * Get vendor by phone.
     */
    public function getByPhone(string $phone): ?Vendor
    {
        return $this->vendorRepository->findByPhone($phone);
    }

    /**
     * Check if slug is available.
     */
    public function isSlugAvailable(string $slug): bool
    {
        return !$this->vendorRepository->slugExists($slug);
    }

    /**
     * Check if phone is available.
     */
    public function isPhoneAvailable(string $phone): bool
    {
        return !$this->vendorRepository->phoneExists($phone);
    }

    /**
     * Get all active vendors.
     */
    public function getActiveVendors()
    {
        return $this->vendorRepository->listActiveVendors();
    }

    /**
     * Get all pending vendors.
     */
    public function getPendingVendors()
    {
        return $this->vendorRepository->listPendingVendors();
    }

    /**
     * Search vendors.
     */
    public function search(string $query)
    {
        return $this->vendorRepository->search($query);
    }

    /**
     * Delete vendor.
     */
    public function delete(Vendor $vendor): bool
    {
        try {
            DB::beginTransaction();

            // Delete images
            if ($vendor->logo) {
                $this->deleteImage($vendor->logo);
            }
            if ($vendor->cover) {
                $this->deleteImage($vendor->cover);
            }

            // Soft delete vendor
            $result = $this->vendorRepository->softDelete($vendor);

            DB::commit();

            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Upload image to storage.
     */
    private function uploadImage($file, string $path): string
    {
        return $file->store($path, 'public');
    }

    /**
     * Delete image from storage.
     */
    private function deleteImage(string $path): void
    {
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Generate unique slug.
     */
    public function generateUniqueSlug(string $shopName): string
    {
        $slug = str($shopName)->slug()->toString();
        $originalSlug = $slug;
        $counter = 1;

        while ($this->vendorRepository->slugExists($slug)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
