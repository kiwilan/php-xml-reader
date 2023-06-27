<?php

namespace Kiwilan\XmlReader;

use Exception;

/**
 * Convert XML string.
 */
class XmlReader
{
    protected ?string $root = null;

    /**
     * @var string[]
     */
    protected array $rootNS = [];

    /**
     * @var array<string, mixed>
     */
    protected array $rootAttributes = [];

    protected ?string $version = null;

    protected ?string $encoding = null;

    /**
     * @var array<string, mixed>
     */
    protected array $content = [];

    protected ?XmlConverter $converter = null;

    protected function __construct(
        protected ?string $path = null,
        protected ?string $filename = null,
        protected bool $validXml = false,
    ) {
    }

    /**
     * @param  string  $xml XML string or path to XML file
     * @param  bool  $mapContent If a key has only `@content` key, return only the value of `@content`. Default: `true`.
     * @param  bool  $failOnError Throw exception if XML is invalid. Default: `true`.
     *
     * @throws Exception
     */
    public static function make(string $xml, bool $mapContent = true, bool $failOnError = true): self
    {
        $path = null;
        $basename = null;

        $extension = pathinfo($xml, PATHINFO_EXTENSION);
        if ($extension) {
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

        $self = new self($path, $basename, $validXml);

        try {
            $self->converter = XmlConverter::make($xml, $mapContent);
            $self->parseConverter();
        } catch (\Throwable $th) {
            if ($failOnError) {
                throw new Exception('Error while parsing XML', 1, $th);
            }

            error_log($th->getMessage()."\n".$th->getTraceAsString());
        }

        return $self;
    }

    /**
     * Get content of entry, if entry has `@content` key, return only the value of `@content` otherwise return the entry.
     */
    public static function getContent(mixed $entry): mixed
    {
        if (is_array($entry)) {
            if (array_key_exists('@content', $entry)) {
                return $entry['@content'];
            }

            return $entry;
        }

        return $entry;
    }

    /**
     * Get attributes of entry, if entry has `@attributes` key.
     *
     * @return array<string, mixed>|null
     */
    public static function getAttributes(mixed $entry): ?array
    {
        if (is_array($entry)) {
            if (array_key_exists('@attributes', $entry)) {
                return $entry['@attributes'];
            }
        }

        return null;
    }

    private function parseConverter(): void
    {
        if (! $this->converter) {
            return;
        }

        $content = $this->converter->content();
        $this->root = $content['@root'] ?? null;
        $this->rootNS = $content['@rootNS'] ?? null;
        $this->version = $content['version'] ?? null;
        $this->encoding = $content['encoding'] ?? null;

        $this->content = $content[$this->root] ?? [];
        if (array_key_exists('@attributes', $this->content)) {
            $this->rootAttributes = $this->content['@attributes'] ?? null;
            unset($this->content['@attributes']);
        }
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
    public function search(string $key): mixed
    {
        $result = $this->findValuesBySimilarKey($this->content, $key);

        return empty($result) ? null : $result;
    }

    /**
     * Search for a key in XML.
     *
     * @param  string  $key  Key to search
     * @param  bool  $strict  If true, search for exact key. Default: `false`.
     * @param  bool  $value  If true, get `_value` directly (if exists). Default: `false`.
     * @param  bool  $attributes  If true, get `@attributes` directly (if exists). Default: `false`.
     */
    public function find(string $key, bool $strict = false, bool $value = false, bool $attributes = false): mixed
    {
        $result = $this->findValuesBySimilarKey($this->content, $key, $strict);
        if (empty($result)) {
            return null;
        }

        $result = reset($result);

        if (is_string($result)) {
            return $result;
        }
        if ($value && array_key_exists('@content', $result)) {
            $result = $result['@content'];
        }
        if ($attributes && array_key_exists('@attributes', $result)) {
            $result = $result['@attributes'];
        }

        return $result;
    }

    private function findValuesBySimilarKey(array $array, string $search, bool $strict = false): array
    {
        $results = [];

        foreach ($array as $key => $value) {
            if ($strict) {
                if ($key === $search) {
                    $results[$key] = $value;
                }
            } else {
                if (str_contains($key, $search)) {
                    $results[$key] = $value;
                }
            }

            if (is_array($value)) {
                $nestedResults = $this->findValuesBySimilarKey($value, $search, $strict);
                $results = [
                    ...$results,
                    ...$nestedResults,
                ];
            }
        }

        return $results;
    }

    /**
     * Value of root element.
     */
    public function root(): ?string
    {
        return $this->root;
    }

    /**
     * Namespaces of root element.
     *
     * @return string[]
     */
    public function rootNS(): array
    {
        return $this->rootNS;
    }

    /**
     * Attributes of root element.
     *
     * @return array<string, mixed>
     */
    public function rootAttributes(): array
    {
        return $this->rootAttributes;
    }

    /**
     * Attribute of root element.
     */
    public function rootAttribute(string $key): mixed
    {
        return $this->rootAttributes[$key] ?? null;
    }

    /**
     * Version of XML.
     */
    public function version(): ?string
    {
        return $this->version;
    }

    /**
     * Encoding of XML.
     */
    public function encoding(): ?string
    {
        return $this->encoding;
    }

    /**
     * Content of XML from root element.
     *
     * @return array<string, mixed>
     */
    public function content(): array
    {
        return $this->content;
    }

    /**
     * If XML is valid.
     */
    public function isValidXml(): bool
    {
        return $this->validXml;
    }

    /**
     * Path of XML file.
     */
    public function path(): ?string
    {
        return $this->path;
    }

    /**
     * Filename of XML file.
     */
    public function filename(): ?string
    {
        return $this->filename;
    }

    /**
     * XML converter.
     */
    public function converter(): ?XmlConverter
    {
        return $this->converter;
    }

    /**
     * Save XML to file.
     */
    public function save(string $path): bool
    {
        return file_put_contents($path, $this->converter->xml()) !== false;
    }

    /**
     * Convert XML to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->converter->content();
    }

    /**
     * Convert XML to string.
     */
    public function __toString(): string
    {
        return $this->converter->xml();
    }
}
