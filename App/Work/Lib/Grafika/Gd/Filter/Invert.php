<?php

namespace App\Work\Lib\Grafika\Gd\Filter;

use App\Work\Lib\Grafika\FilterInterface;
use App\Work\Lib\Grafika\Gd\Image;

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

        imagefilter($image->getCore(), IMG_FILTER_NEGATE);
        return $image;
    }

}