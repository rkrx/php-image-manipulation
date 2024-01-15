<?php

namespace Kir\Image;

use Closure;
use GdImage;
use Kir\Image\Tools\ImageCalculator;
use Kir\Image\Tools\ImageFactory;
use Kir\Image\Tools\ImageTools;
use Kir\Image\Tools\ImageTypeTools;

class Image {
	/** @var GdImage */
	private $resource;
	private ?int $lastFileType = null;

	/**
	 * Detects the image type of the given image file.
	 *
	 * Example:
	 * ```php
	 * use Kir\Image\Image;
	 * $imageType = Image::getImageType('image.png');
	 * echo $imageType; // the image type constant, e.g. IMAGETYPE_PNG
	 * ```
	 *
	 * @param string $filename The filename of the image.
	 * @return int
	 */
	public static function getImageType(string $filename): int {
		return ImageFactory::getImageType($filename);
	}

	/**
	 * Returns the default file extension for a given image type determined using mime_content_type.
	 *
	 * Example:
	 * ```php
	 * use Kir\Image\Image;
	 * $extension = Image::getDefaultImageExtension('image-without-extension');
	 * echo $extension; // the file extension, e.g. "png"
	 * ```
	 *
	 * @param string $filename The filename of the image.
	 * @return string|null The file extension of the image determined by using mime_content_type.
	 */
	public static function getDefaultImageExtension(string $filename): ?string {
		return ImageTypeTools::getDefaultImageExtensionFromFile($filename);
	}

	/**
	 * Loads an image using all available image functions.
	 *
	 * Example:
	 * ```php
	 * use Kir\Image\Image;
	 * $contents = file_get_contents('image.png');
	 * $image = Image::loadFromString($contents);
	 * ```
	 *
	 * @param string $data The image data.
	 * @return Image The new image.
	 */
	public static function loadFromString(string $data): Image {
		$resource = ImageTools::nonFalse(fn() => imagecreatefromstring($data));
		/** @var GdImage $resource */
		return new Image($resource);
	}

	/**
	 * Loads an image using all available image functions.
	 *
	 * Example:
	 * ```php
	 * use Kir\Image\Image;
	 * $image = Image::loadFromFile('image.png');
	 * ```
	 *
	 * @param string $filename The filename of the image.
	 * @return Image The new image.
	 */
	public static function loadFromFile(string $filename): Image {
		return ImageFactory::loadImageFromFile($filename);
	}
	
	/**
	 * Remove the alpha channel from the image. The alpha channel will be replaced with opaque white.
	 *
	 * @template T
	 * @param Image $srcIm The source image.
	 * @param Closure(Image): T $fn The function that will be called with the new image.
	 * @return void
	 */
	private static function temporaryRemoveAlphaChannel(Image $srcIm, Closure $fn) {
		$newBackground = Image::create($srcIm->getWidth(), $srcIm->getHeight(), Color::whiteOpaque(), $srcIm->getFileType());
		$srcIm->pasteOn($newBackground->getGdImage());
		$fn($newBackground);
	}
	
	/**
	 * Creates a new image resource with the given width and height and a background color.
	 * Optionally an [image type](https://www.php.net/manual/en/image.constants.php#constant.imagetype-gif)
	 * can be specified.
	 *
	 * Example:
	 * ```php
	 * use Kir\Image\Image;
	 * $im = Image::create(100, 100, Color::whiteTransparent(), IMAGETYPE_PNG);
	 * ```
	 *
	 * @param int $width The width of the new image.
	 * @param int $height The height of the new image.
	 * @param Color|null $color The background color of the new image.
	 * @param int|null $imageType The image type of the new image.
	 * @return Image
	 */
	public static function create(int $width, int $height, ?Color $color = null, ?int $imageType = null) {
		if($color === null) {
			$color = Color::whiteTransparent();
		}
		$resource = self::createResource($width, $height, $color);
		return new self($resource, $imageType);
	}

	/**
	 * @param GdImage|resource|null $resource The resource to create the image from.
	 */
	public function __construct($resource, ?int $type = null) {
		if(!($resource instanceof GdImage || is_resource($resource))) {
			throw new ImageRuntimeException('Invalid resource');
		}
		/** @var GdImage $resource */
		$this->resource = $resource;
		$this->lastFileType = $type;
	}

	public function __destruct() {
		imagedestroy($this->resource);
	}

	/**
	 * Returns the current image resource as a GdImage object.
	 *
	 * Example:
	 * ```php
	 * use Kir\Image\Image;
	 * $im = Image::create(100, 100, Color::whiteTransparent(), IMAGETYPE_PNG);
	 * $resource = $im->getGdImage();
	 * image_jpeg($resource, 'image.jpg');
	 * ```
	 *
	 * @return GdImage The gd image resource.
	 */
	public function getGdImage() {
		return $this->resource;
	}

