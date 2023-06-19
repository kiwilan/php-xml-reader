<?php

namespace Kiwilan\XmlReader\Converters;

use DOMDocument;
use DOMElement;

class XmlArray
{
    /**
     * @return array<string, mixed>
     */
    public static function make(string $xml): array
    {
        $self = new self();

        $previousValue = libxml_use_internal_errors(true);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->loadXml($xml);
        libxml_use_internal_errors($previousValue);

        if (libxml_get_errors()) {
            return [];
        }

        $root = $dom->documentElement;
        $content = $self->domToArray($dom);
        $content['@root'] = $root->tagName ?? null;

        return $content;
    }

    /**
     * @return array<string, mixed>
     */
    private function domToArray(DOMDocument|DOMElement $root): array
    {
        $output = [];

        if ($root->hasAttributes()) {
            $this->parseAttributes($root, $output);
        }

        if ($root->hasChildNodes()) {
            $this->parseChildNodes($root, $output);
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
                $output['_value'] = $child->nodeValue;

                return count($output) == 1
                    ? $output['_value']
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
