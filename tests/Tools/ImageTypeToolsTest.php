<?php

namespace Kir\Image\Tools;

use PHPUnit\Framework\TestCase;

class ImageTypeToolsTest extends TestCase {
	public function testFileExtensions(): void {
		self::assertSame(IMAGETYPE_GIF, ImageTypeTools::getImageTypeFromFileExtension('test-filename.gif'));
		self::assertSame(IMAGETYPE_JPEG, ImageTypeTools::getImageTypeFromFileExtension('test-filename.jpeg'));
		self::assertSame(IMAGETYPE_JPEG, ImageTypeTools::getImageTypeFromFileExtension('test-filename.jpg'));
		self::assertSame(IMAGETYPE_PNG, ImageTypeTools::getImageTypeFromFileExtension('test-filename.PNG'));
		self::assertSame(IMAGETYPE_BMP, ImageTypeTools::getImageTypeFromFileExtension('test-filename.bmp'));
		self::assertSame(IMAGETYPE_WEBP, ImageTypeTools::getImageTypeFromFileExtension('test-filename.webp'));
	}

	public function testUnknownOrMissingFileExtensionsReturnNull(): void {
		self::assertNull(ImageTypeTools::getImageTypeFromFileExtension(null));
		self::assertNull(ImageTypeTools::getImageTypeFromFileExtension('test-filename.doc'));
		self::assertNull(ImageTypeTools::getImageTypeFromFileExtension('test-filename'));
	}

	public function testDefaultImageExtensionCanBeResolvedFromTypeAndFile(): void {
		self::assertSame('.png', ImageTypeTools::getDefaultImageExtensionForType(IMAGETYPE_PNG));
		self::assertNull(ImageTypeTools::getDefaultImageExtensionForType(null));
		self::assertSame(
			'.png',
			ImageTypeTools::getDefaultImageExtensionFromFile(__DIR__.'/../images/cat.png')
		);
	}
}
