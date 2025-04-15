<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Kpopia\LegacyRedirect\Model\Validation;

use Laminas\Validator\Regex;
use Magento\Framework\Validator\AbstractValidator;
use Magento\Framework\Validator\RegexFactory;
use Magento\Store\Model\Validation\StoreCodeValidator as BaseValidator;
/**
 * Validator for store code.
 */
class StoreCodeValidator extends BaseValidator
{
    /**
     * @var RegexFactory
     */
    private $regexValidatorFactory;

    /**
     * @param RegexFactory $regexValidatorFactory
     */
    public function __construct(RegexFactory $regexValidatorFactory)
    {
        $this->regexValidatorFactory = $regexValidatorFactory;
    }

    /**
     * @inheritDoc
     */
    public function isValid($value)
    {
        $validator = $this->regexValidatorFactory->create(['pattern' => '/^[a-z]+[a-z0-9_-]*$/i']);
        $validator->setMessage(
            __(
                'The store code may contain only letters (a-z), numbers (0-9), underscore (_) or dash (-),'
                . ' and the first character must be a letter.'
            ),
            Regex::NOT_MATCH
        );
        $result = $validator->isValid($value);
        $this->_messages = $validator->getMessages();

        return $result;
    }
}
