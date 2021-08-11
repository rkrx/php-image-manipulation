<?php

namespace Kir\Image;

use GdImage;

class Image {
	/** @var GdImage */
	private $resource;
	
	/**
	 * @param string $filename
	 * @return int
	 */
	public static function getImageType(string $filename): int {
		$imageType = exif_imagetype($filename);
		if($imageType === false) {
			throw new ImageRuntimeException('Unknown image type');
		}
		return $imageType;
	}
	
	/**
	 * @param string $filename
	 * @return string
	 */
	public static function getDefaultImageExtension(string $filename): string {
		$typeId = self::getImageType($filename);
		$ext = image_type_to_extension($typeId);
		if($ext === false) {
			throw new ImageRuntimeException('Unknown image extension');
		}
		return $ext;
	}
	
	/**
	 * Loads an image using all available image functions
	 *
	 * @param string $filename
	 * @return Image
	 */
	public static function loadFromString(string $data): Image {
		$resource = self::nonFalse(fn() => imagecreatefromstring($data));
		return new Image($resource);
	}
	
	/**
	 * Loads an image using all available image functions
	 *
	 * @param string $filename
	 * @return Image
	 */
	public static function loadFromFile(string $filename): Image {
		$loadImage = static function ($filename) {
			$imageType = self::getImageType($filename);
			switch ($imageType) {
				case IMAGETYPE_GIF: return imagecreatefromgif($filename);
				case IMAGETYPE_JPEG: return imagecreatefromjpeg($filename);
				case IMAGETYPE_PNG: return imagecreatefrompng($filename);
				case IMAGETYPE_BMP: return imagecreatefrombmp($filename);
				case IMAGETYPE_WEBP: return imagecreatefromwebp($filename);
				case IMAGETYPE_XBM: return imagecreatefromxbm($filename);
				default: throw new ImageRuntimeException("Unsupported image format: {$imageType}");
			}
		};
		$resource = $loadImage($filename);
		if($resource === false) {
			throw new ImageRuntimeException('Could not load image');
		}
		$w = imagesx($resource);
		$h = imagesy($resource);
		$image = self::create($w, $h);
		imagecopy($image->getGdImage(), $resource, 0, 0, 0, 0, $w, $h);
		imagedestroy($resource);
		return $image;
	}
	
	/**
	 * @param int $width
	 * @param int $height
	 */
	public static function create(int $width, int $height, ?Color $color = null) {
		if($color === null) {
			$color = Color::fromRGB(255, 255, 255);
		}
		$resource = self::createResource($width, $height);
		$image = new self($resource);
		$image->fill(0, 0, $color);
		return $image;
	}
	
