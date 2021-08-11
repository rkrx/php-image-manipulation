<?php

namespace Kir\Image;

use PHPUnit\Framework\TestCase;

class ImageTest extends TestCase {
	public function testLoadBmpFromFile() {
		$image = Image::loadFromFile(__DIR__.'/images/cat.bmp');
		self::assertEquals([$image->getWidth(), $image->getHeight()], [4, 4]);
	}
	
	public function testLoadGifFromFile() {
		$image = Image::loadFromFile(__DIR__.'/images/cat.gif');
		self::assertEquals([$image->getWidth(), $image->getHeight()], [2, 2]);
	}
	
	public function testLoadJpegFromFile() {
		$image = Image::loadFromFile(__DIR__.'/images/cat.jpg');
		self::assertEquals([$image->getWidth(), $image->getHeight()], [2, 2]);
	}
	
	public function testLoadPngFromFile() {
		$image = Image::loadFromFile(__DIR__.'/images/cat.png');
		self::assertEquals([$image->getWidth(), $image->getHeight()], [2, 2]);
	}
	
	public function testLoadWebpFromFile() {
		$image = Image::loadFromFile(__DIR__.'/images/cat.webp');
		self::assertEquals([$image->getWidth(), $image->getHeight()], [2, 2]);
	}
	
	public function testLoadXbmFromFile() {
		$image = Image::loadFromFile(__DIR__.'/images/cat.xbm');
		self::assertEquals([$image->getWidth(), $image->getHeight()], [2, 2]);
	}
	
	public function testLoadBmpFromString() {
		$image = Image::loadFromString(file_get_contents(__DIR__.'/images/cat.bmp'));
		self::assertEquals([$image->getWidth(), $image->getHeight()], [4, 4]);
	}
	
	public function testLoadGifFromString() {
		$image = Image::loadFromString(file_get_contents(__DIR__.'/images/cat.gif'));
		self::assertEquals([$image->getWidth(), $image->getHeight()], [2, 2]);
	}
	
	public function testLoadJpegFromString() {
		$image = Image::loadFromString(file_get_contents(__DIR__.'/images/cat.jpg'));
		self::assertEquals([$image->getWidth(), $image->getHeight()], [2, 2]);
	}
	
	public function testLoadPngFromString() {
		$image = Image::loadFromString(file_get_contents(__DIR__.'/images/cat.png'));
		self::assertEquals([$image->getWidth(), $image->getHeight()], [2, 2]);
	}
	
	public function testLoadWebpFromString() {
		$image = Image::loadFromString(file_get_contents(__DIR__.'/images/cat.webp'));
		self::assertEquals([$image->getWidth(), $image->getHeight()], [2, 2]);
	}
	
	public function testCreate() {
		$image = Image::create(500, 500);
		self::assertEquals([$image->getWidth(), $image->getHeight()], [500, 500]);
	}
	
	public function testGetImageObject() {
		$image = Image::create(500, 500);
		$resource = $image->getGdImage();
		self::assertTrue($resource instanceof \GdImage || is_resource($resource));
	}
	
	public function testDetectInnerObject() {
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
	
	public function testResizeProportional() {
		$image = Image::loadFromFile(__DIR__.'/images/cat.webp');
		$image->resizeProportional(600);
		self::assertEquals(600, $image->getWidth());
		self::assertEquals(600, $image->getHeight());
	}
}
