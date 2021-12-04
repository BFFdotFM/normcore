<?php declare(strict_types=1);

use BFFdotFM\Normcore\Normcore;
use PHPUnit\Framework\TestCase;

/**
 * Test Record Label Conversions
 */
final class RecordLabelCleanTest extends TestCase {

  public function testRemovesLeadingWhitespace() : void {
    $this->assertEquals('Capitol', Normcore::cleanRecordLabelName('  Capitol Records'));
    $this->assertEquals('Capitol', Normcore::cleanRecordLabelName("\tCapitol Records"));
    $this->assertEquals('Capitol', Normcore::cleanRecordLabelName("\nCapitol Records"));
  }

  public function testRemovesOrganizationDetritus() : void {
    $this->assertEquals('Capitol', Normcore::cleanRecordLabelName('Capitol Records LLC'));
    $this->assertEquals('Night Time Stories', Normcore::cleanRecordLabelName('Night Time Stories Ltd'));
    $this->assertEquals('Night Time Stories', Normcore::cleanRecordLabelName('Night Time Stories Ltd.'));
    $this->assertEquals('AGE 101', Normcore::cleanRecordLabelName('AGE 101 MUSIC'));
    $this->assertEquals('Polydor', Normcore::cleanRecordLabelName('  Polydor Records '));
    $this->assertEquals('A&M', Normcore::cleanRecordLabelName(' A&M Records'));
    $this->assertEquals('Bad Boy', Normcore::cleanRecordLabelName(' Bad Boy Records LLC'));
    $this->assertEquals('Retromedia', Normcore::cleanRecordLabelName('Retromedia Entertainment Group, Inc.'));
    $this->assertEquals('Sound Design', Normcore::cleanRecordLabelName(' Sound Design Records Inc.'));
    $this->assertEquals('A$AP Rocky', Normcore::cleanRecordLabelName('A$AP Rocky Recordings'));
  }

  public function testRemovesTrailingYearOfRelease() : void {
    $this->assertEquals('Alam El Phan', Normcore::cleanRecordLabelName('Alam El Phan (1974)'));
    $this->assertEquals('Alam El Phan', Normcore::cleanRecordLabelName('Alam El Phan (1974)'));
    $this->assertEquals('Ace', Normcore::cleanRecordLabelName('Ace (1964/2021)'));
  }

  public function testRemovesPrecedingYearOfRelease() : void {
    $this->assertEquals('1985', Normcore::cleanRecordLabelName('1985 Music'));
    $this->assertEquals('1984', Normcore::cleanRecordLabelName('1984 Records'));
    $this->assertEquals('Sub Pop', Normcore::cleanRecordLabelName('© 2017 Sub Pop Records'));
    $this->assertEquals('King', Normcore::cleanRecordLabelName('℗ 1978 King Record Co., Ltd.'));
    $this->assertEquals('Beggars Banquet', Normcore::cleanRecordLabelName('℗ 1985 Beggars Banquet Records Ltd'));
    $this->assertEquals('Motown', Normcore::cleanRecordLabelName('1971 Motown Records'));
  }

  public function testDicardsExtendedCopyrightStatements() : void {
    $this->assertEquals('Def Jam', Normcore::cleanRecordLabelName('Def Jam Recordings, a division of UMG Recordings, Inc.'));
    $this->assertEquals('Domino', Normcore::cleanRecordLabelName('℗ 1999 Robert Wyatt under exclusive license to Domino Recording Co Ltd'));
    $this->assertEquals('RCA', Normcore::cleanRecordLabelName('℗ 1981 RCA Records, a division of Sony Music Entertainment'));
    $this->assertEquals('Elektra', Normcore::cleanRecordLabelName('℗ 1982 Elektra Entertainment, Marketed by Rhino Entertainment Company, a Warner Music Group Company'));
  }

  public function testRemovesUndesiredPunctuation() : void {
    $this->assertEquals('Self Released', Normcore::cleanRecordLabelName(' Self Released)'));
    $this->assertEquals('Extensión', Normcore::cleanRecordLabelName('- Extensión'));
    $this->assertEquals('Cave', Normcore::cleanRecordLabelName('((Cave)) Recordings'));
    $this->assertEquals('PIAS', Normcore::cleanRecordLabelName('[PIAS] Recordings'));
  }

  public function testMaintainsDesirablePunctuation() : void {
    $this->assertEquals('Mom+Pop', Normcore::cleanRecordLabelName('Mom+Pop'));
    $this->assertEquals('Wax Trax!', Normcore::cleanRecordLabelName('Wax Trax!'));
    $this->assertEquals('X-Energy', Normcore::cleanRecordLabelName('X-Energy'));
    $this->assertEquals('1-2-3-4 Go!', Normcore::cleanRecordLabelName('1-2-3-4 Go! Records'));
    $this->assertEquals('A&M Octone', Normcore::cleanRecordLabelName('A&M Octone Records'));
  }

  public function testComplexExamples() : void {
    $this->assertEquals('Cinq', Normcore::cleanRecordLabelName('Cinq Music Group, LLC'));
    $this->assertEquals('Fader', Normcore::cleanRecordLabelName('Clairo Records, LLC under exclusive license to Fader'));
    $this->assertEquals('RCA', Normcore::cleanRecordLabelName('Question Everything, Inc. under exclusive license to RCA Records, a division of Sony Music Entertainment'));
    $this->assertEquals('Craft', Normcore::cleanRecordLabelName('R.E.M./Athens L.L.C., Under exclusive license to Craft Recordings. Distributed by Concord.'));
  }

  public function testPreservesUnicodeNames() : void {
    $this->assertEquals('Δισκογραφικός Συνεταιρισμός Καλλιτεχνών', Normcore::cleanRecordLabelName('Δισκογραφικός Συνεταιρισμός Καλλιτεχνών'));
    $this->assertEquals('有限会社尾島事務所', Normcore::cleanRecordLabelName('有限会社尾島事務所'));
  }
}
