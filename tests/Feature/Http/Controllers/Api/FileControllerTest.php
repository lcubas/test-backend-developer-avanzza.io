<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\File;
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
    }

	public function test_can_get_all_files()
	{
		$file = File::factory()->create();

		$response = $this->getJson(route('api.files.index'));

		$response->assertOk();
		$response->assertJson([
			'data' => [ $file->toArray() ]
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

    public function test_can_get_specific_file()
	{
		$file = File::factory()->create();

		$response = $this->getJson(route('api.files.show', $file));

		$response->assertOk();
		$response->assertJson([
			'data' => $file->toArray()
		]);
	}

    public function test_cannot_get_specific_file()
	{
		$response = $this->getJson(route('api.files.show', 999));

		$response->assertNotFound();
	}

    public function test_can_delete_logically_a_file()
    {
        $file = File::factory()->create();

        $response = $this->deleteJson(route('api.files.destroy', $file));

        $response->assertNoContent();
        $this->assertSoftDeleted('files', ['id' => $file->id]);
        $this->assertTrue(
            Storage::disk(config('filesystems.default'))->exists('files/' . $file->name)
        );
    }

    public function test_can_delete_physically_a_file()
    {
        $file = File::factory()->create();

        $response = $this->deleteJson(route('api.files.destroy', $file), ['destroy_file_to' => 1]);

        $response->assertNoContent();
        $this->assertSoftDeleted('files', ['id' => $file->id]);
        $this->assertFalse(
            Storage::disk(config('filesystems.default'))->exists('files/' . $file->name)
        );
    }
}
