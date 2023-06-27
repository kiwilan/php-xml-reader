<?php

use Kiwilan\XmlReader\XmlConverter;
use Kiwilan\XmlReader\XmlReader;

it('can parse opf', function () {
    $xml = XmlReader::make(OPF);

    $metadata = $xml->extract('metadata');
    $meta = $xml->extract(['metadata', 'meta']);
    $anykey = $xml->extract('anykey');

    $metaContent = $xml->extract('metadata');
    $metaContent = XmlReader::getContent($metaContent);

    $title = $xml->extract(['metadata', 'dc:title']);
    $title = XmlReader::getContent($title);

    $creator = $xml->extract(['metadata', 'dc:creator']);
    $creator = XmlReader::getContent($creator);

    $titleAttributes = $xml->extract(['metadata', 'dc:title']);
    $titleAttributes = XmlReader::getAttributes($titleAttributes);

    $creatorAttributes = $xml->extract(['metadata', 'dc:creator']);
    $creatorAttributes = XmlReader::getAttributes($creatorAttributes);

    expect($metaContent)->toBeArray();
    expect($title)->toBe("Le clan de l'ours des cavernes");
    expect($creator)->toBe('Jean M. Auel');
    expect($titleAttributes)->toBeNull();
    expect($creatorAttributes)->toBeArray();

    expect($metadata)->toBeArray();
    expect($meta)->toBeArray();
    expect($anykey)->toBeNull();

    expect($xml->content())->toBeArray();
    expect($xml->root())->toBe('package');
    expect($xml->rootNS())->toBeArray();
    expect($xml->rootAttributes())->toBeArray();
    expect($xml->rootAttribute('version'))->toBe('2.0');
    expect($xml->version())->toBe('1.0');
    expect($xml->encoding())->toBe('UTF-8');
    expect($xml->isValidXml())->toBeTrue();
    expect($xml->path())->toBe(OPF);
    expect($xml->filename())->toBe('epub.opf');
    expect($xml->converter())->toBeInstanceOf(XmlConverter::class);
    expect($xml->toArray())->toBeArray();
    expect($xml->__toString())->toBeString();
});

it('can search', function () {
    $xml = XmlReader::make(OPF);

    $creator = $xml->find('creator', strict: true);
    $dc = $xml->search('dc:');
    $titleNear = $xml->find('dc:ti');
    $title = $xml->find('dc:title');
    $dccreator = $xml->find('dc:creator');
    $publisher = $xml->find('dc:publisher', value: true);
    $attributes = $xml->find('creator', attributes: true);

    expect($dc)->toBeArray();
    expect($dc['dc:title'])->toBe("Le clan de l'ours des cavernes");
    expect($titleNear)->toBe("Le clan de l'ours des cavernes");
    expect($title)->toBe("Le clan de l'ours des cavernes");
    expect($creator)->toBeNull();
    expect($dccreator)->toBeArray();
    expect($publisher)->toBeString();
    expect($attributes)->toBeArray();
});

it('can find', function () {
    $xml = XmlReader::make(OPF);

    $creator = $xml->find('creator', strict: true);
    $dccreator = $xml->find('dc:creator');
    $publisher = $xml->find('dc:publisher', value: true);
    $attributes = $xml->find('creator', attributes: true);

    expect($creator)->toBeNull();
    expect($dccreator)->toBeArray();
    expect($publisher)->toBeString();
    expect($attributes)->toBeArray();
});

it('can find without map content', function () {
    $xml = XmlReader::make(OPF, mapContent: false);

    $creator = $xml->find('creator', strict: true);
    $dccreator = $xml->find('dc:creator');
    $publisher = $xml->find('dc:publisher', value: true);
    $attributes = $xml->find('creator', attributes: true);

    expect($creator)->toBeNull();
    expect($dccreator)->toBeArray();
    expect($publisher)->toBeString();
    expect($attributes)->toBeArray();
});

it('can parse rss', function () {
    $xml = XmlReader::make(RSS);

    expect($xml->content())->toBeArray();
    expect($xml->root())->toBe('rss');
});

it('can parse xml', function () {
    $xml = XmlReader::make(XML);

    expect($xml->content())->toBeArray();
    expect($xml->root())->toBe('rss');
});

it('can save xml', function () {
    $xml = XmlReader::make(OPF);
    $xml->save('tests/test.xml');

    expect(file_exists('tests/test.xml'))->toBeTrue();
    expect(file_get_contents('tests/test.xml'))->toBeString();
});

it('can skip xml error', function () {
    $xml = XmlReader::make(ERROR_XML, failOnError: false);

    expect($xml->converter())->toBeNull();
    expect($xml->content())->toBeArray();
});

it('can fail xml error', function () {
    expect(fn () => XmlReader::make(ERROR_XML))
        ->toThrow(Exception::class);
});

it('can read path or content', function () {
    $xml = XmlReader::make(file_get_contents(OPF));
    expect($xml->content())->toBeArray();

    $xml = XmlReader::make(OPF);
    expect($xml->content())->toBeArray();
});

it('can fail if not exists', function () {
    expect(fn () => XmlReader::make('not-exist'))
        ->toThrow(Exception::class, 'XML is not valid');

    expect(fn () => XmlReader::make('./not-exist.xml'))
        ->toThrow(Exception::class, 'File `./not-exist.xml` not found');
});
