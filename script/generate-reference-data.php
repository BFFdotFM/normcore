<?php declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use BFFdotFM\Normcore\Normcore;
use League\Csv\Reader;
use League\Csv\Writer;

$mappings = array(
  'albums.csv' => 'AlbumTitle',
  'artists.csv' => 'ArtistName',
  'labels.csv' => 'RecordLabelName',
  'titles.csv' => 'TrackTitle'
);

foreach ($mappings as $filename => $function) {
  $cleanFunc = "clean$function";
  $keyFunc = "key$function";

  $dataPath = __DIR__ . '/../test/data/' . $filename;
  $csv = Reader::createFromPath($dataPath, 'r');
  $csv->setDelimiter("\t");

  $newData = array();

  foreach ($csv as $row) {
    $inputString = $row[0];

    $newData[] = array(
      $inputString,
      Normcore::$cleanFunc($inputString),
      Normcore::$keyFunc($inputString)
    );
  }

  $csvOut = Writer::createFromFileObject(new SplTempFileObject());
  $csvOut->setDelimiter("\t");
  $csvOut->insertAll($newData);
  file_put_contents($dataPath, $csvOut->toString());
}
