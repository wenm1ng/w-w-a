<?php

namespace App\Work\Lib\Grafika\Imagick\Filter;

use App\Work\Lib\Grafika\FilterInterface;
use App\Work\Lib\Grafika\Imagick\Image;

/**
 * Invert the image colors.
 */
class Invert implements FilterInterface{

    /**
     * @param Image $image
     *
     * @return Image
     */
    public function apply( $image ) {

        $image->getCore()->negateImage(false);
        return $image;
    }

}