<?php

namespace X501\ASN1\AttributeValue;

use X501\ASN1\AttributeType;
use X501\ASN1\AttributeValue\Feature\DirectoryString;


/**
 * 'pseudonym' attribute value.
 *
 * @link
 *       https://www.itu.int/ITU-T/formal-language/itu-t/x/x520/2012/SelectedAttributeTypes.html#SelectedAttributeTypes.pseudonym
 */
class PseudonymValue extends DirectoryString
{
	public function __construct($value, $string_tag = DirectoryString::UTF8) {
		$this->_oid = AttributeType::OID_PSEUDONYM;
		parent::__construct($value, $string_tag);
	}
}