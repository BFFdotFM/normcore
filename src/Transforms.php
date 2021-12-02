<?php declare(strict_types=1);

namespace BFFdotFM\Normcore;

use Normalizer;
use Transliterator;

class Transforms {

  /**
   * Generic function to remove string suffixes from the end of a string
   */
  protected static function removeSuffix(string $string, array $suffixes, bool $matchCase = true) : string {
    $pattern = sprintf('/\s(?:%s)$/%s', implode('|', $suffixes), $matchCase ? '' : 'i');
    return preg_replace($pattern, '', $string);
  }

  #!!! Text Cleaning

  static function downCase(string $string) : string {
    return strtolower($string);
  }

  static function normalizeUnicode(string $string) : string {
    $transliterator = Transliterator::createFromRules(':: Any-Latin; :: Latin-ASCII; :: NFD; :: [:Nonspacing Mark:] Remove; :: NFC;', Transliterator::FORWARD);
    $result = $transliterator->transliterate($string);
    if ($result === false) {
      return '';
    }
    return trim($result);
  }

  static function flattenStylisticCharacters(string $string) : string {
    return str_replace('$', 'S', $string);
  }

  # TODO: Determine if this should strip to no punctuation or extra whitespace
  # e.g. R.E.M. should be REM, not R E M?
  # Will we ultimately drop all whitespace, in which case …moot?
  static function removePunctuation(string $string) : string {
    return preg_replace('/[^a-z\d\s]+/i', '', $string);
  }

  /**
   * Remove whitespace characters from the beginning and end of the string
   */
  static function trimWhitespace(string $string) : string {
    return trim($string);
  }

  /**
   * Remove non-word characters from the beginning and end of the string
   */
  static function trimPunctuation(string $string) : string {
    return trim($string, " \n\r\t\v\0,.:;&/\\'-");
  }

  public const REDUNDANT_WORDS = array('the', 'and');
  # 'and' must go else clashes with '&'
  # not sure impact of 'the'?
  static function filterRedundantWords(string $string) : string {
    return implode(' ', array_filter(preg_split('/(\s)+/i', $string), function ($word, $index) {
      # Ignore first word
      return ($index === 0) || !(empty($word) || in_array($word, self::REDUNDANT_WORDS));
    }, ARRAY_FILTER_USE_BOTH));
  }

  static function removeWhitespace(string $string) : string {
    return preg_replace('/\s+/', '', $string);
  }

  #!!! Artist Cleaning

  # Must not match the insides of words.
  # Should this depend on punctuation for ft. and feat. ? Query all 'featuring' and 'feat' examples from DB
  /**
   * Remove additional credited artists from a string, e.g. “The Rolling Stones (feat. Pitbull)
   */
  static function discardContributors(string $string) : string {
    $parts = preg_split('/\s(?:,\s|\()?(?:ft|feat|featuring)\.?\s/i', $string);
    return array_shift($parts);
  }

  #!!! Label/Organization Clean Functions

  /**
   * Split and discard extraneous licensing blurb sometimes included in label credits pasted from Spotify, etc.
   */
  static function discardLicensingBlurb(string $string) : string {
    $parts = preg_split('/\s(?:under exclusive license|under license|a division of|marketed by|rights management|in partnership with|in association with)\.?\s/i', $string);
    return array_shift($parts);
  }

  /**
   * Remove a four-digit number from the end of the string, where someone has included the copyright or release year in label credit
   */
  static function removeTrailingYear(string $string) : string {
    return preg_replace('/\s+\(?\d{4}(?:\/\d{4})?\)?$/', '', $string);
  }

  static function discardCopyright(string $string) : string {
    # Remove (c) 2021 prefix from label strings
    # (c) 2020
    # (p) 2019
    # © 1982 2020
    $stringCopyPrefix = preg_replace('/^(?:\(c\)|\(p\)|©|℗)\s*(?:\d{4}\s)+/i', '', $string);

    # Remove 'Copyright Control' text from end of string
    return preg_replace('/\s(?:Copyright Control)?\s*(?:All Rights Reserved)?$/i', '', $stringCopyPrefix);
  }

  public const CORP_SUFFIXES = array('LLC', 'LLCs', 'LTD', 'Limited', 'Unlimited', 'Inc', 'Corporation', 'Corp');
  static function discardIncorporation(string $string) : string {
    return self::removeSuffix($string, self::CORP_SUFFIXES, false);
  }

  public const SOFT_GROUP_SUFFIXES = array('Co', 'Group', 'Recording Company', 'Recording Co', 'Record Label', 'Publishing', 'Music', 'Music Group', 'Music Publishing', 'International');
  static function discardOrganizationGroup(string $string) : string {
    return self::removeSuffix($string, self::SOFT_GROUP_SUFFIXES, false);
  }

  public const REDUNDANT_NAME_SUFFIXES = array('Records', 'Recordings');
  static function discardLabelNameRedundancies(string $string) : string {
    return self::removeSuffix($string, self::REDUNDANT_NAME_SUFFIXES, false);
  }

}
