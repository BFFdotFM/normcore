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

  /** Convert string to lower case */
  static function downCase(string $string) : string {
    return strtolower($string);
  }

  /** Converty Unicode to ASCII representation  */
  static function normalizeUnicode(string $string) : string {
    $transliterator = Transliterator::createFromRules(':: Any-Latin; :: Latin-ASCII; :: NFD; :: [:Nonspacing Mark:] Remove; :: NFC;', Transliterator::FORWARD);
    $result = $transliterator->transliterate($string);
    if ($result === false) {
      return '';
    }
    return trim($result);
  }

  /** Convert ambiguous stylistic characters to flat ASCII alternatives */
  static function flattenStylisticCharacters(string $string) : string {
    return str_replace('$', 'S', $string);
  }

  /** Convert stylistic characters and punctuation to character alternatives  */
  static function normalizeStylisticCharacters(string $string) : string {
    return str_replace('...', '…', $string);
  }

  # TODO: Determine if this should strip to no punctuation or extra whitespace
  # e.g. R.E.M. should be REM, not R E M?
  # Will we ultimately drop all whitespace, in which case …moot?
  static function removePunctuation(string $string) : string {
    return preg_replace('/[^a-z\d\s]+/i', '', $string);
  }

  static function removePhrasePunctuation(string $string) : string {
    return preg_replace('/[.,"\';:\(\)\[\]\\<>]+/', '', $string);
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
    return trim($string, " \n\r\t\v\0,.:;/\\'-");
  }

  /**
   * Remove joining words that could otherwise ambiguously pollute strings
   */
  static function filterRedundantWords(string $string) : string {
    return preg_replace('/\b(?:the|and|a)(?:[\s\b])/i', '', $string);
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
    $parts = preg_split('/\s(?:,\s|\()?(?:ft|feat|featuring|w\/)\.?\s/i', $string);
    return array_shift($parts);
  }

  #!!! Album Name Clean Functions

  static function discardEpLpSuffix(string $string) : string {
    return preg_replace('/\s(?:EP|LP|12"|7")$/i', '', $string);
  }

  #!!! Label/Organization Clean Functions

  /**
   * DistroKid has a scheme for self-released artists who use their platform to distribute music without
   * providing an explicit label name, resulting in tracks being listed as “0000000 Records DK” on
   * streaming platforms.
   *
   * e.g    123456 Records DK
   *
   * Require the 6-digit ID, allow “DK” to be absent, per BFF.fm crowd data.
   * Where these are entered into a database, treat them as “Self Released”.
   */
  static function handleDistroKidLabels(string $string) : string {
    if (preg_match('/\d{6,} Records(?: DK)?/i', $string)) {
      return 'Self Released';
    }
    return $string;
  }

  /**
   * Split and discard extraneous licensing blurb sometimes included in label credits pasted from Spotify, etc.
   */
  static function discardLicensingBlurb(string $string) : string {
    # Phrases that make the second part of the string relevant
    $parts = preg_split('/\s(?:under exclusive license to|under license to|exclusively licensed to|)\.?\s/i', $string);
    if (count($parts) > 1) {
      $string = $parts[1];
    }

    # Phrases that make the first part of the string relevant
    $parts = preg_split('/\s(?:(?:exclusively )?distributed by|under(?: exclusive)? license|exclusively licensed|a division of|a div\.? of|marketed by|rights management|in partnership with|in association with)\.?\s/i', $string);

    return array_shift($parts);
  }

  /**
   * Remove a four-digit number from the end of the string, where someone has included the copyright or release year in label credit
   */
  static function removeTrailingYear(string $string) : string {
    return preg_replace('/\s+\(?\d{4}(?:\/\d{4})?\)?$/', '', $string);
  }

  static function discardYearPrefix(string $string) : string {
    # Remove (c) 2021 prefix from label strings
    # (c) 2020
    # (p) 2019
    # © 1982 2020
    # 1975
    return preg_replace('/^(?:\(c\)|\(p\)|©|℗)?\s*(?:\d{4}\s)+/i', '', $string);
  }

  static function discardCopyright(string $string) : string {
    # Remove 'Copyright Control' text from end of string
    return preg_replace('/\s(?:Copyright Control)?\s*(?:All Rights Reserved)?$/i', '', $string);
  }

  private const CORP_PATTERN = '/\s(?:llcs?|ltd|(?:un)?limited|inc(?:orporated)?|corp(?:oration)?)\.?$/i';
  static function discardIncorporation(string $string) : string {
    return preg_replace(self::CORP_PATTERN, '', $string);
  }

  private const ORG_GROUP_PATTERN = '/\s(?:Co|Group|Record(?:ing)? Co(?:mpany)?|Record Label|Publishing(?: Group)?|Productions|Music(?: (?:Group|Publishing))?|International|Entertainment(?: Group)?)$/i';
  static function discardOrganizationGroup(string $string) : string {
    return preg_replace(self::ORG_GROUP_PATTERN, '', $string);
  }

  private const DISCARD_LABEL_REDUNDANCIES_PATTERN = '/\s(?:Records|Recordings)$/i';
  static function discardLabelNameRedundancies(string $string) : string {
    return preg_replace(self::DISCARD_LABEL_REDUNDANCIES_PATTERN, '', $string);
  }

  private const DISCARD_COUNTRY_PATTERN = '/\s(?:USA|UK|France|Japan|Germany)$/i';
  static function discardCountrySuffixes(string $string) : string {
    return preg_replace(self::DISCARD_COUNTRY_PATTERN, '', $string);
  }

}
