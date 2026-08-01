<?php

namespace Kir\Image;

use PHPUnit\Framework\TestCase;

class ImageMetadataAndOutputTest extends TestCase {
	public function testImageTypeExtensionAndMimeTypeMetadata(): void {
		$filename = __DIR__.'/images/cat.png';

		self::assertSame(IMAGETYPE_PNG, Image::getImageType($filename));
		self::assertSame('.png', Image::getDefaultImageExtension($filename));
		self::assertSame('image/png', Image::loadFromFile($filename)->getMimeType());
		self::assertNull(Image::create(1, 1)->getMimeType());
	}

	/**
	 * @dataProvider provideOutputFormats
	 */
	public function testSaveAsStringEncodesRequestedFormat(int $type, string $prefix, ?string $marker = null): void {
		$image = Image::create(1, 1, Color::whiteTransparent());

		$data = $image->saveAsString($type, 80);
		self::assertStringStartsWith($prefix, $data);
		if($marker !== null) {
			self::assertSame($marker, substr($data, 8, strlen($marker)));
		}
	}

	/**
	 * @return iterable<string, array{int, string, string|null}>
	 */
	public function provideOutputFormats(): iterable {
		yield 'gif' => [IMAGETYPE_GIF, 'GIF8', null];
		yield 'jpeg' => [IMAGETYPE_JPEG, "\xFF\xD8\xFF", null];
		yield 'png' => [IMAGETYPE_PNG, "\x89PNG\r\n\x1a\n", null];
		yield 'bmp' => [IMAGETYPE_BMP, 'BM', null];
		yield 'webp' => [IMAGETYPE_WEBP, 'RIFF', 'WEBP'];
	}

	public function testSaveAsUsesExtensionAndExplicitType(): void {
		$image = Image::create(1, 1, Color::fromRGB(255, 0, 0));
		$gifFilename = $this->createTemporaryFilename('gif');
		$explicitPngFilename = $this->createTemporaryFilename('data');

		try {
			self::assertSame($image, $image->saveAs($gifFilename));
			self::assertSame(IMAGETYPE_GIF, Image::getImageType($gifFilename));

			self::assertSame($image, $image->saveAs($explicitPngFilename, IMAGETYPE_PNG));
			self::assertSame(IMAGETYPE_PNG, Image::getImageType($explicitPngFilename));
		} finally {
			foreach([$gifFilename, $explicitPngFilename] as $filename) {
				if(is_file($filename)) {
					unlink($filename);
				}
			}
		}
	}

	public function testMeasureTextRejectsUnreadableFont(): void {
		$image = Image::create(1, 1);
		$this->expectException(ImageRuntimeException::class);
		$this->expectExceptionMessage('Font file not found or not readable');

		$image->measureText('Text', __DIR__.'/missing-font.ttf', 12);
	}

	public function testTextRejectsUnreadableFont(): void {
		$image = Image::create(1, 1);
		$this->expectException(ImageRuntimeException::class);
		$this->expectExceptionMessage('Font file not found or not readable');

		$image->text(
			'Text',
			0,
			0,
			__DIR__.'/missing-font.ttf',
			12,
			Color::fromRGB(0, 0, 0)
		);
	}

	private function createTemporaryFilename(string $extension): string {
		return sys_get_temp_dir().'/php-image-manipulation-'.bin2hex(random_bytes(8)).'.'.$extension;
	}
}
