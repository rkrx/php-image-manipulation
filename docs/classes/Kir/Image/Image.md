***

# Image





* Full name: `\Kir\Image\Image`




## Methods


### getImageType

Detects the image type of the given image file.

```php
public static getImageType(string $filename): int
```

Example:
```php
use Kir\Image\Image;
$imageType = Image::getImageType('image.png');
echo $imageType; // the image type constant, e.g. IMAGETYPE_PNG
```

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$filename` | **string** | The filename of the image. |




***

### getDefaultImageExtension

Returns the default file extension for a given image type determined using mime_content_type.

```php
public static getDefaultImageExtension(string $filename): string|null
```

Example:
```php
use Kir\Image\Image;
$extension = Image::getDefaultImageExtension('image-without-extension');
echo $extension; // the file extension, e.g. "png"
```

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$filename` | **string** | The filename of the image. |


**Return Value:**

The file extension of the image determined by using mime_content_type.



***

### loadFromString

Loads an image using all available image functions.

```php
public static loadFromString(string $data): \Kir\Image\Image
```

Example:
```php
use Kir\Image\Image;
$contents = file_get_contents('image.png');
$image = Image::loadFromString($contents);
```

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | **string** | The image data. |


**Return Value:**

The new image.



***

### loadFromFile

Loads an image using all available image functions.

```php
public static loadFromFile(string $filename): \Kir\Image\Image
```

Example:
```php
use Kir\Image\Image;
$image = Image::loadFromFile('image.png');
```

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$filename` | **string** | The filename of the image. |


**Return Value:**

The new image.



***

### create

Creates a new image resource with the given width and height and a background color.

```php
public static create(int $width, int $height, \Kir\Image\Color|null $color = null, int|null $imageType = null): \Kir\Image\Image
```

Optionally an [image type](https://www.php.net/manual/en/image.constants.php#constant.imagetype-gif)
can be specified.

Example:
```php
use Kir\Image\Image;
$im = Image::create(100, 100, Color::whiteTransparent(), IMAGETYPE_PNG);
```

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$width` | **int** | The width of the new image. |
| `$height` | **int** | The height of the new image. |
| `$color` | **\Kir\Image\Color&#124;null** | The background color of the new image. |
| `$imageType` | **int&#124;null** | The image type of the new image. |




***

### __construct



```php
public __construct(\GdImage|resource|null $resource, ?int $type = null): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$resource` | **\GdImage&#124;resource&#124;null** | The resource to create the image from. |
| `$type` | **?int** |  |




***

### __destruct



```php
public __destruct(): mixed
```











***

### getGdImage

Returns the current image resource as a GdImage object.

```php
public getGdImage(): \GdImage
```

Example:
```php
use Kir\Image\Image;
$im = Image::create(100, 100, Color::whiteTransparent(), IMAGETYPE_PNG);
$resource = $im->getGdImage();
image_jpeg($resource, 'image.jpg');
```







**Return Value:**

The gd image resource.



***

### getWidth

Returns the width of the image. This is the horizontal size in pixels.

```php
public getWidth(): int
```

Example:
```php
use Kir\Image\Image;
$im = Image::loadFromFile('image.png');
$width = $im->getWidth();
```







**Return Value:**

The image type constant.



***

### getHeight

Returns the height of the image. This is the vertical size in pixels.

```php
public getHeight(): int
```

Example:
```php
use Kir\Image\Image;
$im = Image::loadFromFile('image.png');
$height = $im->getHeight();
```







**Return Value:**

The image type constant.



***

### getFileType

Returns a php gd image format constant-int for a specific format that was used to create this image. If the image
was created from scratch, and no format was specified, null will be returned.

```php
public getFileType(): int|null
```

