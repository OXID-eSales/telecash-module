<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API;

use OxidSolutionCatalysts\TeleCash\IPG\API\Exception\ResponseException;

/**
 * Class AbstractResponse
 */
abstract class AbstractResponse
{
    /**
     * @param \DOMDocument $doc
     * @param string $namespace
     * @param string $tagName
     * @param bool $isOptional
     * @param string $default
     * @return string
     * @throws ResponseException
     */
    protected function firstElementByTagNSString(
        \DOMDocument $doc,
        string $namespace,
        string $tagName,
        bool $isOptional = false,
        string $default = ''
    ): string {
        $elements = $doc->getElementsByTagNameNS($namespace, $tagName);

        if ($elements->length > 0) {
            $item0 = $elements->item(0);
            if ($item0) {
                return trim((string)$item0->nodeValue);
            }
        }
        if ($isOptional) {
            return $default;
        }

        throw new ResponseException("Tag " . $namespace . ':' . $tagName . " not found");
    }
}
