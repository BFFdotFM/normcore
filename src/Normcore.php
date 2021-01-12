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

  static function normalizeArtistName($string) {

  }

  static function normalizeTrackTitle($string) {

  }

  static function normalizeAlbumTitle($string) {

  }

  static function normalizeRecordLabelName($string) {

  }

  protected static function transform($string, array $transforms = array()) {
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

    $transformedString = array_reduce($stages['setup'], function($inputString, $function) {
      $newVal = trim(Transforms::$function($inputString));
      if (!empty($newVal)) {
        return $newVal;
      } else {
        return $inputString;
      }
    }, $string);




    # optimize (iterate until nothing)
    # finally (clean-up)
  }

}
