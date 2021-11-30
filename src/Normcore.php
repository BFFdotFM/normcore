<?php

namespace BFFdotFM\Normcore;

/**
 * Normcore is a set of normalization functions for wrangling music metadata
 * @author Ben Ward
 * @author Nick Mirov
 * @copyright BFF.fm
 * @license MIT
 */
class Normcore {

  static function normalizeArtistName(string $string) : string {

  }

  static function normalizeTrackTitle(string $string) : string {

  }

  static function normalizeAlbumTitle(string $string) : string {

  }

  static function normalizeRecordLabelName(string $string) : string {

  }

  protected static function transform(string $string, array $transforms = array()) {
    $stages = array(
      'setup' => array(),
      'optimize' => array(),
      'finish' => array()
    );

    if (array_has_key('setup', $transforms) || array_has_key('optimize', $transforms) || array_has_key('finish', $transforms)) {
      array_merge($stages, $transforms);
    } else {
      $stages['optimize'] = $transforms;
    }

    $preparedString = array_reduce($stages['setup'], function($inputString, $function) {
      $newVal = trim(Transforms::$function($inputString));
      if (!empty($newVal)) {
        return $newVal;
      } else {
        return $inputString;
      }
    }, $string);

    # optimize (iterate until nothing)
    $transformedString = array_reduce($stages['optimize'], function($inputString, $function) {
      $newVal = trim(Transforms::$function($inputString));
      if (!empty($newVal)) {
        return $newVal;
      } else {
        return $inputString;
      }
    }, $string);

    # finally (clean-up)
    $finishedString = array_reduce($stages['finish'], function($inputString, $function) {
      $newVal = trim(Transforms::$function($inputString));
      if (!empty($newVal)) {
        return $newVal;
      } else {
        return $inputString;
      }
    }, $string);

    return $finishedString;
  }

}
