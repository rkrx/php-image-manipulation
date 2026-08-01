<?php

namespace Kir\Image;

use GdImage;
use PHPUnit\Framework\TestCase;

class ImageTest extends TestCase {
	public function testSaveAsStringPngKeepsAlphaWhenUsingImgPngConstant(): void {
		$image = Image::create(1, 1, Color::whiteTransparent(), IMAGETYPE_PNG);
		$string = $image->saveAsString(IMAGETYPE_PNG);
		self::assertStringStartsWith("\x89PNG\r\n\x1a\n", $string);
		$gd = imagecreatefromstring($string);
		self::assertNotFalse($gd);
		$alpha = (imagecolorat($gd, 0, 0) >> 24) & 0x7F;
		self::assertSame(127, $alpha);
		
		$image2 = Image::create(1, 1, Color::whiteTransparent(), IMAGETYPE_PNG);
		$string2 = $image2->saveAsString();
		self::assertStringStartsWith("\x89PNG\r\n\x1a\n", $string2);
	}

	public function testLoadBmpFromFile(): void {
		$image = Image::loadFromFile(__DIR__.'/images/cat.bmp');
		self::assertEquals([$image->getWidth(), $image->getHeight()], [4, 4]);
	}
	
	public function testLoadGifFromFile(): void {
		$image = Image::loadFromFile(__DIR__.'/images/cat.gif');
		self::assertEquals([$image->getWidth(), $image->getHeight()], [2, 2]);
	}
	
	public function testLoadJpegFromFile(): void {
		$image = Image::loadFromFile(__DIR__.'/images/cat.jpg');
		self::assertEquals([$image->getWidth(), $image->getHeight()], [2, 2]);
	}
	
	public function testLoadPngFromFile(): void {
		$image = Image::loadFromFile(__DIR__.'/images/cat.png');
		self::assertEquals([$image->getWidth(), $image->getHeight()], [2, 2]);
	}
	
	public function testLoadWebpFromFile(): void {
		$image = Image::loadFromFile(__DIR__.'/images/cat.webp');
		self::assertEquals([$image->getWidth(), $image->getHeight()], [2, 2]);
	}
	
	public function testLoadXbmFromFile(): void {
		$image = Image::loadFromFile(__DIR__.'/images/cat.xbm');
		self::assertEquals([$image->getWidth(), $image->getHeight()], [2, 2]);
	}
	
	public function testLoadBmpFromString(): void {
		$image = Image::loadFromString((string) file_get_contents(__DIR__.'/images/cat.bmp'));
		self::assertEquals([$image->getWidth(), $image->getHeight()], [4, 4]);
	}
	
	public function testLoadGifFromString(): void {
		$image = Image::loadFromString((string) file_get_contents(__DIR__.'/images/cat.gif'));
		self::assertEquals([$image->getWidth(), $image->getHeight()], [2, 2]);
	}
	
	public function testLoadJpegFromString(): void {
		$image = Image::loadFromString((string) file_get_contents(__DIR__.'/images/cat.jpg'));
		self::assertEquals([$image->getWidth(), $image->getHeight()], [2, 2]);
	}
	
	public function testLoadPngFromString(): void {
		$image = Image::loadFromString((string) file_get_contents(__DIR__.'/images/cat.png'));
		self::assertEquals([$image->getWidth(), $image->getHeight()], [2, 2]);
	}
	
	public function testLoadWebpFromString(): void {
		$image = Image::loadFromString((string) file_get_contents(__DIR__.'/images/cat.webp'));
		self::assertEquals([$image->getWidth(), $image->getHeight()], [2, 2]);
	}
	
	public function testCreate(): void {
		$image = Image::create(500, 500);
		self::assertEquals([$image->getWidth(), $image->getHeight()], [500, 500]);
	}

	public function testFormatSupportMetadata(): void {
		self::assertSame(function_exists('imagecreatefrompng'), Image::supportsFormat(IMAGETYPE_PNG));
		self::assertSame(function_exists('imagecreatefrompng'), Image::supportsFormat('.PNG'));
		self::assertSame(Image::supportsFormat('jpeg'), Image::supportsFormat('jpg'));
		self::assertSame(function_exists('imagecreatefromxpm'), Image::supportsFormat('xpm'));
		self::assertFalse(Image::supportsFormat('unknown'));

		self::assertSame(function_exists('imagepng'), Image::supportsFormat('png', true));
		self::assertFalse(Image::supportsFormat('avif', true));
		self::assertSame(Image::supportsFormat('png'), in_array('png', Image::getSupportedFormats(), true));
		self::assertSame(Image::supportsFormat('png', true), in_array('png', Image::getSupportedFormats(true), true));
		self::assertNotContains('avif', Image::getSupportedFormats(true));
	}

	public function testCreateRejectsZeroSizedImage(): void {
		$this->expectException(ImageRuntimeException::class);
		$this->expectExceptionMessage('Image width and height must be greater than zero');

		Image::create(0, 1);
	}

	public function testColorConstructorRejectsOutOfRangeChannel(): void {
		$this->expectException(ColorRuntimeException::class);
		$this->expectExceptionMessage('The value for red must be between 0 and 255');

		new Color(256, 0, 0, 255);
	}
	
	public function testGetImageObject(): void {
		$image = Image::create(500, 500);
		$resource = $image->getGdImage();
		self::assertTrue($resource instanceof GdImage || is_resource($resource));
	}
	
