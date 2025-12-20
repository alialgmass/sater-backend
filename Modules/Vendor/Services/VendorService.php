<?php

namespace Modules\Vendor\Services;

use DB;
use Exception;
use Modules\Vendor\DTOs\VendorDTO;
use Modules\Vendor\Models\Vendor;
use Modules\Vendor\Repositories\Contracts\VendorRepositoryInterface;
use Storage;

class VendorService
{

    public function __construct(
        private VendorRepositoryInterface $vendorRepository
    ) {
    }
    public function listActiveVendors(): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->vendorRepository->listActiveVendors();
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
     * Check if slug is available.
     */
    public function isSlugAvailable(string $slug): bool
    {
        return !$this->vendorRepository->slugExists($slug);
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
