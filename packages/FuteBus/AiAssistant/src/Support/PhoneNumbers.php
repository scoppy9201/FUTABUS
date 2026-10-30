<?php

declare(strict_types=1);

namespace FuteBus\AiAssistant\Support;

use libphonenumber\PhoneNumberUtil;

class PhoneNumbers
{
    public static function util(): PhoneNumberUtil
    {
        return PhoneNumberUtil::getInstance();
    }
}
