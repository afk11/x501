<?php

namespace X501\ASN1\AttributeValue\Feature;

use ASN1\Element;
use ASN1\Type\Primitive\BMPString;
use ASN1\Type\Primitive\PrintableString;
use ASN1\Type\Primitive\T61String;
use ASN1\Type\Primitive\UniversalString;
use ASN1\Type\Primitive\UTF8String;
use X501\ASN1\AttributeValue\AttributeValue;
use X501\DN\DNParser;
use X501\MatchingRule\CaseIgnoreMatch;
use X501\StringPrep\TranscodeStep;


/**
 * Base class for attribute values having <i>(Unbounded)DirectoryString</i>
 * as a syntax.
 *
 * @link
 *       https://www.itu.int/ITU-T/formal-language/itu-t/x/x520/2012/SelectedAttributeTypes.html#SelectedAttributeTypes.UnboundedDirectoryString
 */
abstract class DirectoryString extends AttributeValue
{
	/**
	 * Teletex string syntax.
	 *
	 * @var int
	 */
	const TELETEX = Element::TYPE_T61_STRING;
	
	/**
	 * Printable string syntax.
	 *
	 * @var int
	 */
	const PRINTABLE = Element::TYPE_PRINTABLE_STRING;
	
	/**
	 * BMP string syntax.
	 *
	 * @var int
	 */
	const BMP = Element::TYPE_BMP_STRING;
	
	/**
	 * Universal string syntax.
	 *
	 * @var int
	 */
	const UNIVERSAL = Element::TYPE_UNIVERSAL_STRING;
	
	/**
	 * UTF-8 string syntax.
	 *
	 * @var int
	 */
	const UTF8 = Element::TYPE_UTF8_STRING;
	
	/**
	 * Mapping from syntax enumeration to ASN.1 class name.
	 *
	 * @var array
	 */
	private static $_tagToCls = array(
		/* @formatter:off */
		self::TELETEX => T61String::class,
		self::PRINTABLE => PrintableString::class,
		self::UNIVERSAL => UniversalString::class,
		self::UTF8 => UTF8String::class,
		self::BMP => BMPString::class
		/* @formatter:on */
	);
	
	/**
	 * ASN.1 type tag for the chosen syntax.
	 *
	 * @var int $_stringTag
	 */
	protected $_stringTag;
	
	/**
	 * String value.
	 *
	 * @var string $_string
	 */
	protected $_string;
	
	/**
	 * Constructor
	 *
	 * @param string $value String value
	 * @param int $string_tag Syntax choice
	 */
	public function __construct($value, $string_tag) {
		$this->_string = $value;
		$this->_stringTag = $string_tag;
	}
	
	/**
	 *
	 * @param Element $el
	 * @throws \UnexpectedValueException
	 * @return self
	 */
	public static function fromASN1(Element $el) {
		$tag = $el->tag();
		if (!isset(self::$_tagToCls[$tag])) {
			throw new \UnexpectedValueException(
				"Type " . Element::tagToName($tag) .
					 " is not valid DirectoryString");
		}
		return new static($el->str(), $tag);
	}
	
	/**
	 *
	 * @see \X501\ASN1\AttributeValue\AttributeValue::toASN1()
	 * @return Element
	 */
	public function toASN1() {
		if (!isset(self::$_tagToCls[$this->_stringTag])) {
			throw new \UnexpectedValueException(
				"Type " . Element::tagToName($this->_stringTag) .
					 " is not valid DirectoryString");
		}
		$cls = self::$_tagToCls[$this->_stringTag];
		return new $cls($this->_string);
	}
	
	/**
	 *
	 * @see \X501\ASN1\AttributeValue\AttributeValue::stringValue()
	 * @return string
	 */
	public function stringValue() {
		return $this->_string;
	}
	
	/**
	 *
	 * @see \X501\ASN1\AttributeValue\AttributeValue::equalityMatchingRule()
	 * @return CaseIgnoreMatch
	 */
	public function equalityMatchingRule() {
		return new CaseIgnoreMatch($this->_stringTag);
	}
	
	/**
	 *
	 * @see \X501\ASN1\AttributeValue\AttributeValue::rfc2253String()
	 * @return string
	 */
	public function rfc2253String() {
		// TeletexString is encoded as binary
		if ($this->_stringTag == self::TELETEX) {
			return $this->_transcodedString();
		}
		return DNParser::escapeString($this->_transcodedString());
	}
	
	/**
	 *
	 * @see \X501\ASN1\AttributeValue\AttributeValue::_transcodedString()
	 * @return string
	 */
	protected function _transcodedString() {
		$step = new TranscodeStep($this->_stringTag);
		return $step->apply($this->_string);
	}
}