<?php

namespace Defr\Parser\Helper;

final class StringHelper
{
    /**
     * @param string $text
     *
     * @return string
     */
    public static function removeEmptyLines($text)
    {
        return preg_replace('/^[ \t]*[\r\n]+/m', '', $text);
    }
}
