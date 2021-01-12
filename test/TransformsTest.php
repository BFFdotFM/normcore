<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use BFFdotFM\Normcore\Transforms;

final class TransformsTest extends TestCase {

  public function testTransformsStringToLowercase(): void {
    $this->assertEquals(Transforms::downCase('HELLO'), 'hello');
  }

  # This is probably going to need a lot more tests given the unicode rule soup we're using  
  public function testNormalizesExtendedCharactersToAscii(): void {
    $this->assertEquals(Transforms::normalizeUnicode('Hæloes'), 'Haeloes');
    $this->assertEquals(Transforms::normalizeUnicode('Mötorhead'), 'Motorhead');
    $this->assertEquals(Transforms::normalizeUnicode('Françious la Frénch'), 'Francious la French');
    $this->assertEquals(Transforms::normalizeUnicode('And you will know us by the trail of dead…'), 'And you will know us by the trail of dead...');
    $this->assertEquals(Transforms::normalizeUnicode('STARGÅTE'), 'STARGATE');
  }
    
  public function testUnicodeNormalizationDoesNotStripValuableCharacters(): void {
    $this->assertEquals(Transforms::normalizeUnicode('Sigur Rós - ()'), 'Sigur Ros - ()');
  }
  
  public function testStylisticNormalizationRemovesAmbiguouslyDecorativeCharacters(): void {
    $this->assertEquals(Transforms::flattenStylisticCharacters('A$AP Rocky'), 'ASAP Rocky');
  }
  
  public function testRemovesPunctuation(): void {
    $this->assertEquals(Transforms::removePunctuation('R.E.M.'), 'REM');
    $this->assertEquals(Transforms::removePunctuation('Godspeed You! Black Emperor'), 'Godspeed You Black Emperor');
    $this->assertEquals(Transforms::removePunctuation('Death From Above 1969'), 'Death From Above 1969');
  }
  
  public function testFiltersRedundantWords(): void {
    $this->assertEquals(Transforms::filterRedundantWords('belle and sebastian'), 'belle sebastian');
    $this->assertEquals(Transforms::filterRedundantWords('the strokes'), 'the strokes');
    $this->assertEquals(Transforms::filterRedundantWords('the the'), 'the');
  }
  
  public function testDiscardsFeaturedGuestArtists(): void {
    $this->assertEquals(Transforms::discardContributors('Open Mike Eagle feat. Video Dave'), 'Open Mike Eagle');
    $this->assertEquals(Transforms::discardContributors('Open Mike Eagle ft. Kari Faux'), 'Open Mike Eagle');
    $this->assertEquals(Transforms::discardContributors('Open Mike Eagle featuring Lil A$e'), 'Open Mike Eagle');
  }
}
