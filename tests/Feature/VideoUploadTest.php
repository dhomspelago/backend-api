<?php

namespace Tests\Feature;

use App\Services\VideoUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use Tests\TestCase;

class VideoUploadTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testVideoUploadCanUpload(): void
    {
        Storage::fake('public');

        $videoFile = UploadedFile::fake()->create('video.mp4', 1000);

        $response = $this->post('/api/upload-video', [
            'file' => $videoFile,
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'full_path',
            'path',
            'name',
            'mime_type',
        ]);
    }

    public function testVideoUploadValidation(): void
    {
        Storage::fake('public');

        $response = $this->post('/api/upload-video', [
            'file' => UploadedFile::fake()->image('avatar.jpg'),
        ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors([
                'file' => 'The file field must be a file of type: mp4, avi, mov, wm.',
            ]);
    }

    //Not sure if we also needed mock testing but this one is example
    public function testVideoUploadWithMockService()
    {
        $this->mock(VideoUploadService::class, function (MockInterface $mock) {
            $mock->shouldReceive('handleUpload')->once()
                ->andReturn([
                    'full_path' => 'xx',
                    'path' => 'xx',
                    'name' => 'xx',
                    'mime_type' => 'xx',
                ]);
        });

        Storage::fake('public');

        $videoFile = UploadedFile::fake()->create('video.mp4', 1000);

        $response = $this->post('/api/upload-video', [
            'file' => $videoFile,
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'full_path',
            'path',
            'name',
            'mime_type',
        ]);
    }
}
