<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Resources\FileResource;
use App\Models\File;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(config('filesystems.default'));

        /** @var User */
        $user = User::factory()->create();
        $this->actingAs($user);
    }

	public function test_can_get_all_files()
	{
		$file = File::factory()->create(['user_id' => 1]);

		$response = $this->getJson(route('api.files.index'));

		$response->assertOk();
		$response->assertExactJson([
			'data' => [
                [
                    'id' => $file->id,
                    'name' => $file->name,
                    'created_at' => $file->created_at,
                    'updated_at' => $file->updated_at,
                ]
            ]
		]);
	}

    public function test_can_store_a_file()
    {
        $response = $this->postJson(route('api.files.store'), [
            'file' => UploadedFile::fake()->create('test-file.csv')
        ]);

        $response->assertCreated();
        $this->assertTrue(
            Storage::disk(config('filesystems.default'))->exists('files/' . $response->decodeResponseJson()['name'])
        );
    }

    public function test_file_field_required()
    {
        $response = $this->postJson(route('api.files.store'));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrorFor('file');
        $this->assertSame(
            __('validation.required', ['attribute' => 'file']),
            $response->json('errors.file.0')
        );
    }

    public function test_file_field_size_is_larger_than_allowed()
    {
        $size = (int) config('file.max_size') + 1;
        $response = $this->postJson(route('api.files.store'), [
            'file' => UploadedFile::fake()->create('test-file.csv', $size)
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrorFor('file');
        $this->assertSame(
            __('validation.max.file', ['attribute' => 'file', 'max' => config('file.max_size')]),
            $response->json('errors.file.0')
        );
    }

    public function test_not_authorized_get_specific_file()
	{
        $user = User::factory()->create();
		$file = File::factory()->create(['user_id' => $user->id]);

		$response = $this->getJson(route('api.files.show', $file));

		$response->assertForbidden();
	}

    public function test_can_get_specific_file()
	{
		$file = File::factory()->create(['user_id' => 1]);

		$response = $this->getJson(route('api.files.show', $file));

		$response->assertOk();
		$response->assertExactJson([
			'data' => [
                'id' => $file->id,
                'name' => $file->name,
                'created_at' => $file->created_at,
                'updated_at' => $file->updated_at,
            ]
		]);
	}

    public function test_not_found_get_specific_file()
	{
		$response = $this->getJson(route('api.files.show', 999));

		$response->assertNotFound();
	}

    public function test_not_authorized_delete_file()
    {
        $user = User::factory()->create();
		$file = File::factory()->create(['user_id' => $user->id]);

        $response = $this->deleteJson(route('api.files.destroy', $file));

        $response->assertForbidden();
    }

    public function test_can_delete_logically_file()
    {
        $file = File::factory()->create(['user_id' => 1]);

        $response = $this->deleteJson(route('api.files.destroy', $file));

        $response->assertNoContent();
        $this->assertSoftDeleted('files', ['id' => $file->id]);
        $this->assertTrue(
            Storage::disk(config('filesystems.default'))->exists('files/' . $file->name)
        );
    }

    public function test_can_delete_physically_file()
    {
        $file = File::factory()->create(['user_id' => 1]);

        $response = $this->deleteJson(route('api.files.destroy', $file), ['destroy_file_to' => 1]);

        $response->assertNoContent();
        $this->assertSoftDeleted('files', ['id' => $file->id]);
        $this->assertFalse(
            Storage::disk(config('filesystems.default'))->exists('files/' . $file->name)
        );
    }
}