	public function testDetectInnerObject(): void {
		$image = Image::create(32, 32, Color::whiteOpaque());
		$values = $image->detectInnerObject(1);
		$expectedValues = ['left' => 0, 'top' => 0, 'bottom' => 0, 'right' => 0, 'width' => 32, 'height' => 32];
		self::assertEquals($expectedValues, $values);
		
		foreach([[255, 0, 0], [0, 255, 0], [0, 0, 255]] as [$r, $g, $b]) {
			$image = Image::create(32, 32, Color::whiteOpaque());
			$image->rectangle(4, 8, 12, 16, Color::fromRGB($r, $g, $b));
			$values = $image->detectInnerObject(1);
			$expectedValues = ['left' => 4, 'top' => 8, 'bottom' => 8, 'right' => 16, 'width' => 12, 'height' => 16];
			self::assertEquals($expectedValues, $values);
		}
	}

	public function testAdjustColorsLeavesSolidImageUnchanged(): void {
		$image = Image::create(2, 2, Color::fromRGB(64, 64, 64));

		self::assertSame($image, $image->adjustColors());
		self::assertSame(64, $image->getRedColorAt(0, 0));
		self::assertSame(64, $image->getGreenColorAt(0, 0));
		self::assertSame(64, $image->getBlueColorAt(0, 0));
	}

	public function testRotateAnticlockwise(): void {
		$image = Image::create(2, 1, Color::fromRGB(255, 0, 0));
		$image->rectangle(1, 0, 1, 1, Color::fromRGB(0, 0, 255));

		self::assertSame($image, $image->rotate(90));
		self::assertSame([1, 2], [$image->getWidth(), $image->getHeight()]);
		self::assertSame([0, 0, 255], [
			$image->getRedColorAt(0, 0),
			$image->getGreenColorAt(0, 0),
			$image->getBlueColorAt(0, 0),
		]);
		self::assertSame([255, 0, 0], [
			$image->getRedColorAt(0, 1),
			$image->getGreenColorAt(0, 1),
			$image->getBlueColorAt(0, 1),
		]);
	}

	public function testRotateRejectsNonFiniteAngle(): void {
		$image = Image::create(1, 1);
		$this->expectException(ImageRuntimeException::class);
		$this->expectExceptionMessage('Rotation angle must be finite');

		$image->rotate(INF);
	}

	public function testRotateUsesTransparentBackgroundByDefault(): void {
		$image = Image::create(2, 2, Color::fromRGB(255, 0, 0));
		$image->rotate(45);

		self::assertSame(0, $image->getAlphaAt($image->getWidth() - 1, 0));
	}

	public function testFlipModes(): void {
		$image = Image::create(2, 2, Color::fromRGB(255, 0, 0));
		$image->rectangle(1, 0, 1, 1, Color::fromRGB(0, 255, 0));
		$image->rectangle(0, 1, 1, 1, Color::fromRGB(0, 0, 255));
		$image->rectangle(1, 1, 1, 1, Color::whiteOpaque());

		foreach([
			IMG_FLIP_HORIZONTAL => [0, 255, 0],
			IMG_FLIP_VERTICAL => [0, 0, 255],
			IMG_FLIP_BOTH => [255, 255, 255],
		] as $mode => $expectedColor) {
			$flipped = $image->getCopy();
			self::assertSame($flipped, $flipped->flip($mode));
			self::assertSame($expectedColor, [
				$flipped->getRedColorAt(0, 0),
				$flipped->getGreenColorAt(0, 0),
				$flipped->getBlueColorAt(0, 0),
			]);
		}

		$flipped = $image->getCopy();
		$flipped->flip();
		self::assertSame(255, $flipped->getGreenColorAt(0, 0));
	}

	public function testFlipRejectsInvalidMode(): void {
		$image = Image::create(1, 1);
		$this->expectException(ImageRuntimeException::class);
		$this->expectExceptionMessage('Invalid image flip mode');

		$image->flip(-1);
	}

	public function testCropToCoverUsesCenteredSourceRegion(): void {
		$image = Image::create(4, 2, Color::fromRGB(255, 0, 0));
		$image->rectangle(1, 0, 1, 2, Color::fromRGB(0, 255, 0));
		$image->rectangle(2, 0, 1, 2, Color::fromRGB(0, 0, 255));
		$image->rectangle(3, 0, 1, 2, Color::whiteOpaque());

		self::assertSame($image, $image->cropToCover(2, 2));
		self::assertSame([2, 2], [$image->getWidth(), $image->getHeight()]);
		self::assertSame([0, 255, 0], [
			$image->getRedColorAt(0, 0),
			$image->getGreenColorAt(0, 0),
			$image->getBlueColorAt(0, 0),
		]);
		self::assertSame([0, 0, 255], [
			$image->getRedColorAt(1, 0),
			$image->getGreenColorAt(1, 0),
			$image->getBlueColorAt(1, 0),
		]);
	}

	public function testCropToCoverRejectsZeroSizedTarget(): void {
		$image = Image::create(1, 1);
		$this->expectException(ImageRuntimeException::class);
		$this->expectExceptionMessage('Image width and height must be greater than zero');

		$image->cropToCover(1, 0);
	}

	/**
	 * @dataProvider provideMismatchedMaskSizes
	 */
	public function testApplyAlphaMaskRejectsMismatchedMaskSize(int $width, int $height): void {
		$image = Image::create(2, 2);
		$mask = Image::create($width, $height);

		$this->expectException(ImageRuntimeException::class);
		$this->expectExceptionMessage('The mask image must have the same size as the source image');

		$image->applyAlphaMaskFromGreyscaleImage($mask);
	}

	/**
	 * @return iterable<string, array{int, int}>
	 */
	public function provideMismatchedMaskSizes(): iterable {
		yield 'different width' => [1, 2];
		yield 'different height' => [2, 1];
	}
	
	public function testResizeProportional(): void {
		$image = Image::loadFromFile(__DIR__.'/images/cat.webp');
		$image->resizeProportional(600);
		self::assertEquals(600, $image->getWidth());
		self::assertEquals(600, $image->getHeight());
	}
}
