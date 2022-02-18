<?php

namespace Kir\Image\Tools;

use PHPUnit\Framework\TestCase;

class ImageTypeFromFileExtensionTests extends TestCase {
	public function testFileExtensions(): void {
		self::assertEquals(IMAGETYPE_GIF , ImageTypeTools::getImageTypeFromFileExtension('test-filename.gif'));
		self::assertEquals(IMAGETYPE_JPEG, ImageTypeTools::getImageTypeFromFileExtension('test-filename.jpeg'));
		self::assertEquals(IMAGETYPE_JPEG, ImageTypeTools::getImageTypeFromFileExtension('test-filename.jpg'));
		self::assertEquals(IMAGETYPE_PNG , ImageTypeTools::getImageTypeFromFileExtension('test-filename.png'));
		self::assertEquals(IMAGETYPE_BMP , ImageTypeTools::getImageTypeFromFileExtension('test-filename.bmp'));
		self::assertEquals(IMAGETYPE_WEBP, ImageTypeTools::getImageTypeFromFileExtension('test-filename.webp'));
		self::assertEquals(null, ImageTypeTools::getImageTypeFromFileExtension('test-filename.doc'));
		self::assertEquals(null, ImageTypeTools::getImageTypeFromFileExtension('test-filename'));
	}
}