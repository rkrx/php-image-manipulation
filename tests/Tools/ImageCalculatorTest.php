<?php

namespace Tools;

use Kir\Image\Tools\ImageCalculator;
use PHPUnit\Framework\TestCase;

class ImageCalculatorTest extends TestCase {
	public function testGetProprtionalCoverSize(): void {
		$originalImageW = 4;
		$originalImageH = 3;
		
		$measures = [
			[2, 2, 'gt', 2],
			[5, 5, 'gt', 2],
			[5, 2, 5, 'gt'],
			[2, 5, 'gt', 5],
		];
		
		foreach($measures as $idx => [$w, $h, $testW, $testH]) {
			[$targetW, $targetH] = ImageCalculator::getProprtionalCoverSizeF($originalImageW, $originalImageH, $w, $h);
			
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
	}
}
