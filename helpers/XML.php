<?php
namespace kfosoft\helpers;

use SimpleXMLElement;

/**
 * Class for encode xml string to array or object & encode array to SimpleXMLElement.
 * @package kfosoft\helpers
 * @version 1.0
 * @copyright (c) 2014-2015 KFOSoftware Team <kfosoftware@gmail.com>
 */
class XML
{
    /** @var string root node. */
    protected $_rootNode = '';

    /** @var string list node. */
    protected $_listNode = '';

    /** @var string item node. */
    protected $_itemNode = '';

    /** @var string charset encoding. */
    protected $_charset = '';

    /** @var string xml version. */
    protected $_xmlVersion = '';

    /** @var string attribute array node. */
    protected $_attributeArrayLabel = '';

    /**
     * @param string $rootNode root node.
     * @param string $listNode list node.
     * @param string $itemNode item node.
     * @param string $charset charset encoding.
     * @param string $xmlVersion xml version.
     * @param string $attributeArrayLabel attribute array node.
     */
    public function __construct($rootNode = 'document', $listNode = 'list', $itemNode = 'item', $charset = 'UTF-8', $xmlVersion = '1.0', $attributeArrayLabel = 'attribute:')
    {
        $this->_rootNode = $rootNode;
        $this->_listNode = $listNode;
        $this->_itemNode = $itemNode;
        $this->_charset = $charset;
        $this->_xmlVersion = $xmlVersion;
        $this->_attributeArrayLabel = $attributeArrayLabel;
    }

    /**
     * Add children node or attribute recursive.
     * @param SimpleXMLElement $element child nodes or attributes.
     * @param array $data data for create child nodes or attributes.
     * @return SimpleXMLElement child nodes with attributes.
     */
    private function _addChildren(SimpleXMLElement $element, $data)
    {
        foreach ($data as $key => $value) {
            if (preg_match('/^' . $this->_attributeArrayLabel . '([a-z0-9\._-]*)/', $key, $attribute)) {
                if (is_array($value)) {
                    foreach ($value as $k => $v) {
                        $element->addAttribute($this->_formatName($k), $this->_formatValue($v));
                    }
                } else {
                    $element->addAttribute($this->_formatName($attribute[1]), $this->_formatValue($value));
                }
                continue;
            }
            $name = is_numeric($key) ? (is_array($value) ? $this->_listNode : $this->_itemNode) : $key;
            $node = $this->_formatName($name);
            if (is_array($value)) {
                $child = $element->addChild($node);
                $this->_addChildren($child, $value);
                continue;
            }
            $element->addChild($node, $value);
        }

        return $element;
    }

    /**
     * Returns formatted name.
     * @param string $string for format.
     * @return string formatted string.
     */
    private function _formatName($string)
    {
        $pattern = ['/[^a-z0-9\._ -]/i' => '', '/(?=^[[0-9]\.\-\:^xml])/i' => $this->_itemNode . '_', '/ /' => '_'];

        return strtolower(preg_replace(array_keys($pattern), array_values($pattern), $string));
    }

    /**
     * Returns formatted value.
     * @param string $string for format.
     * @return string formatted string.
     */
    private function _formatValue($string)
    {
        return is_null($string) ? '' : (is_bool($string) ? $this->_bool($string) : $string);
    }

    /**
     * Returns bool in string.
     * @param bool $bool for format.
     * @return string formatted string.
     */
    private function _bool($bool)
    {
        return $bool ? 'TRUE' : 'FALSE';
    }

    /**
     * Is bool string.
     * @param string $bool
     * @return bool
     */
    private function isBool($bool)
    {
        return $bool === 'TRUE' || $bool === 'true' || $bool === 'True' || $bool === 'FALSE' || $bool === 'false' || $bool === 'False';
    }

    /**
     * Is bool string.
     * @param string $bool
     * @return bool
     */
    private function toBool($bool)
    {
        if ($bool === 'TRUE' || $bool === 'true' || $bool === 'True') {
            $bool = true;
        } elseif ($bool === 'FALSE' || $bool === 'false' || $bool === 'False') {
            $bool = false;
        } else {
            $bool = null;
        }

        return $bool;
    }

    /**
     * XML to array.
     * @param SimpleXMLElement $element xml for parse.
     * @return array data.
     */
    private function _toArray(SimpleXMLElement $element)
    {
        $array = [];
        $attributes = (array)$element->attributes();
        if (isset($attributes['@attributes'])) {
            $array['xmlAttributes'] = $attributes['@attributes'];
        }

        foreach ($element->children() as $key => $child) {
            $value = (string)$child;
            $_children = $this->_toArray($child);
            $_push = ($_hasChild = (count($_children) > 0)) ? $_children : ($this->isBool($value) ? $this->toBool($value) : $value);
            if ($_hasChild && !empty($value)) {
                $_push[] = $this->isBool($value) ? $this->toBool($value) : $value;
            }

            if (($key == $this->_itemNode) || ($key == $this->_listNode)) {
                $array[] = $_push;
            } else {
                $array[$key] = $_push;
            }
        }

        return $array;
    }

    /**
     * Encode array to XML.
     * @param array $data data for encode.
     * @param string|null $root root element.
     * @param bool $asString as xml string.
     * @return SimpleXMLElement|string
     */
    public function encode(array $data, $root = null, $asString = true)
    {
        /* Get first node name & value. */
        $node = $this->_formatName(is_null($root) ? $this->_rootNode : $root);
        $value = ($isArray = is_array($data)) ? null : $this->_formatValue($data);

        /* Create XML document. */
        $xml = "<?xml version=\"" . $this->_xmlVersion . "\"?><{$node}>{$value}</{$node}>";
        $xml = new SimpleXMLElement($xml);

        /* Get other part of document */
        if ($isArray) {
            $xml = $this->_addChildren($xml, $data);
        }

        return $asString ? $xml->asXML() : $xml;
    }

    /**
     * Decode xml in object or array.
     * @param string|SimpleXMLElement $xml xml for decode.
     * @param bool $asObject return as object?
     * @return array|\stdClass
     */
    public function decode($xml, $asObject = false)
    {
        /* If not instance of SimpleXMLElement create object of SimpleXMLElement */
        !($xml instanceof SimpleXMLElement) && ($xml = new SimpleXMLElement($xml));

        $result = $this->_toArray($xml);

        return $asObject ? (object)$result : $result;
    }
}
