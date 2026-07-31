<?php

namespace Kir\Image\Tools;

use GdImage;
use Kir\Image\Image;
use Kir\Image\ImageRuntimeException;

class ImageFactory {
	/** @var array<int, string> */
	private const LOADERS_BY_IMAGE_TYPE = [
		IMAGETYPE_AVIF => 'imagecreatefromavif',
		IMAGETYPE_BMP => 'imagecreatefrombmp',
		IMAGETYPE_GIF => 'imagecreatefromgif',
		IMAGETYPE_JPEG => 'imagecreatefromjpeg',
		IMAGETYPE_PNG => 'imagecreatefrompng',
		IMAGETYPE_WBMP => 'imagecreatefromwbmp',
		IMAGETYPE_WEBP => 'imagecreatefromwebp',
		IMAGETYPE_XBM => 'imagecreatefromxbm',
	];

	/**
	 * GD, GD2, TGA and XPM have no matching IMAGETYPE_* constant and therefore
	 * cannot be identified by exif_imagetype().
	 *
	 * @var array<string, string>
	 */
	private const LOADERS_BY_FILE_EXTENSION = [
		'gd' => 'imagecreatefromgd',
		'gd2' => 'imagecreatefromgd2',
		'tga' => 'imagecreatefromtga',
		'xpm' => 'imagecreatefromxpm',
	];

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
		$w = imagesx($resource);
		$h = imagesy($resource);
		$image = Image::create($w, $h, null, $type);
		imagecopy($image->getGdImage(), $resource, 0, 0, 0, 0, $w, $h);
		return $image;
	}
	
	/**
	 * @param string $filename
	 * @return array{GdImage|false, int|null}
	 */
	public static function loadImageResource(string $filename) {
		$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
		$loader = self::LOADERS_BY_FILE_EXTENSION[$extension] ?? null;
		if($loader !== null) {
			return self::loadImageResourceUsing($filename, $loader, null);
		}

		$imageType = self::getImageType($filename);
		$loader = self::LOADERS_BY_IMAGE_TYPE[$imageType] ?? null;
		if($loader === null) {
			throw new ImageRuntimeException("Unsupported image format: {$imageType}");
		}
		return self::loadImageResourceUsing($filename, $loader, $imageType);
	}

	/**
	 * @param string $filename
	 * @param string $loader
	 * @param int|null $imageType
	 * @return array{GdImage|false, int|null}
	 */
	private static function loadImageResourceUsing(string $filename, string $loader, ?int $imageType): array {
		if(!function_exists($loader)) {
			throw new ImageRuntimeException("Image loader {$loader}() is not available in the installed GD library");
		}
		$resource = $loader($filename);
		if(!($resource instanceof GdImage) && $resource !== false) {
			throw new ImageRuntimeException("Image loader {$loader}() returned an invalid result");
		}
		return [$resource, $imageType];
	}
	
	/**
	 * @param string $filename
	 * @return int
	 */
	public static function getImageType(string $filename): int {
		if(function_exists('exif_imagetype')) {
			$imageType = exif_imagetype($filename);
		} else {
			$imageInfo = getimagesize($filename);
			$imageType = $imageInfo[2] ?? false;
		}
		if($imageType === false) {
			throw new ImageRuntimeException('Unknown image type');
		}
		return $imageType;
	}
}
