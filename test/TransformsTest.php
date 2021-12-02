<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use BFFdotFM\Normcore\Transforms;

final class TransformsTest extends TestCase {

  public function testTransformsStringToLowercase(): void {
    $this->assertEquals('hello', Transforms::downCase('HELLO'));
  }

  # This is probably going to need a lot more tests given the unicode rule soup we're using
  public function testNormalizesExtendedCharactersToAscii(): void {
    $this->assertEquals('Haeloes', Transforms::normalizeUnicode('Hæloes'));
    $this->assertEquals('Motorhead', Transforms::normalizeUnicode('Mötorhead'));
    $this->assertEquals('Francious la French', Transforms::normalizeUnicode('Françious la Frénch'));
    $this->assertEquals('And you will know us by the trail of dead...', Transforms::normalizeUnicode('And you will know us by the trail of dead…'));
    $this->assertEquals('STARGATE', Transforms::normalizeUnicode('STARGÅTE'));
  }

  public function testUnicodeNormalizationDoesNotStripValuableCharacters(): void {
    $this->assertEquals('Sigur Ros - ()', Transforms::normalizeUnicode('Sigur Rós - ()'));
  }

  public function testStylisticNormalizationRemovesAmbiguouslyDecorativeCharacters(): void {
    $this->assertEquals('ASAP Rocky', Transforms::flattenStylisticCharacters('A$AP Rocky'));
  }

  public function testRemovesPunctuation(): void {
    $this->assertEquals('REM', Transforms::removePunctuation('R.E.M.'));
    $this->assertEquals('Godspeed You Black Emperor', Transforms::removePunctuation('Godspeed You! Black Emperor'));
    $this->assertEquals('Death From Above 1969', Transforms::removePunctuation('Death From Above 1969'));
  }

  public function testTrimsPunctuation(): void {
    $this->assertEquals('Enter: a bear', Transforms::trimPunctuation('Enter: a bear.'));
    $this->assertEquals('Godspeed You! Black Emperor', Transforms::trimPunctuation('Godspeed You! Black Emperor'));
    $this->assertEquals('And You Will Know Us By', Transforms::trimPunctuation('And You Will Know Us By...'));
    $this->assertEquals('The Strokes', Transforms::trimPunctuation(', The Strokes.'));
    $this->assertEquals('Franz Ferdinand', Transforms::trimPunctuation('Franz Ferdinand: '));
  }

  public function testFiltersRedundantWords(): void {
    $this->assertEquals('belle sebastian', Transforms::filterRedundantWords('belle and sebastian'));
    $this->assertEquals('the strokes', Transforms::filterRedundantWords('the strokes'));
    $this->assertEquals('the', Transforms::filterRedundantWords('the the'), 'Transform stripped even when resultant string was empty');
  }

  public function testDiscardsFeaturedGuestArtists(): void {
    $this->assertEquals('Open Mike Eagle', Transforms::discardContributors('Open Mike Eagle feat. Video Dave'));
    $this->assertEquals('Open Mike Eagle', Transforms::discardContributors('Open Mike Eagle ft. Kari Faux'));
    $this->assertEquals('Open Mike Eagle', Transforms::discardContributors('Open Mike Eagle featuring Lil A$e'));
    $this->assertEquals('Open Mike Eagle', Transforms::discardContributors('Open Mike Eagle (feat. Video Dave)'));
  }

  public function testDiscardLicensingBlurb() : void {
    $this->assertEquals('A&M Records', Transforms::discardLicensingBlurb('A&M Records Under Exclusive License to Concord Music Group, Inc.'));
    $this->assertEquals('A&M Records', Transforms::discardLicensingBlurb('A&M Records Under License to Concord Music Group, Inc.'));
  }

  public function testRemoveTrailingYear() : void {
    $this->assertEquals('Domino', Transforms::removeTrailingYear('Domino 2020'));
    $this->assertEquals('4AD', Transforms::removeTrailingYear('4AD'));
    $this->assertEquals('Matchbox 20', Transforms::removeTrailingYear('Matchbox 20'));
  }

  public function testDiscardCopyrightStatements() : void {
    $this->assertEquals('4AD', Transforms::discardCopyright('(c) 2020 4AD'));
    $this->assertEquals('4AD', Transforms::discardCopyright('(P) 2020 4AD'));
    $this->assertEquals('4AD', Transforms::discardCopyright('© 2020 4AD'));
    $this->assertEquals('4AD', Transforms::discardCopyright('℗ 1979 2019 4AD'));
    $this->assertEquals('4AD', Transforms::discardCopyright('℗ 1979 4AD Copyright Control'));
  }

  public function testDiscardIncorporation() : void {
    $this->assertEquals('Warner Brothers', Transforms::discardIncorporation('Warner Brothers, Inc'));
    $this->assertEquals('Warner Brothers', Transforms::discardIncorporation('Warner Brothers LLC'));
    $this->assertEquals('Domino Records', Transforms::discardIncorporation('Domino Records Ltd'));
  }

  public function testDiscardLabelNameRedundancies() : void {
    $this->assertEquals('Domino', Transforms::discardLabelNameRedundancies('Domino Records'));
    $this->assertEquals('XL', Transforms::discardLabelNameRedundancies('XL Recordings'));
  }
}

