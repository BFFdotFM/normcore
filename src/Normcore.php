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
    return self::transform($string, array(
      'trimWhitespace',
      'discardContributors',
      'normalizeStylisticCharacters',
      'trimPunctuation'
    ));
  }

  public static function cleanTrackTitle(string $string) : string {
    return self::transform($string, array(
      'trimWhitespace',
      'discardExplicitWarning',
      'discardRemasters',
      'discardContributors',
      'normalizeStylisticCharacters',
      'trimPunctuation'
    ));
  }

  public static function cleanAlbumTitle(string $string) : string {
    return self::transform($string, array(
      'trimWhitespace',
      'normalizeVolumes',
      'discardDiscNumber',
      'discardExplicitWarning',
      'discardSpecialEditions',
      'discardRemasters',
      'discardContributors',
      'normalizeStylisticCharacters',
      'trimPunctuation'
    ));
  }

  public static function cleanRecordLabelName(string $string) : string {
    return self::transform($string, array(
      'trimWhitespace',
      'handleDistroKidLabels',
      'discardLicensingBlurb',
      'removeTrailingYear',
      'discardCopyright',
      'removePhrasePunctuation',
      'discardIncorporation',
      'discardOrganizationGroup',
      'discardLabelNameRedundancies',
      'discardYearPrefix',
      'trimPunctuation'
    ));
  }

  #!!! Functions to create common normalized keys for metadata

  /**
   * Shared set of transformations to turn any cleaned string into a key
   */
  protected static function convertToKey(string $string) : string {
    return self::transform($string, array(
      'normalizeUnicode',
      'flattenStylisticCharacters',
      'downCase',
      'filterRedundantWords',
      'removePunctuation',
      'removeWhitespace'
    ));
  }

  public static function keyArtistName(string $string) : string {
    return self::convertToKey(self::cleanArtistName($string));
  }

  public static function keyTrackTitle(string $string) : string {
    return self::convertToKey(self::cleanTrackTitle($string));
  }

  public static function keyAlbumTitle(string $string) : string {
    return self::convertToKey(
      self::transform(self::cleanAlbumTitle($string), array('discardEpLpSuffix'))
    );
  }

  public static function keyRecordLabelName(string $string) : string {
    return self::convertToKey(self::cleanRecordLabelName($string));
  }

  protected static function transform(string $string, array $transforms = array()) : string {
    return array_reduce($transforms, function($inputString, $function) {
      $newVal = Transforms::$function($inputString);
      if (!empty($newVal)) {
        return $newVal;
      } else {
        return $inputString;
      }
    }, $string);
  }


  protected static $analysis = array();

  /**
   * Run transform against data, recording execution time for each transform
   */
  public static function analyzeTransforms(string $string, array $transforms = array()) : string {
    return array_reduce($transforms, function($inputString, $function) {
      $start = microtime(true);
      $newVal = Transforms::$function($inputString);
      $execTime = microtime(true) - $start;
      if (!isset(self::$analysis[$function])) {
        self::$analysis[$function] = array();
      }
      self::$analysis[$function][] = $execTime;

      if (!empty($newVal)) {
        return $newVal;
      } else {
        return $inputString;
      }
    }, $string);
  }

  public static function getAnalysis() {
    return array_map(function ($func) {
      return array(
        'function' => $func,
        'calls' => count(self::$analysis[$func]),
        'time' => array_sum(self::$analysis[$func])
      );
    }, array_keys(self::$analysis));
  }

  public static function resetAnalysis() {
    self::$analysis = array();
  }

}
