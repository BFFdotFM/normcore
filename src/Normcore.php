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
      'removeControlCharacters',
      'trimWhitespace',
      'normalizeQuoteCharacters',
      'discardContributors',
      'normalizeStylisticCharacters',
      'trimPunctuation'
    ));
  }

  public static function cleanTrackTitle(string $string) : string {
    return self::transform($string, array(
      'removeControlCharacters',
      'trimWhitespace',
      'normalizeQuoteCharacters',
      'discardExplicitWarning',
      'discardRemasters',
      'discardContributors',
      'normalizeStylisticCharacters',
      'trimPunctuation'
    ));
  }

  public static function cleanAlbumTitle(string $string) : string {
    return self::transform($string, array(
      'removeControlCharacters',
      'trimWhitespace',
      'normalizeQuoteCharacters',
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
      'removeControlCharacters',
      'trimWhitespace',
      'normalizeQuoteCharacters',
      # Strip streaming-metadata legal/presentational clauses first, so the end-anchored
      # suffix strippers below can then reach the real label name.
      'discardCopyrightScaffolding',
      'discardDistributionClause',
      'discardDoingBusinessAs',
      'discardTerritory',
      'discardTrailingUrl',
      'handleDistroKidLabels',
      # Licensing must run BEFORE discardCorporateParent: the licensee (the real label)
      # follows "under licen[cs]e to …", and that clause can itself contain
      # "Group"/"Company" which the article-less corporate-parent rule would otherwise
      # eat first (e.g. "A&M Records, Under Exclusive License to Concord Music Group").
      'discardLicensingBlurb',
      'discardCorporateParent',
      'removeTrailingYear',
      'discardCopyright',
      'removePhrasePunctuation',
      'discardIncorporation',
      'discardCountrySuffixes',
      # Second incorporation pass: a trailing country can hide an incorporation suffix
      # behind it (e.g. "Polydor Ltd. (UK)", "Numan music llc USA"); once the country is
      # gone the exposed "Ltd"/"llc" needs stripping too.
      'discardIncorporation',
      'discardOrganizationGroup',
      'discardLabelNameRedundancies',
      'discardCountrySuffixes',
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
      # Keep the transformed value unless it blanked the string. Compare against '' rather
      # than empty(), since empty('0') is true and would discard a legitimate '0' result.
      if ($newVal !== '') {
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

      # Mirror transform(): keep the value unless it blanked the string (see note there).
      if ($newVal !== '') {
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
