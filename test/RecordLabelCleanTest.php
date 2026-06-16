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
    $this->assertEquals('1984', Normcore::cleanRecordLabelName('1984 Record Company'));
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

  public function testDiscardsStreamingMetadataNoise() : void {
    $this->assertEquals('Rhino', Normcore::cleanRecordLabelName('℗ 1969 Rhino Entertainment, a Warner Music Group Company'));
    $this->assertEquals('Atlantic', Normcore::cleanRecordLabelName('℗ 1969 Atlantic Recording Corporation, a Warner Music Group Company. Marketed by Rhino Entertainment Company, a Warner Music Group Company.'));
    $this->assertEquals('Extra Term Audio', Normcore::cleanRecordLabelName('℗ 1982 2020 The Estate of Jeffrey Lee Pierce, under Exclusive License to Extra Term Audio LLC for the United States'));
    $this->assertEquals('Elektra', Normcore::cleanRecordLabelName('℗ 1983 Elektra Entertainment, manufactured and marketed by Rhino Entertainment Company, a Warner Music Group company'));
    $this->assertEquals('ABKCO Music & Records', Normcore::cleanRecordLabelName('℗ 1987 ABKCO Music & Records, Inc.'));
    $this->assertEquals('AMDISCS Futures Reserve Label', Normcore::cleanRecordLabelName('℗ 2014 AMDISCS: Futures Reserve Label. www.amdiscs.com'));
    $this->assertEquals('Anthology', Normcore::cleanRecordLabelName('℗ 2014 Kemado Records, Inc. d/b/a Anthology Recordings'));
    $this->assertEquals('Bonsound', Normcore::cleanRecordLabelName('℗ 2021 Laurence-Anne, under exclusive licence to Bonsound'));
    $this->assertEquals('Mute', Normcore::cleanRecordLabelName('℗ 2021 Mute Records Ltd., a BMG Company'));
    $this->assertEquals('XXIM', Normcore::cleanRecordLabelName('℗ 2021 XXIM Records, a label of Sony Music Entertainment'));
    $this->assertEquals('Sony Music', Normcore::cleanRecordLabelName('℗ Originally released 1967. All rights reserved by Sony Music Entertainment'));
    $this->assertEquals('Sony Music', Normcore::cleanRecordLabelName('℗ Originally Released 1965, 1966, 1967, 1968, 1969, 1970, 1971 (P) 2003 Sony Music Entertainment Inc.'));
    $this->assertEquals('Arista', Normcore::cleanRecordLabelName('℗ This compilation (P) 2011 Arista Records, LLC'));
    $this->assertEquals('Western Vinyl', Normcore::cleanRecordLabelName('℗ Western Vinyl'));
    $this->assertEquals('Atlantic', Normcore::cleanRecordLabelName('2017 Atlantic Recording Corporation for the US and WEA International Inc. for the world outside of the US. A Warner Music Group Company'));
    $this->assertEquals('Geffen', Normcore::cleanRecordLabelName('A Geffen Records Release; ℗ 1966 UMG Recordings, Inc.'));
    $this->assertEquals('Motown', Normcore::cleanRecordLabelName('A Motown Records Release; This Compilation ℗ 2006 UMG Recordings, Inc.'));
    $this->assertEquals('A&M', Normcore::cleanRecordLabelName('An A&M Records Release; ℗ 1969 UMG Recordings, Inc.'));
    # Leading copyright runs mixing glyphs, &/and connectors and annotated year runs.
    $this->assertEquals('Rough Trade', Normcore::cleanRecordLabelName('(P) & (C) 2010 Rough Trade Records'));
    $this->assertEquals('Potion', Normcore::cleanRecordLabelName('(P) and © Potion Records'));
    $this->assertEquals('Warner', Normcore::cleanRecordLabelName('(P) and Warner Records UK Limited'));
    $this->assertEquals('Alfa', Normcore::cleanRecordLabelName('℗ 1978(2) 1982(1,3-10) Alfa Music, Inc.'));
    # The leading-year scaffolding strip must not eat into a DistroKid catalogue id, which
    # handleDistroKidLabels still needs to recognise.
    $this->assertEquals('Self Released', Normcore::cleanRecordLabelName('℗ 2019 1150200 Records DK'));
    # Stacked country + incorporation suffixes ("Ltd. (UK)", "llc USA").
    $this->assertEquals('Polydor', Normcore::cleanRecordLabelName('℗ 2006 Polydor Ltd. (UK)'));
    $this->assertEquals('Numan', Normcore::cleanRecordLabelName('Numan music llc USA'));
    # Article-less corporate "division of" clause.
    $this->assertEquals('Asylum', Normcore::cleanRecordLabelName('Asylum Records UK, division of Warner Music Uk Ltd'));
    # "International" is part of the name, not an organisational suffix.
    $this->assertEquals('Amnesty International', Normcore::cleanRecordLabelName('Amnesty International USA'));
    # Territory clauses with "for"/"in" and optional article must not orphan a preposition.
    $this->assertEquals('Mute Artists', Normcore::cleanRecordLabelName('Little Idiot under exclusive license to Mute Artists for USA'));
    $this->assertEquals('Interscope', Normcore::cleanRecordLabelName('XL Recordings Ltd., under exclusive license to Interscope Records in the USA'));
    # "u/l" abbreviation of "under licence to".
    $this->assertEquals('Sony', Normcore::cleanRecordLabelName('Mark Ronson u/l to Sony UK'));
    # Licensing is resolved before the corporate-parent strip, so a licensee clause that
    # itself contains "Group"/"Company" still yields the licensee, not the licensor.
    $this->assertEquals('Concord', Normcore::cleanRecordLabelName('A&M Records, Under Exclusive License to Concord Music Group, Inc.'));
    $this->assertEquals('Loma Vista', Normcore::cleanRecordLabelName('2017 PH Recordings, LLC. Under exclusive license to Loma Vista Recordings. Distributed by Concord Music Group, Inc.'));
  }

  public function testPreservesUnicodeNames() : void {
    $this->assertEquals('Δισκογραφικός Συνεταιρισμός Καλλιτεχνών', Normcore::cleanRecordLabelName('Δισκογραφικός Συνεταιρισμός Καλλιτεχνών'));
    $this->assertEquals('有限会社尾島事務所', Normcore::cleanRecordLabelName('有限会社尾島事務所'));
  }
}
