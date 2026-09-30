<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\MobileAppDistribution;
use Illuminate\Http\RedirectResponse;

class MobileAppDownloadController extends Controller
{
    public function __construct(
        private readonly MobileAppDistribution $mobileAppDistribution,
    ) {}

    public function index(): RedirectResponse
    {
        return $this->redirectToMobileSection();
    }

    public function android(): RedirectResponse
    {
        return $this->redirectToDestination(
            $this->mobileAppDistribution->androidUrl(),
        );
    }

    public function ios(): RedirectResponse
    {
        return $this->redirectToDestination(
            $this->mobileAppDistribution->iosUrl(),
        );
    }

    private function redirectToDestination(?string $destination): RedirectResponse
    {
        if ($destination === null) {
            return $this->redirectToMobileSection();
        }

        return redirect()->away($destination);
    }

    private function redirectToMobileSection(): RedirectResponse
    {
        return redirect()->to(route('home').'#mobile-app');
    }
}
