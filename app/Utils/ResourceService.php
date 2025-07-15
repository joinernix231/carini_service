<?php

declare(strict_types=1);

namespace App\Utils;

use Aws\S3\Exception\S3Exception;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
 * Class ResourceService
 *
 * Handles uploading of images, files, and audio to AWS S3.
 */
class ResourceService
{
    private const IMAGES_DIR = 'images/';

    private const DOCS_DIR = 'docs/';

    private const AUDIOS_DIR = 'audios/';

    /**
     * Upload an image to S3 without using Intervention Image, assuming base64 data for string input.
     *
     * @param  string  $filename  The base filename without extension.
     * @param  string|UploadedFile  $image  The image file, path, or base64-encoded string.
     * @return array{status: bool, filename?: string, message?: string}
     */
    public function uploadImage(string $filename, string|UploadedFile $image): array
    {
        try {
            if ($image instanceof UploadedFile) {
                // For UploadedFile, read its contents.
                $imageStream = file_get_contents($image->getRealPath());
            } elseif (is_string($image)) {
                // Assume the string is base64 encoded image data.
                $decoded = base64_decode($image, true);
                if ($decoded === false) {
                    throw new \InvalidArgumentException('Invalid base64 image data.');
                }
                $imageStream = $decoded;
            } else {
                throw new \InvalidArgumentException('Invalid image type provided.');
            }

            $s3Path = self::IMAGES_DIR.$filename.'.PNG';

            Storage::disk('s3')->put($s3Path, $imageStream);

            return [
                'status' => true,
                'filename' => $filename.'.PNG',
            ];
        } catch (S3Exception $e) {
            return [
                'status' => false,
                'message' => $e->getStatusCode().' - '.$e->getAwsErrorCode(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Image upload failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Upload a file to S3.
     *
     * @param  string  $fileName  The base filename.
     * @param  mixed  $file  The file to upload or raw content.
     * @param  bool  $fromRaw  Indicates if the file content is raw.
     * @return array{status: bool, filename?: string, message?: string}
     */
    public function uploadFile(string $fileName, mixed $file, bool $fromRaw = false): array
    {
        try {
            $s3Path = self::DOCS_DIR.$fileName;

            if ($fromRaw) {
                Storage::disk('s3')->put($s3Path, $file);
            } else {
                if ($file instanceof UploadedFile) {
                    $fileContents = file_get_contents($file->getRealPath());
                } elseif (is_string($file)) {
                    $fileContents = file_get_contents($file);
                } else {
                    throw new \InvalidArgumentException('Unsupported file type.');
                }
                Storage::disk('s3')->put($s3Path, $fileContents);
            }

            return [
                'status' => true,
                'filename' => $fileName,
            ];
        } catch (S3Exception $e) {
            return [
                'status' => false,
                'message' => $e->getStatusCode().' - '.$e->getAwsErrorCode(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'File upload failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Upload an audio file to S3.
     *
     * @param  string  $fileName  The base filename.
     * @param  UploadedFile|string  $audio  The audio file to upload.
     * @return array{status: bool, filename?: string, message?: string}
     */
    public function uploadAudioFile(string $fileName, $audio): array
    {
        try {
            $file = $audio instanceof UploadedFile ? new File($audio->getRealPath()) : $audio;
            Storage::disk('s3')->putFileAs(self::AUDIOS_DIR, $file, $fileName);

            return [
                'status' => true,
                'filename' => $fileName,
            ];
        } catch (S3Exception $e) {
            return [
                'status' => false,
                'message' => $e->getStatusCode().' - '.$e->getAwsErrorCode(),
            ];
        } catch (FileException|\Exception $e) {
            return [
                'status' => false,
                'message' => 'Audio upload failed: '.$e->getMessage(),
            ];
        }
    }
}
