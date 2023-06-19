<?php

namespace Kiwilan\XmlReader\Converters;

use DOMDocument;
use DOMElement;

class GaarfXml
{
    /**
     * convert xml string to php array - useful to get a serializable value
     *
     * @author Adrien aka Gaarf & contributors
     *
     * @see http://gaarf.info/2009/08/13/xml-string-to-php-array/
     *
     * @return array<string, mixed>
     */
    public static function make(string $xml): array
    {
        $self = new self($xml);

        $doc = new DOMDocument();
        $doc->loadXML($xml);

        $root = $doc->documentElement;
        $content = $self->parseDomNode($root);
        $content['@root'] = $root->tagName ?? null;

        return $content;
    }

    /**
     * @param  DOMElement  $node
     * @return array<string, mixed>
     */
    private function parseDomNode(mixed $node): array|string
    {
        $output = [];

        match ($node->nodeType) {
            XML_CDATA_SECTION_NODE,
            XML_TEXT_NODE => $output = trim($node->textContent),
            XML_ELEMENT_NODE => $output = $this->xmlElementNode($node),
        };

        return $output;
    }

    private function xmlElementNode(mixed $node): array|string
    {
        $output = [];
        for ($i = 0, $m = $node->childNodes->length; $i < $m; $i++) {
            $child = $node->childNodes->item($i);
            $v = $this->parseDomNode($child);

            if (isset($child->tagName)) {
                $t = $child->tagName;

                if (! isset($output[$t])) {
                    $output[$t] = [];
                }
                $output[$t][] = $v;
            } elseif ($v || '0' === $v) {
                $output = (string) $v;
            }
        }

        if ($node->attributes->length && ! is_array($output)) { // Has attributes but isn't an array
            $output = ['@content' => $output]; // Change output into an array.
        }

        if (is_array($output)) {
            if ($node->attributes->length) {
                $a = [];

                foreach ($node->attributes as $attrName => $attrNode) {
                    $a[$attrName] = (string) $attrNode->value;
                }
                $output['@attributes'] = $a;
            }

            foreach ($output as $t => $v) {
                if (is_array($v) && 1 == count($v) && '@attributes' != $t) {
                    $output[$t] = $v[0];
                }
            }
        }

        return $output;
    }
}
