<?php

namespace App\Traits;

trait ParseXML
{
    /**
     * @param \SimpleXMLElement $xml
     * @param array $options
     * @return array
     */
    public function xmlToArray($xml, $options = [])
    {
        if (!$xml instanceof \SimpleXMLElement) {
            throw new \InvalidArgumentException('Provided XML is not an instance of SimpleXMLElement.');
        }

        $defaults = [
            'namespaceSeparator' => ':',//you may want this to be something other than a colon
            'attributePrefix' => '@',   //to distinguish between attributes and nodes with the same name
            'alwaysArray' => ['Narrative', 'EventValue', 'Person', 'Evidence'],        //array of xml tag names which should always become arrays
            'autoArray' => true,        //only create arrays for tags which appear more than once
            'textContent' => 'value',   //key used for the text content of elements
            'autoText' => true,         //skip textContent key if node has no attributes or child nodes
        ];
        $options = array_merge($defaults, $options);
        $namespaces = $xml->getDocNamespaces();
        $namespaces[''] = null; //add base (empty) namespace

        //get attributes from all namespaces
        $attributesArray = [];
        foreach ($namespaces as $prefix => $namespace) {
            foreach ($xml->attributes($namespace) as $attributeName => $attribute) {
                $attributeKey = $options['attributePrefix']
                    . ($prefix ? $prefix . $options['namespaceSeparator'] : '')
                    . $attributeName;
                $attributesArray[$attributeKey] = (string)$attribute;
            }
        }

        //get child nodes from all namespaces
        $tagsArray = [];
        foreach ($namespaces as $prefix => $namespace) {
            foreach ($xml->children($namespace) as $childXml) {
                //recurse into child nodes
                $childArray = $this->xmlToArray($childXml, $options);

                foreach ($childArray as $key => $val) {
                    $childTagName = $key;
                    $childProperties = $val;
                    break;
                }
                next($childArray);

                //add namespace prefix, if any
                if ($prefix) $childTagName = $prefix . $options['namespaceSeparator'] . $childTagName;

                if (!isset($tagsArray[$childTagName])) {
                    //only entry with this key
                    //test if tags of this type should always be arrays, no matter the element count
                    $tagsArray[$childTagName] =
                        in_array($childTagName, $options['alwaysArray']) || !$options['autoArray']
                            ? [$childProperties] : $childProperties;
                } elseif (
                    is_array($tagsArray[$childTagName])
                    && array_keys($tagsArray[$childTagName]) === range(0, count($tagsArray[$childTagName]) - 1)
                ) {
                    //key already exists and is integer indexed array
                    $tagsArray[$childTagName][] = $childProperties;
                } else {
                    //key exists so convert to integer indexed array with previous value in position 0
                    $tagsArray[$childTagName] =[$tagsArray[$childTagName], $childProperties];
                }
            }
        }

        //get text content of node
        $textContentArray = [];
        $plainText = trim((string)$xml);
        if ($plainText !== '') $textContentArray[$options['textContent']] = $plainText;

        //stick it all together
        $propertiesArray = !$options['autoText'] || $attributesArray || $tagsArray || ($plainText === '')
            ? array_merge($attributesArray, $tagsArray, $textContentArray) : $plainText;

        //return node as array
        return [$xml->getName() => $propertiesArray];
    }

    /**
     * @param \SimpleXMLElement $xml
     * @param array $options
     * @return string
     */
    public function xmlToJson($xml, $options = [])
    {
        return json_encode($this->xmlToArray($xml, $options));
    }
}
