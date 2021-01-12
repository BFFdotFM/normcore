# Normcore

**Normcore** is a PHP library for extracting consistent identifying representations of music metadata
fields: such as artist names, track titles, album names and record labels.

Created by BFF.fm — community radio from the heart of San Francisco. If you find this useful, please
consider a donation to help keep our station on air: https://bff.fm.

## Requirements

* Maintains support PHP 5.6+
* PHP 7 for testing and development

## Usage

```
use BFFdotFM\Normcore\Normcore;

Normcore::normalizeArtistName('Godspeed you! Black emperor'); // godspeedyoublackemperor
Normcore::normalizeArtistName('Godspeed You Black Emperor!'); // godspeedyoublackemperor
Normcore::normalizeArtistName('The The'); // thethe

Normcore::normalizeRecordLabel('Picadilly Records, LLC'); // picadilly
```

## Contributions

Contributions and patches are welcome, and are subject to the BFF.fm developer code of
conduct: https://developer.bff.fm/about/developer-code.