	/**
	 * @param GdImage
	 */
	public function __construct($resource) {
		if(!($resource instanceof GdImage || is_resource($resource))) {
			throw new ImageRuntimeException('Invalid resource');
		}
		$this->resource = $resource;
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
	
	public function getWidth(): int {
		return imagesx($this->resource);
	}
	
	public function getHeight(): int {
		return imagesy($this->resource);
	}
	
	/**
	 * @return Image A copy of the current image.
	 */
	public function getCopy(): self {
		$im = self::create($this->getWidth(), $this->getHeight(), Color::fromRGBA(255, 255, 255, 0));
		imagecopy($im->getGdImage(), $this->resource, 0, 0, 0, 0, $this->getWidth(), $this->getHeight());
		return $im;
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
		$color = self::nonFalse(fn() => imagecolorat($this->resource, $x, $y));
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
		$dstRes = self::createResource($w, $h);
		
		for($y = 0; $y < $h; $y++) {
			for($x = 0; $x < $w; $x++) {
				$alphaColor = 127 - ((imagecolorat($maskRes, $x, $y) & 0xFF) >> 1);
				$color = imagecolorat($srcRes, $x, $y);
				$c = imagecolorallocatealpha($maskRes, ($color >> 16) & 255, ($color >> 8) & 255, $color & 255, $alphaColor);
				imagesetpixel($dstRes, $x, $y, $c);
			}
		}
		
		imagedestroy($this->resource);
		$this->resource = $dstRes;
		
		return $this;
	}
	
	/**
	 * @param Image $mask
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
			$w = $ca * $a ?: 1;
			$h = $ca * $b ?: 1;
			$tmp = imagecreatetruecolor($w, $h);
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
	 * @param int|null $width
	 * @param int|null $height
	 * @param int|null $offsetX
	 * @param int|null $offsetY
	 * @param Color|null $backgroundColor
	 * @return $this
	 */
	public function resizeCanvas(?int $width = null, ?int $height = null, ?int $offsetX = null, ?int $offsetY = null, ?Color $backgroundColor = null) {
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
	 * @param int|null $width
	 * @param int|null $height
	 * @param Color|null $backgroundColor
	 * @return $this
	 */
	public function resizeCanvasCentered(?int $width = null, ?int $height = null, ?Color $backgroundColor = null) {
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
	 * Resize an image proportionally.
	 *
	 * @param int|null $width If zero, this value is based on the current proportion of the current canvas.
	 * @param int|null $height If zero, this value is based on the current proportion of the current canvas.
	 * @return $this
	 */
	public function resizeProportional(?int $width = null, ?int $height = null) {
		$sourceW = $this->getWidth();
		$sourceH = $this->getHeight();
		
		if($width !== null && $height === null) {
			$height = (int) round($width / $sourceW * $sourceH);
		} elseif($width === null && $height !== null) {
			$width = (int) round($height / $sourceH * $sourceW);
		}

		if($width > $height) {
			$targetWidth = $width;
			$targetHeight = $sourceH * $width / $sourceW;

			if($targetHeight > $height) {
				$targetWidth = $sourceW * $height / $sourceH;
				$targetHeight = $height;
			}
		} else {
			$targetWidth = $sourceW * $height / $sourceH;
			$targetHeight = $height;

			if($targetWidth > $width) {
				$targetWidth = $width;
				$targetHeight = $sourceH * $width / $sourceW;
			}
		}

		$targetWidth = (int) round($targetWidth);
		$targetHeight = (int) round($targetHeight);

		$this->resize($targetWidth, $targetHeight);

		return $this;
	}
	
	public function fill(int $x, int $y, Color $color) {
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
	 * @param string $filename
	 * @throws ImageRuntimeException
	 */
	public function saveAsPng(string $filename) {
		imagepng($this->getGdImage(), $filename);
	}
	
	/**
	 * @param string $filename
	 * @param int $quality
	 * @throws ImageRuntimeException
	 */
	public function saveAsJpeg(string $filename, int $quality = 100) {
		imagejpeg($this->getGdImage(), $filename, $quality);
	}
	
	/**
	 * @param string $filename
	 * @throws ImageRuntimeException
	 */
	public function saveAsGif(string $filename) {
		imagegif($this->getGdImage(), $filename);
	}
	
	/**
	 * @param string $filename
	 * @param int $quality
	 * @throws ImageRuntimeException
	 */
	public function saveAsWebP(string $filename, int $quality = 100) {
		imagewebp($this->getGdImage(), $filename, $quality);
	}
	
	/**
	 * @param string $filename
	 * @throws ImageRuntimeException
	 */
	public function saveAsBmp(string $filename) {
		imagebmp($this->getGdImage(), $filename);
	}
	
	/**
	 * @param int $width
	 * @param int $height
	 * @return GdImage
	 */
	private static function createResource(int $width, int $height) {
		$resource = imagecreatetruecolor($width, $height);
		imagesavealpha($resource, true);
		return $resource;
	}
	
	/**
	 * @template T
	 * @param callable(): T $fn
	 * @return T
	 */
	private static function nonFalse($fn) {
		$result = $fn();
		if($result === false) {
			throw new ImageRuntimeException();
		}
		return $result;
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
		$alpha = 127 - (($color->getAlpha() & 255) >> 1);
		return self::nonFalse(fn() => imagecolorallocatealpha($resource, $red, $green, $blue, $alpha));
	}
}