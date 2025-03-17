<?php

namespace App\Http\Traits\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use DateTimeInterface;
use Carbon\Carbon;
use Config;
use Illuminate\Support\Facades\Storage;

trait AttributeTrait
{
    protected function dateAttribute()
    {
        return Attribute::make(
            get: fn ($value) => $value != null ? Carbon::parse($value)->format('Y-m-d') : $value,
        );
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format("Y-m-d H:i:s");
    }


    private function toS3PrivateLink($link)
    {
        $client = Storage::disk('s3')->getClient();
        $command = $client->getCommand('GetObject', [
            'Bucket' => Config::get('filesystems.disks.s3.bucket'),
            'Key'    => str_replace(Config::get('filesystems.disks.s3.url').'/', '', $link)
        ]);
        $request = $client->createPresignedRequest($command, '+10 minutes');
        return $request->getUri();
    }

    private function isS3Img($value)
    {
        return env('FILESYSTEM_DISK') === 's3' && strpos($value, 'amazonaws.com') !== false;
    }

    protected function ContractImg() : Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->isS3Img($value) ? $this->toS3PrivateLink($value) : $value
        );
    }

    protected function BsinLicImg() : Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->isS3Img($value) ? $this->toS3PrivateLink($value) : $value
        );
    }

    protected function IdImg() : Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->isS3Img($value) ? $this->toS3PrivateLink($value) : $value
        );
    }

    protected function SealImg() : Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->isS3Img($value) ? $this->toS3PrivateLink($value) : $value
        );
    }

    protected function PassbookImg() : Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->isS3Img($value) ? $this->toS3PrivateLink($value) : $value
        );
    }
}
