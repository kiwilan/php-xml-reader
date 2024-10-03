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
    protected array $contents = [];

    protected ?XmlConverter $converter = null;

    protected function __construct(
        protected ?string $path = null,
        protected ?string $filename = null,
        protected bool $validXml = false,
    ) {}

    /**
     * @param  string  $xml  XML string or path to XML file
     * @param  bool  $mapContent  If a key has only `@content` key, return only the value of `@content`. Default: `true`.
     * @param  bool  $failOnError  Throw exception if XML is invalid. Default: `true`.
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
        $validXml = (new XmlValidator)->isXMLContentValid($xml);

        if (! $validXml) {
            $msg = 'XML is not valid';
            if ($failOnError) {
                throw new Exception($msg, 1);
            }

            error_log($msg);
        }

        $self = new self($path, $basename, $validXml);

        if (! $validXml) {
            return $self;
        }

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
     * Parse content of entry, if entry has `@content` key, return only the value of `@content` otherwise return the entry.
     */
    public static function parseContent(mixed $entry): mixed
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
     * Parse attributes of entry, if entry has `@attributes` key.
     *
     * @return array<string, mixed>|null
     */
    public static function parseAttributes(mixed $entry): ?array
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

        $contents = $this->converter->getContents();
        $this->root = $contents['@root'] ?? null;
        $this->rootNS = $contents['@rootNS'] ?? null;
        $this->version = $contents['version'] ?? null;
        $this->encoding = $contents['encoding'] ?? null;

        $this->contents = $contents[$this->root] ?? [];
        if (array_key_exists('@attributes', $this->contents)) {
            $this->rootAttributes = $this->contents['@attributes'] ?? null;
            unset($this->contents['@attributes']);
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

        $data = $this->contents;
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
     */
    public function search(string $key): mixed
    {
        $result = $this->findValuesBySimilarKey($this->contents, $key);

        return empty($result) ? null : $result;
    }

    /**
     * Search for a key in XML.
     *
     * @param  string  $key  Key to search
     * @param  bool  $strict  If true, search for exact key. Default: `true`.
     * @param  bool  $content  If true, get `@content` directly (if exists). Default: `false`.
     * @param  bool  $attributes  If true, get `@attributes` directly (if exists). Default: `false`.
     */
    public function find(string $key, bool $strict = true, bool $content = false, bool $attributes = false): mixed
    {
        $result = $this->findValuesBySimilarKey($this->contents, $key, $strict);
        if (empty($result)) {
            return null;
        }

        $result = reset($result);

        if (is_string($result)) {
            return $result;
        }
        if ($content && array_key_exists('@content', $result)) {
            $result = $result['@content'];
        }
        if ($attributes && array_key_exists('@attributes', $result)) {
            $result = $result['@attributes'];
        }

        return $result;
    }

    private function findValuesBySimilarKey(array $array, string $search, bool $strict = false, int $index = 0): array
    {
        $results = [];

        foreach ($array as $key => $value) {
            if ($strict && $key === $search) {
                $results[$key] = $value;
            }

            if (! $strict && str_contains($key, $search)) {
                $unique = $key.'-'.$index;
                if (array_key_exists($key, $results)) {
                    $results[$unique] = $value;
                } else {
                    $results[$key] = $value;
                }
            }

            if (is_array($value)) {
                $nestedResults = $this->findValuesBySimilarKey($value, $search, $strict, $index);
                if (empty($nestedResults)) {
                    continue;
                }

                foreach ($nestedResults as $k => $v) {
                    $unique = $k.'-'.$index;
                    if (array_key_exists($k, $results)) {
                        $results[$unique] = $v;
                    } else {
                        $results[$k] = $v;
                    }
                }
                $index++;
            }
        }

        return $results;
    }

    /**
     * Value of root element.
     */
    public function getRoot(): ?string
    {
        return $this->root;
    }

    /**
     * Namespaces of root element.
     *
     * @return string[]
     */
    public function getRootNS(): array
    {
        return $this->rootNS;
    }

    /**
     * Attributes of root element.
     *
     * @return array<string, mixed>
     */
    public function getRootAttributes(): array
    {
        return $this->rootAttributes;
    }

    /**
     * Attribute of root element.
     */
    public function getRootAttribute(string $key): mixed
    {
        return $this->rootAttributes[$key] ?? null;
    }

    /**
     * Version of XML.
     */
    public function getVersion(): ?string
    {
        return $this->version;
    }

    /**
     * Encoding of XML.
     */
    public function getEncoding(): ?string
    {
        return $this->encoding;
    }

    /**
     * Content of XML from root element.
     *
     * @return array<string, mixed>
     *
     * @deprecated Use `getContents()` instead.
     */
    public function getContent(): array
    {
        return $this->contents;
    }

    /**
     * Content of XML from root element.
     *
     * @return array<string, mixed>
     */
    public function getContents(): array
    {
        return $this->contents;
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
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * Filename of XML file.
     */
    public function getFilename(): ?string
    {
        return $this->filename;
    }

    /**
     * XML converter.
     */
    public function getConverter(): ?XmlConverter
    {
        return $this->converter;
    }

    /**
     * Save XML to file.
     */
    public function save(string $path): bool
    {
        return file_put_contents($path, $this->converter->getXml()) !== false;
    }

    /**
     * Convert XML to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->converter->getContents();
    }

    /**
     * Convert XML to string.
     */
    public function __toString(): string
    {
        return $this->converter->getXml();
    }
}
