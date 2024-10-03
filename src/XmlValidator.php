<?php

namespace Kiwilan\XmlReader;

/**
 * @author Francesco Casula <fra.casula@gmail.com>
 *
 * @docs https://stackoverflow.com/a/30058598/11008206
 */
class XmlValidator
{
    /**
     * @param  string  $xmlFilename  Path to the XML file
     * @param  string  $version  1.0
     * @param  string  $encoding  utf-8
     */
    public function isXMLFileValid($xmlFilename, $version = '1.0', $encoding = 'utf-8'): bool
    {
        $xmlContent = file_get_contents($xmlFilename);

        return $this->isXMLContentValid($xmlContent, $version, $encoding);
    }

    /**
     * @param  string  $xmlContent  A well-formed XML string
     * @param  string  $version  1.0
     * @param  string  $encoding  utf-8
     */
    public function isXMLContentValid($xmlContent, $version = '1.0', $encoding = 'utf-8'): bool
    {
        if (trim($xmlContent) == '') {
            return false;
        }

        libxml_use_internal_errors(true);

        $doc = new \DOMDocument($version, $encoding);
        $doc->loadXML($xmlContent);

        $errors = libxml_get_errors();
        libxml_clear_errors();

        return empty($errors);
    }
}
