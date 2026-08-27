<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

it('uploads a mobile profile photo', function () {
    $user = User::factory()
        ->withoutTwoFactor()
        ->create();

    $token = $user->createToken('Test phone');

    $response = $this
        ->withToken($token->plainTextToken)
        ->post('/api/v1/auth/user/avatar', [
            'avatar' => UploadedFile::fake()
                ->image('avatar.jpg', 400, 400)
                ->size(500),
        ], [
            'Accept' => 'application/json',
        ]);

    $image = $user->images()->sole();

    $response
        ->assertOk()
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath(
            'data.avatar_url',
            Storage::disk('public')->url($image->path),
        );

    expect($image->mime_type)->toBe('image/jpeg');

    Storage::disk('public')->assertExists($image->path);
});

it('replaces the authenticated mobile users existing profile photo', function () {
    $user = User::factory()
        ->withoutTwoFactor()
        ->create();

    $oldPath = 'images/user/old-avatar.jpg';

    Storage::disk('public')->put($oldPath, 'old avatar');

    $oldImage = $user->images()->create([
        'name' => 'old-avatar.jpg',
        'path' => $oldPath,
        'mime_type' => 'image/jpeg',
        'size' => 10,
    ]);

    $token = $user->createToken('Test phone');

    $this
        ->withToken($token->plainTextToken)
        ->post('/api/v1/auth/user/avatar', [
            'avatar' => UploadedFile::fake()
                ->image('new-avatar.png', 400, 400)
                ->size(500),
        ], [
            'Accept' => 'application/json',
        ])
        ->assertOk();

    $newImage = $user->images()->sole();

    expect($newImage->id)->not->toBe($oldImage->id);

    $this->assertDatabaseMissing('images', [
        'id' => $oldImage->id,
    ]);

    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($newImage->path);
});

it('removes the authenticated mobile users profile photo', function () {
    $user = User::factory()
        ->withoutTwoFactor()
        ->create();

    $path = 'images/user/avatar.jpg';

    Storage::disk('public')->put($path, 'avatar');

    $image = $user->images()->create([
        'name' => 'avatar.jpg',
        'path' => $path,
        'mime_type' => 'image/jpeg',
        'size' => 6,
    ]);

    $token = $user->createToken('Test phone');

    $this
        ->withToken($token->plainTextToken)
        ->deleteJson('/api/v1/auth/user/avatar')
        ->assertNoContent();

    $this->assertDatabaseMissing('images', [
        'id' => $image->id,
    ]);

    Storage::disk('public')->assertMissing($path);
});

it('allows removing a profile photo when none exists', function () {
    $user = User::factory()
        ->withoutTwoFactor()
        ->create();

    $token = $user->createToken('Test phone');

    $this
        ->withToken($token->plainTextToken)
        ->deleteJson('/api/v1/auth/user/avatar')
        ->assertNoContent();
});

it('validates the mobile profile photo', function () {
    $user = User::factory()
        ->withoutTwoFactor()
        ->create();

    $token = $user->createToken('Test phone');

    $this
        ->withToken($token->plainTextToken)
        ->post('/api/v1/auth/user/avatar', [], [
            'Accept' => 'application/json',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('avatar');

    $this
        ->withToken($token->plainTextToken)
        ->post('/api/v1/auth/user/avatar', [
            'avatar' => UploadedFile::fake()
                ->create('document.pdf', 100, 'application/pdf'),
        ], [
            'Accept' => 'application/json',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('avatar');

    $this
        ->withToken($token->plainTextToken)
        ->post('/api/v1/auth/user/avatar', [
            'avatar' => UploadedFile::fake()
                ->image('large.jpg')
                ->size(5121),
        ], [
            'Accept' => 'application/json',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('avatar');
});

it('requires authentication to manage a mobile profile photo', function () {
    $this
        ->post('/api/v1/auth/user/avatar', [
            'avatar' => UploadedFile::fake()->image('avatar.jpg'),
        ], [
            'Accept' => 'application/json',
        ])
        ->assertUnauthorized();

    $this
        ->deleteJson('/api/v1/auth/user/avatar')
        ->assertUnauthorized();
});
