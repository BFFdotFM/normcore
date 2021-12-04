<?php declare(strict_types=1);

use BFFdotFM\Normcore\Normcore;
use PHPUnit\Framework\TestCase;

/**
 * Test Record Label Conversions
 */
final class RecordLabelKeyTest extends TestCase {

  public function testRemovesLeadingWhitespace() : void {
    $this->assertEquals('capitol', Normcore::keyRecordLabelName('  Capitol Records'));
    $this->assertEquals('capitol', Normcore::keyRecordLabelName("\tCapitol Records"));
    $this->assertEquals('capitol', Normcore::keyRecordLabelName("\nCapitol Records"));
  }

  public function testRemovesOrganizationDetritus() : void {
    $this->assertEquals('capitol', Normcore::keyRecordLabelName('Capitol Records LLC'));
    $this->assertEquals('nighttimestories', Normcore::keyRecordLabelName('Night Time Stories Ltd'));
    $this->assertEquals('nighttimestories', Normcore::keyRecordLabelName('Night Time Stories Ltd.'));
    $this->assertEquals('age101', Normcore::keyRecordLabelName('AGE 101 MUSIC'));
    $this->assertEquals('polydor', Normcore::keyRecordLabelName('  Polydor Records '));
    $this->assertEquals('am', Normcore::keyRecordLabelName(' A&M Records'));
    $this->assertEquals('badboy', Normcore::keyRecordLabelName(' Bad Boy Records LLC'));
    $this->assertEquals('retromedia', Normcore::keyRecordLabelName('Retromedia Entertainment Group, Inc.'));
    $this->assertEquals('sounddesign', Normcore::keyRecordLabelName(' Sound Design Records Inc.'));
    $this->assertEquals('asaprocky', Normcore::keyRecordLabelName('A$AP Rocky Recordings'));
  }

  public function testRemovesRedundantWords() : void {
    $this->assertEquals('benward', Normcore::keyRecordLabelName('Ben and Ward Records'));
    $this->assertEquals('benwardlabel', Normcore::keyRecordLabelName('The Ben Ward Label'));
  }

  public function testRemovesTrailingYearOfRelease() : void {
    $this->assertEquals('alamelphan', Normcore::keyRecordLabelName('Alam El Phan (1974)'));
    $this->assertEquals('ace', Normcore::keyRecordLabelName('Ace (1964/2021)'));
  }

  public function testRemovesPrecedingYearOfRelease() : void {
    $this->assertEquals('1985', Normcore::keyRecordLabelName('1985 Music'));
    $this->assertEquals('1984', Normcore::keyRecordLabelName('1984 Records'));
    $this->assertEquals('subpop', Normcore::keyRecordLabelName('© 2017 Sub Pop Records'));
    $this->assertEquals('king', Normcore::keyRecordLabelName('℗ 1978 King Record Co., Ltd.'));
    $this->assertEquals('beggarsbanquet', Normcore::keyRecordLabelName('℗ 1985 Beggars Banquet Records Ltd'));
    $this->assertEquals('motown', Normcore::keyRecordLabelName('1971 Motown Records'));
  }

  public function testDicardsExtendedCopyrightStatements() : void {
    $this->assertEquals('defjam', Normcore::keyRecordLabelName('Def Jam Recordings, a division of UMG Recordings, Inc.'));
    $this->assertEquals('domino', Normcore::keyRecordLabelName('℗ 1999 Robert Wyatt under exclusive license to Domino Recording Co Ltd'));
    $this->assertEquals('rca', Normcore::keyRecordLabelName('℗ 1981 RCA Records, a division of Sony Music Entertainment'));
    $this->assertEquals('elektra', Normcore::keyRecordLabelName('℗ 1982 Elektra Entertainment, Marketed by Rhino Entertainment Company, a Warner Music Group Company'));
  }

  public function testRemovesPunctuation() : void {
    $this->assertEquals('selfreleased', Normcore::keyRecordLabelName(' Self Released)'));
    $this->assertEquals('extension', Normcore::keyRecordLabelName('- Extensión'));
    $this->assertEquals('cave', Normcore::keyRecordLabelName('((Cave)) Recordings'));
    $this->assertEquals('pias', Normcore::keyRecordLabelName('[PIAS] Recordings'));
    $this->assertEquals('mompop', Normcore::keyRecordLabelName('Mom+Pop'));
    $this->assertEquals('waxtrax', Normcore::keyRecordLabelName('Wax Trax!'));
    $this->assertEquals('xenergy', Normcore::keyRecordLabelName('X-Energy'));
    $this->assertEquals('1234go', Normcore::keyRecordLabelName('1-2-3-4 Go! Records'));
    $this->assertEquals('amoctone', Normcore::keyRecordLabelName('A&M Octone Records'));
  }

  public function testComplexExamples() : void {
    $this->assertEquals('cinq', Normcore::keyRecordLabelName('Cinq Music Group, LLC'));
    $this->assertEquals('fader', Normcore::keyRecordLabelName('Clairo Records, LLC under exclusive license to Fader'));
    $this->assertEquals('rca', Normcore::keyRecordLabelName('Question Everything, Inc. under exclusive license to RCA Records, a division of Sony Music Entertainment'));
    $this->assertEquals('craft', Normcore::keyRecordLabelName('R.E.M./Athens L.L.C., Under exclusive license to Craft Recordings. Distributed by Concord.'));
  }

  public function testConvertsUnicodeNamesToAscii() : void {
    $this->assertEquals('diskographikossynetairismoskallitechnon', Normcore::keyRecordLabelName('Δισκογραφικός Συνεταιρισμός Καλλιτεχνών'));
    $this->assertEquals('youxianhuisheweidaoshiwusuo', Normcore::keyRecordLabelName('有限会社尾島事務所'));
  }
}
