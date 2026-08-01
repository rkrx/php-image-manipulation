<?php

namespace Kir\Image;

use PHPUnit\Framework\TestCase;

class ColorTest extends TestCase {
	/**
	 * @dataProvider provideInvalidColorChannels
	 */
	public function testConstructorRejectsInvalidChannels(
		int $red,
		int $green,
		int $blue,
		int $alpha,
		string $channel
	): void {
		$this->expectException(ColorRuntimeException::class);
		$this->expectExceptionMessage("The value for {$channel} must be between 0 and 255");

		new Color($red, $green, $blue, $alpha);
	}

	/**
	 * @return iterable<string, array{int, int, int, int, string}>
	 */
	public function provideInvalidColorChannels(): iterable {
		yield 'red below range' => [-1, 0, 0, 0, 'red'];
		yield 'green above range' => [0, 256, 0, 0, 'green'];
		yield 'blue below range' => [0, 0, -1, 0, 'blue'];
		yield 'alpha above range' => [0, 0, 0, 256, 'alpha'];
	}
}
