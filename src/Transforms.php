<?php

namespace BFFdotFM\Normcore;

use Normalizer;
use Transliterator;

class Transforms {

  static function downCase($string) {
    return strtolower($string);
  }

  static function normalizeUnicode($string) {
    $transliterator = Transliterator::createFromRules(':: Any-Latin; :: Latin-ASCII; :: NFD; :: [:Nonspacing Mark:] Remove; :: NFC;', Transliterator::FORWARD);
    return $transliterator->transliterate($string);
  }

  static function flattenStylisticCharacters($string) {
    return str_replace('$', 'S', $string);
  }

  # TODO: Determine if this should strip to no punctuation or extra whitespace
  # e.g. R.E.M. should be REM, not R E M?
  # Will we ultimately drop all whitespace, in which case …moot?
  static function removePunctuation($string) {
    return preg_replace('/[^a-z\d\s]+/i', '', $string);
  }


  public const REDUNDANT_WORDS = array('the', 'and');
  # 'and' must go else clashes with '&'
  # not sure impact of 'the'?
  static function filterRedundantWords($string) {
    return implode(' ', array_filter(preg_split('/(\s)+/', $string), function ($word, $index) {
      # Ignore first word
      return ($index === 0) || !(empty($word) || in_array($word, self::REDUNDANT_WORDS));
    }, ARRAY_FILTER_USE_BOTH));
  }

  static function removeWhitespace($string) {
    return preg_replace('/\s+/', '', $string);
  }

  # Must match the insides of words.
  # Should this depend on punctuation for ft. and feat. ? Query all 'featuring' and 'feat' examples from DB
  static function discardContributors($string) {
    $parts = preg_split('/\s(?:ft|feat|featuring)\.?\s/', $string);
    return array_shift($parts);
  }

}
