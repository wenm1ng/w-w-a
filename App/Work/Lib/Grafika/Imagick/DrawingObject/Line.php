<?php
namespace App\Work\Lib\Grafika\Imagick\DrawingObject;

use App\Work\Lib\Grafika\DrawingObject\Line as Base;
use App\Work\Lib\Grafika\DrawingObjectInterface;
use App\Work\Lib\Grafika\Imagick\Image;

/**
 * Class Line
 * @package Grafika
 */
class Line extends Base implements DrawingObjectInterface
{

    /**
     * @param Image $image
     *
     * @return Image
     */
    public function draw($image)
    {

        $strokeColor = new \ImagickPixel($this->getColor()->getHexString());

        $draw = new \ImagickDraw();

        $draw->setStrokeColor($strokeColor);

        $draw->setStrokeWidth($this->thickness);

        list($x1, $y1) = $this->point1;
        list($x2, $y2) = $this->point2;
        $draw->line($x1, $y1, $x2, $y2);

        $image->getCore()->drawImage($draw);

        return $image;
    }


}