<?php declare(strict_types=1);

use BFFdotFM\Normcore\Normcore;
use PHPUnit\Framework\TestCase;

/**
 * Test Generic of Keys
 */
final class KeysTest extends TestCase {

  public function testArtistExamples() : void {
    $this->assertEquals('Fat Boys', Normcore::cleanArtistName('Fat Boys, ft. The Beach Boys'));
    $this->assertEquals('(Shield Flip)', Normcore::cleanArtistName(' (Shield Flip)'));
    $this->assertEquals('…And You Will Know Us By The Trail Of Dead', Normcore::cleanArtistName('...And You Will Know Us By The Trail Of Dead'));
    $this->assertEquals('…And You Will Know Us By The Trail Of Dead', Normcore::cleanArtistName('…And You Will Know Us By The Trail Of Dead'));
    $this->assertEquals('"Little" Louie & Marc Anthony', Normcore::cleanArtistName('"Little" Louie & Marc Anthony'));
    $this->assertEquals('Africa Bambaata', Normcore::cleanArtistName('Africa Bambaata featuring Sonic Soul Force'));
  }

  public function testKeyRemovesPunctuation() : void {
    $this->assertEquals('youwillknowusbytrailofdead', Normcore::keyArtistName('...And You Will Know Us By The Trail Of Dead'));
    $this->assertEquals('actresslondoncontemporaryorchestra', Normcore::keyArtistName('Actress & The London Contemporary Orchestra'));
    $this->assertEquals('awakenmylove', Normcore::keyAlbumTitle('"Awaken, My Love!"'));

  }

  public function testKeyHandlesBandsWithRedundantNames() : void {
    $this->assertEquals('and', Normcore::keyArtistName('The And'));
    $this->assertEquals('the', Normcore::keyArtistName('The The'));
    $this->assertEquals('artblakeyjazzmessengers', Normcore::keyArtistName('Art Blakey & The Jazz Messengers'));
    $this->assertEquals('artblakeyjazzmessengers', Normcore::keyArtistName('Art Blakey and the Jazz Messengers'));
  }

  public function testKeyRemovesFeaturedGuests() : void {
    $this->assertEquals('alicecoltrane', Normcore::keyArtistName('Alice Coltrane (featuring Pharoah Sanders)'));
    $this->assertEquals('artmooneyorchestra', Normcore::keyArtistName('Art Mooney Orchestra w/ Barry Gordon'));
  }

  public function testKeyFlattensUnicode() : void {
    $this->assertEquals('badgeepoqueensemble', Normcore::keyArtistName('Badge Époque Ensemble'));
    $this->assertEquals('baxterduryetiennedecrecydelilahholliday', Normcore::keyArtistName('Baxter Dury & Étienne de Crécy & Delilah Holliday'));
    $this->assertEquals('caro', Normcore::keyArtistName('caro♡'));


    # Thanks, FourTet... Thortet.
    # This is the kind of Unicode we're happy to have behave … oddly … outside the bounds of the design, so this test exists to
    # ensure behaviour doesn't change unexpectedly
    $this->assertEquals('1111111', Normcore::keyAlbumTitle('̸ ̡ ҉ ҉.·๑ඕั ҉ ̸ ̡ ҉ ҉.·๑ඕั ҉ ̸ ̡ ҉ ҉.·๑ඕั ҉ ̸ ̡ ҉ ҉.·๑ඕั ҉ ̸ ̡ ҉ ҉.·๑ඕั ҉ ̸ ̡ ҉ ҉.·๑ඕั ҉ ̸ ̡ ҉ ҉.·๑ඕั ҉'));
    $this->assertEquals('cmpilatin4martinhellstpsurgeryvisa', Normcore::keyAlbumTitle('🍼🍇🎀​~​C🌚MPILATI🌐N 4 MARTIN HELL\'S T🌍P SURGERY & VISA 💖🏀✨'));
    $this->assertEquals('o', Normcore::keyArtistName('Ω'));
  }

  public function testKeyFlattensStylisticCharacters() : void {
    $this->assertEquals('asaprocky', Normcore::keyArtistName('A$AP Rocky'));
    $this->assertEquals('tydollasign', Normcore::keyArtistName('Ty Dolla $ign'));
  }

  public function testAlbumsDiscardReleaseSuffixes() : void {
    $this->assertEquals('optimist', Normcore::keyAlbumTitle('The Optimist LP'));
    $this->assertEquals('bettertomorrow', Normcore::keyAlbumTitle('A Better Tomorrow - EP'));
    $this->assertEquals('bluemonday', Normcore::keyAlbumTitle('Blue Monday 12"'));
    $this->assertEquals('almostready', Normcore::keyAlbumTitle('Almost Ready 7"'));
  }


}
