<?php

use Kiwilan\XmlReader\XmlConverter;
use Kiwilan\XmlReader\XmlReader;

it('can parse opf', function () {
    $xml = XmlReader::make(OPF);

    $metadata = $xml->extract('metadata');
    $meta = $xml->extract(['metadata', 'meta']);
    $anykey = $xml->extract('anykey');

    $metaContent = $xml->extract('metadata');
    $metaContent = XmlReader::parseContent($metaContent);

    $title = $xml->extract(['metadata', 'dc:title']);
    $title = XmlReader::parseContent($title);

    $creator = $xml->extract(['metadata', 'dc:creator']);
    $creator = XmlReader::parseContent($creator);

    $titleAttributes = $xml->extract(['metadata', 'dc:title']);
    $titleAttributes = XmlReader::parseAttributes($titleAttributes);

    $creatorAttributes = $xml->extract(['metadata', 'dc:creator']);
    $creatorAttributes = XmlReader::parseAttributes($creatorAttributes);

    expect($metaContent)->toBeArray();
    expect($title)->toBe("Le clan de l'ours des cavernes");
    expect($creator)->toBe('Jean M. Auel');
    expect($titleAttributes)->toBeNull();
    expect($creatorAttributes)->toBeArray();

    expect($metadata)->toBeArray();
    expect($meta)->toBeArray();
    expect($anykey)->toBeNull();

    expect($xml->getContents())->toBeArray();
    expect($xml->getRoot())->toBe('package');
    expect($xml->getRootNS())->toBeArray();
    expect($xml->getRootAttributes())->toBeArray();
    expect($xml->getRootAttribute('version'))->toBe('2.0');
    expect($xml->getVersion())->toBe('1.0');
    expect($xml->getEncoding())->toBe('UTF-8');
    expect($xml->isValidXml())->toBeTrue();
    expect($xml->getPath())->toBe(OPF);
    expect($xml->getFilename())->toBe('epub.opf');
    expect($xml->getConverter())->toBeInstanceOf(XmlConverter::class);
    expect($xml->toArray())->toBeArray();
    expect($xml->__toString())->toBeString();
});

it('can parse opf comment', function () {
    $xml = XmlReader::make(OPF_INSURGENT);

    $metadata = $xml->extract('metadata');
    $title = $xml->find('dc:title');

    expect($title)->toBe('Insurgent');
});

it('can search', function () {
    $xml = XmlReader::make(OPF);

    $creator = $xml->find('creator');
    $dc = $xml->search('dc:');
    $titleNear = $xml->find('dc:ti', strict: false);
    $title = $xml->find('dc:title');
    $dccreator = $xml->find('dc:creator');
    $publisher = $xml->find('dc:publisher', content: true);
    $attributes = $xml->find('creator', strict: false, attributes: true);
    $meta = $xml->find('meta');

    expect($creator)->toBeNull();
    expect($dc)->toBeArray();
    expect($dc['dc:title'])->toBe("Le clan de l'ours des cavernes");
    expect($titleNear)->toBe("Le clan de l'ours des cavernes");
    expect($title)->toBe("Le clan de l'ours des cavernes");
    expect($dccreator)->toBeArray();
    expect($publisher)->toBeString();
    expect($attributes)->toBeArray();
    expect($meta)->toBeArray();
});

it('can find', function () {
    $xml = XmlReader::make(OPF);

    $creator = $xml->find('creator');
    $dccreator = $xml->find('dc:creator');
    $publisher = $xml->find('dc:publisher', content: true);
    $attributes = $xml->find('creator', strict: false, attributes: true);

    expect($creator)->toBeNull();
    expect($dccreator)->toBeArray();
    expect($publisher)->toBeString();
    expect($attributes)->toBeArray();
});

it('can find without map content', function () {
    $xml = XmlReader::make(OPF, mapContent: false);

    $creator = $xml->find('creator');
    $dccreator = $xml->find('dc:creator');
    $publisher = $xml->find('dc:publisher', content: true);
    $attributes = $xml->find('creator', strict: false, attributes: true);

    expect($creator)->toBeNull();
    expect($dccreator)->toBeArray();
    expect($publisher)->toBeString();
    expect($attributes)->toBeArray();
});

it('can parse rss', function () {
    $xml = XmlReader::make(RSS);

    expect($xml->getContents())->toBeArray();
    expect($xml->getRoot())->toBe('rss');
});

it('can parse xml', function () {
    $xml = XmlReader::make(XML);

    expect($xml->getContents())->toBeArray();
    expect($xml->getRoot())->toBe('rss');
});

it('can save xml', function () {
    $xml = XmlReader::make(OPF);
    $xml->save('tests/test.xml');

    expect(file_exists('tests/test.xml'))->toBeTrue();
    expect(file_get_contents('tests/test.xml'))->toBeString();
});

it('can skip xml error', function () {
    $xml = XmlReader::make(ERROR_XML, failOnError: false);

    expect($xml->getConverter())->toBeNull();
    expect($xml->getContents())->toBeArray();
});

it('can fail xml error', function () {
    expect(fn () => XmlReader::make(ERROR_XML))
        ->toThrow(Exception::class);
});

it('can read path or content', function () {
    $xml = XmlReader::make(file_get_contents(OPF));
    expect($xml->getContents())->toBeArray();

    $xml = XmlReader::make(OPF);
    expect($xml->getContents())->toBeArray();
});

it('can fail if not exists', function () {
    expect(fn () => XmlReader::make('not-exist'))
        ->toThrow(Exception::class, 'XML is not valid');

    expect(fn () => XmlReader::make('./not-exist.xml'))
        ->toThrow(Exception::class, 'File `./not-exist.xml` not found');
});

it('can parse feed', function () {
    $xml = XmlReader::make(FEED);

    $find = $xml->find('title');
    $search = $xml->search('title');

    expect($find)->toBeString();
    expect($find)->toBe('2 Heures De Perdues');
    expect($search)->toBeArray();
    expect($search['title'])->toBe('2 Heures De Perdues');
});

it('can use deprecated methods', function () {
    $xml = XmlReader::make(FEED);

    expect($xml->getContents())->toBeArray();
});
