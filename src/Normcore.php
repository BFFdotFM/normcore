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
    ), array(
      'removePlaceholderPunctuation',
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

  protected static function transform(string $string, array $transforms = array(), array $finishers = array()) : string {
    # Perform transforms, which will be applied so long as the returned string is not empty
    $transformed = array_reduce($transforms, function($inputString, $function) {
      $newVal = Transforms::$function($inputString);
      if (!empty($newVal)) {
        return $newVal;
      } else {
        return $inputString;
      }
    }, $string);

    # Perform finishers, which will be applied even if an empty response results
    return array_reduce($finishers, function($inputString, $function) {
      return Transforms::$function($inputString);
    }, $transformed);
  }

}
