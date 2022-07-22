<?php

namespace Kir\Image\Tools;

class ImageCalculator {
	/**
	 * @param int $origWidth
	 * @param int $origHeight
	 * @param int|null $targWidth
	 * @param int|null $targHeight
	 * @return array{int, int}
	 */
	public static function getProportionalSize(int $origWidth, int $origHeight, ?int $targWidth, ?int $targHeight): array {
		if($targWidth !== null && $targHeight === null) {
			$targHeight = (int) round($targWidth / $origWidth * $origHeight);
		} elseif($targWidth === null && $targHeight !== null) {
			$targWidth = (int) round($targHeight / $origHeight * $origWidth);
		} elseif($targWidth === null && $targHeight === null) {
			// No new width and height given. Retain measures as is as if resize was commanded with original width and height.
			return [$origWidth, $origHeight];
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

		$w = (int) round($targetWidth ?: 1);
		$h = (int) round($targetHeight ?: 1);
		
		return [$w, $h];
	}
	
	/**
	 * @param int $origWidth
	 * @param int $origHeight
	 * @param int|null $targWidth
	 * @param int|null $targHeight
	 * @return array{int, int}
	 */
	public static function getProprtionalCoverSize(int $origWidth, int $origHeight, ?int $targWidth, ?int $targHeight): array {
		[$finalW, $finalH] = self::getProprtionalCoverSizeF($origWidth, $origHeight, $targWidth, $targHeight);
		return [(int) $finalW, (int) $finalH];
	}
	
	/**
	 * @param int $origWidth
	 * @param int $origHeight
	 * @param int|null $targWidth
	 * @param int|null $targHeight
	 * @return array{float, float}
	 */
	public static function getProprtionalCoverSizeF(int $origWidth, int $origHeight, ?int $targWidth, ?int $targHeight): array {
		if($targWidth !== null && $targHeight === null) {
			$targHeight = (int) round($targWidth / $origWidth * $origHeight);
		} elseif($targWidth === null && $targHeight !== null) {
			$targWidth = (int) round($targHeight / $origHeight * $origWidth);
		} elseif($targWidth === null && $targHeight === null) {
			// No new width and height given. Retain measures as is as if resize was commanded with original width and height.
			return [$origWidth, $origHeight];
		}
		
		if($targHeight >= $targWidth) {
			$finalH = $targHeight;
			$finalW = $finalH * $origWidth / $origHeight;
			
			if(!($finalW >= $targWidth)) {
				$finalW = $targWidth;
				$finalH = $finalW * $origHeight / $origWidth;
			}
		} else {
			$finalW = $targWidth;
			$finalH = $finalW * $origHeight / $origWidth;
			
			if(!($finalH >= $targHeight)) {
				$finalH = $targHeight;
				$finalW = $finalH * $origWidth / $origHeight;
			}
		}
		
		/** @var int $finalW */
		/** @var int $finalH */
		return [$finalW, $finalH];
	}
}