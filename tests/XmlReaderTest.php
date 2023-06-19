<?php

use Kiwilan\XmlReader\XmlReader;

it('can parse opf', function () {
    $xml = XmlReader::make(OPF);

    ray($xml);
    expect($xml->content())->toBeArray();
    expect($xml->content()['@root'])->toBe('package');
    // expect($xml->content()['@attributes'])->toBeArray();
    // expect($xml->content()['@attributes']['xmlns'])->toBe('http://www.idpf.org/2007/opf');

    expect($xml->toArray())->toBeArray();
    expect($xml->__toString())->toBeString();
});

it('can save xml', function () {
    $xml = XmlReader::make(OPF);
    $xml->save('tests/test.xml');

    expect(file_exists('tests/test.xml'))->toBeTrue();
    expect(file_get_contents('tests/test.xml'))->toBeString();
});

it('can use `gaarf` type', function () {
    $xml = XmlReader::make(OPF, type: 'gaarf');

    expect($xml->content())->toBeArray();
    expect($xml->content()['@root'])->toBe('package');

    expect($xml->toArray())->toBeArray();
    expect($xml->__toString())->toBeString();
});

it('can skip xml error', function () {
    $xml = XmlReader::make(ERROR_XML, failOnError: false);

    expect($xml->content())->toBeArray();
});

it('can parse rss', function () {
    $xml = XmlReader::make(RSS);

    expect($xml->content())->toBeArray();
});

it('can parse xml', function () {
    $xml = XmlReader::make(XML);

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
