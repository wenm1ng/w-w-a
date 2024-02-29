<?php
namespace App\Work\Lib\Grafika;

/**
 * Interface DrawingObjectInterface
 * @package Grafika
 */
interface DrawingObjectInterface {

    /**
     * @param ImageInterface $image
     *
     * @return ImageInterface
     */
    public function draw( $image );

}