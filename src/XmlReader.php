<?php

namespace Kiwilan\XmlReader;

use DOMDocument;
use Exception;

/**
 * Convert XML string.
 *
 * @author Ewilan Rivière
 * @author Adrien aka Gaarf & contributors
 *
 * @see From: https://stackoverflow.com/a/30234924
 */
class XmlReader
{
    protected array $content = [];

    protected function __construct(
        protected string $xml,
    ) {
    }

    /**
     * @param  string  $xml XML string or path to XML file
     *
     * @throws Exception
     */
    public static function make(string $xml): self
    {
        if (strpos($xml, "\0") === false) {
            $xml = file_get_contents($xml);
        }

        $self = new self($xml);

        try {
            $self->content = $self->parse();
        } catch (\Throwable $th) {
            throw new Exception('Error while parsing XML', 1, $th);
        }

        return $self;
    }

    /**
     * @return array<string, mixed>
     */
    public function content(): array
    {
        return $this->content;
    }

    public function save(string $path): bool
    {
        return file_put_contents($path, $this->content) !== false;
    }

    private function parse()
    {
        $doc = new DOMDocument();
        $doc->loadXML($this->xml);
        $root = $doc->documentElement;
        $output = $this->domnodeToArray($root);
        $output['@root'] = $root->tagName ?? null;

        return $output;
    }

    private function domnodeToArray(mixed $node)
    {
        $output = [];

        switch ($node->nodeType) {
            case XML_CDATA_SECTION_NODE:
            case XML_TEXT_NODE:
                $output = trim($node->textContent);

                break;

            case XML_ELEMENT_NODE:
                for ($i = 0, $m = $node->childNodes->length; $i < $m; $i++) {
                    $child = $node->childNodes->item($i);
                    $v = $this->domnodeToArray($child);

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

                break;
        }

        return $output;
    }
}
