# Changelog

All notable changes to `php-xml-reader` will be documented in this file.

## 1.0.1 - 2023-08-10

- `XmlReader` `converter` method is now `getConverter`

## 1.0.0 - 2023-08-08

### BREAKING CHANGES

- All simple getters have now `get` prefix. For example, `getRoot()` instead of `root()`, `getEncoding()` instead of `encoding()`, etc. It concerns all simple getters of `XmlReader`, `XmlConverter` classes.
- `getContent` method of `XmlReader` class has been renamed to `parseContent`.
- `getAttributes` method of `XmlReader` class has been renamed to `parseAttributes`.

> Why?
All these classes have some methods like setters or actions. To be consistent and clear, all simple getters have now `get` prefix.

### Bugfixes

- Remove strong type for `XmlConverter`, some XML can have a different type than `DOMDocument|DOMElement`

## 0.2.31 - 2023-06-28

- Clean `find()` and `search()` methods

## 0.2.30 - 2023-06-28

- With `find()` method, `strict` param is now `true` by default
- With `search()` method, all values are now returned as array with default key if not duplicate, otherwise keys are incremented

## 0.2.22 - 2023-06-27

- Fix spread array error on linux

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
