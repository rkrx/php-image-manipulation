# php-image-manipulation
A simple image manipulation library using gd-lib

## Installation

```sh
composer require rkr/image
```

## Usage

### Resample proportionally

```php
use Kir\Image\Image;
$image = Image::loadFromFile('image.png');

$newImage = $image->getCopy();
$newImage->resizeProportional(500);
$newImage->saveAsWebP('new-image-width-500.webp');

$newImage = $image->getCopy();
$newImage->resizeProportional(null, 500);
$newImage->saveAsWebP('new-image-height-500.webp');

$newImage = $image->getCopy();
$newImage->resizeProportional(500, 500);
$newImage->saveAsWebP('new-image-largest-side-to-500.webp');
```

### Enlarge canvas

```php
use Kir\Image\Image;
$image = Image::loadFromFile('image.png');
$image->resizeProportional(500, 500);
$image->resizeCanvasCentered(500, 500);
$image->saveAsWebP('500x500.webp');
```

### Auto crop image with optional border

The crop function is solved via a separate algorithm. If a border width is specified as a percentage, but too much of the original graphic was cut away during the actual cropping, then this image portion is retained in the border area.

```php
use Kir\Image\Image;
use Kir\Image\Color;
$image = Image::loadFromFile('image.png');

$image->getCopy()
    ->crop(15 /* Threshold in 0..255 color scale */, 3 /* Border width in % */, Color::whiteOpaque())
    ->resizeProportional(500, 500)
    ->resizeCanvasCentered(500, 500)
    ->saveAsWebP('500x500.webp');
```

Additional documentation will follow...