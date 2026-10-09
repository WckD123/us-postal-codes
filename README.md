# wckd123/us-postal-codes

Offline US ZIP code lookup for PHP. No API calls, no database: the data ships as plain PHP arrays,
split into one file per 3-digit prefix, so a lookup loads only one small file (and OPcache keeps it).

- ZIP and ZIP+4 → state (USPS code) and city. Covers the 50 states, DC, PR, VI, GU, AS and MP.
- State lists: `UsState` (50 states + DC) and `UsAddressState` (adds the territories).

## Install

```bash
composer require wckd123/us-postal-codes
```

## Use

```php
use WckD123\UsPostalCodes\PostalCodeLookup;
use WckD123\UsPostalCodes\UsAddressState;

$lookup = new PostalCodeLookup();

$match = $lookup->find('94105-1234');
// ['state' => 'CA', 'cities' => ['San Francisco']]

UsAddressState::NAMES_BY_CODE[$match[PostalCodeLookup::STATE]]; // 'California'

$lookup->find('123456'); // null - malformed
$lookup->find('00000');  // null - unknown
```

`find()` returns `null` for malformed and unknown ZIPs. Pass a folder to the constructor to read your
own data (same layout as `data/`), e.g. a fixture in tests.

## Regenerating the data

Run from a clone of this repo (not from `vendor/`):

```bash
composer install
mkdir -p /tmp/geonames && cd /tmp/geonames
for c in US PR VI GU AS MP; do curl -sSLO https://download.geonames.org/export/zip/$c.zip && unzip -o -q $c.zip -d $c; done
cd - && php bin/generate-postal-code-data.php /tmp/geonames
```

## Licence

Code: MIT. Data: see [data/NOTICE.md](data/NOTICE.md) (GeoNames, CC BY 4.0 - attribution required).
