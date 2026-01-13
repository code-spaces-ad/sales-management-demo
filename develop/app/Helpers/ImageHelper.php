<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Helpers;

use HeadOfficeInfoConst;
use Illuminate\Http\UploadedFile;
use Image;

/**
 * 画像ヘルパークラス
 */
class ImageHelper
{
    /**
     * 画像リサイズ処理
     *
     * @param UploadedFile $file
     * @param int $resize_width
     * @param int $resize_height
     * @return \Intervention\Image\Image
     */
    public static function resizeImage(UploadedFile $file,
        int $resize_width = HeadOfficeInfoConst::COMPANY_SEAL_IMAGE_FILE_NAME_MAX_WIDTH,
        int $resize_height = HeadOfficeInfoConst::COMPANY_SEAL_IMAGE_FILE_NAME_MAX_HEIGHT): \Intervention\Image\Image
    {
        $image = Image::make($file->getRealPath());
        // リサイズ判定
        if ($image->width() > $resize_width || $image->height() > $resize_height) {
            $image->resize($resize_width, $resize_height, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize(); // 小さい画像を拡大しない
            });
        }

        return $image;
    }
}