	/**
	 * Returns the width of the image. This is the horizontal size in pixels.
	 *
	 * Example:
	 * ```php
	 * use Kir\Image\Image;
	 * $im = Image::loadFromFile('image.png');
	 * $width = $im->getWidth();
	 * ```
	 *
	 * @return int The image type constant.
	 */
	public function getWidth(): int {
		return imagesx($this->resource);
	}

	/**
	 * Returns the height of the image. This is the vertical size in pixels.
	 *
	 * Example:
	 * ```php
	 * use Kir\Image\Image;
	 * $im = Image::loadFromFile('image.png');
	 * $height = $im->getHeight();
	 * ```
	 *
	 * @return int The image type constant.
	 */
	public function getHeight(): int {
		return imagesy($this->resource);
	}
	
	/**
	 * Returns a php gd image format constant-int for a specific format that was used to create this image. If the image
	 * was created from scratch, and no format was specified, null will be returned.
	 * See possible [file types](https://www.php.net/manual/en/image.constants.php#constant.imagetype-gif).
	 *
	 * Example:
	 * ```php
	 * use Kir\Image\Image;
	 * $im = Image::loadFromFile('image.png');
	 * $fileType = $im->getFileType();
	 * ```
	 *
	 * @return int|null The image type constant.
	 */
	public function getFileType(): ?int {
		return $this->lastFileType;
	}
	
	/**
	 * The mime-type of an image.
	 *
	 * Example:
	 * ```php
	 * use Kir\Image\Image;
	 * $im = Image::loadFromFile('image.png');
	 * $mimeType = $im->getMimeType();
	 * echo $mimeType; // image/png
	 * ```
	 *
	 * @return string|null The mime type of the image.
	 */
	public function getMimeType(): ?string {
		$fileType = $this->getFileType();
		return $fileType !== null ? image_type_to_mime_type($fileType) : null;
	}

	/**
	 * Returns a copy of the current image.
	 *
	 * Example:
	 * ```php
	 * use Kir\Image\Image;
	 * $im = Image::loadFromFile('image.png');
	 * $copy = $im->getCopy();
	 * ```
	 *
	 * @return Image A copy of the current image.
	 */
	public function getCopy(): self {
		$fileType = $this->getFileType();
		$width = $this->getWidth();
		$height = $this->getHeight();
		$whiteTransparent = Color::whiteTransparent();
		$im = self::create($width, $height, $whiteTransparent, $fileType);
		imagecopy($im->getGdImage(), $this->resource, 0, 0, 0, 0, $width, $height);
		return $im;
	}
	
	/**
	 * @deprecated Use {@see Image::pasteOn()} instead.
	 * @param Image|GdImage|resource $targetImage
	 */
	public function placeImageOn($targetImage, int $offsetX = 0, int $offsetY = 0): self {
		return $this->pasteOn($targetImage, $offsetX, $offsetY);
	}
	
	/**
	 * Place an image onto the current image.
	 *
	 * Example:
	 * ```php
	 * use Kir\Image\Image;
	 * $im = Image::loadFromFile('image.png');
	 * $logo = Image::loadFromFile('logo.png');
	 * $im->placeImageOn($logo, 10, 10);
	 * ```
	 *
	 * @param Image|GdImage|resource $targetImage The target image.
	 * @param int $offsetX The horizontal offset, left to right.
	 * @param int $offsetY The vertical offset, top to bottom.
	 * @return self
	 */
	public function pasteOn($targetImage, int $offsetX = 0, int $offsetY = 0): self {
		if($targetImage instanceof Image) {
			$targetImage = $targetImage->getGdImage();
		}
		/** @var GdImage $targetImage */
		imagecopy($targetImage, $this->resource, $offsetX, $offsetY, 0, 0, $this->getWidth(), $this->getHeight());
		return $this;
	}

	/**
	 * Returns the red color at the given position. 0 means no red color, 255 means full red color.
	 *
	 * Example:
	 * ```php
	 * use Kir\Image\Image;
	 * $im = Image::loadFromFile('image.png');
	 * $red = $im->getRedColorAt(10, 10);
	 * echo $red; // A value between 0 and 255
	 * ```
	 *
	 * @param int $x The horizontal position, left to right.
	 * @param int $y The vertical position, top to bottom.
	 * @return int The red color code at the given position. 0 means no red color, 255 means full red color.
	 * @throws ImageRuntimeException
	 */
	public function getRedColorAt(int $x, int $y) {
		return $this->getChannelColorAt($x, $y, 16);
	}

