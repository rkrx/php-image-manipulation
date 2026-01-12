# php-image-manipulation
A simple image manipulation library using gd-lib

## Installation

```sh
composer require rkr/image
```

## Usage (PHP 8.2)

More information is also available in the generated docs: `docs/classes/Kir/Image/Image.md`.

### Resample proportionally

```php
use Kir\Image\Image;
$image = Image::loadFromFile('image.png');

$newImage = $image->getCopy();
$newImage->resizeProportional(width: 500);
$newImage->saveAsWebP(filename: 'new-image-width-500.webp');

$newImage = $image->getCopy();
$newImage->resizeProportional(height: 500);
$newImage->saveAsWebP(filename: 'new-image-height-500.webp');

$newImage = $image->getCopy();
$newImage->resizeProportional(width: 500, height: 500);
$newImage->saveAsWebP(filename: 'new-image-largest-side-to-500.webp');
```

### Enlarge canvas

```php
use Kir\Image\Image;
$image = Image::loadFromFile('image.png');
$image->resizeProportional(width: 500, height: 500);
$image->resizeCanvasCentered(width: 500, height: 500);
$image->saveAsWebP(filename: '500x500.webp');
```

### Auto crop image with optional border

The crop function is solved via a separate algorithm. If a border width is specified as a percentage, but too much of the original graphic was cut away during the actual cropping, then this image portion is retained in the border area.

```php
use Kir\Image\Image;
use Kir\Image\Color;
$image = Image::loadFromFile('image.png');

$image->getCopy()
    ->crop(
        threshold: 15, // 0..255 color scale
        borderPercent: 3, // %
        backgroundColor: Color::whiteOpaque(),
    )
    ->resizeProportional(width: 500, height: 500)
    ->resizeCanvasCentered(width: 500, height: 500)
    ->saveAsWebP(filename: '500x500.webp');
```

### Text rendering (TrueType)

Requires GD with FreeType support and a `.ttf`/`.otf` font file.

```php
use Kir\Image\Image;
use Kir\Image\Color;

$image = Image::create(600, 240, Color::whiteOpaque());
$image->text(
    text: 'Hello World',
    x: 300,
    y: 120,
    fontFile: __DIR__ . '/Roboto-Regular.ttf',
    fontSize: 42,
    color: Color::fromRGB(0, 0, 0),
    angle: 0,
    anchor: 'center',
);
$image->saveAsPng(filename: 'text.png');
```

## Image API reference (all methods)

All methods throw `Kir\Image\ImageRuntimeException` on invalid input / unsupported operations.

### Static methods

- `public static function getImageType(string $filename): int`  
  Detects the image type constant (e.g. `IMAGETYPE_PNG`) for a file.
- `public static function getDefaultImageExtension(string $filename): ?string`  
  Returns a default extension (e.g. `"png"`) detected via `mime_content_type`.
- `public static function loadFromString(string $data): Image`  
  Loads an image from raw bytes.
- `public static function loadFromFile(string $filename): Image`  
  Loads an image from a file path.
- `public static function create(int $width, int $height, ?Color $color = null, ?int $imageType = null): Image`  
  Creates a new blank image (defaults to a transparent white background).

### Object lifecycle

- `public function __construct(\GdImage $resource, ?int $type = null)`  
  Wraps an existing GD image resource/object (prefer `loadFromFile()` / `create()` if possible).
- `public function __destruct(): void`  
  Frees the underlying GD resource on PHP < 8.0.

### Basic information

- `public function getGdImage(): \GdImage`  
  Returns the underlying GD image.
- `public function getWidth(): int`  
  Returns the image width in pixels.
- `public function getHeight(): int`  
  Returns the image height in pixels.
- `public function getFileType(): ?int`  
  Returns the last known `IMAGETYPE_*` (if available).
- `public function getMimeType(): ?string`  
  Returns a MIME type like `"image/png"` if the file type is known.
- `public function getCopy(): self`  
  Creates a full copy of the image.

### Compositing / paste

- `public function placeImageOn(Image|\GdImage $targetImage, int $offsetX = 0, int $offsetY = 0): self`  
  Deprecated alias for `pasteOn()`.
- `public function pasteOn(Image|\GdImage $targetImage, int $offsetX = 0, int $offsetY = 0): self`  
  Copies this image onto the given target (also accepts a GD resource on PHP < 8.0).

