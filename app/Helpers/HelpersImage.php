<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Image;

class HelpersImage
{


    /**
     * Get Extension by mime type
     *
     * @param Image $image
     * @return false|string
     */
    public static function getExtensionByMime(Image $image)
    {
        $mime = $image->mime();
        if ($mime == 'image/jpeg') {
            return '.jpg';
        } elseif ($mime == 'image/png') {
            return '.png';
        } elseif ($mime == 'image/gif') {
            return '.gif';
        } else {
            return false;
        }
    }

    /**
     * @param Image $image
     * @param string $type
     * @param string|null $fileName
     * @param array $params
     * @param array $option
     * @return false|string
     */
    public static function upload(
        Image $image,
        string $type = 'vehicle_image',
        string $fileName = null,
        array $params = [],
        array $option = ['visibility' => 'public']
    ) {
        $fileName = self::fileName(($fileName ?? md5(uniqid(rand(), true))));
        $extension = self::getExtensionByMime($image);
        $path = self::getPathByType($type, $params);

        $path = $path . $fileName . $extension;

        $image = Storage::put($path, $image->encode(), $option);

        return $image ? $path : false;
    }

    /**
     * @param string $type
     * @param array $params
     * @return string
     */
    public static function getPathByType(string $type, array $params = [])
    {
        $dir = Config::get('constant.default_path');
        $path = Config::get('constant.images_path.' . $type);

        foreach ($params as $key => $param) {
            $path = str_replace($key, $param, $path);
        }

        return $dir . $path;
    }

    /**
     * Padroniza o nome do arquivo, removendo ascentos, espaços e outros caracteres
     *
     * @param string $name
     * @return string
     */
    public static function fileName(string $name)
    {
        $name = Helpers::sanitizeString($name);

        return $name;
    }
}
