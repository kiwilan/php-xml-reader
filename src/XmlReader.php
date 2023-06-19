<?php

namespace Kiwilan\XmlReader;

use Exception;
use Kiwilan\XmlReader\Converters\GaarfXml;
use Kiwilan\XmlReader\Converters\XmlArray;

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
