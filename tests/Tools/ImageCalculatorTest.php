<?php

namespace Tools;

use Kir\Image\Tools\ImageCalculator;
use PHPUnit\Framework\TestCase;

class ImageCalculatorTest extends TestCase {
	public function testGetProportionalCoverSizeF(): void {
		$originalImageW = 4;
		$originalImageH = 3;
		
		$measures = [
			[2, 2, 'gt', 2],
			[5, 5, 'gt', 2],
			[5, 2, 5, 'gt'],
			[2, 5, 'gt', 5],
		];
		
		foreach($measures as $idx => [$w, $h, $testW, $testH]) {
			[$targetW, $targetH] = ImageCalculator::getProportionalCoverSizeF($originalImageW, $originalImageH, $w, $h);
			
			if($testW === 'gt') {
				self::assertGreaterThan($w, $targetW, sprintf('Index %d: Expected target width to be greater than %d, got %d', $idx, $w, $targetW));
			} else {
				self::assertEquals($w, $targetW, sprintf('Index %d: Expected target width to be greater than %d, got %d', $idx, $w, $targetW));
			}
			
			if($testH === 'gt') {
				self::assertGreaterThan($h, $targetH, sprintf('Index %d: Expected target height to be greater than %d, got %d', $idx, $h, $targetH));
			} else {
				self::assertEquals($h, $targetH, sprintf('Index %d: Expected target height to be greater than %d, got %d', $idx, $h, $targetH));
			}
		}

		self::assertSame([12, 9], ImageCalculator::getProportionalCoverSizeF(4, 3, 10, 9));
		self::assertSame([9, 12], ImageCalculator::getProportionalCoverSizeF(3, 4, 9, 10));
	}

	public function testGetProportionalCoverSize(): void {
		self::assertSame([6, 5], ImageCalculator::getProportionalCoverSize(4, 3, 5, 5));
		self::assertSame([8, 6], ImageCalculator::getProportionalCoverSize(4, 3, 8, null));
		self::assertSame([8, 6], ImageCalculator::getProportionalCoverSize(4, 3, null, 6));
		self::assertSame([4, 3], ImageCalculator::getProportionalCoverSize(4, 3, null, null));
	}

	public function testMisspelledCoverSizeMethodsRemainCompatibleAliases(): void {
		self::assertSame(
			ImageCalculator::getProportionalCoverSize(4, 3, 5, 2),
			ImageCalculator::getProprtionalCoverSize(4, 3, 5, 2)
		);
		self::assertSame(
			ImageCalculator::getProportionalCoverSizeF(4, 3, 2, 5),
			ImageCalculator::getProprtionalCoverSizeF(4, 3, 2, 5)
		);
	}
}
