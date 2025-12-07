<?php

namespace Modules\Vendor\DTOs;

use Illuminate\Http\UploadedFile;
use Modules\Vendor\Enums\VendorStatus;
use Modules\Vendor\Http\Requests\VendorRegisterRequest;

readonly class VendorDTO
{
    public function __construct(
        public string        $name,
        public string        $phone,
        public string        $password,
        public string        $shop_name,
        public string        $shop_slug,
        public string        $whatsapp,
        public ?string       $description,
        public ?UploadedFile $logo,
        public ?UploadedFile $cover,
        public string        $status = 'pending'
    ) {
    }

    /**
     * Create DTO from VendorRegisterRequest.
     */
    public static function fromRequest(VendorRegisterRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            phone: $request->validated('phone'),
            password: $request->validated('password'),
            shop_name: $request->validated('shop_name'),
            shop_slug: $request->validated('shop_slug'),
            whatsapp: $request->validated('whatsapp'),
            description: $request->validated('description'),
            logo: $request->file('logo'),
            cover: $request->file('cover'),
            status: VendorStatus::PENDING->value
        );
    }

    /**
     * Create DTO from array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            phone: $data['phone'],
            password: $data['password'],
            shop_name: $data['shop_name'],
            shop_slug: $data['shop_slug'],
            whatsapp: $data['whatsapp'],
            description: $data['description'] ?? null,
            logo: $data['logo'] ?? null,
            cover: $data['cover'] ?? null,
            status: $data['status'] ?? VendorStatus::PENDING->value
        );
    }

    /**
     * Convert DTO to array.
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'phone' => $this->phone,
            'password' => $this->password,
            'shop_name' => $this->shop_name,
            'shop_slug' => $this->shop_slug,
            'whatsapp' => $this->whatsapp,
            'description' => $this->description,
            'status' => $this->status,
        ];
    }

    /**
     * Get array without sensitive data.
     */
    public function toArrayWithoutPassword(): array
    {
        $data = $this->toArray();
        unset($data['password']);
        return $data;
    }

    /**
     * Check if logo file exists.
     */
    public function hasLogo(): bool
    {
        return $this->logo !== null && $this->logo instanceof UploadedFile;
    }

    /**
     * Check if cover file exists.
     */
    public function hasCover(): bool
    {
        return $this->cover !== null && $this->cover instanceof UploadedFile;
    }

    /**
     * Get logo file.
     */
    public function getLogo(): ?UploadedFile
    {
        return $this->logo;
    }

    /**
     * Get cover file.
     */
    public function getCover(): ?UploadedFile
    {
        return $this->cover;
    }
}