See possible [file types](https://www.php.net/manual/en/image.constants.php#constant.imagetype-gif).

Example:
```php
use Kir\Image\Image;
$im = Image::loadFromFile('image.png');
$fileType = $im->getFileType();
```







**Return Value:**

The image type constant.



***

### getMimeType

The mime-type of an image.

```php
public getMimeType(): string|null
```

Example:
```php
use Kir\Image\Image;
$im = Image::loadFromFile('image.png');
$mimeType = $im->getMimeType();
echo $mimeType; // image/png
```







**Return Value:**

The mime type of the image.



***

### getCopy

Returns a copy of the current image.

```php
public getCopy(): \Kir\Image\Image
```

Example:
```php
use Kir\Image\Image;
$im = Image::loadFromFile('image.png');
$copy = $im->getCopy();
```







**Return Value:**

A copy of the current image.



***

### placeImageOn



```php
public placeImageOn(\Kir\Image\Image|\GdImage|resource $targetImage, int $offsetX, int $offsetY): self
```






* **Warning:** this method is **deprecated**. This means that this method will likely be removed in a future version.



**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$targetImage` | **\Kir\Image\Image&#124;\GdImage&#124;resource** |  |
| `$offsetX` | **int** |  |
| `$offsetY` | **int** |  |




***

### pasteOn

Place an image onto the current image.

```php
public pasteOn(\Kir\Image\Image|\GdImage|resource $targetImage, int $offsetX, int $offsetY): self
```

Example:
```php
use Kir\Image\Image;
$im = Image::loadFromFile('image.png');
$logo = Image::loadFromFile('logo.png');
$im->placeImageOn($logo, 10, 10);
```






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$targetImage` | **\Kir\Image\Image&#124;\GdImage&#124;resource** | The target image. |
| `$offsetX` | **int** | The horizontal offset, left to right. |
| `$offsetY` | **int** | The vertical offset, top to bottom. |




***

### getRedColorAt

Returns the red color at the given position. 0 means no red color, 255 means full red color.

```php
public getRedColorAt(int $x, int $y): int
```

Example:
```php
use Kir\Image\Image;
$im = Image::loadFromFile('image.png');
$red = $im->getRedColorAt(10, 10);
echo $red; // A value between 0 and 255
```






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$x` | **int** | The horizontal position, left to right. |
| `$y` | **int** | The vertical position, top to bottom. |


**Return Value:**

The red color code at the given position. 0 means no red color, 255 means full red color.



***

### getGreenColorAt

Returns the green color at the given position. 0 means no green color, 255 means full green color.

```php
public getGreenColorAt(int $x, int $y): int
```

Example:
```php
use Kir\Image\Image;
$im = Image::loadFromFile('image.png');
$green = $im->getGreenColorAt(10, 10);
echo $green; // A value between 0 and 255
```






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$x` | **int** | The horizontal position, left to right. |
| `$y` | **int** | The vertical position, top to bottom. |


**Return Value:**

The green color at the given position. 0 means no green color, 255 means full green color.



***

### getBlueColorAt

Returns the blue color at the given position. 0 means no blue color, 255 means full blue color.

```php
public getBlueColorAt(int $x, int $y): int
```

Example:
```php
use Kir\Image\Image;
$im = Image::loadFromFile('image.png');
$blue = $im->getBlueColorAt(10, 10);
echo $blue; // A value between 0 and 255
```






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$x` | **int** | The horizontal position, left to right. |
| `$y` | **int** | The vertical position, top to bottom. |


**Return Value:**

The blue color code at the given position. 0 means no blue color, 255 means full blue color.



***

### getAlphaAt

Returns the alpha value at the given position. 0 means no alpha, 255 means full alpha.

```php
public getAlphaAt(int $x, int $y): int
```

Example:
```php
use Kir\Image\Image;
$im = Image::loadFromFile('image.png');
$alpha = $im->getAlphaAt(10, 10);
echo $alpha; // A value between 0 and 255
```






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$x` | **int** | The horizontal position, left to right. |
| `$y` | **int** | The vertical position, top to bottom. |


**Return Value:**

The alpha value at the given position. 0 means no alpha, 255 means full alpha.



***

### getChannelColorAt

Returns a channel value at the given position. The color value is between 0 and 255 according to the given bit mask.

```php
public getChannelColorAt(int $x, int $y, int $bitMask): int
```

Example:
```php
use Kir\Image\Image;
$im = Image::loadFromFile('image.png');
$red = $im->getChannelColorAt(10, 10, 0xFF0000);
$green = $im->getChannelColorAt(10, 10, 0x00FF00);
$blue = $im->getChannelColorAt(10, 10, 0x0000FF);
printf("R = %d, G = %d, B = %d\n", $red, $green, $blue);
```






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$x` | **int** | The horizontal position, left to right. |
| `$y` | **int** | The vertical position, top to bottom. |
| `$bitMask` | **int** |  |


**Return Value:**

The channel value at the given position. The color value is between 0 and 255 according to the given bit mask.



***

### greyscale

Converts the current image to a grey scale image.

```php
public greyscale(): $this
```

Example:
```php
use Kir\Image\Image;
$im = Image::loadFromFile('image.png');
$im->greyscale(); // The image is now a grey scale image.
```









***

### applyAlphaMaskFromGreyscaleImage

Applies an image mask to the current image. The mask image must be a greyscale image. The channel value of the
mask image will be used as alpha value for the current image.

```php
public applyAlphaMaskFromGreyscaleImage(\Kir\Image\Image $mask): $this
```

Example:
```php
use Kir\Image\Image;
$im = Image::loadFromFile('image.png');
$mask = Image::loadFromFile('mask.png');
$im->applyAlphaMaskFromGreyscaleImage($mask);
```






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$mask` | **\Kir\Image\Image** | The grey scale mask image. |


**Return Value:**

The new image.



***

### adjustColors

The colors of the image will be adjusted to the full range of 0 to 255.

```php
public adjustColors(): $this
```

Example:
```php
use Kir\Image\Image;
$im = Image::loadFromFile('image.png');
$im->adjustColors();
```







**Return Value:**

The current image.



***

### crop

Remove excess white space around the image.

```php
public crop(int $threshold = 15, int $borderPercent, \Kir\Image\Color|null $backgroundColor = null): $this
```

The threshold value is the value of which a non-white color will be still treated as white. A threshold value of
15 means that a color with a value of 240 will be treated as white. A threshold value of 255 means that only a
color with a value of 255 will be treated as white.

The border width is in percent. 3 means 3% on each side.

Example:
```php
use Kir\Image\Image;
$im = Image::loadFromFile('image.png');
$im->crop(15, 3);
```






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$threshold` | **int** | The threshold value of which a non-white color will be still treated as white. |
| `$borderPercent` | **int** | The border width in percent. |
| `$backgroundColor` | **\Kir\Image\Color&#124;null** | The background color of the new image. |


**Return Value:**

This instance with a new image resource.



***

### detectInnerObject

Detects the inner object of the image. The inner object is the object without any white space around it.

```php
public detectInnerObject(int $threshold = 15): array{left: int, top: int, right: int, bottom: int, width: int, height: int}
```

The threshold value is the value of which a non-white color will be still treated as white. A threshold value of
15 means that a color with a value of 240 will be treated as white. A threshold value of 255 means that only a
color with a value of 255 will be treated as white.

Example:
```php
use Kir\Image\Image;
$im = Image::loadFromFile('image.png');
$measures = $im->detectInnerObject(15);
```






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$threshold` | **int** | The threshold value of which a non-white color will be still treated as white. |


**Return Value:**

The measures of the inner object.



***

### resizeCanvas

Resizes the current image to the given width and height. Will not keep the current proportion.

```php
public resizeCanvas(int $width, int $height, int|null $offsetX = null, int|null $offsetY = null, \Kir\Image\Color|null $backgroundColor = null): $this
```

Is the image is larger than the given width and height, the image will be cropped. If the image is smaller than
the given width and height, there will be colored space added around the image with the given color.

Example:
```php
use Kir\Image\Image;
$im = Image::loadFromFile('image.png');
$im->resizeCanvas(500, 500); // Image will be forcefully cropped or enlarged to 500x500 pixels.
```






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$width` | **int** | The width of the new image. |
| `$height` | **int** | The height of the new image. |
| `$offsetX` | **int&#124;null** | The horizontal offset, left to right. |
| `$offsetY` | **int&#124;null** | The vertical offset, top to bottom. |
| `$backgroundColor` | **\Kir\Image\Color&#124;null** | The background color of the new image. |


**Return Value:**

This instance with a new image resource.



***

### resizeCanvasCentered

Resizes the current image to the given width and height. Will not keep the current proportion.

```php
public resizeCanvasCentered(int $width, int $height, \Kir\Image\Color|null $backgroundColor = null): $this
```

Is the image is larger than the given width and height, the image will be cropped. If the image is smaller than
the given width and height, there will be colored space added around the image with the given color.

Example:
```php
use Kir\Image\Image;
$im = Image::loadFromFile('image.png');
$im->resizeCanvasCentered(500, 500); // Image will be forcefully cropped or enlarged to 500x500 pixels.
```






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$width` | **int** | The width of the new image. |
| `$height` | **int** | The height of the new image. |
| `$backgroundColor` | **\Kir\Image\Color&#124;null** | The background color of the new image. |


**Return Value:**

This instance with a new image resource.



***

### resize

Resizes the current image to the given width and height. Will **not** keep the current proportion.

```php
public resize(int|null $width = null, int|null $height = null): $this
```

When omitting the width or height, the current width or height will be used.

Example:
```php
use Kir\Image\Image;
$im = Image::loadFromFile('image.png');
$im->resize(100, 100); // Image will be forcefully resampled into 100x100 pixels.
```






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$width` | **int&#124;null** | If null, this value is based on the width of the current canvas. |
| `$height` | **int&#124;null** | If null, this value is based on the height of the current canvas. |


**Return Value:**

This instance with a new image resource.



***

### shrinkProportional

Resize image only if it is larger than the targeted size. The image will be shrinked into a rectangle within the
given width and height. If the image is already smaller than the given width and height, nothing will happen.

```php
public shrinkProportional(int|null $width = null, int|null $height = null): $this
```

```php
use Kir\Image\Image;
$image = Image::loadFromFile('image.png');

$newImage = $image->getCopy();
$newImage->resizeProportional(500, 500);
$newImage->resizeCanvasCentered(500, 500);
$newImage->saveAsWebP('500x500.webp');
```






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$width` | **int&#124;null** | If null, this value is based on the current proportion of the current canvas. |
| `$height` | **int&#124;null** | If null, this value is based on the current proportion of the current canvas. |


**Return Value:**

The new image.



***

### resizeProportional

Resize an image proportionally. The image will be shrinked or enlarged to a rectangle within the given width and
height. Is only the width or height given, the other value will be calculated automatically. If both width and
height are given, the image will be fitted into the targeted rectangle.

```php
public resizeProportional(int|null $width = null, int|null $height = null): $this
```

Example:
```php
use Kir\Image\Image;
$image = Image::loadFromFile('image.png');
$image->resizeProportional(500, 500); // Image will be resampled to 500x500 pixels while keeping the proportion.
```






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$width` | **int&#124;null** | If null, this value is based on the current proportion of the current canvas. |
| `$height` | **int&#124;null** | If null, this value is based on the current proportion of the current canvas. |


**Return Value:**

The new image.



***

### fill

Fills the current image with the given color.

```php
public fill(int $x, int $y, \Kir\Image\Color $color): $this
```

Example:
```php
use Kir\Image\Image;
$im = Image::loadFromFile('image.png');
$im->fill(0, 0, Color::whiteOpaque());
```






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$x` | **int** | The horizontal position, left to right. |
| `$y` | **int** | The vertical position, top to bottom. |
| `$color` | **\Kir\Image\Color** | The fill color. |


**Return Value:**

The current image.



***

### rectangle

Draws a rectangle on the current image.

```php
public rectangle(int $x, int $y, int $width, int $height, \Kir\Image\Color $color): $this
```

Example:
```php
use Kir\Image\Image;
$im = Image::loadFromFile('image.png');
$im->rectangle(10, 10, 100, 100, Color::whiteOpaque());
// Draws a white rectangle on the image. The top-left corner is at 10,10 and the bottom-right corner is at 110,110.
```






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$x` | **int** | The horizontal position, left to right. |
| `$y` | **int** | The vertical position, top to bottom. |
| `$width` | **int** | The width of the rectangle. |
| `$height` | **int** | The height of the rectangle. |
| `$color` | **\Kir\Image\Color** | The fill color. |


**Return Value:**

The current image.



***

### removeAlphaBackground

Set the alpha channel of an image to

```php
public removeAlphaBackground(?\Kir\Image\Color $color = null): $this
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$color` | **?\Kir\Image\Color** |  |




***

### saveAs

Saves the current image to a file. The file type will be determined by the file extension of the given filename
or if $explicitType is set, by that given type.

```php
public saveAs(string|null $filename, ?int $explicitType = null): $this
```

Example:
```php
use Kir\Image\Image;
$im = Image::loadFromFile('image.png');
$im->saveAs('image.jpg'); // Saves the image as a jpeg image.
```






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$filename` | **string&#124;null** | The filename of the image. |
| `$explicitType` | **?int** |  |


**Return Value:**

The current image.



***

### saveAsPng

Saves the current image as a png image.

```php
public saveAsPng(string|null $filename = null): $this
```

Example:
```php
use Kir\Image\Image;
$im = Image::loadFromFile('image.png');
$im->saveAsPng('image.png'); // Saves the image as a png image.
```






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$filename` | **string&#124;null** | The filename of the image. |


**Return Value:**

The current image.



***

### saveAsJpeg

Saves the current image as a jpeg image.

```php
public saveAsJpeg(string|null $filename = null, int $quality = 100): $this
```

Example:
```php
use Kir\Image\Image;
$im = Image::loadFromFile('image.png');
$im->saveAsJpeg('image.jpg'); // Saves the image as a jpeg image.
```






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$filename` | **string&#124;null** | The filename of the image. |
| `$quality` | **int** | The quality of the image. 0 means worst quality, 100 means best quality. |


**Return Value:**

The current image.



***

### saveAsGif

Saves the current image as a gif image.

```php
public saveAsGif(string|null $filename = null): $this
```

Example:
```php
use Kir\Image\Image;
$im = Image::loadFromFile('image.png');
$im->saveAsGif('image.gif'); // Saves the image as a gif image.
```






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$filename` | **string&#124;null** | The filename of the image. |


**Return Value:**

The current image.



***

### saveAsWebP

Saves the current image as a webp image.

```php
public saveAsWebP(string|null $filename = null, int $quality = 100): $this
```

Example:
```php
use Kir\Image\Image;
$im = Image::loadFromFile('image.png');
$im->saveAsWebP('image.webp'); // Saves the image as a webp image.
```






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$filename` | **string&#124;null** | The filename of the image. |
| `$quality` | **int** | The quality of the image. 0 means worst quality, 100 means best quality. |


**Return Value:**

The current image.



***

### saveAsBmp

Saves the current image as a bmp image.

```php
public saveAsBmp(string|null $filename = null): $this
```

Example:
```php
use Kir\Image\Image;
$im = Image::loadFromFile('image.png');
$im->saveAsBmp('image.bmp'); // Saves the image as a bmp image.
```






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$filename` | **string&#124;null** | The filename of the image. |


**Return Value:**

The current image.



***

### saveAsString

Saves the current image as a string.

```php
public saveAsString(int|null $imageType = null, int $quality = 100): string
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$imageType` | **int&#124;null** |  |
| `$quality` | **int** |  |




***


***
> Automatically generated from source code comments on 2024-01-15 using [phpDocumentor](http://www.phpdoc.org/) and [saggre/phpdocumentor-markdown](https://github.com/Saggre/phpDocumentor-markdown)
