<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;

class VideoUploadService
{
    public function handleUpload($request, $key)
    {
        Log::info('Recieve the file to be chunk file key= ' . $key);

        $receiver = new FileReceiver($key, $request, HandlerFactory::classFromRequest($request));

        if (!$receiver->isUploaded()) {
            return response()->json([
                'error' => 'File not uploaded.',
            ], 400);
        }

        $fileRecieve = $receiver->receive();

        if ($fileRecieve->isFinished()) {
            return $this->saveFile($fileRecieve->getFile());
        }

        $handler = $fileRecieve->handler();

        Log::info('Chunking in progress current ' . $handler->getPercentageDone() . ' completed');

        return [
            'done' => $handler->getPercentageDone(),
            'status' => true,
        ];
    }

    /**
     * Saves the file
     *
     * @param UploadedFile $file
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function saveFile(UploadedFile $file)
    {
        Log::info('Saving File');
        $fileName = $this->createFilename($file);
        // Group files by mime type
        $mime = str_replace('/', '-', $file->getMimeType());
        // Group files by the date (week
        $dateFolder = date("Y-m-W");

        // Build the file path
        $filePath = "videos/{$dateFolder}/";
        $finalPath = storage_path("app/public/" . $filePath);

        // move the file name
        $file->move($finalPath, $fileName);

        Log::info('Successfully created File with path= ' . $finalPath . ' and name= ' . $fileName);
        return response()->json([
            'full_path' => asset('storage/' . $filePath . $fileName),
            'path' => $filePath,
            'name' => $fileName,
            'mime_type' => $mime,
        ]);
    }

    /**
     * Create unique filename for uploaded file
     * @param UploadedFile $file
     * @return string
     */
    protected function createFilename(UploadedFile $file)
    {
        $extension = $file->getClientOriginalExtension();
        $filename = str_replace("." . $extension, "", $file->getClientOriginalName()); // Filename without extension

        // Add timestamp hash to name of the file
        $filename .= "_" . md5(time()) . "." . $extension;

        return $filename;
    }
}
