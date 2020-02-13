<?php

namespace App\Helper;

use Gumlet\ImageResize;

abstract class ImageHelper
{
    /**
     * Permet de réduire la taille d'une image jusqu'à un poids déterminé
     * @param $imagePath
     * @param $sizeInBytes
     * @param null $height
     * @return bool
     * @throws \Gumlet\ImageResizeException
     */
    public static function resizeToSize($imagePath, $sizeInBytes, $height = null)
    {
        $image = new ImageResize($imagePath);

        if (!$height) {
            $height = $image->getSourceHeight();
        }

        $image->resizeToHeight($height);
        $image->save($imagePath);
        $fileSize = filesize($imagePath);

        if ($fileSize > $sizeInBytes) {
            $height -= 10;
            return self::resizeToSize($imagePath, $sizeInBytes, $height);
        }

        return true;
    }

}