	/**
	 * Returns the green color at the given position. 0 means no green color, 255 means full green color.
	 *
	 * Example:
	 * ```php
	 * use Kir\Image\Image;
	 * $im = Image::loadFromFile('image.png');
	 * $green = $im->getGreenColorAt(10, 10);
	 * echo $green; // A value between 0 and 255
	 * ```
	 *
	 * @param int $x The horizontal position, left to right.
	 * @param int $y The vertical position, top to bottom.
	 * @return int The green color at the given position. 0 means no green color, 255 means full green color.
	 * @throws ImageRuntimeException
	 */
	public function getGreenColorAt(int $x, int $y) {
		return $this->getChannelColorAt($x, $y, 8);
	}

	/**
	 * Returns the blue color at the given position. 0 means no blue color, 255 means full blue color.
	 *
	 * Example:
	 * ```php
	 * use Kir\Image\Image;
	 * $im = Image::loadFromFile('image.png');
	 * $blue = $im->getBlueColorAt(10, 10);
	 * echo $blue; // A value between 0 and 255
	 * ```
	 *
	 * @param int $x The horizontal position, left to right.
	 * @param int $y The vertical position, top to bottom.
	 * @return int The blue color code at the given position. 0 means no blue color, 255 means full blue color.
	 * @throws ImageRuntimeException
	 */
	public function getBlueColorAt(int $x, int $y) {
		return $this->getChannelColorAt($x, $y, 0);
	}

	/**
	 * Returns the alpha value at the given position. 0 means no alpha, 255 means full alpha.
	 *
	 * Example:
	 * ```php
	 * use Kir\Image\Image;
	 * $im = Image::loadFromFile('image.png');
	 * $alpha = $im->getAlphaAt(10, 10);
	 * echo $alpha; // A value between 0 and 255
	 * ```
	 *
	 * @param int $x The horizontal position, left to right.
	 * @param int $y The vertical position, top to bottom.
	 * @return int The alpha value at the given position. 0 means no alpha, 255 means full alpha.
	 * @throws ImageRuntimeException
	 */
	public function getAlphaAt(int $x, int $y) {
		$a = 127 - $this->getChannelColorAt($x, $y, 24);
		return $a === 127 ? 255 : ($a * 2);
	}

	/**
	 * Returns a channel value at the given position. The color value is between 0 and 255 according to the given bit mask.
	 *
	 * Example:
	 * ```php
	 * use Kir\Image\Image;
	 * $im = Image::loadFromFile('image.png');
	 * $red = $im->getChannelColorAt(10, 10, 0xFF0000);
	 * $green = $im->getChannelColorAt(10, 10, 0x00FF00);
	 * $blue = $im->getChannelColorAt(10, 10, 0x0000FF);
	 * printf("R = %d, G = %d, B = %d\n", $red, $green, $blue);
	 * ```
	 *
	 * @param int $x The horizontal position, left to right.
	 * @param int $y The vertical position, top to bottom.
	 * @return int The channel value at the given position. The color value is between 0 and 255 according to the given bit mask.
	 * @throws ImageRuntimeException
	 */
	public function getChannelColorAt(int $x, int $y, int $bitMask) {
		$color = ImageTools::nonFalse(fn() => imagecolorat($this->resource, $x, $y));
		return ($color >> $bitMask) & 0xFF;
	}

	/**
	 * Converts the current image to a grey scale image.
	 *
	 * Example:
	 * ```php
	 * use Kir\Image\Image;
	 * $im = Image::loadFromFile('image.png');
	 * $im->greyscale(); // The image is now a grey scale image.
	 * ```
	 *
	 * @return $this
	 */
	public function greyscale() {
		imagefilter($this->resource, IMG_FILTER_GRAYSCALE);
		return $this;
	}

