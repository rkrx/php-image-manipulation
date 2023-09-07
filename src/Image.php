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
	 * @param string $filename
	 * @return int
	 */
	public static function getImageType(string $filename): int {
		return ImageFactory::getImageType($filename);
	}

	/**
	 * @param string $filename
	 * @return string|null
	 */
	public static function getDefaultImageExtension(string $filename): ?string {
		return ImageTypeTools::getDefaultImageExtensionFromFile($filename);
	}

	/**
	 * Loads an image using all available image functions
	 *
	 * @param string $data
	 * @return Image
	 */
	public static function loadFromString(string $data): Image {
		$resource = ImageTools::nonFalse(fn() => imagecreatefromstring($data));
		/** @var GdImage $resource */
		return new Image($resource);
	}

	/**
	 * Loads an image using all available image functions
	 *
	 * @param string $filename
	 * @return Image
	 */
	public static function loadFromFile(string $filename): Image {
		return ImageFactory::loadImageFromFile($filename);
	}
	
	/**
	 * @template T
	 * @param Image $srcIm
	 * @param Closure(Image): T $fn
	 * @return void
	 */
	private static function removeAlphaChannel(Image $srcIm, Closure $fn) {
		$newBackground = Image::create($srcIm->getWidth(), $srcIm->getHeight(), Color::whiteOpaque(), $srcIm->getFileType());
		$srcIm->placeImageOn($newBackground->getGdImage());
		$fn($newBackground);
	}
	
	/**
	 * @param int $width
	 * @param int $height
	 * @param Color|null $color
	 * @param int|null $imageType
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
	 * @param GdImage|resource|null $resource
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
	 * @return GdImage
	 */
	public function getGdImage() {
		return $this->resource;
	}

	/**
	 * @return int
	 */
	public function getWidth(): int {
		return imagesx($this->resource);
	}

	/**
	 * @return int
	 */
	public function getHeight(): int {
		return imagesy($this->resource);
	}
	
	/**
	 * Returns a php gd image format constant-int for a specific format that was used to create this image. If the image
	 * was created from scratch, and no format was specified, null will be returned.
	 *
	 * @return int|null
	 */
	public function getFileType(): ?int {
		return $this->lastFileType;
	}
	
	/**
	 * The mime type of an image
	 *
	 * @return string|null
	 */
	public function getMimeType(): ?string {
		$fileType = $this->getFileType();
		return $fileType !== null ? image_type_to_mime_type($fileType) : null;
	}

	/**
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
	 * @param GdImage|resource $targetImage
	 * @param int $offsetX
	 * @param int $offsetY
	 * @return self
	 */
	public function placeImageOn($targetImage, int $offsetX = 0, int $offsetY = 0): self {
		/** @var GdImage $targetImage */
		imagecopy($targetImage, $this->resource, $offsetX, $offsetY, 0, 0, $this->getWidth(), $this->getHeight());
		return $this;
	}

	/**
	 * @param int $x
	 * @param int $y
	 * @return int
	 * @throws ImageRuntimeException
	 */
	public function getRedColorAt(int $x, int $y) {
		return $this->getChannelColorAt($x, $y, 16);
	}

	/**
	 * @param int $x
	 * @param int $y
	 * @return int
	 * @throws ImageRuntimeException
	 */
	public function getGreenColorAt(int $x, int $y) {
		return $this->getChannelColorAt($x, $y, 8);
	}

	/**
	 * @param int $x
	 * @param int $y
	 * @return int
	 * @throws ImageRuntimeException
	 */
	public function getBlueColorAt(int $x, int $y) {
		return $this->getChannelColorAt($x, $y, 0);
	}

	/**
	 * @param int $x
	 * @param int $y
	 * @return int
	 * @throws ImageRuntimeException
	 */
	public function getAlphaAt(int $x, int $y) {
		$a = 127 - $this->getChannelColorAt($x, $y, 24);
		return $a === 127 ? 255 : ($a * 2);
	}

	/**
	 * @param int $x
	 * @param int $y
	 * @return int
	 * @throws ImageRuntimeException
	 */
	public function getChannelColorAt(int $x, int $y, int $bitMask) {
		$color = ImageTools::nonFalse(fn() => imagecolorat($this->resource, $x, $y));
		return ($color >> $bitMask) & 0xFF;
	}

	/**
	 * @return $this
	 */
	public function greyscale() {
		imagefilter($this->resource, IMG_FILTER_GRAYSCALE);
		return $this;
	}

	/**
	 * @param Image $mask
	 * @return $this
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
	 * @return $this
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
				$c = imagecolorallocatealpha($res, ($r - $cMin) * $f, ($g - $cMin) * $f, ($b - $cMin) * $f, $a);
				/** @var int $c */
				imagesetpixel($res, $x, $y, $c);
			}
		}

		return $this;
	}

	/**
	 * @param int $threshold
	 * @param int $borderPercent
	 * @param Color|null $backgroundColor
	 * @return $this
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
	 * @param int $threshold
	 * @return array{left: int, top: int, right: int, bottom: int, width: int, height: int}
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
	 * @param int $width
	 * @param int $height
	 * @param int|null $offsetX
	 * @param int|null $offsetY
	 * @param Color|null $backgroundColor
	 * @return $this
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
	 * @param int $width
	 * @param int $height
	 * @param Color|null $backgroundColor
	 * @return $this
	 */
	public function resizeCanvasCentered(int $width, int $height, ?Color $backgroundColor = null) {
		$absoluteOffsetX = (int) round($width / 2 - $this->getWidth() / 2);
		$absoluteOffsetY = (int) round($height / 2 - $this->getHeight() / 2);
		$this->resizeCanvas($width, $height, $absoluteOffsetX, $absoluteOffsetY, $backgroundColor);
		return $this;
	}

	/**
	 * @param int|null $width
	 * @param int|null $height
	 * @return $this
	 */
	public function resize(?int $width = null, ?int $height = null) {
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
	 * Resize image only if it is larger than the targeted measures
	 *
	 * ```
	 * use Kir\Image\Image;
	 * $image = Image::loadFromFile('image.png');
	 *
	 * $newImage = $image->getCopy();
	 * $newImage->resizeProportional(500, 500);
	 * $newImage->resizeCanvasCentered(500, 500);
	 * $newImage->saveAsWebP('500x500.webp');
	 * ```
	 *
	 * @param int|null $width
	 * @param int|null $height
	 * @return $this
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
	 * Resize an image proportionally.
	 *
	 * @param int|null $width If zero, this value is based on the current proportion of the current canvas.
	 * @param int|null $height If zero, this value is based on the current proportion of the current canvas.
	 * @return $this
	 */
	public function resizeProportional(?int $width = null, ?int $height = null) {
		$sourceW = $this->getWidth();
		$sourceH = $this->getHeight();

		[$targetWidth, $targetHeight] = ImageCalculator::getProportionalSize($sourceW, $sourceH, $width, $height);

		$this->resize($targetWidth, $targetHeight);

		return $this;
	}

	/**
	 * @param int $x
	 * @param int $y
	 * @param Color $color
	 * @return $this
	 */
	public function fill(int $x, int $y, Color $color): self {
		$colorCode = self::createGdColorFromColor($this->resource, $color);
		imagefill($this->resource, $x, $y, $colorCode);
		return $this;
	}

	/**
	 * @param int $x
	 * @param int $y
	 * @param int $width
	 * @param int $height
	 * @param Color $color
	 * @return $this
	 */
	public function rectangle(int $x, int $y, int $width, int $height, Color $color) {
		$gdColor = self::createGdColorFromColor($this->resource, $color);
		imagefilledrectangle($this->resource, $x, $y, $x + $width - 1, $y + $height - 1, $gdColor);
		return $this;
	}

	/**
	 * @param string|null $filename
	 * @return $this
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
	 * @param string|null $filename
	 * @return $this
	 * @throws ImageRuntimeException
	 */
	public function saveAsPng(?string $filename = null): self {
		imagepng($this->getGdImage(), $filename);
		return $this;
	}

	/**
	 * @param string|null $filename
	 * @param int $quality
	 * @return $this
	 * @throws ImageRuntimeException
	 */
	public function saveAsJpeg(?string $filename = null, int $quality = 100): self {
		self::removeAlphaChannel($this, fn(Image $im) => imagejpeg($im->getGdImage(), $filename, $quality));
		return $this;
	}

	/**
	 * @param string|null $filename
	 * @return $this
	 * @throws ImageRuntimeException
	 */
	public function saveAsGif(?string $filename = null): self {
		self::removeAlphaChannel($this, fn(Image $im) => imagegif($im->getGdImage(), $filename));
		return $this;
	}

	/**
	 * @param string|null $filename
	 * @param int $quality
	 * @return $this
	 * @throws ImageRuntimeException
	 */
	public function saveAsWebP(?string $filename = null, int $quality = 100): self {
		imagewebp($this->getGdImage(), $filename, $quality);
		return $this;
	}

	/**
	 * @param string|null $filename
	 * @return $this
	 * @throws ImageRuntimeException
	 */
	public function saveAsBmp(?string $filename = null): self {
		self::removeAlphaChannel($this, fn(Image $im) => imagebmp($im->getGdImage(), $filename));
		return $this;
	}

	/**
	 * @param int $width
	 * @param int $height
	 * @return GdImage
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
	 * @param GdImage $resource
	 * @param Color $color
	 * @return int
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
