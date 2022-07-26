<?php

namespace App\Helper;

use Gumlet\ImageResize;
use Gumlet\ImageResizeException;

abstract class ImageHelper
{
    /**
     * Permet de réduire la taille d'une image jusqu'à un poids déterminé
     * @throws ImageResizeException
     */
    public static function resizeToSize(string $imagePath, int $sizeInBytes, int $height = null): bool
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
