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

  /** Store the unicode transliterator */
  private static $ut = null;

  /** Convert Unicode to ASCII representation  */
  static function normalizeUnicode(string $string) : string {
    # Only instantiate one transliterator per run, as the creation is very expensive.
    if (!isset(self::$ut)) {
      self::$ut = Transliterator::createFromRules(':: Any-Latin; :: Latin-ASCII; :: NFD; :: [:Nonspacing Mark:] Remove; :: NFC;', Transliterator::FORWARD);
    }
    $result = self::$ut->transliterate($string);
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
    $parts = preg_split('/\s(?:,\s|\(|\[)?(?:ft|feat|featuring|w\/)\.?\s/i', $string);
    return array_shift($parts);
  }

  #!!! Album Name Clean Functions

  static function discardEpLpSuffix(string $string) : string {
    return preg_replace('/\s(?:EP|LP|(?:(?:12"|7")(?: version)?))$/i', '', $string);
  }

  static function discardDiscNumber(string $string) : string {
    return preg_replace('/\s(?:-\s?|\[|\(|\s)?Dis[ck] \d+(?:[\]\)]|$)/i', '', $string);
  }


  private const NUMBER_WORDS = array('_', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten');
  static function normalizeVolumes(string $string) : string {
    return preg_replace_callback('/,?\s?(?:\[|\(|\s)?Vol(?:ume)?(?:\.\s?|\s)(?:(\d+)|(I+)|(one|two|three|four|five|six|seven|eight|nine|ten))(?:\]|\)|\b)(\s?)/i', function ($matches) {
      # 0: Match
      # 1: Numeric match
      # 2: III match

      if (isset($matches[3]) && $index = array_search(strtolower($matches[3]), self::NUMBER_WORDS)) {
        $volume = $index;
      } elseif (isset($matches[2]) && !empty($matches[2])) {
        # Convert III to number
        # We only support “III”, not full Roman numerals, so just count how many
        $volume = strlen($matches[2]);
      } else {
        $volume = $matches[1];
      }

      return sprintf(' (Volume %d)%s', $volume, $matches[4] ?? '');
    }, $string);
  }

  # discardExplicit/Clean tags
  static function discardExplicitWarning(string $string) : string {
    return preg_replace('/\s?[\[\(](?:clean|explicit|dirty|radio)(?: (?:version|edits?))?[\]\)]/i', '', $string);
  }

  static function discardRemasters(string $string) : string {
    # Remove remaster tags
    return preg_replace('/(?:[\s\/]|\s[\(\[])(?:\d{4} )?(?:Digital )?Remaster(?:ed)?( \d{4})?(?: (?:version|edition))?(?:[\)\]]|$)/i', '', $string);
  }

  static function discardSpecialEditions(string $string) : string {

    // '/(?: -|(?:\(|\[))/'
    // '/(?:\]|\))/'

    # (Deluxe) [Deluxe]
    $working = preg_replace('/ - Deluxe$/i', '', $string);
    $working = preg_replace('/\s?(?:\(|\[)Deluxe(?:\]|\))/i', '', $working);

    # Special Edition
    # Deluxe Edition
    # Deluxe Version
    # Super Deluxe Version
    # xxth Anniversary Edition
    # Expanded xxth Anniversary Edition
    # xxth Anniversary Deluxe Edition
    # 10 Year Anniversary Deluxe Edition
    # Expanded Edition
    # Extended Edition
    # (Limited Edition)
    # Legacy Edition
    # Collector's Edition
    # Gold/Silver/Chrome/Platinum Edition
    # Definitive Edition
    # (0000 Edition)
    $matchContent = '(?:(?:Super|Special|Deluxe|(?:(?:\d+[sthrd]{2}|\d+ Year) )?Anniversary|Expanded|Extended|Limited|Legacy|Collector\'?s|Gold|Silver|Chrome|Platinum|Definitive|Remaster(?:ed)?|\d{4}) )+(?:Edition|Version)';

    $working = preg_replace('/ - ' . $matchContent . '$/i', '', $working);
    $working = preg_replace('/\s?(?:\(|\[)' . $matchContent . '(?:\]|\))/i', '', $working);

    $working = preg_replace('/\s(?:Special|Deluxe) (?:Version|Edition)$/i', '', $working);

    return $working;
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
