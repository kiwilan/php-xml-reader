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

You can use path or content to read XML file.

```php
use KiwiLan\XmlReader\XmlReader;

// Read XML from path or from content
// `mapContent` If a key has only `@content` key, return only the value of `@content`. Default: `true`.
// `failOnError` Throw exception if XML is invalid. Default: `true`.
$xml = XmlReader::make('path/to/file.xml', bool $mapContent = true, bool $failOnError = true);

$root = $xml->root(); // Value of root element
$rootNS = $xml->rootNS(); // Namespaces of root element
$rootAttributes = $xml->rootAttributes(); // Attributes of root element
$rootAttribute = $xml->rootAttributes('key'); // Value of `key` attribute of root element
$version = $xml->version(); // XML version
$encoding = $xml->encoding(); // XML encoding
$isValidXml = $xml->isValidXml(); // Check if XML is valid
$path = $xml->path(); // Path of XML file
$filename = $xml->filename(); // Filename of XML file
$converter = $xml->converter(); // Converter used to convert XML to array

$xml->save('path/to/file.xml'); // Save XML file
$array = $xml->toArray(); // Convert XML to array
$string = $xml->__toString(); // Convert XML to string
```

XML as multidimensional array from `root` (safe).

```php
$content = $xml->content();

// Basic usage
$title = $content['metadata']['dc:title'] ?? null;
```

### Search and extract

You can search and extract key from XML file.

```php
$title = $xml->find('title', strict: false); // Find key will return first value where key that contain `title` (safe)
$dcTitle = $xml->find('dc:title'); // Find key will return first value where key is `dc:title` (safe)
$dcCreator = $xml->find('dc:creator', content: true); // Find key will return first value where key that contain `dc:title` and return `@content` (safe)
$dcCreator = $xml->find('dc:creator', attributes: true); // Find key will return first value where key contain `dc:creator` and return `@attributes` (safe)

$dc = $xml->search('dc'); // Search will return all values that contain `dc` (safe)

$rootKey = $xml->extract('metadata'); // Extract `metadata` key, if not found return null (safe)
$subSubKey = $xml->extract(['metadata', 'dc:title']); // Extract `metadata` and `dc:title` keys (safe)
```

### Get content

If you want to extract only `@content` you could use `getContent()` method, if you want to extract only `@attributes` you could use `getAttributes()` method.

```php
$title = $xml->extract(['metadata', 'dc:title']);
$title = XmlReader::getContent($title); // Extract `dc:title` key and return `@content` (safe)

$creator = $xml->extract(['metadata', 'dc:creator']);
$creator = XmlReader::getContent($creator); // Extract `dc:creator` key and return `@content` (safe)

$creatorAttributes = $xml->extract(['metadata', 'dc:creator']);
$creatorAttributes = XmlReader::getAttributes($creatorAttributes); // Extract `dc:creator` key and return `@attributes` (safe)
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

[version-src]: https://img.shields.io/packagist/v/kiwilan/php-xml-reader.svg?style=flat-square&colorA=18181B&colorB=777BB4
[version-href]: https://packagist.org/packages/kiwilan/php-xml-reader
[php-version-src]: https://img.shields.io/static/v1?style=flat-square&label=PHP&message=v8.0&color=777BB4&logo=php&logoColor=ffffff&labelColor=18181b
[php-version-href]: https://www.php.net/
[downloads-src]: https://img.shields.io/packagist/dt/kiwilan/php-xml-reader.svg?style=flat-square&colorA=18181B&colorB=777BB4
[downloads-href]: https://packagist.org/packages/kiwilan/php-xml-reader
[license-src]: https://img.shields.io/github/license/kiwilan/php-xml-reader.svg?style=flat-square&colorA=18181B&colorB=777BB4
[license-href]: https://github.com/kiwilan/php-xml-reader/blob/main/README.md
[tests-src]: https://img.shields.io/github/actions/workflow/status/kiwilan/php-xml-reader/run-tests.yml?branch=main&label=tests&style=flat-square&colorA=18181B
[tests-href]: https://packagist.org/packages/kiwilan/php-xml-reader
[codecov-src]: https://codecov.io/gh/kiwilan/php-xml-reader/branch/main/graph/badge.svg?token=P9XIK2KV9G
[codecov-href]: https://codecov.io/gh/kiwilan/php-xml-reader
