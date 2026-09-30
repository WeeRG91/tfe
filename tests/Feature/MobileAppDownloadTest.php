<?php

use Inertia\Testing\AssertableInertia as Assert;

it('redirects the permanent app URL to the mobile app section', function () {
    $this->get(route('mobile-app.index'))
        ->assertRedirect(route('home').'#mobile-app');
});

it(
    'returns unavailable platform downloads to the mobile app section',
    function (string $routeName, string $configKey) {
        config()->set($configKey, null);

        $this->get(route($routeName))
            ->assertRedirect(route('home').'#mobile-app');
    },
)->with([
    'Android' => [
        'mobile-app.android',
        'services.mobile.android_download_url',
    ],
    'iOS' => [
        'mobile-app.ios',
        'services.mobile.ios_download_url',
    ],
]);

it(
    'redirects configured platforms to their HTTPS destinations',
    function (
        string $routeName,
        string $configKey,
        string $destination,
    ) {
        config()->set($configKey, $destination);

        $this->get(route($routeName))
            ->assertRedirect($destination);
    },
)->with([
    'Android' => [
        'mobile-app.android',
        'services.mobile.android_download_url',
        'https://downloads.example.com/kin-dee.apk',
    ],
    'iOS' => [
        'mobile-app.ios',
        'services.mobile.ios_download_url',
        'https://testflight.apple.com/join/example',
    ],
]);

it(
    'rejects invalid or unsafe download destinations',
    function (string $destination) {
        config()->set(
            'services.mobile.android_download_url',
            $destination,
        );

        $this->get(route('mobile-app.android'))
            ->assertRedirect(route('home').'#mobile-app');
    },
)->with([
    'empty value' => '',
    'plain text' => 'not-a-url',
    'insecure HTTP URL' => 'http://downloads.example.com/kin-dee.apk',
    'JavaScript URL' => 'javascript:alert(document.cookie)',
]);

it('reports both mobile platforms as unavailable without destinations', function () {
    config()->set('services.mobile.android_download_url', null);
    config()->set('services.mobile.ios_download_url', null);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('client/Home')
            ->where('mobileApp.androidAvailable', false)
            ->where('mobileApp.iosAvailable', false)
        );
});

it('reports only valid HTTPS mobile destinations as available', function () {
    config()->set(
        'services.mobile.android_download_url',
        'https://downloads.example.com/kin-dee.apk',
    );

    config()->set(
        'services.mobile.ios_download_url',
        'http://testflight.example.com/insecure',
    );

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('client/Home')
            ->where('mobileApp.androidAvailable', true)
            ->where('mobileApp.iosAvailable', false)
        );
});