	/**
	 * Applies an image mask to the current image. The mask image must be a greyscale image. The channel value of the
	 * mask image will be used as alpha value for the current image.
	 *
	 * Example:
	 * ```php
	 * use Kir\Image\Image;
	 * $im = Image::loadFromFile('image.png');
	 * $mask = Image::loadFromFile('mask.png');
	 * $im->applyAlphaMaskFromGreyscaleImage($mask);
	 * ```
	 *
	 * @param Image $mask The grey scale mask image.
	 * @return $this The new image.
	 */
	public function applyAlphaMaskFromGreyscaleImage(Image $mask): self {
		$maskIm = $mask->getCopy();
		$maskIm->greyscale();
		$maskRes = $maskIm->getGdImage();
		$srcRes = $this->resource;
		$w = $this->getWidth();
		$h = $this->getHeight();
		$dstRes = self::createResource($w, $h, Color::fromRGBA(0, 0, 0, 0));

		for($y = 0; $y < $h; $y++) {
			for($x = 0; $x < $w; $x++) {
				$alphaColor = 127 - ((imagecolorat($maskRes, $x, $y) & 0xFF) >> 1);
				$color = imagecolorat($srcRes, $x, $y);
				/** @var int $c */
				$c = imagecolorallocatealpha($dstRes, ($color >> 16) & 255, ($color >> 8) & 255, $color & 255, $alphaColor);
				imagesetpixel($dstRes, $x, $y, $c);
			}
		}

		imagedestroy($this->resource);
		$this->resource = $dstRes;

		return $this;
	}

	/**
	 * The colors of the image will be adjusted to the full range of 0 to 255.
	 *
	 * Example:
	 * ```php
	 * use Kir\Image\Image;
	 * $im = Image::loadFromFile('image.png');
	 * $im->adjustColors();
	 * ```
	 *
	 * @return $this The current image.
	 */
	public function adjustColors(): self {
		$res = $this->resource;
		$w = $this->getWidth();
		$h = $this->getHeight();
		$cMin = 255;
		$cMax = 0;

		for($y = 0; $y < $h; $y++) {
			for($x = 0; $x < $w; $x++) {
				$color = imagecolorat($res, $x, $y);
				$r = ($color >> 16) & 255;
				$g = ($color >> 8) & 255;
				$b = $color & 255;
				$cMin = min($cMin, $r, $g, $b);
				$cMax = max($cMax, $r, $g, $b);
			}
		}

		$f = 255 / ($cMax - $cMin);

		for($y = 0; $y < $h; $y++) {
			for($x = 0; $x < $w; $x++) {
				$color = imagecolorat($res, $x, $y);
				$a = ($color >> 24) & 127;
				$r = ($color >> 16) & 255;
				$g = ($color >> 8) & 255;
				$b = $color & 255;
				$c = imagecolorallocatealpha(
					$res,
					(int) (($r - $cMin) * $f),
					(int) (($g - $cMin) * $f),
					(int) (($b - $cMin) * $f),
					$a
				);
				/** @var int $c */
				imagesetpixel($res, $x, $y, $c);
			}
		}

		return $this;
	}

	/**
	 * Remove excess white space around the image.
	 *
	 * The threshold value is the value of which a non-white color will be still treated as white. A threshold value of
	 * 15 means that a color with a value of 240 will be treated as white. A threshold value of 255 means that only a
	 * color with a value of 255 will be treated as white.
	 *
	 * The border width is in percent. 3 means 3% on each side.
	 *
	 * Example:
	 * ```php
	 * use Kir\Image\Image;
	 * $im = Image::loadFromFile('image.png');
	 * $im->crop(15, 3);
	 * ```
	 *
	 * @param int $threshold The threshold value of which a non-white color will be still treated as white.
	 * @param int $borderPercent The border width in percent.
	 * @param Color|null $backgroundColor The background color of the new image.
	 * @return $this This instance with a new image resource.
	 */
	public function crop(int $threshold = 15, int $borderPercent = 0, ?Color $backgroundColor = null) {
		if($backgroundColor === null) {
			$backgroundColor = Color::whiteOpaque();
		}

		$measures = $this->detectInnerObject($threshold);

		$w = $this->getWidth();
		$h = $this->getHeight();

		$srcOffsetX = $measures['left'];
		$srcOffsetY = $measures['top'];
		$srcRight = $measures['right'];
		$srcBottom = $measures['bottom'];

		$dstOffsetX = 0;
		$dstOffsetY = 0;
		$dstWidth = $srcWidth = $measures['width'];
		$dstHeight = $srcHeight = $measures['height'];

		if(round($borderPercent, 5) > 0.000001) {
			// Now enlarge the projected region by the given border width
			$maxEdge = max($srcWidth, $srcHeight);
			$additionalBorderPX = (int) round($maxEdge * $borderPercent / 100);

			$srcOffsetX -= $additionalBorderPX;
			$srcOffsetY -= $additionalBorderPX;
			$srcRight -= $additionalBorderPX;
			$srcBottom -= $additionalBorderPX;

			$dstRight = $srcRight;
			$dstBottom = $srcBottom;

			$dstWidth = $w - $srcOffsetX - $srcRight;
			$dstHeight = $h - $srcOffsetY - $srcBottom;

			if($srcOffsetX < 0) {
				$dstOffsetX = -$srcOffsetX;
				$srcOffsetX = 0;
			}

			if($srcOffsetY < 0) {
				$dstOffsetY = -$srcOffsetY;
				$srcOffsetY = 0;
			}

			if($srcRight < 0) {
				$dstOffsetX = -$srcRight;
			}

			if($srcBottom < 0) {
				$dstOffsetY = -$srcBottom;
			}

			$srcWidth = $w - $dstOffsetX - $dstRight;
			$srcHeight = $h - $dstOffsetY - $dstBottom;
		}

		$newRes = self::createResource($dstWidth, $dstHeight);
		imagefill($newRes, 0, 0, self::createGdColorFromColor($newRes, $backgroundColor));
		imagecopy($newRes, $this->resource, $dstOffsetX, $dstOffsetY, $srcOffsetX, $srcOffsetY, $srcWidth, $srcHeight);

		imagedestroy($this->resource);
		$this->resource = $newRes;

		return $this;
	}

