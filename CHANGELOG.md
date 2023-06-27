# Changelog

All notable changes to `php-xml-reader` will be documented in this file.

## 0.2.21 - 2023-06-27

- `find()` parameter `value` name to `content`
- improve docs

## 0.2.2 - 2023-06-27

- `search()` method has now only one parameter with key to search, it will search without `strict` option and return **all values** near to the key
- Add new method `find()` with all options of previous `search()` method

## 0.2.10 - 2023-06-20

- add `rootAttribute()` method to extract safely an attribute from the root element

## 0.2.0 - 2023-06-20

- `XmlReader::class` has now some properties to get XML informations: `root`, `rootNS`, `rootAttributes`, `version`, `encoding`, `path`, `filename`, `validXml`
- `content` method offer XML data from `root`
- `converter` method is an instance of `XmlConverter::class`
- `getContent()` method extract `@content` from a key
- `getAttributes()` method extract `@attributes` from a key
- `extract()` method extract a key from XML data
- `search()` method search a key from XML data
- `toArray()` method convert XML data to array
- `__toString()` method convert XML data to string

## 0.1.10 - 2023-06-19

- Fix error with file testing

## 0.1.0 - 2023-06-19

- init
