# PHP XML Reader

![Banner with cards catalog picture in background and PHP XML Reader title](https://raw.githubusercontent.com/kiwilan/php-xml-reader/main/docs/banner.jpg)

[![php][php-version-src]][php-version-href]
[![version][version-src]][version-href]
[![downloads][downloads-src]][downloads-href]
[![license][license-src]][license-href]
[![tests][tests-src]][tests-href]
[![codecov][codecov-src]][codecov-href]

PHP package to read XML with nice API, heavily inspired by [`stackoverflow answer`](https://stackoverflow.com/a/46349713/11008206).

## About

PHP have native functions to read XML files with [`SimpleXML`](https://www.php.net/manual/en/book.simplexml.php), but it's not easy to use and not very flexible. This package offer to read XML files as array, with a simple and flexible API.

## Requirements

-   **PHP version** >= _8.0_

## Installation

You can install the package via composer:

```bash
composer require kiwilan/php-xml-reader
```

## Usage

You can read XML from path or from content.

-   `mapContent`: `boolean` If a key has only `@content` key, return only the value of `@content`. Default: `true`.
-   `failOnError`: `boolean` Throw exception if XML is invalid. Default: `true`.

```php
use Kiwilan\XmlReader\XmlReader;

$xml = XmlReader::make('path/to/file.xml', bool $mapContent = true, bool $failOnError = true);
```

### Methods

|              Method               |               Description                |               Type                |
| :-------------------------------: | :--------------------------------------: | :-------------------------------: |
|         `$xml->getRoot()`         |          Value of root element           |             `?string`             |
|        `$xml->getRootNS()`        |        Namespaces of root element        |            `string[]`             |
|    `$xml->getRootAttributes()`    |        Attributes of root element        |      `array<string, mixed>`       |
| `$xml->getRootAttributes('key');` | Value of `key` attribute of root element |              `mixed`              |
|       `$xml->getVersion();`       |               XML version                |             `?string`             |
|      `$xml->getEncoding();`       |               XML encoding               |             `?string`             |
|       `$xml->isValidXml();`       |          Check if XML is valid           |              `bool`               |
|        `$xml->getPath();`         |             Path of XML file             |             `?string`             |
|      `$xml->getFilename();`       |           Filename of XML file           |             `?string`             |
|      `$xml->getConverter();`      |  Converter used to convert XML to array  | `\Kiwilan\XmlReader\XmlConverter` |
| `$xml->save('path/to/file.xml');` |              Save XML file               |              `bool`               |
|        `$xml->toArray();`         |           Convert XML to array           |      `array<string, mixed>`       |
|       `$xml->__toString();`       |          Convert XML to string           |             `string`              |

### Advanced methods

|              Method               |                       Description                       |               Type               |              Options              |
| :-------------------------------: | :-----------------------------------------------------: | :------------------------------: | :-------------------------------: |
|       `$xml->getContents()`       |              XML as multidimensional array              |      `array<string, mixed>`      |                                   |
|        `$xml->find('key')`        | Find key will return first value where that contain key |      `array<string, mixed>`      | `strict`, `content`, `attributes` |
|       `$xml->search('key')`       |     Search will return all values that contain key      |             `mixed`              |                                   |
|     `$xml->extract(...keys)`      |   Extract `metadata` key, if not found return `null`    |             `mixed`              |                                   |
|  `XmlReader::parseContent(key)`   |                 Parse content of entry                  |             `mixed`              |                                   |
| `XmlReader::parseAttributes(key)` |                Parse attributes of entry                | `array<string, mixed>` or `null` |                                   |

### Basic usage

XML as multidimensional array from `root` (safe).

```php
use Kiwilan\XmlReader\XmlReader;

$xml = XmlReader::make('path/to/file.xml');
$contents = $xml->getContents();
```

Basic usage.

```php
use Kiwilan\XmlReader\XmlReader;

$xml = XmlReader::make('path/to/file.xml');
$contents = $xml->getContents();
$title = $contents['metadata']['dc:title'] ?? null;
```

### Find

Find key will return first value where key that contain `title` (safe).

```php
use Kiwilan\XmlReader\XmlReader;

$xml = XmlReader::make('path/to/file.xml');
$title = $xml->find('title', strict: false);
```

Find key will return first value where key is `dc:title` (safe)

```php
use Kiwilan\XmlReader\XmlReader;

$xml = XmlReader::make('path/to/file.xml');
$dcTitle = $xml->find('dc:title');
```

Find key will return first value where key that contain `dc:title` and return `@content` (safe)

```php
use Kiwilan\XmlReader\XmlReader;

$xml = XmlReader::make('path/to/file.xml');
$dcCreator = $xml->find('dc:creator', content: true);
```

Find key will return first value where key contain `dc:creator` and return `@attributes` (safe)

```php
use Kiwilan\XmlReader\XmlReader;

$xml = XmlReader::make('path/to/file.xml');
$dcCreator = $xml->find('dc:creator', attributes: true);
```

### Search

Search will return all values that contain `dc` (safe)

```php
use Kiwilan\XmlReader\XmlReader;

$xml = XmlReader::make('path/to/file.xml');
$dc = $xml->search('dc');
```

### Extract

Extract `metadata` key, if not found return null (safe)

```php
use Kiwilan\XmlReader\XmlReader;

$xml = XmlReader::make('path/to/file.xml');
$rootKey = $xml->extract('metadata');
```

Extract `metadata` and `dc:title` keys (safe)

```php
use Kiwilan\XmlReader\XmlReader;

$xml = XmlReader::make('path/to/file.xml');
$subSubKey = $xml->extract(['metadata', 'dc:title']);
```

### Get content

If you want to extract only `@content` you could use `parseContent()` method, if you want to extract only `@attributes` you could use `parseAttributes()` method.

Extract `dc:title` key and return `@content` (safe)

```php
use Kiwilan\XmlReader\XmlReader;

$xml = XmlReader::make('path/to/file.xml');
$title = $xml->extract(['metadata', 'dc:title']);
$title = XmlReader::parseContent($title);
```

Extract `dc:creator` key and return `@content` (safe)

```php
use Kiwilan\XmlReader\XmlReader;

$xml = XmlReader::make('path/to/file.xml');
$creator = $xml->extract(['metadata', 'dc:creator']);
$creator = XmlReader::parseContent($creator);
```

Extract `dc:creator` key and return `@attributes` (safe)

```php
use Kiwilan\XmlReader\XmlReader;

$xml = XmlReader::make('path/to/file.xml');
$creatorAttributes = $xml->extract(['metadata', 'dc:creator']);
$creatorAttributes = XmlReader::parseAttributes($creatorAttributes);
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

-   [`spatie`](https://github.com/spatie) for `spatie/package-skeleton-php`

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[<img src="https://user-images.githubusercontent.com/48261459/201463225-0a5a084e-df15-4b11-b1d2-40fafd3555cf.svg" height="120rem" width="100%" />](https://github.com/kiwilan)

[version-src]: https://img.shields.io/packagist/v/kiwilan/php-xml-reader.svg?style=flat&colorA=18181B&colorB=777BB4
[version-href]: https://packagist.org/packages/kiwilan/php-xml-reader
[php-version-src]: https://img.shields.io/static/v1?style=flat&label=PHP&message=v8.0&color=777BB4&logo=php&logoColor=ffffff&labelColor=18181b
[php-version-href]: https://www.php.net/
[downloads-src]: https://img.shields.io/packagist/dt/kiwilan/php-xml-reader.svg?style=flat&colorA=18181B&colorB=777BB4
[downloads-href]: https://packagist.org/packages/kiwilan/php-xml-reader
[license-src]: https://img.shields.io/github/license/kiwilan/php-xml-reader.svg?style=flat&colorA=18181B&colorB=777BB4
[license-href]: https://github.com/kiwilan/php-xml-reader/blob/main/README.md
[tests-src]: https://img.shields.io/github/actions/workflow/status/kiwilan/php-xml-reader/run-tests.yml?branch=main&label=tests&style=flat&colorA=18181B
[tests-href]: https://packagist.org/packages/kiwilan/php-xml-reader
[codecov-src]: https://img.shields.io/codecov/c/gh/kiwilan/php-xml-reader/main?style=flat&colorA=18181B&colorB=777BB4
[codecov-href]: https://codecov.io/gh/kiwilan/php-xml-reader
