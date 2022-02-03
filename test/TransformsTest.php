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

  public function testRemovesControlCharacters(): void {
    $this->assertEquals('Under Control', Transforms::removeControlCharacters('Under Control'));
    $this->assertEquals('Under Control', Transforms::removeControlCharacters('Under Control'));
    $this->assertEquals('Under Control', Transforms::removeControlCharacters("Under\tControl"));
    $this->assertEquals('Under Control', Transforms::removeControlCharacters("Under\nControl"));
    $this->assertEquals('Under Control', Transforms::removeControlCharacters("Under\r\nControl"));
    $this->assertEquals('Under Control', Transforms::removeControlCharacters("Under Control\r"));
  }

  public function testRemovesPunctuation(): void {
    $this->assertEquals('REM', Transforms::removePunctuation('R.E.M.'));
    $this->assertEquals('Godspeed You Black Emperor', Transforms::removePunctuation('Godspeed You! Black Emperor'));
    $this->assertEquals('Death From Above 1969', Transforms::removePunctuation('Death From Above 1969'));
  }

  public function testRemovesPlaceholderPunctuation(): void {
    $this->assertEquals('', Transforms::removePlaceholderPunctuation('-'));
    $this->assertEquals('', Transforms::removePlaceholderPunctuation('?'));
    $this->assertEquals('', Transforms::removePlaceholderPunctuation('_'));
    $this->assertEquals('', Transforms::removePlaceholderPunctuation('`'));
    $this->assertEquals('WHY', Transforms::removePunctuation('WHY?'));
    $this->assertEquals('NeOh', Transforms::removePunctuation('Ne-Oh'));
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
    $this->assertEquals('100 gecs - gecgecgec (Remix)', Transforms::discardContributors('100 gecs - gecgecgec (Remix) [feat. Lil West and Tony Velour]'));
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
    $this->assertEquals('4AD', Transforms::discardYearPrefix('P 2020 4AD'));
    $this->assertEquals('4AD', Transforms::discardYearPrefix('C 2020 4AD'));
    $this->assertEquals('4AD', Transforms::discardYearPrefix('© 2020 4AD'));
    $this->assertEquals('4AD', Transforms::discardYearPrefix('℗ 1979 2019 4AD'));
    $this->assertEquals('4AD', Transforms::discardYearPrefix('2019 4AD'));
    $this->assertEquals('Flower Moon', Transforms::discardYearPrefix('℗ 2001 Flower Moon'));
    $this->assertEquals('Sony Music', Transforms::discardYearPrefix('℗ Originally Released 1958 1959 Sony Music'));
    $this->assertEquals('Sony Music', Transforms::discardYearPrefix('℗ Originally Released 1966 Sony Music'));
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
    $this->assertEquals('The Greatest Hits (Volume 2)', Transforms::normalizeVolumes('The Greatest Hits (Vol.2)'));
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

  public function testRemovesRemaster() : void {
    $this->assertEquals('Movement Era Singles', Transforms::discardRemasters('Movement Era Singles (2008 Remaster)'));
    $this->assertEquals('1983-1988', Transforms::discardRemasters('1983-1988 Remastered'));
    $this->assertEquals('20/20', Transforms::discardRemasters('20/20 (Remastered)'));
    $this->assertEquals('Aladdin Sane', Transforms::discardRemasters('Aladdin Sane [Remastered 2013]'));
    $this->assertEquals('Aladdin Sane', Transforms::discardRemasters('Aladdin Sane [Remastered]'));
    $this->assertEquals('Garbage (20th Anniversary Super Deluxe Edition)', Transforms::discardRemasters('Garbage (20th Anniversary Super Deluxe Edition) [Remastered]'));
    $this->assertEquals('Life After Death', Transforms::discardRemasters('Life After Death (Remastered Edition)'));
  }

  public function testDiscardEpLpSuffixes() : void {
    $this->assertEquals('The Optimist', Transforms::discardEpLpSuffix('The Optimist LP'));
    $this->assertEquals('The Optimist', Transforms::discardEpLpSuffix('The Optimist EP'));
    $this->assertEquals('The Optimist', Transforms::discardEpLpSuffix('The Optimist 7"'));
    $this->assertEquals('The Optimist', Transforms::discardEpLpSuffix('The Optimist 12" Version'));
  }

  public function testDiscardDeluxeEditions() : void {
    $this->assertEquals('Special Edition Grand Master Deluxe', Transforms::discardSpecialEditions('Special Edition Grand Master Deluxe'));
    $this->assertEquals('The Contino Sessions', Transforms::discardSpecialEditions('The Contino Sessions (Special Edition)'));
    $this->assertEquals('Blue', Transforms::discardSpecialEditions('Blue [Special Edition]'));
    $this->assertEquals('forevher', Transforms::discardSpecialEditions('forevher (deluxe edition)'));
    $this->assertEquals('Garbage [Remastered]', Transforms::discardSpecialEditions('Garbage (Super Deluxe Edition) [Remastered]'));

    $this->assertEquals('The Album', Transforms::discardSpecialEditions('The Album (Deluxe)'));
    $this->assertEquals('The Album', Transforms::discardSpecialEditions('The Album [Deluxe]'));
    $this->assertEquals('The Album', Transforms::discardSpecialEditions('The Album - Deluxe'));
    $this->assertEquals('The Album', Transforms::discardSpecialEditions('The Album - Special Edition'));
    $this->assertEquals('The Album', Transforms::discardSpecialEditions('The Album - Deluxe Edition'));
    $this->assertEquals('The Album', Transforms::discardSpecialEditions('The Album (Deluxe Version)'));
    $this->assertEquals('The Album', Transforms::discardSpecialEditions('The Album (Super Deluxe Version)'));
    $this->assertEquals('The Album', Transforms::discardSpecialEditions('The Album - 9th Anniversary Edition'));
    $this->assertEquals('The Album', Transforms::discardSpecialEditions('The Album (Expanded 12th Anniversary Version)'));
    $this->assertEquals('The Album', Transforms::discardSpecialEditions('The Album (20th Anniversary Deluxe Edition)'));
    $this->assertEquals('The Album', Transforms::discardSpecialEditions('The Album (3rd Anniversary Deluxe Edition)'));
    $this->assertEquals('The Album', Transforms::discardSpecialEditions('The Album (101st Anniversary Deluxe Edition)'));
    $this->assertEquals('The Album', Transforms::discardSpecialEditions('The Album (10 Year Anniversary Deluxe Edition)'));
    $this->assertEquals('The Album', Transforms::discardSpecialEditions('The Album (Expanded Edition)'));
    $this->assertEquals('The Album', Transforms::discardSpecialEditions('The Album (Extended 2021 Version)'));
    $this->assertEquals('The Album', Transforms::discardSpecialEditions('The Album (Limited Edition)'));
    $this->assertEquals('The Album', Transforms::discardSpecialEditions('The Album [Legacy Edition]'));
    $this->assertEquals('The Album', Transforms::discardSpecialEditions('The Album (Collector\'s Edition)'));
    $this->assertEquals('The Album', Transforms::discardSpecialEditions('The Album (2020 Collectors Edition)'));
    $this->assertEquals('The Album', Transforms::discardSpecialEditions('The Album (Gold Edition)'));
    $this->assertEquals('The Album', Transforms::discardSpecialEditions('The Album [Gold Silver Edition]'));
    $this->assertEquals('The Album', Transforms::discardSpecialEditions('The Album - Chrome Edition'));
    $this->assertEquals('The Album', Transforms::discardSpecialEditions('The Album (Platinum Version)'));
    $this->assertEquals('The Album', Transforms::discardSpecialEditions('The Album - Definitive Edition'));
    $this->assertEquals('The Album', Transforms::discardSpecialEditions('The Album (1998 Version)'));
    $this->assertEquals('The Album', Transforms::discardSpecialEditions('The Album Special Edition'));
  }

  public function testExplicitContentWarningsAreRemoved() : void {
    $this->assertEquals('This Song Spits Fire', Transforms::discardExplicitWarning('This Song Spits Fire (Radio Edit)'));
    $this->assertEquals('This Song Spits Fire', Transforms::discardExplicitWarning('This Song Spits Fire [Clean]'));
    $this->assertEquals('This Song Spits Fire', Transforms::discardExplicitWarning('This Song Spits Fire [Explicit]'));
    $this->assertEquals('This Song Spits Fire', Transforms::discardExplicitWarning('This Song Spits Fire [Clean Version]'));
    $this->assertEquals('This Song Spits Fire', Transforms::discardExplicitWarning('This Song Spits Fire (clean edits)'));
  }

  public function testRemoveDiscNumbers() : void {
    $this->assertEquals('The Blue Album', Transforms::discardDiscNumber('The Blue Album (Disk 2)'));
    $this->assertEquals('The Blue Album', Transforms::discardDiscNumber('The Blue Album [Disk 3]'));
    $this->assertEquals('The Blue Album', Transforms::discardDiscNumber('The Blue Album - Disk 2)'));
    $this->assertEquals('The Blue Album', Transforms::discardDiscNumber('The Blue Album (Disk 2)'));
    $this->assertEquals('The Blue Album (Remastered)', Transforms::discardDiscNumber('The Blue Album (Disk 2) (Remastered)'));
  }
}

