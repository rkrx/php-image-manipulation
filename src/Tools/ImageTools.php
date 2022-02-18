<?php

namespace Kir\Image\Tools;

use Kir\Image\ImageRuntimeException;

class ImageTools {
	/**
	 * @template T
	 * @param callable(): (T|false) $fn
	 * @return T
	 */
	public static function nonFalse($fn) {
		$result = $fn();
		if(is_bool($result)) {
			throw new ImageRuntimeException();
		}
		return $result;
	}
}
