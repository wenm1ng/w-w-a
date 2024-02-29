<?php

namespace App\Work\Lib\Grafika\Gd\Filter;

use App\Work\Lib\Grafika\FilterInterface;
use App\Work\Lib\Grafika\Gd\Image;

/**
 * Blurs the image.
 */
class Blur implements FilterInterface
{

    /**
     * @var int
     */
    protected $amount;

    /**
     * Blur constructor.
     * @param int $amount The amount of blur to apply. Possible values 1-100.
     */
    public function __construct($amount = 1)
    {
        $this->amount = (int) $amount;
    }

    /**
     * @param Image $image
     *
     * @return Image
     */
    public function apply($image)
    {
        for ($i=0; $i < $this->amount; $i++) {
            imagefilter($image->getCore(), IMG_FILTER_GAUSSIAN_BLUR);
        }
        return $image;
    }
}