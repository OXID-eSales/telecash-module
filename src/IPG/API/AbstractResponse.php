<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API;

/**
 * Class AbstractResponse
 */
abstract class AbstractResponse
{
    /**
     * @param \DOMDocument $doc
     * @param string $namespace
     * @param string $tagName
     * @return string
     * @throws \Exception
     */
    protected function firstElementByTagNSString(\DOMDocument $doc, string $namespace, string $tagName): string
    {
        $elements = $doc->getElementsByTagNameNS($namespace, $tagName);

        if ($elements->length > 0) {
            $item0 = $elements->item(0);
            if ($item0) {
                return trim((string)$item0->nodeValue);
            }
        }

        throw new \Exception("Tag " . $namespace . ':' . $tagName . " not found");
    }
}
