<?php

namespace App\Traits;

use App\Exceptions\ApiOperationFailedException;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage as FacadesStorage;
use Image;
use Storage;

/**
 * Trait ImageTrait.
 */
trait ImageTrait
{
    /**
     * @param string $file
     *
     * @return bool
     */
    public static function deleteImage($file)
    {
        if (FacadesStorage::exists($file)) {
            FacadesStorage::delete($file);
            return true;
        }
        return false;
    }

    /**
     * @param UploadedFile $file
     * @param string       $path
     * @param array        $options
     *
     * @throws ApiOperationFailedException
     *
     * @return string
     */
    public static function makeImage($file, $path, $options = [])
    {
        try {
            $fileName = '';
            if (!empty($file)) {
                $extension = $file->getClientOriginalExtension(); // getting image extension
                /*if (!in_array(strtolower($extension), ['jpg', 'gif', 'png', 'jpeg'])) {
                    throw  new ApiOperationFailedException('invalid image', Response::HTTP_BAD_REQUEST);
                }*/
                $date = Carbon::now()->format('Y-m-d');
                $fileName = 'file_' . time() . '.' . $extension;
                // logger("option sus an " . json_encode($options));
                if (!empty($options)) {
                    $imageThumb = Image::make($file->getRealPath())->fit($options['width'], $options['height']);
                    $imageThumb = $imageThumb->stream();
                    FacadesStorage::put($path . DIRECTORY_SEPARATOR . $fileName, $imageThumb->__toString());
                } else {
                    FacadesStorage::putFileAs($path, $file, $fileName, 'public');
                }
            }
            return $fileName;
        } catch (Exception $e) {
            logger("============= ERROR ::  makeImage =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function imageUrl($path = "")
    {
        return $this->urlEncoding(Storage::url($path));
    }

    /**
     * @param string $url
     *
     * @return mixed
     */
    public function urlEncoding($url)
    {
        $entities = ['%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B', '%24', '%2C', '%2F', '%3F', '%25', '%23', '%5B', '%5D', '%5C'];
        $replacements = ['!', '*', "'", '(', ')', ';', ':', '@', '&', '=', '+', '$', ',', '/', '?', '%', '#', '[', ']', '/'];
        return str_replace($entities, $replacements, urlencode($url));
    }

    /**
     * @param UploadedFile $file
     * @param string       $path
     *
     * @throws ApiOperationFailedException
     *
     * @return string
     */
    public static function makeAttachment($file, $path)
    {
        try {
            $fileName = '';
            if (!empty($file)) {
                $extension = $file->getClientOriginalExtension(); // getting image extension
                if (!in_array(strtolower($extension), ['xls', 'pdf', 'doc', 'docx', 'xlsx', 'jpg', 'jpeg', 'png'])) {
                    // logger("============= ERROR ::  makeAttachment =============");
                    // logger("Invalid Attachment");
                    return response()->json(['data' => 'Invalid Attachment'], 422);
                }
                $date = Carbon::now()->format('Y-m-d');
                $fileName = $date . '_' . uniqid() . '.' . $extension;
                $contents = file_get_contents($file->getRealPath());
                Storage::put($path . DIRECTORY_SEPARATOR . $fileName, $contents);
            }

            return $fileName;
        } catch (Exception $e) {
            logger("============= ERROR ::  makeAttachment =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }
}
