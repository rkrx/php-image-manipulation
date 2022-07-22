<?php

namespace Kir\Image\Tools;

class ImageCalculator {
	/**
	 * @param int $origWidth
	 * @param int $origHeight
	 * @param int|null $targWidth
	 * @param int|null $targHeight
	 * @return array{int, int}|array{null, null}
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

		$w = (int) round($targetWidth ?: 1);
		$h = (int) round($targetHeight ?: 1);
		
		return [$w, $h];
	}
	
	/**
	 * @param int $srcW
	 * @param int $srcH
	 * @param int|null $dstW
	 * @param int|null $dstH
	 * @return array{int, int}|array{null, null}
	 */
	public static function getProprtionalCoverSize(int $srcW, int $srcH, ?int $dstW, ?int $dstH): array {
		[$finalW, $finalH] = self::getProprtionalCoverSizeF($srcW, $srcH, $dstW, $dstH);
		return [(int) $finalW, (int) $finalH];
	}
	
	/**
	 * @param int $srcW
	 * @param int $srcH
	 * @param int|null $dstW
	 * @param int|null $dstH
	 * @return array{float, float}|array{null, null}
	 */
	public static function getProprtionalCoverSizeF(int $srcW, int $srcH, ?int $dstW, ?int $dstH): array {
		if($dstH >= $dstW) {
			$finalH = $dstH;
			$finalW = $finalH * $srcW / $srcH;
			
			if(!($finalW >= $dstW)) {
				$finalW = $dstW;
				$finalH = $finalW * $srcH / $srcW;
			}
		} else {
			$finalW = $dstW;
			$finalH = $finalW * $srcH / $srcW;
			
			if(!($finalH >= $dstH)) {
				$finalH = $dstH;
				$finalW = $finalH * $srcW / $srcH;
			}
		}
		
		return [$finalW, $finalH];
	}
}