	/**
	 * Detects the inner object of the image. The inner object is the object without any white space around it.
	 *
	 * The threshold value is the value of which a non-white color will be still treated as white. A threshold value of
	 * 15 means that a color with a value of 240 will be treated as white. A threshold value of 255 means that only a
	 * color with a value of 255 will be treated as white.
	 *
	 * Example:
	 * ```php
	 * use Kir\Image\Image;
	 * $im = Image::loadFromFile('image.png');
	 * $measures = $im->detectInnerObject(15);
	 * ```
	 *
	 * @param int $threshold The threshold value of which a non-white color will be still treated as white.
	 * @return array{left: int, top: int, right: int, bottom: int, width: int, height: int} The measures of the inner object.
	 */
	public function detectInnerObject(int $threshold = 15): array {
		$copy = $this->getCopy();
		$copyRes = $copy->getGdImage();
		$w = $copy->getWidth();
		$h = $copy->getHeight();

		imagefilter($copyRes, IMG_FILTER_GRAYSCALE);
		imagefilter($copyRes, IMG_FILTER_BRIGHTNESS, $threshold);
		imagetruecolortopalette($copyRes, false, 255);

		$wf = static function ($copyRes, $a, $b, int $ca, int $cb) {
			$w = (int) ImageTools::nonFalse(static fn() => ceil($ca * $a ?: 1));
			$h = (int) ImageTools::nonFalse(static fn() => ceil($ca * $b ?: 1));
			/** @var GdImage $tmp */
			$tmp = ImageTools::nonFalse(static fn() => imagecreatetruecolor($w, $h));
			try {
				for($s = 0; $s < $cb; $s++) {
					imagecopy($tmp, $copyRes, 0, 0, $s * $b, $s * $a, $w, $h);
					imagetruecolortopalette($tmp, false, 255);
					if(imagecolorstotal($tmp) > 1) {
						return $s;
					}
				}
				return 0;
			} finally {
				imagedestroy($tmp);
			}
		};

		$offsetX = $wf($copyRes, 0, 1, $h, $w);
		$offsetY = $wf($copyRes, 1, 0, $w, $h);
		imageflip($copyRes, IMG_FLIP_BOTH);
		$bottom = $wf($copyRes, 1, 0, $w, $h);
		$right = $wf($copyRes, 0, 1, $h, $w);

		$width = $w - $offsetX - $right;
		$height = $h - $offsetY - $bottom;

		return [
			'left' => $offsetX,
			'top' => $offsetY,
			'right' => $right,
			'bottom' => $bottom,
			'width' => $width,
			'height' => $height,
		];
	}

	/**
	 * Resizes the current image to the given width and height. Will not keep the current proportion.
	 * Is the image is larger than the given width and height, the image will be cropped. If the image is smaller than
	 * the given width and height, there will be colored space added around the image with the given color.
	 *
	 * Example:
	 * ```php
	 * use Kir\Image\Image;
	 * $im = Image::loadFromFile('image.png');
	 * $im->resizeCanvas(500, 500); // Image will be forcefully cropped or enlarged to 500x500 pixels.
	 * ```
	 *
	 * @param int $width The width of the new image.
	 * @param int $height The height of the new image.
	 * @param int|null $offsetX The horizontal offset, left to right.
	 * @param int|null $offsetY The vertical offset, top to bottom.
	 * @param Color|null $backgroundColor The background color of the new image.
	 * @return $this This instance with a new image resource.
	 */
	public function resizeCanvas(int $width, int $height, ?int $offsetX = null, ?int $offsetY = null, ?Color $backgroundColor = null) {
		$intOffsetX = $offsetX ?? (int) round(floor($width / 2) - floor($this->getWidth() / 2));
		$intOffsetY = $offsetY ?? (int) round(floor($height / 2) - floor($this->getHeight() / 2));
		$backgroundColor = $backgroundColor ?? Color::whiteOpaque();

		$newResource = self::createResource($width, $height);
		imagefill($newResource, 0, 0, self::createGdColorFromColor($this->resource, $backgroundColor));
		imagecopyresampled($newResource, $this->resource, $intOffsetX, $intOffsetY, 0, 0, $this->getWidth(), $this->getHeight(), $this->getWidth(), $this->getHeight());

		imagedestroy($this->resource);
		$this->resource = $newResource;

		return $this;
	}

