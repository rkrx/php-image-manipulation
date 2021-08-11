<?php

namespace Kir\Image;

class Color {
	private int $red;
	private int $green;
	private int $blue;
	private int $alpha;
	
	/**
	 * @param int $red A number between 0 and 255 (0 = no red, 255 = full red)
	 * @param int $green A number between 0 and 255 (0 = no green, 255 = full green)
	 * @param int $blue A number between 0 and 255 (0 = no blue, 255 = full blue)
	 * @return self
	 */
	public static function fromRGB(int $red, int $green, int $blue): self {
		return self::fromRGBA($red, $green, $blue, 255);
	}
	
	/**
	 * @param int $red A number between 0 and 255 (0 = no red, 255 = full red)
	 * @param int $green A number between 0 and 255 (0 = no green, 255 = full green)
	 * @param int $blue A number between 0 and 255 (0 = no blue, 255 = full blue)
	 * @param int $alpha A number between 0 and 255 (0 = fully transparent, 255 = fully opaque)
	 * @return self
	 */
	public static function fromRGBA(int $red, int $green, int $blue, int $alpha): self {
		if($red < 0 || $red > 255) {
			throw new ColorRuntimeException('The value for red must be between 0 and 255');
		}
		if($green < 0 || $green > 255) {
			throw new ColorRuntimeException('The value for green must be between 0 and 255');
		}
		if($blue < 0 || $blue > 255) {
			throw new ColorRuntimeException('The value for blue must be between 0 and 255');
		}
		if($alpha < 0 || $alpha > 255) {
			throw new ColorRuntimeException('The value for alpha must be between 0 and 255');
		}
		return new self($red, $green, $blue, $alpha);
	}
	
	/**
	 * @return self
	 */
	public static function whiteOpaque(): self {
		return self::fromRGB(255, 255, 255);
	}
	
	/**
	 * @param int $red A number between 0 and 255 (0 = no red, 255 = full red)
	 * @param int $green A number between 0 and 255 (0 = no green, 255 = full green)
	 * @param int $blue A number between 0 and 255 (0 = no blue, 255 = full blue)
	 * @param int $alpha A number between 0 and 255 (0 = fully transparent, 255 = fully opaque)
	 */
	public function __construct(int $red, int $green, int $blue, int $alpha) {
		$this->red = $red;
		$this->green = $green;
		$this->blue = $blue;
		$this->alpha = $alpha;
	}
	
	/**
	 * @return int A number between 0 and 255 (0 = no red, 255 = full red)
	 */
	public function getRed(): int {
		return $this->red;
	}
	
	/**
	 * @return int A number between 0 and 255 (0 = no green, 255 = full green)
	 */
	public function getGreen(): int {
		return $this->green;
	}
	
	/**
	 * @return int A number between 0 and 255 (0 = no blue, 255 = full blue)
	 */
	public function getBlue(): int {
		return $this->blue;
	}
	
	/**
	 * @return int A number between 0 and 255 (0 = fully transparent, 255 = fully opaque)
	 */
	public function getAlpha(): int {
		return $this->alpha;
	}
}