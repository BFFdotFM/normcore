<?php declare(strict_types=1);

use BFFdotFM\Normcore\Normcore;
use League\Csv\Reader;
use PHPUnit\Framework\TestCase;

/**
 * Execute Normcore against the batch CSV data files in /test/data
 * @group Batch
 */
final class NormcoreBatchTest extends TestCase {

  private function getTestData(string $name) : Reader {
    $csv = Reader::createFromPath(__DIR__ . "/data/$name.csv", 'r');
    $csv->setDelimiter("\t");
    return $csv;
  }

  private function batchTestGeneric(string $dataName, string $functionName, int $dataIndex) : void {
    $csv = $this->getTestData($dataName);
    foreach ($csv as $row) {
      $input = $row[0];
      $expectedClean = $row[$dataIndex];
      $this->assertEquals($expectedClean, Normcore::$functionName($input), "Expected `$input` transform to `$expectedClean`");
    }
  }

  public function testCleansArtistNames(): void {
    $this->batchTestGeneric('artists', 'cleanArtistName', 1);
  }

  public function testMakesArtistKey(): void {
    $this->batchTestGeneric('artists', 'keyArtistName', 2);
  }

  public function testCleansAlbumTitle(): void {
    $this->batchTestGeneric('albums', 'cleanAlbumTitle', 1);
  }

  public function testMakesAlbumKey(): void {
    $this->batchTestGeneric('albums', 'keyAlbumTitle', 2);
  }

  public function testCleansTrackTitle(): void {
    $this->batchTestGeneric('titles', 'cleanTrackTitle', 1);
  }

  public function testMakesTrackKey(): void {
    $this->batchTestGeneric('titles', 'keyTrackTitle', 2);
  }

  public function testCleansRecordLabelNames(): void {
    $this->batchTestGeneric('labels', 'cleanRecordLabelName', 1);
  }

  public function testMakesArtistKeys(): void {
    $this->batchTestGeneric('labels', 'keyRecordLabelName', 2);
  }

}
