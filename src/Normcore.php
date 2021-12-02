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
      'trimPunctuation'
    ));
  }

  public static function cleanTrackTitle(string $string) : string {
    return self::transform($string, array(
      'trimWhitespace',
      'discardContributors',
      'trimPunctuation'
    ));
  }

  public static function cleanAlbumTitle(string $string) : string {
    return self::transform($string, array(
      'trimWhitespace',
      'discardContributors',
      'trimPunctuation'
    ));
  }

  public static function cleanRecordLabelName(string $string) : string {
    return self::transform($string, array(
      'trimWhitespace',
      'discardLicensingBlurb',
      'removeTrailingYear',
      'discardCopyright',
      'removePunctuation',
      'discardIncorporation',
      'discardOrganizationGroup',
      'discardLabelNameRedundancies',
    ));
  }

  #!!! Functions to create common normalized keys for metaata

  public static function keyArtistName(string $string) : string {
    return self::transform($string, array(
      'downCase',
      'normalizeUnicode',
      'flattenStylisticCharacters',
      'removePunctuation',
      'discardContributors',
      'filterRedundantWords',
      'removeWhitespace'
    ));
  }

  public static function keyTrackTitle(string $string) : string {
    return self::transform($string, array(
      'downCase',
      'normalizeUnicode',
      'removePunctuation',
      'discardContributors',
      'filterRedundantWords',
      'removeWhitespace'
    ));
  }

  public static function keyAlbumTitle(string $string) : string {
    return self::transform($string, array(
      'downCase',
      'normalizeUnicode',
      'removePunctuation',
      'discardContributors',
      'filterRedundantWords',
      'removeWhitespace'
    ));
  }

  public static function keyRecordLabelName(string $string) : string {
    return self::transform($string, array(
      'downCase',
      'normalizeUnicode',
      'removePunctuation',
      'discardLicensingBlurb',
      'removeTrailingYear',
      'discardCopyright',
      'discardIncorporation',
      'discardOrganizationGroup',
      'discardLabelNameRedundancies',
      'filterRedundantWords',
      'removeWhitespace'
    ));
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

}
