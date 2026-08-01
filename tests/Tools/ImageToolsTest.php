<?php

namespace Kir\Image\Tools;

use Kir\Image\ImageRuntimeException;
use PHPUnit\Framework\TestCase;

class ImageToolsTest extends TestCase {
	public function testNonFalseReturnsSuccessfulResult(): void {
		self::assertSame('result', ImageTools::nonFalse(static fn(): string => 'result'));
	}

	public function testNonFalseConvertsGdFailureIntoException(): void {
		$this->expectException(ImageRuntimeException::class);

		ImageTools::nonFalse(static fn(): bool => false);
	}
}
