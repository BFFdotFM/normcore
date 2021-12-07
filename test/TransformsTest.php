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
    $this->assertEquals('...And you will know us by the trail of dead', Transforms::normalizeUnicode('…And you will know us by the trail of dead'));
    $this->assertEquals('STARGATE', Transforms::normalizeUnicode('STARGÅTE'));
  }

  public function testUnicodeNormalizationDoesNotStripValuableCharacters(): void {
    $this->assertEquals('Sigur Ros - ()', Transforms::normalizeUnicode('Sigur Rós - ()'));
  }

  public function testStylisticFlatteningRemovesAmbiguouslyDecorativeCharacters(): void {
    $this->assertEquals('…And you will know us by the trail of dead', Transforms::normalizeStylisticCharacters('...And you will know us by the trail of dead'));
  }

  public function testStylisticNormalizationConvergesInconsistentRepresentations(): void {
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
    $this->assertEquals('strokes', Transforms::filterRedundantWords('the strokes'));
    $this->assertEquals('the', Transforms::filterRedundantWords('the the'), 'Transform stripped even when resultant string was empty');
    $this->assertEquals('and', Transforms::filterRedundantWords('the and'), 'Transform stripped even when resultant string was empty');
    $this->assertEquals('night at opera', Transforms::filterRedundantWords('A night at the opera'));
    $this->assertEquals('Hand That Feeds', Transforms::filterRedundantWords('The Hand That Feeds'));
  }

  public function testDiscardsFeaturedGuestArtists(): void {
    $this->assertEquals('Open Mike Eagle', Transforms::discardContributors('Open Mike Eagle feat. Video Dave'));
    $this->assertEquals('Open Mike Eagle', Transforms::discardContributors('Open Mike Eagle ft. Kari Faux'));
    $this->assertEquals('Open Mike Eagle', Transforms::discardContributors('Open Mike Eagle featuring Lil A$e'));
    $this->assertEquals('Open Mike Eagle', Transforms::discardContributors('Open Mike Eagle (feat. Video Dave)'));
  }

  public function testDiscardLicensingBlurb() : void {
    $this->assertEquals('Concord Music Group, Inc.', Transforms::discardLicensingBlurb('A&M Records Under Exclusive License to Concord Music Group, Inc.'));
    $this->assertEquals('Concord Music Group, Inc.', Transforms::discardLicensingBlurb('A&M Records Under License to Concord Music Group, Inc.'));
    $this->assertEquals('A&M Records', Transforms::discardLicensingBlurb('A&M Records Under License from Concord Music Group, Inc.'));
  }

  public function testRemoveTrailingYear() : void {
    $this->assertEquals('Domino', Transforms::removeTrailingYear('Domino 2020'));
    $this->assertEquals('Domino', Transforms::removeTrailingYear('Domino (1997)'));
    $this->assertEquals('Domino', Transforms::removeTrailingYear('Domino (1997/2021)'));
    $this->assertEquals('4AD', Transforms::removeTrailingYear('4AD'));
    $this->assertEquals('Matchbox 20', Transforms::removeTrailingYear('Matchbox 20'));
  }

  public function testDiscardCopyrightStatements() : void {
    $this->assertEquals('4AD', Transforms::discardCopyright('4AD Copyright Control'));
    $this->assertEquals('4AD', Transforms::discardCopyright('4AD All Rights Reserved'));
    $this->assertEquals('4AD', Transforms::discardCopyright('4AD Copyright Control All Rights Reserved'));
  }

  public function testRemovesYearPrefixes() : void {
    $this->assertEquals('4AD', Transforms::discardYearPrefix('(c) 2020 4AD'));
    $this->assertEquals('4AD', Transforms::discardYearPrefix('(P) 2020 4AD'));
    $this->assertEquals('4AD', Transforms::discardYearPrefix('© 2020 4AD'));
    $this->assertEquals('4AD', Transforms::discardYearPrefix('℗ 1979 2019 4AD'));
    $this->assertEquals('4AD', Transforms::discardYearPrefix('2019 4AD'));

  }

  public function testDiscardIncorporation() : void {
    $this->assertEquals('Warner Brothers,', Transforms::discardIncorporation('Warner Brothers, Inc'));
    $this->assertEquals('Warner Brothers', Transforms::discardIncorporation('Warner Brothers LLC'));
    $this->assertEquals('Domino Records', Transforms::discardIncorporation('Domino Records Ltd'));

    $this->assertEquals('The Label', Transforms::discardIncorporation('The Label LLC'));
    $this->assertEquals('The Label', Transforms::discardIncorporation('The Label LLCs'));
    $this->assertEquals('The Label', Transforms::discardIncorporation('The Label Ltd.'));
    $this->assertEquals('The Label', Transforms::discardIncorporation('The Label Limited'));
    $this->assertEquals('The Label', Transforms::discardIncorporation('The Label Unlimited'));
    $this->assertEquals('The Label', Transforms::discardIncorporation('The Label Inc.'));
    $this->assertEquals('The Label', Transforms::discardIncorporation('The Label Corporation'));
    $this->assertEquals('The Label', Transforms::discardIncorporation('The Label Corp'));
  }

  public function testDiscardsGroupNames() : void {
     $this->assertEquals('The Label', Transforms::discardOrganizationGroup('The Label Co'));
     $this->assertEquals('The Label', Transforms::discardOrganizationGroup('The Label Group'));
     $this->assertEquals('The Label', Transforms::discardOrganizationGroup('The Label Recording Company'));
     $this->assertEquals('The Label', Transforms::discardOrganizationGroup('The Label Record Co'));
     $this->assertEquals('The Label', Transforms::discardOrganizationGroup('The Label Recording Co'));
     $this->assertEquals('The Label', Transforms::discardOrganizationGroup('The Label Record Label'));
     $this->assertEquals('The Label', Transforms::discardOrganizationGroup('The Label Publishing'));
     $this->assertEquals('The Label', Transforms::discardOrganizationGroup('The Label Publishing Group'));
     $this->assertEquals('The Label', Transforms::discardOrganizationGroup('The Label Productions'));
     $this->assertEquals('The Label', Transforms::discardOrganizationGroup('The Label Music'));
     $this->assertEquals('The Label', Transforms::discardOrganizationGroup('The Label Music Group'));
     $this->assertEquals('The Label', Transforms::discardOrganizationGroup('The Label Music Publishing'));
     $this->assertEquals('The Label', Transforms::discardOrganizationGroup('The Label International'));
     $this->assertEquals('The Label', Transforms::discardOrganizationGroup('The Label Entertainment'));
     $this->assertEquals('The Label', Transforms::discardOrganizationGroup('The Label Entertainment Group'));
  }

  public function testDiscardLabelNameRedundancies() : void {
    $this->assertEquals('Domino', Transforms::discardLabelNameRedundancies('Domino Records'));
    $this->assertEquals('XL', Transforms::discardLabelNameRedundancies('XL Recordings'));
  }

  public function testDistroKidSelfReleases() : void {
    $this->assertEquals('Self Released', Transforms::handleDistroKidLabels('123456 Records DK'));
    # Matches in the case where other junk data is prefixed
    $this->assertEquals('Self Released', Transforms::handleDistroKidLabels('2010 123456 Records DK'));
    # Some entires in our database have “DK2” as the suffix
    $this->assertEquals('Self Released', Transforms::handleDistroKidLabels('123456 Records DK2'));
    $this->assertEquals('Self Released', Transforms::handleDistroKidLabels('123456 Records'));

    # Allow longer IDs
    $this->assertEquals('Self Released', Transforms::handleDistroKidLabels('12345678 Records'));

    # Require 6-digit ID
    $this->assertEquals('12345 Records', Transforms::handleDistroKidLabels('12345 Records'));
  }

  public function testNormalizeVolumes() : void {
    $this->assertEquals('The Greatest Hits (Volume 2)', Transforms::normalizeVolumes('The Greatest Hits (Volume 2)'));
    $this->assertEquals('The Greatest Hits (Volume 2)', Transforms::normalizeVolumes('The Greatest Hits (Vol. II)'));
    $this->assertEquals('The Greatest Hits (Volume 2)', Transforms::normalizeVolumes('The Greatest Hits (Vol 2)'));
    $this->assertEquals('The Greatest Hits (Volume 2)', Transforms::normalizeVolumes('The Greatest Hits (Vol. 2)'));
    $this->assertEquals('The Greatest Hits (Volume 1)', Transforms::normalizeVolumes('The Greatest Hits [Volume 1]'));
    $this->assertEquals('The Greatest Hits (Volume 1)', Transforms::normalizeVolumes('The Greatest Hits [volume 1]'));
    $this->assertEquals('The Greatest Hits (Volume 3)', Transforms::normalizeVolumes('The Greatest Hits Volume III'));
    $this->assertEquals('The Greatest Hits (Volume 1)', Transforms::normalizeVolumes('The Greatest Hits[Volume I]'));
    $this->assertEquals('The Greatest Hits (Volume 3) [Gold]', Transforms::normalizeVolumes('The Greatest Hits [Volume III] [Gold]'));

    $this->assertEquals('The Greatest Hits (Volume 1)', Transforms::normalizeVolumes('The Greatest Hits Volume One'));
    $this->assertEquals('The Greatest Hits (Volume 5)', Transforms::normalizeVolumes('The Greatest Hits Volume Five'));
    $this->assertEquals('The Greatest Hits (Volume 10)', Transforms::normalizeVolumes('The Greatest Hits Volume Ten'));

    $this->assertEquals('The Greatest Hits [Volume IV]', Transforms::normalizeVolumes('The Greatest Hits [Volume IV]'), 'Not expected to support full Roman Numerals implementation');
  }
}

