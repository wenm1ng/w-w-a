<?php

namespace App\Work\Lib\Grafika\Imagick\Filter;

use App\Work\Lib\Grafika\FilterInterface;
use App\Work\Lib\Grafika\Imagick\Image;

/**
 * Sharpen an image.
 */
class Sharpen implements FilterInterface{

    /**
     * @var int $amount
     */
    protected $amount;

    /**
     * Sharpen constructor.
     * @param int $amount Amount of sharpening from >= 1 to <= 100
     */
    public function __construct($amount)
    {
        $this->amount = (int) $amount;
    }

    /**
     * @param Image $image
     *
     * @return Image
     */
    public function apply( $image ) {
        $image->getCore()->unsharpMaskImage(1, 1, $this->amount / 6.25, 0);
        return $image;
    }

}