<?php declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use BFFdotFM\Normcore\Normcore;
use League\Csv\CharsetConverter;
use League\Csv\Reader;
use League\Csv\Writer;

$mappings = array(
  'albums.csv' => 'AlbumTitle',
  'artists.csv' => 'ArtistName',
  'labels.csv' => 'RecordLabelName',
  'titles.csv' => 'TrackTitle'
);


$encoder = (new CharsetConverter())
    ->inputEncoding('utf-8')
    ->outputEncoding('utf-8');

foreach ($mappings as $filename => $function) {
  $cleanFunc = "clean$function";
  $keyFunc = "key$function";

  $dataPath = __DIR__ . '/../test/data/' . $filename;
  $csv = Reader::createFromPath($dataPath, 'r');
  $csv->setDelimiter("\t");

  $csvOut = Writer::createFromFileObject(new SplTempFileObject());
  $csvOut->setOutputBOM(Writer::BOM_UTF8);
  $csvOut->addFormatter($encoder);
  $csvOut->setDelimiter("\t");

  foreach ($csv as $row) {
    $inputString = $row[0];

    $csvOut->insertOne(array(
      $inputString,
      Normcore::$cleanFunc($inputString),
      Normcore::$keyFunc($inputString)
    ));
  }

  file_put_contents($dataPath, $csvOut->toString());
}
