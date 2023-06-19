<?php

use Kiwilan\XmlReader\XmlReader;

it('can parse opf', function () {
    $xml = XmlReader::make(OPF);

    $package = $xml->extract('package');
    $metadata = $xml->extract(['package', 'metadata', 'meta']);
    $anykey = $xml->extract('anykey');

    expect($package)->toBeArray();
    expect($metadata)->toBeArray();
    expect($anykey)->toBeNull();

    expect($xml->content())->toBeArray();
    expect($xml->content()['@root']['tagName'])->toBe('package');
    expect($xml->toArray())->toBeArray();
    expect($xml->__toString())->toBeString();
});

it('can search', function () {
    $xml = XmlReader::make(OPF);

    $creator = $xml->search('creator', strict: true);
    $dccreator = $xml->search('dc:creator');
    $publisher = $xml->search('dc:publisher', value: true);
    $attributes = $xml->search('creator', attributes: true);

    expect($creator)->toBeNull();
    expect($dccreator)->toBeArray();
    expect($publisher)->toBeString();
    expect($attributes)->toBeArray();
});

it('can parse rss', function () {
    $xml = XmlReader::make(RSS);

    expect($xml->content())->toBeArray();
    expect($xml->content()['@root']['tagName'])->toBe('rss');
});

it('can parse xml', function () {
    $xml = XmlReader::make(XML);

    expect($xml->content())->toBeArray();
    expect($xml->content()['@root']['tagName'])->toBe('rss');
});

it('can save xml', function () {
    $xml = XmlReader::make(OPF);
    $xml->save('tests/test.xml');

    expect(file_exists('tests/test.xml'))->toBeTrue();
    expect(file_get_contents('tests/test.xml'))->toBeString();
});

it('can skip xml error', function () {
    $xml = XmlReader::make(ERROR_XML, failOnError: false);

    expect($xml->content())->toBeArray();
});

it('can fail xml error', function () {
    expect(fn () => XmlReader::make(ERROR_XML))
        ->toThrow(Exception::class);
});

it('can read path or content', function () {
    expect(fn () => XmlReader::make(file_get_contents(OPF)))
        ->not()
        ->toThrow(Exception::class, 'File `not-exist` not found');

    expect(fn () => XmlReader::make(OPF))
        ->not()
        ->toThrow(Exception::class, 'File `not-exist` not found');
});

it('can fail if not exists', function () {
    expect(fn () => XmlReader::make('not-exist'))
        ->toThrow(Exception::class, 'File `not-exist` not found');
});
