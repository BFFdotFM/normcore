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
    # Pure-ASCII strings transliterate to themselves, so skip the expensive ICU call.
    # Measured ~58x faster on the ~96% of real inputs that are already ASCII, with no
    # change in output.
    if (!preg_match('/[^\x00-\x7F]/', $string)) {
      return trim($string);
    }
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
    # Collapse any run of three or more dots to a single ellipsis, so '...' and '....' both
    # normalise (str_replace('...', …) would leave a trailing dot on four-dot strings).
    return preg_replace('/\.{3,}/', '…', $string);
  }

  /** Fold typographic (smart) quotes onto their ASCII equivalents */
  static function normalizeQuoteCharacters(string $string) : string {
    # Primes (U+2032 ′ / U+2033 ″) are intentionally excluded — mapping ″ to " would
    # collide with the 7"/12" matching in discardEpLpSuffix.
    return str_replace(
      array("\u{2018}", "\u{2019}", "\u{201A}", "\u{201B}",   # ‘ ’ ‚ ‛  -> '
            "\u{201C}", "\u{201D}", "\u{201E}", "\u{201F}"),   # “ ” „ ‟  -> "
      array("'", "'", "'", "'", '"', '"', '"', '"'),
      $string
    );
  }

  /**
   * Strip non-printing characters and normalise exotic whitespace.
   *
   * Does more than its name suggests: newlines/tabs become spaces, Unicode format
   * characters (Cf — LRM/RLM, zero-width space/joiner, BOM, soft hyphen) are removed,
   * Unicode separators (Zs/Zl/Zp — NBSP, narrow/figure spaces, line/paragraph separators)
   * collapse to a regular space for the subsequent trim/whitespace steps, and remaining
   * C0/C1 control characters (Cc) are removed. The POSIX [[:cntrl:]] / category Cc class
   * does NOT match Cf or Zs, which is why those are handled explicitly — a trailing LRM
   * after a space (e.g. "Backs Records ‎") otherwise blocks trim() and persists noise.
   */
  static function removeControlCharacters(string $string) : string {
    $string = str_replace(array("\n", "\t"), ' ', $string);
    $string = preg_replace('/\p{Cf}/u', '', $string);
    $string = preg_replace('/[\p{Zs}\p{Zl}\p{Zp}]/u', ' ', $string);
    return preg_replace('/[[:cntrl:]]/', '', $string);
  }

  static function removePunctuation(string $string) : string {
    return preg_replace('/[^a-z\d\s]+/i', '', $string);
  }

  static function removePhrasePunctuation(string $string) : string {
    # Apostrophes (') are intentionally retained so label names like "Don't Be Afraid"
    # stay readable and curly/straight variants converge on the apostrophe'd form.
    return preg_replace('/[.,"`;:\(\)\[\]\\<>]+/', '', $string);
  }

  static function removePlaceholderPunctuation(string $string) : string {
    return preg_replace('/[\-_\?`]+/', '', $string);
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
    return trim($string, " \n\r\t\v\0,.:;/\\'-*");
  }

  /**
   * Remove joining words that could otherwise ambiguously pollute strings
   */
  static function filterRedundantWords(string $string) : string {
    # NB: the trailing \s (not a word boundary) is intentional — a redundant word at the
    # very end of a string is deliberately retained, so 'The The' keys to 'the' and
    # 'The And' to 'and' rather than collapsing to empty. (The original wrote [\s\b],
    # but \b inside a character class is a backspace, not a boundary, so this preserves
    # the existing behaviour while dropping the misleading token.)
    return preg_replace('/\b(?:the|and|a)\s/i', '', $string);
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
    if (preg_match('/\d{5,} (Record(?:ing)?s(?: DK)?|DK)/i', $string)) {
      return 'Self Released';
    }
    return $string;
  }

  /**
   * Split and discard extraneous licensing blurb sometimes included in label credits pasted from Spotify, etc.
   */
  static function discardLicensingBlurb(string $string) : string {
    # Phrases that make the second part of the string relevant. licen[cs]e matches both the
    # US (license) and British (licence) spellings, which both appear in the crowd data.
    # "u/l" is a common crowd abbreviation of "under licen[cs]e to" and appears both with
    # and without a trailing "to" (e.g. "Mark Ronson u/l to Sony", "Kinks u/l Sony").
    $parts = preg_split('/\s(?:under exclusive licen[cs]e to|under licen[cs]e to|exclusively licen[cs]ed to|u\/l(?: to)?)\.?\s/i', $string);
    if (count($parts) > 1) {
      $string = $parts[1];
    }

    # Phrases that make the first part of the string relevant
    $parts = preg_split('/\s(?:(?:exclusively )?distributed by|under(?: exclusive)? licen[cs]e|exclusively licen[cs]ed|a division of|a div\.? of|marketed by|rights management|in partnership with|in association with)\.?\s/i', $string);

    return array_shift($parts);
  }

  /**
   * Strip copyright/publishing boilerplate that streaming feeds inject around the label
   * name: filler lead-ins ("This compilation", "All rights reserved by", "Originally
   * released") and embedded (P)/(C) year runs or bare comma/space-separated year lists.
   *
   * NB: the lead-ins are removed but any label name that follows them is retained — e.g.
   * "All rights reserved by Sony Music Entertainment" keeps "Sony Music Entertainment" for
   * the downstream suffix strippers. The leading lone ℗/© glyph is handled by
   * discardYearPrefix.
   */
  static function discardCopyrightScaffolding(string $string) : string {
    $working = preg_replace('/\bthis compilation\b/i', '', $string);
    $working = preg_replace('/\ball rights reserved by\b/i', '', $working);
    $working = preg_replace('/\boriginally released(?: by| in)?\b/i', '', $working);
    # A leading copyright run: anchored on a ℗/©/(P)/(C) glyph and consuming the chain of
    # connectors that follows it — further glyphs, "&"/"and"/commas, and years (with the
    # optional parenthetical track annotations streaming feeds add, e.g. "1978(2)" or
    # "1982(1,3-10)"). Handles "(P) & (C) 2010 …", "(P) and © …", "℗ 1978(2) 1982(1,3-10) …".
    # (?<!\d)\d{4}(?!\d) so a 4-digit year is never matched inside a longer digit run such
    # as a 7-digit DistroKid catalogue id (e.g. "1150200 Records DK"), which must reach
    # handleDistroKidLabels intact.
    $year = '(?<!\d)\d{4}(?!\d)(?:\s*\([0-9,\s\x{2013}-]+\))?';
    $atom = "(?:\\([pc]\\)|©|℗|&|and\\b|,|$year)";
    $working = preg_replace("/^\\s*(?:\\([pc]\\)|©|℗)\\s*(?:$atom\\s*)*/iu", '', $working);
    # Embedded (P)/(C) copyright markers with their year.
    $working = preg_replace('/\(?[pc]\)?\s*\d{4}\b/i', '', $working);
    # Runs of two or more years (comma/space separated). Requiring 2+ avoids eating a
    # single leading year that is itself the label name (e.g. "1985 Music" → "1985").
    $working = preg_replace('/\b\d{4}(?:[,\s]+\d{4})+\b/', '', $working);
    return trim($working);
  }

  /**
   * Discard a trailing corporate-relationship clause, e.g.
   * "Rhino Entertainment, a Warner Music Group Company" or
   * "XXIM Records, a label of Sony Music Entertainment".
   *
   * Anchored on a comma, with an optional article ("a"/"an"), so it fires on the
   * appositive relationship form including article-less variants like
   * "Asylum Records UK, division of Warner Music UK Ltd". Names that merely contain
   * "Company"/"Label" without a comma lead-in (e.g. "Not On Label",
   * "The Beautiful Music Company") are left intact.
   */
  static function discardCorporateParent(string $string) : string {
    return preg_replace('/,\s*(?:an?\s+)?[^,]*?\b(?:company|division|label of|music group)\b.*$/i', '', $string);
  }

  /**
   * Discard distribution / manufacturing / release-attribution scaffolding that frames the
   * real label, e.g. "… Marketed by Rhino Entertainment Company", "manufactured and
   * marketed by …", or the leading "A Geffen Records Release; ℗ 1966 UMG …" form.
   */
  static function discardDistributionClause(string $string) : string {
    # Leading "A/An <Name> Records Release; <copyright tail>" — keep the named label.
    $working = preg_replace('/^\s*an?\s+(.+?\b(?:Records?|Recordings?))\s+Release\b.*$/i', '$1', $string);
    # Drop a semicolon-delimited tail when what follows is clearly a copyright statement
    # (℗/© glyph, "(P)/(C) year", or "this compilation"). A bare year after the semicolon
    # is NOT treated as a tail — the label name often follows the year (e.g.
    # "℗ 1983; 2007 The Compact Organization"), so those are left for the year strippers.
    $working = preg_replace('/;\s*(?=(?:℗|©|\([pc]\)\s*\d{4}|this compilation)).*$/i', '', $working);
    # Marketing / manufacturing / distribution clauses to the end of the string.
    $working = preg_replace('/[.,;]?\s+(?:manufactured(?: and marketed)?(?: by)?|marketed by|distributed by)\b.*$/i', '', $working);
    $working = preg_replace('/\s+manufactured and\s*$/i', '', $working);
    return trim($working);
  }

  /**
   * Discard a trailing "doing business as" alias, keeping the trading name, e.g.
   * "Kemado Records, Inc. d/b/a Anthology Recordings" → "Anthology Recordings".
   */
  static function discardDoingBusinessAs(string $string) : string {
    return preg_replace('/^.*\bd\/b\/a\s+/i', '', $string);
  }

  /**
   * Discard a trailing territorial-rights clause, e.g. "… for the United States",
   * "… for the world outside of the US", "… for USA", or "… in the USA". Both the "for"/
   * "in" preposition and the article are flexible, so a stray "for"/"in the" is not left
   * behind when the country word is later stripped.
   */
  static function discardTerritory(string $string) : string {
    return preg_replace('/[.,]?\s+(?:for|in)(?: the)?\s+(?:us|u\.s\.?|usa|uk|world|united states|territory of|rest of)\b.*$/i', '', $string);
  }

  /**
   * Remove a trailing website URL sometimes appended to label credits, e.g.
   * "AMDISCS: Futures Reserve Label. www.amdiscs.com". Strips only the URL.
   */
  static function discardTrailingUrl(string $string) : string {
    return preg_replace('/[.\s]*(?:visit\s+)?(?:https?:\/\/|www\.)\S+\s*$/i', '', $string);
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
    # ℗ Western Vinyl  (lone leading symbol, no year — the year run is optional so the
    # copyright glyph is still stripped from streaming credits that omit a year)
    return preg_replace('/^(?:c |p |\(c\)|\(p\)|©|℗)?(?: originally released(?: by| in)?)?\s*(?:\d{4}\s)*/i', '', $string);
  }

  static function discardCopyright(string $string) : string {
    # Remove 'Copyright Control' text from end of string
    return preg_replace('/\s(?:Copyright Control)?\s*(?:All Rights Reserved)?$/i', '', $string);
  }

  static function discardIncorporation(string $string) : string {
    return preg_replace('/\s(?:llcs?|ltd|(?:un)?limited|inc(?:orporated)?|corp(?:oration)?)\.?$/i', '', $string);
  }

  static function discardOrganizationGroup(string $string) : string {
    # NB: "International" is intentionally NOT stripped — in the crowd data it is almost
    # always part of the label name (e.g. "Philadelphia International", "DJ International",
    # "Amnesty International"), not a redundant organisational suffix.
    return preg_replace('/\s(?:Co|Group|Record(?:ing)? Co(?:mpany)?|Record Label|Publishing(?: Group)?|Productions|Music(?: (?:Group|Publishing))?|Entertainment(?: Group)?)$/i', '', $string);
  }

  static function discardLabelNameRedundancies(string $string) : string {
    # "Records"/"Recordings" and the singular gerund "Recording" (e.g. "Atlantic
    # Recording") are redundant industry suffixes. The bare singular "Record" is NOT — it
    # is usually part of the name (e.g. "Sympathy for the Record"), so it is left alone.
    # Also keep "Records" when joined by &/+/and, where it reads as part of the name
    # (e.g. "ABKCO Music & Records") rather than a suffix.
    if (preg_match('/(?:&|\+|\band)\s+(?:Records|Recordings?)$/i', $string)) {
      return $string;
    }
    return preg_replace('/\s(?:Records|Recordings?)$/i', '', $string);
  }

  static function discardCountrySuffixes(string $string) : string {
    return preg_replace('/\s(?:USA|UK|France|Japan|Germany)$/i', '', $string);
  }

}
