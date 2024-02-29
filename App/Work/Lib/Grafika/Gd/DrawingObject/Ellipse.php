<?php
namespace App\Work\Lib\Grafika\Gd\DrawingObject;

use App\Work\Lib\Grafika\DrawingObject\Ellipse as Base;
use App\Work\Lib\Grafika\DrawingObjectInterface;
use App\Work\Lib\Grafika\Gd\Editor;
use App\Work\Lib\Grafika\ImageInterface;

/**
 * Class Ellipse
 * @package Grafika
 */
class Ellipse extends Base implements DrawingObjectInterface
{

    /**
     * TODO: Anti-aliased curves
     * @param ImageInterface $image
     * @return ImageInterface
     */
    public function draw($image)
    {

        list($x, $y) = $this->pos;
        $left = $x + $this->width / 2;
        $top = $y + $this->height / 2;

        if( null !== $this->fillColor ){
            list($r, $g, $b, $alpha) = $this->fillColor->getRgba();
            $fillColorResource = imagecolorallocatealpha($image->getCore(), $r, $g, $b, Editor::gdAlpha($alpha));
            imagefilledellipse($image->getCore(), $left, $top, $this->width, $this->height, $fillColorResource);
        }
        // Create borders. It will be placed on top of the filled ellipse (if present)
        if ( 0 < $this->getBorderSize() and null !== $this->borderColor) { // With border > 0 AND borderColor !== null
            list($r, $g, $b, $alpha) = $this->borderColor->getRgba();
            $borderColorResource = imagecolorallocatealpha($image->getCore(), $r, $g, $b, Editor::gdAlpha($alpha));
            imageellipse($image->getCore(), $left, $top, $this->width, $this->height, $borderColorResource);
        }

        return $image;
    }
}