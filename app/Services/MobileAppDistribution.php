<?php

namespace App\Services;

class MobileAppDistribution
{
    public function androidUrl(): ?string
    {
        return $this->validatedHttpsUrl(
            config('services.mobile.android_download_url'),
        );
    }

    public function iosUrl(): ?string
    {
        return $this->validatedHttpsUrl(
            config('services.mobile.ios_download_url'),
        );
    }

    private function validatedHttpsUrl(mixed $destination): ?string
    {
        if (! is_string($destination)) {
            return null;
        }

        $destination = trim($destination);

        if (
            $destination === '' ||
            filter_var($destination, FILTER_VALIDATE_URL) === false ||
            strtolower((string) parse_url($destination, PHP_URL_SCHEME)) !== 'https'
        ) {
            return null;
        }

        return $destination;
    }
}
