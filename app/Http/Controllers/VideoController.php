<?php

namespace App\Http\Controllers;

use App\Http\Requests\VideoRequest;
use App\Services\VideoUploadService;

class VideoController extends Controller
{
    public function __construct(protected VideoUploadService $videoUploadService)
    {
        //
    }
    public function upload(VideoRequest $request)
    {
        return $this->videoUploadService->handleUpload($request, 'file');
    }
}
