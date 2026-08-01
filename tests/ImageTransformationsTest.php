<?php

namespace Kir\Image;

use PHPUnit\Framework\TestCase;

class ImageTransformationsTest extends TestCase {
	public function testGreyscaleConvertsAllColorChannelsToTheSameValue(): void {
		$image = Image::create(1, 1, Color::fromRGB(255, 0, 0));

		self::assertSame($image, $image->greyscale());
		self::assertSame(
			$image->getRedColorAt(0, 0),
			$image->getGreenColorAt(0, 0)
		);
		self::assertSame(
			$image->getGreenColorAt(0, 0),
			$image->getBlueColorAt(0, 0)
		);
	}

	public function testGreyscaleMaskControlsAlphaAndPreservesColor(): void {
		$image = Image::create(2, 1, Color::fromRGB(255, 0, 0));
		$mask = Image::create(2, 1, Color::whiteOpaque());
		$mask->rectangle(1, 0, 1, 1, Color::fromRGB(0, 0, 0));

		self::assertSame($image, $image->applyAlphaMaskFromGreyscaleImage($mask));
		self::assertSame([255, 0], [$image->getAlphaAt(0, 0), $image->getAlphaAt(1, 0)]);
		self::assertSame(255, $image->getRedColorAt(0, 0));
	}

	public function testAdjustColorsStretchesValuesAndPreservesAlpha(): void {
		$image = Image::create(2, 1, Color::fromRGBA(50, 100, 150, 128));
		imagealphablending($image->getGdImage(), false);
		$image->rectangle(1, 0, 1, 1, Color::fromRGBA(100, 150, 200, 128));

		self::assertSame($image, $image->adjustColors());
		self::assertSame([0, 85, 170, 128], $this->getPixel($image, 0, 0));
		self::assertSame([85, 170, 255, 128], $this->getPixel($image, 1, 0));
	}

	public function testCropRemovesWhitespaceAroundInnerObject(): void {
		$image = Image::create(6, 4, Color::whiteOpaque());
		$image->rectangle(2, 1, 2, 2, Color::fromRGB(0, 0, 0));

		self::assertSame($image, $image->crop(1));
		self::assertSame([2, 2], [$image->getWidth(), $image->getHeight()]);
		self::assertSame([0, 0, 0, 255], $this->getPixel($image, 0, 0));
	}

	public function testCropAddsRequestedBorderOutsideOriginalCanvas(): void {
		$image = Image::create(5, 5, Color::whiteOpaque());
		$image->rectangle(0, 0, 1, 1, Color::fromRGB(0, 0, 0));

		$image->crop(1, 100, Color::fromRGB(0, 255, 0));
		self::assertSame([3, 3], [$image->getWidth(), $image->getHeight()]);
		self::assertSame([0, 255, 0, 255], $this->getPixel($image, 0, 0));
		self::assertSame([0, 0, 0, 255], $this->getPixel($image, 1, 1));
	}

	public function testResizeCanvasCentersImageAndUsesBackgroundColor(): void {
		$image = Image::create(1, 1, Color::fromRGB(255, 0, 0));

		self::assertSame($image, $image->resizeCanvasCentered(3, 3, Color::fromRGB(0, 255, 0)));
		self::assertSame([3, 3], [$image->getWidth(), $image->getHeight()]);
		self::assertSame([0, 255, 0, 255], $this->getPixel($image, 0, 0));
		self::assertSame([255, 0, 0, 255], $this->getPixel($image, 1, 1));

		$resource = $image->getGdImage();
		self::assertSame($image, $image->resizeCanvas(3, 3));
		self::assertSame($resource, $image->getGdImage());
	}

	public function testResizeSupportsOmittedDimensionsAndNoOp(): void {
		$image = Image::create(2, 1, Color::fromRGB(255, 0, 0));
		$resource = $image->getGdImage();

		self::assertSame($image, $image->resize());
		self::assertSame($resource, $image->getGdImage());
		$image->resize(null, 2);
		self::assertSame([2, 2], [$image->getWidth(), $image->getHeight()]);
		$image->resize(4, null);
		self::assertSame([4, 2], [$image->getWidth(), $image->getHeight()]);
	}

	public function testShrinkProportionalOnlyShrinks(): void {
		$image = Image::create(4, 2);
		self::assertSame($image, $image->shrinkProportional(2, 2));
		self::assertSame([2, 1], [$image->getWidth(), $image->getHeight()]);

		$smallImage = Image::create(2, 1);
		$resource = $smallImage->getGdImage();
		$smallImage->shrinkProportional(4);
		self::assertSame([2, 1], [$smallImage->getWidth(), $smallImage->getHeight()]);
		self::assertSame($resource, $smallImage->getGdImage());
	}

	public function testEnlargeProportionalOnlyEnlarges(): void {
		$image = Image::create(2, 1);
		self::assertSame($image, $image->enlargeProportional(4));
		self::assertSame([4, 2], [$image->getWidth(), $image->getHeight()]);

		$largeImage = Image::create(4, 2);
		$resource = $largeImage->getGdImage();
		$largeImage->enlargeProportional(2, 2);
		self::assertSame([4, 2], [$largeImage->getWidth(), $largeImage->getHeight()]);
		self::assertSame($resource, $largeImage->getGdImage());
	}

	public function testImageCanBePastedOnImageAndGdImageTargets(): void {
		$source = Image::create(1, 1, Color::fromRGB(255, 0, 0));
		$target = Image::create(2, 2, Color::fromRGB(0, 0, 255));

		self::assertSame($source, $source->pasteOn($target, 1, 1));
		self::assertSame([255, 0, 0, 255], $this->getPixel($target, 1, 1));

		$rawTarget = Image::create(1, 1, Color::fromRGB(0, 0, 255))->getGdImage();
		self::assertSame($source, $source->placeImageOn($rawTarget));
		$wrappedTarget = new Image($rawTarget);
		self::assertSame([255, 0, 0, 255], $this->getPixel($wrappedTarget, 0, 0));
	}

	public function testRemoveAlphaBackgroundUsesOpaqueColor(): void {
		$image = Image::create(1, 1, Color::whiteTransparent());

		self::assertSame($image, $image->removeAlphaBackground(Color::fromRGB(0, 255, 0)));
		self::assertSame([0, 255, 0, 255], $this->getPixel($image, 0, 0));

		$image = Image::create(1, 1, Color::whiteTransparent());
		$image->removeAlphaBackground();
		self::assertSame([255, 255, 255, 255], $this->getPixel($image, 0, 0));
	}

	public function testFillChangesConnectedArea(): void {
		$image = Image::create(2, 1, Color::fromRGB(255, 0, 0));

		self::assertSame($image, $image->fill(0, 0, Color::fromRGB(0, 0, 255)));
		self::assertSame([0, 0, 255, 255], $this->getPixel($image, 1, 0));
	}

	/**
	 * @return array{int, int, int, int}
	 */
	private function getPixel(Image $image, int $x, int $y): array {
		return [
			$image->getRedColorAt($x, $y),
			$image->getGreenColorAt($x, $y),
			$image->getBlueColorAt($x, $y),
			$image->getAlphaAt($x, $y),
		];
	}
}