### Pixel inspection

- `public function getRedColorAt(int $x, int $y): int`
- `public function getGreenColorAt(int $x, int $y): int`
- `public function getBlueColorAt(int $x, int $y): int`
- `public function getAlphaAt(int $x, int $y): int`  
  Returns alpha in 0..255 (0 transparent, 255 opaque).
- `public function getChannelColorAt(int $x, int $y, int $bitMask): int`  
  Low-level helper used by the channel getters.

### Filters / color operations

- `public function greyscale(): self`  
  Applies `IMG_FILTER_GRAYSCALE`.
- `public function applyAlphaMaskFromGreyscaleImage(Image $mask): self`  
  Uses a greyscale mask’s channel value as alpha for the current image.
- `public function adjustColors(): self`  
  Stretches colors to fill the full 0..255 range.

### Auto-crop / content detection

- `public function crop(int $threshold = 15, int $borderPercent = 0, ?Color $backgroundColor = null): self`  
  Removes excess whitespace around the detected inner object and optionally adds a border.
- `public function detectInnerObject(int $threshold = 15): array`  
  Returns measures: `left`, `top`, `right`, `bottom`, `width`, `height`.

### Canvas resizing (crop/pad)

- `public function resizeCanvas(int $width, int $height, ?int $offsetX = null, ?int $offsetY = null, ?Color $backgroundColor = null): self`  
  Changes canvas size; crops or pads with background color (offset defaults to centered).
- `public function resizeCanvasCentered(int $width, int $height, ?Color $backgroundColor = null): self`  
  Centered convenience wrapper for `resizeCanvas()`.

### Resampling / proportional resizing

- `public function resize(?int $width = null, ?int $height = null): self`  
  Resamples to exact size (non-proportional); omitted dimensions keep current size.
- `public function shrinkProportional(?int $width = null, ?int $height = null): self`  
  Only shrinks if the image is larger than the target rectangle.
- `public function resizeProportional(?int $width = null, ?int $height = null): self`  
  Resizes proportionally to fit inside the target rectangle.
- `public function enlargeProportional(?int $width = null, ?int $height = null): self`  
  Resizes proportionally aiming to enlarge to the target rectangle.

### Drawing

- `public function fill(int $x, int $y, Color $color): self`  
  Flood-fill starting at the given pixel.
- `public function rectangle(int $x, int $y, int $width, int $height, Color $color): self`  
  Draws a filled rectangle.

### Text rendering (TrueType)

Requires GD with FreeType support.

- `public function measureText(string $text, string $fontFile, float $fontSize, float $angle = 0.0): array`  
  Returns `width`, `height`, `offsetX`, `offsetY` and the raw `bbox` array (from `imagettfbbox()`).
- `public function text(string $text, int $x, int $y, string $fontFile, float $fontSize, Color $color, float $angle = 0.0, string $anchor = 'top-left'): self`  
  Draws TTF text; anchors include `top-left`, `center`, `bottom-right`, `baseline-left`, etc.

### Alpha/background

- `public function removeAlphaBackground(?Color $color = null): self`  
  Replaces transparency with an opaque background color (default: white).

### Saving / output

- `public function saveAs(?string $filename, ?int $explicitType = null): self`  
  Saves based on extension (or forced type) and defaults to the last known file type.
- `public function saveAsPng(?string $filename = null): self`
- `public function saveAsJpeg(?string $filename = null, int $quality = 100): self`
- `public function saveAsGif(?string $filename = null): self`
- `public function saveAsWebP(?string $filename = null, int $quality = 100): self`
- `public function saveAsBmp(?string $filename = null): self`
- `public function saveAsString(?int $imageType = null, int $quality = 100): string`  
  Encodes the image into bytes (useful for HTTP responses or storage).

### Internal helpers (private, not part of the public API)

- `private static function temporaryRemoveAlphaChannel(Image $srcIm, \Closure $fn): void`  
  Used by JPEG/GIF/BMP saving to replace alpha with an opaque background.
- `private static function createResource(int $width, int $height, ?Color $color = null): \GdImage`  
  Creates a new `\GdImage` and initializes alpha handling.
- `private static function createGdColorFromColor(\GdImage $resource, Color $color): int`  
  Converts `Color` into a GD color index for the given image resource.
