<?php

namespace Kir\Image\Tools;

use Kir\Image\ImageRuntimeException;

class ImageTypeTools {
	/**
	 * Try to resolve a file-extension to an php-gd image type. Returns null if not successful.
	 *
	 * @param string|null $filename
	 * @return int|null
	 */
	public static function getImageTypeFromFileExtension(?string $filename): ?int {
		if($filename === null) {
			return null;
		}
		if(preg_match('{\\.([a-z]+)$}i', $filename, $matches)) {
			$ext = strtolower($matches[1]);
			switch ($ext) {
				case 'jpeg':
				case 'jpg' : return IMAGETYPE_JPEG;
				case 'gif' : return IMAGETYPE_GIF;
				case 'png' : return IMAGETYPE_PNG;
				case 'bmp' : return IMAGETYPE_BMP;
				case 'webp': return IMAGETYPE_WEBP;
				default: return null;
			}
		}
		return null;
	}
	
	/**
	 * @param string $filename
	 * @return string|null
	 */
	public static function getDefaultImageExtensionFromFile(string $filename): ?string {
		$typeId = ImageFactory::getImageType($filename);
		return self::getDefaultImageExtensionForType($typeId);
	}
	
	/**
	 * @param int|null $typeId
	 * @return string|null
	 */
	public static function getDefaultImageExtensionForType(?int $typeId): ?string {
		if($typeId === null) {
			return null;
		}
		$ext = image_type_to_extension($typeId);
		if($ext === false) {
			return null;
		}
		return $ext;
	}
}
