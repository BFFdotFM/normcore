<?php declare(strict_types=1);

# This script is used to benchmark Normcore transforms against

require __DIR__ . '/../vendor/autoload.php';

use BFFdotFM\Normcore\Normcore;
use League\Csv\CharsetConverter;
use League\Csv\Reader;


# $dataSource = 'albums.csv';
# $dataSource = 'artists.csv';
# $dataSource = 'titles.csv';
$dataSource = 'labels.csv';
$limit = 50000;

$transforms = array(
  'trimWhitespace',
  'handleDistroKidLabels',
  'discardLicensingBlurb',
  'removeTrailingYear',
  'discardCopyright',
  'removePhrasePunctuation',
  'discardIncorporation',
  'discardOrganizationGroup',
  'discardLabelNameRedundancies',
  'discardYearPrefix',
  'trimPunctuation',
  'normalizeUnicode',
  'flattenStylisticCharacters',
  'downCase',
  'filterRedundantWords',
  'removePunctuation',
  'removeWhitespace'
);

$encoder = (new CharsetConverter())
    ->inputEncoding('utf-8')
    ->outputEncoding('utf-8');

  $dataPath = __DIR__ . '/../test/data/labels.csv';
  $csv = Reader::createFromPath($dataPath, 'r');
  $csv->skipInputBOM();
  $bom = $csv->getInputBOM();
  $csv->setDelimiter("\t");

  Normcore::resetAnalysis();
  echo "\n";

  foreach ($csv as $index => $row) {
    if ($limit && $index > $limit) {
      break;
    }

    # Ignore the BOM/Header line:
    if ($index === 0) {
      continue;
    }
    $inputString = $row[0];

    Normcore::analyzeTransforms($inputString, $transforms);
    echo '.';
  }

  echo "\n\n";
  echo "Performance Breakdown\n";
  $stats = Normcore::getAnalysis();
  usort($stats, fn($a, $b) => $a['time'] - $b['time']);
  foreach ($stats as $stat) {
    echo(sprintf("%s\t%d\t%.2f\t%.2f\n", $stat['function'], $stat['calls'], $stat['time'], $stat['time'] / $stat['calls']));
  }
