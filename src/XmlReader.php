<?php

namespace Kiwilan\XmlReader;

use Exception;

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
        protected ?string $path = null,
        protected ?string $filename = null,
        protected bool $validXml = false,
    ) {
    }

    /**
     * @param  string  $xml XML string or path to XML file
     * @param  bool  $failOnError Throw exception if XML is invalid. Default: `true`.
     *
     * @throws Exception
     */
    public static function make(string $xml, bool $failOnError = true): self
    {
        $path = null;
        $basename = null;
        if (is_file($xml)) {
            if (! file_exists($xml)) {
                throw new Exception("File `{$xml}` not found");
            }
            $path = $xml;
            $basename = basename($xml);
            $xml = file_get_contents($xml);
        }

        $validXml = false;
        try {
            if (simplexml_load_string($xml)) {
                $validXml = true;
            }
        } catch (\Throwable $th) {
            if ($failOnError) {
                throw new Exception('XML is not valid', 1, $th);
            }

            error_log($th->getMessage()."\n".$th->getTraceAsString());
        }

        $self = new self($xml, $path, $basename, $validXml);

        try {
            $self->content = XmlConverter::make($xml);
        } catch (\Throwable $th) {
            if ($failOnError) {
                throw new Exception('Error while parsing XML', 1, $th);
            }

            error_log($th->getMessage()."\n".$th->getTraceAsString());
        }

        return $self;
    }

    /**
     * Safely extract value from XML.
     *
     * @param  string[]|string  $keys  Keys to extract
     */
    public function extract(array|string $keys): mixed
    {
        if (is_string($keys)) {
            $keys = [$keys];
        }

        $data = $this->content;
        $res = null;
        foreach ($keys as $k => $key) {
            if (array_key_exists($key, $data)) {
                $data = $data[$key];
                $res = $data;
            } else {
                return null;
            }
        }

        return $res;
    }

    /**
     * Search for a key in XML.
     *
     * @param  string  $key  Key to search
     * @param  bool  $strict  If true, search for exact key. Default: `false`.
     * @param  bool  $value  If true, get `_value` directly (if exists). Default: `false`.
     * @param  bool  $attributes  If true, get `@attributes` directly (if exists). Default: `false`.
     */
    public function search(string $key, bool $strict = false, bool $value = false, bool $attributes = false): mixed
    {
        $result = $this->parseArray($this->content, $key, $strict);
        if ($value && array_key_exists('_value', $result)) {
            $result = $result['_value'];
        }
        if ($attributes && array_key_exists('@attributes', $result)) {
            $result = $result['@attributes'];
        }

        return $result;
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

    /**
     * @return array<string, mixed>
     */
    public function content(): array
    {
        return $this->content;
    }

    /**
     * Save XML to file.
     */
    public function save(string $path): bool
    {
        return file_put_contents($path, $this->xml) !== false;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->content;
    }

    public function __toString(): string
    {
        return $this->xml;
    }
}
