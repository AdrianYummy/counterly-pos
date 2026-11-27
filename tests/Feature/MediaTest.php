<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_users_can_view_stored_media_without_a_public_storage_link(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/example.jpg', 'example-image');

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('media.show', ['path' => 'products/example.jpg']))
            ->assertOk()
            ->assertHeader('x-content-type-options', 'nosniff')
            ->assertContent('example-image');
    }

    public function test_media_is_private_and_missing_or_unsafe_paths_are_rejected(): void
    {
        Storage::fake('public');

        $this->get('/media/products/example.jpg')->assertRedirect('/login');

        $user = User::factory()->create();

        $this->actingAs($user)->get('/media/products/missing.jpg')->assertNotFound();
        $this->actingAs($user)->get('/media/../.env')->assertNotFound();
    }
}
