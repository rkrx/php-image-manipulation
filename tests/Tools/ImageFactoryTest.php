<?php

namespace Kir\Image\Tools;

use Kir\Image\Image;
use Kir\Image\ImageRuntimeException;
use PHPUnit\Framework\TestCase;

class ImageFactoryTest extends TestCase {
	public function testLoadAvifFromFile(): void {
		$this->assertGeneratedImageCanBeLoaded('avif', 'imageavif', IMAGETYPE_AVIF);
	}

	public function testLoadWbmpFromFile(): void {
		$this->assertGeneratedImageCanBeLoaded('wbmp', 'imagewbmp', IMAGETYPE_WBMP);
	}

	public function testLoadGdFromFile(): void {
		$this->assertGeneratedImageCanBeLoaded('gd', 'imagegd', null);
	}

	public function testLoadGd2FromFile(): void {
		$this->assertGeneratedImageCanBeLoaded('gd2', 'imagegd2', null);
	}

	public function testLoadTgaFromFile(): void {
		$filename = $this->createTemporaryFilename('tga');
		$data = hex2bin('0000020000000000000000000200020018000000ff00ff00ff0000ffffff');
		self::assertIsString($data);
		try {
			self::assertSame(strlen($data), file_put_contents($filename, $data));
			$image = Image::loadFromFile($filename);
			self::assertSame([2, 2], [$image->getWidth(), $image->getHeight()]);
			self::assertNull($image->getFileType());
		} finally {
			if(is_file($filename)) {
				unlink($filename);
			}
		}
	}

	public function testLoadXpmFromFile(): void {
		if(!function_exists('imagecreatefromxpm')) {
			$this->expectException(ImageRuntimeException::class);
			$this->expectExceptionMessage('imagecreatefromxpm() is not available');
			Image::loadFromFile(__DIR__.'/../images/cat.xpm');
			return;
		}
		$image = Image::loadFromFile(__DIR__.'/../images/cat.xpm');
		self::assertSame([2, 2], [$image->getWidth(), $image->getHeight()]);
		self::assertNull($image->getFileType());
	}

	private function assertGeneratedImageCanBeLoaded(string $extension, string $writer, ?int $expectedType): void {
		if(!function_exists($writer)) {
			self::markTestSkipped("The installed GD library has no {$extension} writer");
		}
		$filename = $this->createTemporaryFilename($extension);
		$source = imagecreatetruecolor(2, 2);
		try {
			if(@$writer($source, $filename) !== true || !is_file($filename) || filesize($filename) === 0) {
				self::markTestSkipped("The installed GD library has no {$extension} support");
			}
			$image = Image::loadFromFile($filename);
			self::assertSame([2, 2], [$image->getWidth(), $image->getHeight()]);
			self::assertSame($expectedType, $image->getFileType());
		} finally {
			if(is_file($filename)) {
				unlink($filename);
			}
		}
	}

	private function createTemporaryFilename(string $extension): string {
		return sys_get_temp_dir().'/php-image-manipulation-'.bin2hex(random_bytes(8)).'.'.$extension;
	}
}
