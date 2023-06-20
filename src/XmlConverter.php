<?php

namespace Kiwilan\XmlReader;

use DOMDocument;
use DOMElement;

class XmlConverter
{
    /**
     * @var array<string, mixed>
     */
    protected array $content = [];

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
        $self->content = $self->domToArray($dom);

        $self->content['version'] = $dom->xmlVersion;
        $self->content['encoding'] = $dom->xmlEncoding;
        $self->content['@root'] = $root->tagName ?? null;
        $self->content['@rootNS'] = $ns;

        return $self;
    }

    public function xml(): string
    {
        return $this->xml;
    }

    /**
     * @return array<string, mixed>
     */
    public function content(): array
    {
        return $this->content;
    }

    /**
     * @return array<string, mixed>
     */
    private function domToArray(DOMDocument|DOMElement $root): array|string
    {
        $output = [];

        if ($root->hasAttributes()) {
            $this->parseAttributes($root, $output);
        }

        if ($root->hasChildNodes()) {
            $this->parseChildNodes($root, $output);
        }

        if ($this->skipContentOnly && count($output) == 1 && isset($output['@content'])) {
            return $output['@content'];
        }

        return $output;
    }

    private function parseAttributes(DOMDocument|DOMElement $root, array &$output): void
    {
        $attrs = $root->attributes;
        foreach ($attrs as $attr) {
            $output['@attributes'][$attr->name] = $attr->value;
        }
    }

    /**
     * @return array<string, mixed>|string
     */
    private function parseChildNodes(DOMDocument|DOMElement $root, array &$output): array|string
    {
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
