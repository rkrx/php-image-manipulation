<?php

namespace Kir\Image\Tools;

use GdImage;
use Kir\Image\Image;
use Kir\Image\ImageRuntimeException;

class ImageFactory {
	/** @var array<string, int> */
	private const IMAGE_TYPES_BY_FORMAT = [
		'avif' => IMAGETYPE_AVIF,
		'bmp' => IMAGETYPE_BMP,
		'gif' => IMAGETYPE_GIF,
		'jpeg' => IMAGETYPE_JPEG,
		'png' => IMAGETYPE_PNG,
		'wbmp' => IMAGETYPE_WBMP,
		'webp' => IMAGETYPE_WEBP,
		'xbm' => IMAGETYPE_XBM,
	];

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
	 * Output formats intentionally match Image's public save methods.
	 *
	 * @var array<int, string>
	 */
	private const WRITERS_BY_IMAGE_TYPE = [
		IMAGETYPE_BMP => 'imagebmp',
		IMAGETYPE_GIF => 'imagegif',
		IMAGETYPE_JPEG => 'imagejpeg',
		IMAGETYPE_PNG => 'imagepng',
		IMAGETYPE_WEBP => 'imagewebp',
	];

	/**
	 * Checks whether the installed GD build and this library support a format.
	 * String formats are canonical extensions such as "png", "jpeg" or "tga".
	 *
	 * @param int|string $format An IMAGETYPE_* constant or file extension.
	 * @param bool $forWriting Check output support instead of input support.
	 */
	public static function supportsFormat(int|string $format, bool $forWriting = false): bool {
		if(is_string($format)) {
			$format = self::normalizeFormatName($format);
			$imageType = self::IMAGE_TYPES_BY_FORMAT[$format] ?? null;
			if($imageType !== null) {
				return self::supportsFormat($imageType, $forWriting);
			}
			if($forWriting) {
				return false;
			}
			$function = self::LOADERS_BY_FILE_EXTENSION[$format] ?? null;
			return $function !== null && function_exists($function);
		}

		$functions = $forWriting ? self::WRITERS_BY_IMAGE_TYPE : self::LOADERS_BY_IMAGE_TYPE;
		$function = $functions[$format] ?? null;
		return $function !== null && function_exists($function);
	}

	/**
	 * Returns canonical format names supported by the installed GD build and this library.
	 *
	 * @return list<string>
	 */
	public static function getSupportedFormats(bool $forWriting = false): array {
		$formats = [];
		foreach(array_merge(array_keys(self::IMAGE_TYPES_BY_FORMAT), array_keys(self::LOADERS_BY_FILE_EXTENSION)) as $format) {
			if(self::supportsFormat($format, $forWriting)) {
				$formats[] = $format;
			}
		}
		return $formats;
	}

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

	private static function normalizeFormatName(string $format): string {
		$format = strtolower(ltrim(trim($format), '.'));
		return $format === 'jpg' ? 'jpeg' : $format;
	}
}
