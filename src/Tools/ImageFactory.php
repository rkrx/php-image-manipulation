<?php

namespace Kir\Image\Tools;

use GdImage;
use Kir\Image\Image;
use Kir\Image\ImageRuntimeException;

class ImageFactory {
	/**
	 * Loads an image using all available image functions
	 *
	 * @param string $filename
	 * @return Image
	 */
	public static function loadImageFromFile(string $filename) {
		[$resource, $type] = self::loadImageResource($filename);
		if($resource === false) {
			throw new ImageRuntimeException('Could not load image');
		}
		try {
			$w = imagesx($resource);
			$h = imagesy($resource);
			$image = Image::create($w, $h, null, $type);
			imagecopy($image->getGdImage(), $resource, 0, 0, 0, 0, $w, $h);
			return $image;
		} finally {
			if(PHP_VERSION_ID < 80000) {
				imagedestroy($resource);
			}
		}
	}
	
	/**
	 * @param string $filename
	 * @return array{GdImage|false, int}
	 */
	public static function loadImageResource(string $filename) {
		$imageType = self::getImageType($filename);
		switch ($imageType) {
			case IMAGETYPE_GIF : return [imagecreatefromgif($filename) , IMAGETYPE_GIF ];
			case IMAGETYPE_JPEG: return [imagecreatefromjpeg($filename), IMAGETYPE_JPEG];
			case IMAGETYPE_PNG : return [imagecreatefrompng($filename) , IMAGETYPE_PNG ];
			case IMAGETYPE_BMP : return [imagecreatefrombmp($filename) , IMAGETYPE_BMP ];
			case IMAGETYPE_WEBP: return [imagecreatefromwebp($filename), IMAGETYPE_WEBP];
			case IMAGETYPE_XBM : return [imagecreatefromxbm($filename) , IMAGETYPE_XBM ];
			default: throw new ImageRuntimeException("Unsupported image format: {$imageType}");
		}
	}
	
	/**
	 * @param string $filename
	 * @return int
	 */
	public static function getImageType(string $filename): int {
		$imageType = exif_imagetype($filename);
		if($imageType === false) {
			throw new ImageRuntimeException('Unknown image type');
		}
		return $imageType;
	}
}