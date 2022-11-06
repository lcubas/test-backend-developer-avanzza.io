<?php

namespace App\Http\Controllers\Api;

use App\Models\File;
use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyFileRequest;
use App\Http\Resources\FileResource;
use App\Http\Requests\StoreFileRequest;
use App\Http\Resources\FileCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $files = File::loggedInUser()->get();

        return new FileCollection($files);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreFileRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreFileRequest $request)
    {
        $path = $request->file('file')->store(config('file.directory'));
        $path = explode('/', $path);

        $file = File::create(['name' => $path[1], 'user_id' => auth()->id()]);

        return response()->json(new FileResource($file), Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\File  $file
     * @return \Illuminate\Http\Response
     */
    public function show(File $file)
    {
        $this->authorize('show', $file);

        return new FileResource($file);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\File  $file
     * @return \Illuminate\Http\Response
     */
    public function download(File $file)
    {
        $this->authorize('download', $file);

        $path = storage_path($file->path);

        return response()->download($path);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\File  $file
     * @return \Illuminate\Http\Response
     */
    public function destroy(DestroyFileRequest $request, File $file)
    {
        $this->authorize('delete', $file);

        $file->delete();

        if ($request->boolean('destroy_file_to')) {
            $filePath = config('file.directory') . '\\' . $file->name;

            Storage::delete($filePath);
        }

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
