<?php

namespace Kir\Image\Tools;

class ImageCalculator {
	/**
	 * @param int $origWidth
	 * @param int $origHeight
	 * @param int|null $targWidth
	 * @param int|null $targHeight
	 * @return array{int|null, int|null}
	 */
	public static function getProportionalSize(int $origWidth, int $origHeight, ?int $targWidth, ?int $targHeight): array {
		if($targWidth !== null && $targHeight === null) {
			$targHeight = (int) round($targWidth / $origWidth * $origHeight);
		} elseif($targWidth === null && $targHeight !== null) {
			$targWidth = (int) round($targHeight / $origHeight * $origWidth);
		} elseif($targWidth === null && $targHeight === null) {
			// No new width and height given. Retain measures as is as if resize was commanded with original width and height.
			return [null, null];
		}

		if($targWidth > $targHeight) {
			$targetWidth = $targWidth;
			$targetHeight = $origHeight * $targWidth / $origWidth;

			if($targetHeight > $targHeight) {
				$targetWidth = $origWidth * $targHeight / $origHeight;
				$targetHeight = $targHeight;
			}
		} else {
			$targetWidth = $origWidth * $targHeight / $origHeight;
			$targetHeight = $targHeight;

			if($targetWidth > $targWidth) {
				$targetWidth = $targWidth;
				$targetHeight = $origHeight * $targWidth / $origWidth;
			}
		}

		$w = (int) round($targetWidth);
		$h = (int) round($targetHeight);
		
		return [$w, $h];
	}
}