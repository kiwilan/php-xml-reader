<?php

namespace Kiwilan\XmlReader;

use Exception;
use Kiwilan\XmlReader\Converters\GaarfXml;
use Kiwilan\XmlReader\Converters\XmlArray;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;

/**
 * Convert XML string.
 */
class XmlReader
{
    /**
     * @var array<string, mixed>
     */
    protected array $content = [];

    protected function __construct(
        protected string $xml,
    ) {
    }

    /**
     * @param  string  $xml XML string or path to XML file
     * @param  bool  $failOnError Throw exception if XML is invalid
     * @param  string  $type Type of parser `original` or `gaarf`
     *
     * @throws Exception
     */
    public static function make(string $xml, bool $failOnError = true, string $type = 'original'): self
    {
        if (strpos($xml, "\0") === false) {
            if (! file_exists($xml)) {
                throw new Exception("File `{$xml}` not found");
            }
            $xml = file_get_contents($xml);
        }

        $self = new self($xml);

        try {
            $self->content = match ($type) {
                'original' => XmlArray::make($xml),
                'gaarf' => GaarfXml::make($xml),
            };
        } catch (\Throwable $th) {
            if ($failOnError) {
                throw new Exception('Error while parsing XML', 1, $th);
            }

            error_log($th->getMessage()."\n".$th->getTraceAsString());
        }

        return $self;
    }

    public function search(string $key, bool $strict = false, bool $value = false)
    {
        // foreach ($this->content as $key => $val) {
        //     if ($val['uid'] === $id) {
        //         return $key;
        //     }
        // }

        $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($this->content));

        // foreach ($iterator as $iKey => $iVal) {
        //     if ($key === $iKey) {
        //         ray($iKey, $iVal);
        //     }

        // $subArray = $value->getSubIterator();
        // if ($subArray['name'] === 'cat 1') {
        //     $outputArray[] = iterator_to_array($subArray);
        // }
        // }

        // $result = array_filter($this->content, function () use ($key) {
        //     $found = false;
        //     array_walk_recursive($key, function ($item, $key) use (&$found) {
        //         $found = $found || $key == 'name' && $item == 'cat 1';
        //     });

        //     return $found;
        // });
        // $result = $this->searchNestedArray($this->content, $key, 'key');
        $result = $this->parseArray($this->content, $key, $strict);
        if ($value && array_key_exists('_value', $result)) {
            $result = $result['_value'];
        }
        ray($key, $result);

        return null;
    }

    private function parseArray(array $array, string $key, bool $strict = false): mixed
    {
        foreach ($array as $k => $v) {
            if ($strict) {
                if ($k === $key) {
                    return $v;
                }
            } else {
                if (str_contains($k, $key)) {
                    return $v;
                }
            }
            if (is_array($v)) {
                $resultat = $this->parseArray($v, $key, $strict);
                if ($resultat !== null) {
                    return $resultat;
                }
            }
        }

        return null;
    }

    private function searchNestedArray(string $search, string $mode = 'value'): bool
    {
        foreach (new RecursiveIteratorIterator(new RecursiveArrayIterator($this->content)) as $key => $value) {
            ray($search);
            if ($search === ${${'mode'}}) {
                return true;
            }
        }

        return false;
    }

    private function searchKeysInMultiDimensionalArray(array $keys)
    {
        $results = [];

        if (is_array($this->content)) {
            $resultArray = array_intersect_key($this->content, array_flip($keys));
            if (! empty($resultArray)) {
                $results[] = $resultArray;
            }

            foreach ($this->content as $subarray) {
                $results = array_merge($results, $this->searchKeysInMultiDimensionalArray($subarray, $keys));
            }
        }

        return $results;
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
        return file_put_contents($path, $this->xml) !== false;
    }

    public function toArray(): array
    {
        return $this->content;
    }

    public function __toString(): string
    {
        return $this->xml;
    }
}
