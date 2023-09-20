<?php

namespace Kiwilan\XmlReader;

use DOMDocument;
use DOMElement;

class XmlConverter
{
    /**
     * @var array<string, mixed>
     */
    protected array $contents = [];

    protected function __construct(
        protected string $xml,
        protected bool $skipContentOnly = true,
    ) {
    }

    public static function make(string $xml, bool $skipContentOnly = true): self
    {
        $self = new self($xml, $skipContentOnly);

        $xml = simplexml_load_string($self->xml);
        $namespaces = $xml->getDocNamespaces(true);

        $ns = [];
        foreach ($namespaces as $prefix => $namespace) {
            $prefix = $prefix === '' ? 'xmlns' : "xmlns:{$prefix}";
            $ns[$prefix] = $namespace;
        }

        $previousValue = libxml_use_internal_errors(true);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->loadXml($self->xml);
        libxml_use_internal_errors($previousValue);

        if (libxml_get_errors()) {
            return [];
        }

        $root = $dom->documentElement;
        $self->contents = $self->domToArray($dom);

        $self->contents['version'] = $dom->xmlVersion;
        $self->contents['encoding'] = $dom->xmlEncoding;
        $self->contents['@root'] = $root->tagName ?? null;
        $self->contents['@rootNS'] = $ns;

        return $self;
    }

    public function getXml(): string
    {
        return $this->xml;
    }

    /**
     * @return array<string, mixed>
     *
     * @deprecated Use getContents() instead
     */
    public function getContent(): array
    {
        return $this->contents;
    }

    /**
     * @return array<string, mixed>
     */
    public function getContents(): array
    {
        return $this->contents;
    }

    /**
     * @return array<string, mixed>
     */
    private function domToArray(mixed $root): array|string
    {
        $output = [];

        /** @var DOMDocument|DOMElement $root */
        if (method_exists($root, 'hasAttributes') && $root->hasAttributes()) {
            $this->parseAttributes($root, $output);
        }

        if (method_exists($root, 'hasChildNodes') && $root->hasChildNodes()) {
            $this->parseChildNodes($root, $output);
        }

        if ($this->skipContentOnly && count($output) == 1 && isset($output['@content'])) {
            return $output['@content'];
        }

        return $output;
    }

    private function parseAttributes(mixed $root, array &$output): void
    {
        /** @var DOMDocument|DOMElement $root */
        if (! property_exists($root, 'attributes')) {
            return;
        }

        $attrs = $root->attributes;
        foreach ($attrs as $attr) {
            $output['@attributes'][$attr->name] = $attr->value;
        }
    }

    /**
     * @return array<string, mixed>|string
     */
    private function parseChildNodes(mixed $root, array &$output): array|string
    {
        /** @var DOMDocument|DOMElement $root */
        if (! property_exists($root, 'childNodes')) {
            return $output;
        }

        $children = $root->childNodes;
        if ($children->length == 1) {
            $child = $children->item(0);
            if (in_array($child->nodeType, [XML_TEXT_NODE, XML_CDATA_SECTION_NODE])) {
                $value = trim($child->nodeValue);
                $output['@content'] = $value;

                return count($output) == 1
                    ? $output['@content']
                    : $output;
            }

        }
        $groups = [];
        foreach ($children as $child) {
            if (! isset($output[$child->nodeName])) {
                $output[$child->nodeName] = $this->domToArray($child);
            } else {
                if (! isset($groups[$child->nodeName])) {
                    $output[$child->nodeName] = [$output[$child->nodeName]];
                    $groups[$child->nodeName] = 1;
                }
                $output[$child->nodeName][] = $this->domToArray($child);
            }
        }

        return $output;
    }
}
