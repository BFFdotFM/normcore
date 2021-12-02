<?php declare(strict_types=1);

namespace BFFdotFM\Normcore;

/**
 * Normcore is a set of normalization functions for wrangling music metadata
 * @author Ben Ward
 * @author Nick Mirov
 * @copyright BFF.fm
 * @license MIT
 */
class Normcore {

  #!!! Functions to clean display of metadata

  public static function cleanArtistName(string $string) : string {
    return 'Clean Name';
  }

  public static function cleanTrackTitle(string $string) : string {
    return '';
  }

  public static function cleanAlbumTitle(string $string) : string {
    return '';
  }

  public static function cleanRecordLabelName(string $string) : string {
    return '';
  }

  #!!! Functions to create common normalized keys for metaata

  public static function keyArtistName(string $string) : string {
    return 'keyname';
  }

  public static function keyTrackTitle(string $string) : string {
    return '';
  }

  public static function keyAlbumTitle(string $string) : string {
    return '';
  }

  public static function keyRecordLabelName(string $string) : string {
    return '';
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
