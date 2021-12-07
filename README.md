# Normcore

**Normcore** is a PHP library for wrangling consistent representations of music metadata fields: artist names, track titles, album names and record labels cleaned and condense into identifiers.

Created by BFF.fm — community radio from the heart of San Francisco. If you find this useful, please consider a donation to help keep our station on air: https://bff.fm.

## Requirements

* PHP 7+

## Usage

```
use BFFdotFM\Normcore;

Normcore::keyArtistName('Godspeed you! Black emperor'); // godspeedyoublackemperor
Normcore::CleanArtistName('Open Mike Eagle feat. Video Dave'); // Open Mike Eagle

Normcore::keyAlbumTitle('Godspeed you! Black emperor'); // completeworksvolume2
Normcore::cleanAlbumTitle('The Complete Works (Volume Two)'); // The Complete Works (Volume 2)

Normcore::keyTrackTitle('A Caged Bird / Imitations of Life (feat. Roots Manuva)') // cagedbirdimitationsoflife
Normcore::cleanTrackTitle('The Complete Works (Volume Two)'); // The Complete Works (Volume 2)

Normcore::keyRecordLabelName('Retromedia Entertainment Group, Inc.'); // retromedia
Normcore::cleanRecordLabelName('℗ 1985 Beggars Banquet Records Ltd'); // Beggars Banquet

```

## Philosophy

BFF.fm shows track metadata from DJs while songs are played: This data is generally crowdsourced from humans (although some DJs may use automated integrations from their playback software), ergo we see variations and errors. The purpose of Normcore is try and collapse a reasonable % of those variants into reliable tags, such that tracking data can be effectively aggregated.

It does not aim to magically fix every bad tag (Four Tet has a side project where track titles are elaborately decorative Unicode… it's OK if that slips through this particular net.)

Two functions are provided for each metadata type: `clean` and `key`. Clean makes a reasonable effort to tidy names for display, while Key collapses down to a lowercase identifier that can be used in URL slugs and elsewhere. Clean functions are intended to work reliably in most cases, but then used in systems where manual override and editing is possible (e.g. the punctuation in “...and you will know us by the trail of dead” and “Godspeed You! Black Emperor” may be lost in the interests of cleaning more common irregularities in how that punctuation may have been entered for other bands.)

Normcore reduces metadata down to identifiers — therefore, “featured” artists in track metadata will be discard. This is meant as no disrespect toward collaborative artists, but because there is inconsistency when crediting contributors in the artist, album, or title field. Long ago, Last.FM and Musicbrainz tried pretty hard to encourage people to use “(feat. Artist, Artist)” form in track titles specifically, rather than other tags, but the struggle continues. A future extension of this project may include an “extract contributors” function so that the data can be indexed.

## Contributions

Contributions, improvements, and patches are welcome, participation is subject to the BFF.fm [https://developer.bff.fm/about/code-of-conduct](developer code of conduct).