	/**
	 * Resizes the current image to the given width and height. Will not keep the current proportion.
	 * Is the image is larger than the given width and height, the image will be cropped. If the image is smaller than
	 * the given width and height, there will be colored space added around the image with the given color.
	 *
	 * Example:
	 * ```php
	 * use Kir\Image\Image;
	 * $im = Image::loadFromFile('image.png');
	 * $im->resizeCanvasCentered(500, 500); // Image will be forcefully cropped or enlarged to 500x500 pixels.
	 * ```
	 *
	 * @param int $width The width of the new image.
	 * @param int $height The height of the new image.
	 * @param Color|null $backgroundColor The background color of the new image.
	 * @return $this This instance with a new image resource.
	 */
	public function resizeCanvasCentered(int $width, int $height, ?Color $backgroundColor = null) {
		$absoluteOffsetX = (int) round($width / 2 - $this->getWidth() / 2);
		$absoluteOffsetY = (int) round($height / 2 - $this->getHeight() / 2);
		$this->resizeCanvas($width, $height, $absoluteOffsetX, $absoluteOffsetY, $backgroundColor);
		return $this;
	}

	/**
	 * Resizes the current image to the given width and height. Will **not** keep the current proportion.
	 * When omitting the width or height, the current width or height will be used.
	 *
	 * Example:
	 * ```php
	 * use Kir\Image\Image;
	 * $im = Image::loadFromFile('image.png');
	 * $im->resize(100, 100); // Image will be forcefully resampled into 100x100 pixels.
	 * ```
	 *
	 * @param int|null $width If null, this value is based on the width of the current canvas.
	 * @param int|null $height If null, this value is based on the height of the current canvas.
	 * @return $this This instance with a new image resource.
	 */
	public function resize(?int $width = null, ?int $height = null) {
		if($width === null && $height === null) {
			return $this;
		}

		$width = $width ?? $this->getWidth();
		$height = $height ?? $this->getHeight();

		$sourceIm = $this->resource;

		$resource = self::createResource($width, $height);
		imagecopyresampled($resource, $sourceIm, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
		imagedestroy($this->resource);
		$this->resource = $resource;

		return $this;
	}

	/**
	 * Resize image only if it is larger than the targeted size. The image will be shrinked into a rectangle within the
	 * given width and height. If the image is already smaller than the given width and height, nothing will happen.
	 *
	 * ```php
	 * use Kir\Image\Image;
	 * $image = Image::loadFromFile('image.png');
	 *
	 * $newImage = $image->getCopy();
	 * $newImage->resizeProportional(500, 500);
	 * $newImage->resizeCanvasCentered(500, 500);
	 * $newImage->saveAsWebP('500x500.webp');
	 * ```
	 *
	 * @param int|null $width If null, this value is based on the current proportion of the current canvas.
	 * @param int|null $height If null, this value is based on the current proportion of the current canvas.
	 * @return $this The new image.
	 */
	public function shrinkProportional(?int $width = null, ?int $height = null) {
		$origWidth = $this->getWidth();
		$origHeight = $this->getHeight();

		if($origWidth < $width && $origHeight < $height) {
			return $this;
		}

		[$targetWidth, $targetHeight] = ImageCalculator::getProportionalSize(
			$origWidth,
			$origHeight,
			$width,
			$height
		);

		return $this->resize($targetWidth, $targetHeight);
	}

	/**
	 * Resize an image proportionally. The image will be shrinked or enlarged to a rectangle within the given width and
	 * height. Is only the width or height given, the other value will be calculated automatically. If both width and
	 * height are given, the image will be fitted into the targeted rectangle.
	 *
	 * Example:
	 * ```php
	 * use Kir\Image\Image;
	 * $image = Image::loadFromFile('image.png');
	 * $image->resizeProportional(500, 500); // Image will be resampled to 500x500 pixels while keeping the proportion.
	 * ```
	 *
	 * @param int|null $width If null, this value is based on the current proportion of the current canvas.
	 * @param int|null $height If null, this value is based on the current proportion of the current canvas.
	 * @return $this The new image.
	 */
	public function resizeProportional(?int $width = null, ?int $height = null) {
		$sourceW = $this->getWidth();
		$sourceH = $this->getHeight();

		[$targetWidth, $targetHeight] = ImageCalculator::getProportionalSize($sourceW, $sourceH, $width, $height);

		$this->resize($targetWidth, $targetHeight);

		return $this;
	}

	/**
	 * Fills the current image with the given color.
	 *
	 * Example:
	 * ```php
	 * use Kir\Image\Image;
	 * $im = Image::loadFromFile('image.png');
	 * $im->fill(0, 0, Color::whiteOpaque());
	 * ```
	 *
	 * @param int $x The horizontal position, left to right.
	 * @param int $y The vertical position, top to bottom.
	 * @param Color $color The fill color.
	 * @return $this The current image.
	 */
	public function fill(int $x, int $y, Color $color): self {
		$colorCode = self::createGdColorFromColor($this->resource, $color);
		imagefill($this->resource, $x, $y, $colorCode);
		return $this;
	}

	/**
	 * Draws a rectangle on the current image.
	 *
	 * Example:
	 * ```php
	 * use Kir\Image\Image;
	 * $im = Image::loadFromFile('image.png');
	 * $im->rectangle(10, 10, 100, 100, Color::whiteOpaque());
	 * // Draws a white rectangle on the image. The top-left corner is at 10,10 and the bottom-right corner is at 110,110.
	 * ```
	 *
	 * @param int $x The horizontal position, left to right.
	 * @param int $y The vertical position, top to bottom.
	 * @param int $width The width of the rectangle.
	 * @param int $height The height of the rectangle.
	 * @param Color $color The fill color.
	 * @return $this The current image.
	 */
	public function rectangle(int $x, int $y, int $width, int $height, Color $color) {
		$gdColor = self::createGdColorFromColor($this->resource, $color);
		imagefilledrectangle($this->resource, $x, $y, $x + $width - 1, $y + $height - 1, $gdColor);
		return $this;
	}
	
	/**
	 * Set the alpha channel of an image to
	 *
	 * @return $this
	 */
	public function removeAlphaBackground(?Color $color = null): self {
		$color = $color ?? Color::whiteOpaque();
		$newBackground = Image::create($this->getWidth(), $this->getHeight(), $color);
		$this->pasteOn($newBackground->getGdImage());
		$this->resource = $newBackground->getGdImage();
		return $this;
	}

	/**
	 * Saves the current image to a file. The file type will be determined by the file extension of the given filename
	 * or if $explicitType is set, by that given type.
	 *
	 * Example:
	 * ```php
	 * use Kir\Image\Image;
	 * $im = Image::loadFromFile('image.png');
	 * $im->saveAs('image.jpg'); // Saves the image as a jpeg image.
	 * ```
	 *
	 * @param string|null $filename The filename of the image.
	 * @return $this The current image.
	 * @throws ImageRuntimeException
	 */
	public function saveAs(?string $filename, ?int $explicitType = null): self {
		$type = $explicitType ?? ImageTypeTools::getImageTypeFromFileExtension($filename) ?? $this->lastFileType;
		switch ($type) {
			case IMAGETYPE_GIF : return $this->saveAsGif($filename);
			case IMAGETYPE_PNG : return $this->saveAsPng($filename);
			case IMAGETYPE_BMP : return $this->saveAsBmp($filename);
			case IMAGETYPE_WEBP: return $this->saveAsWebP($filename);
			default:             return $this->saveAsJpeg($filename);
		}
	}

	/**
	 * Saves the current image as a png image.
	 *
	 * Example:
	 * ```php
	 * use Kir\Image\Image;
	 * $im = Image::loadFromFile('image.png');
	 * $im->saveAsPng('image.png'); // Saves the image as a png image.
	 * ```
	 *
	 * @param string|null $filename The filename of the image.
	 * @return $this The current image.
	 * @throws ImageRuntimeException
	 */
	public function saveAsPng(?string $filename = null): self {
		imagepng($this->getGdImage(), $filename);
		return $this;
	}

	/**
	 * Saves the current image as a jpeg image.
	 *
	 * Example:
	 * ```php
	 * use Kir\Image\Image;
	 * $im = Image::loadFromFile('image.png');
	 * $im->saveAsJpeg('image.jpg'); // Saves the image as a jpeg image.
	 * ```
	 *
	 * @param string|null $filename The filename of the image.
	 * @param int $quality The quality of the image. 0 means worst quality, 100 means best quality.
	 * @return $this The current image.
	 * @throws ImageRuntimeException
	 */
	public function saveAsJpeg(?string $filename = null, int $quality = 100): self {
		self::temporaryRemoveAlphaChannel($this, static fn(Image $im) => imagejpeg($im->getGdImage(), $filename, $quality));
		return $this;
	}

	/**
	 * Saves the current image as a gif image.
	 *
	 * Example:
	 * ```php
	 * use Kir\Image\Image;
	 * $im = Image::loadFromFile('image.png');
	 * $im->saveAsGif('image.gif'); // Saves the image as a gif image.
	 * ```
	 *
	 * @param string|null $filename The filename of the image.
	 * @return $this The current image.
	 * @throws ImageRuntimeException
	 */
	public function saveAsGif(?string $filename = null): self {
		self::temporaryRemoveAlphaChannel($this, static fn(Image $im) => imagegif($im->getGdImage(), $filename));
		return $this;
	}

	/**
	 * Saves the current image as a webp image.
	 *
	 * Example:
	 * ```php
	 * use Kir\Image\Image;
	 * $im = Image::loadFromFile('image.png');
	 * $im->saveAsWebP('image.webp'); // Saves the image as a webp image.
	 * ```
	 *
	 * @param string|null $filename The filename of the image.
	 * @param int $quality The quality of the image. 0 means worst quality, 100 means best quality.
	 * @return $this The current image.
	 * @throws ImageRuntimeException
	 */
	public function saveAsWebP(?string $filename = null, int $quality = 100): self {
		imagewebp($this->getGdImage(), $filename, $quality);
		return $this;
	}

	/**
	 * Saves the current image as a bmp image.
	 *
	 * Example:
	 * ```php
	 * use Kir\Image\Image;
	 * $im = Image::loadFromFile('image.png');
	 * $im->saveAsBmp('image.bmp'); // Saves the image as a bmp image.
	 * ```
	 *
	 * @param string|null $filename The filename of the image.
	 * @return $this The current image.
	 * @throws ImageRuntimeException
	 */
	public function saveAsBmp(?string $filename = null): self {
		self::temporaryRemoveAlphaChannel($this, static fn(Image $im) => imagebmp($im->getGdImage(), $filename));
		return $this;
	}
	
	/**
	 * Saves the current image as a string.
	 *
	 * @param int|null $imageType
	 * @return string
	 * @throws ImageRuntimeException
	 */
	public function saveAsString(?int $imageType = null, int $quality = 100): string {
		$type = $imageType ?? $this->lastFileType;
		ob_start();
		try {
			switch ($type) {
				case IMAGETYPE_GIF :
					$this->saveAsGif('php://output');
					break;
				case IMAGETYPE_PNG :
					$this->saveAsPng('php://output');
					break;
				case IMAGETYPE_BMP :
					$this->saveAsBmp('php://output');
					break;
				case IMAGETYPE_WEBP:
					$this->saveAsWebP('php://output', $quality);
					break;
				default:
					$this->saveAsJpeg('php://output', $quality);
			}
			$contents = ob_get_contents();
			if($contents === false) {
				throw new ImageRuntimeException('Could not get image contents');
			}
			return $contents;
		} finally {
			ob_end_clean();
		}
	}

	/**
	 * Creates a new image resource with the given width and height and fills it with the given color.
	 * Alpha blending will be enabled.
	 *
	 * @param int $width The width of the new image.
	 * @param int $height The height of the new image.
	 * @return GdImage The new image resource.
	 */
	private static function createResource(int $width, int $height, ?Color $color = null) {
		if($color === null) {
			$color = Color::whiteTransparent();
		}
		/** @var GdImage $resource */
		$resource = ImageTools::nonFalse(static fn() => imagecreatetruecolor($width, $height));
		imagealphablending($resource, false);
		imagefill($resource, 0, 0, self::createGdColorFromColor($resource, $color));
		imagealphablending($resource, true);
		imagesavealpha($resource, true);
		return $resource;
	}

	/**
	 * Create a new color from the given color.
	 *
	 * @param GdImage $resource The resource to create the color from.
	 * @param Color $color The color to create.
	 * @return int The color code.
	 */
	private static function createGdColorFromColor($resource, Color $color): int {
		$red = $color->getRed();
		$green = $color->getGreen();
		$blue = $color->getBlue();
		$alpha = $color->getAlpha();
		$a = 127 - (($alpha & 255) >> 1);
		return (int) ImageTools::nonFalse(static fn() => imagecolorallocatealpha($resource, $red, $green, $blue, $a));
	}
}
