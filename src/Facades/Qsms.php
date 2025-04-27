<?php

namespace Qsms\QbiezSms\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array send(string $to, string $message)
 * @method static array sendTemplate(string $to, string $template, array $variables = [])
 * @method static string formatPhoneNumber(string $number)
 *
 * @see \Qsms\QbiezSms\SendSMS
 */
class Qsms extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'qsms';
    }
}
