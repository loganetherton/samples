<?php
namespace ValuePad\DAL\Location\Support;
use Ascope\Libraries\Enum\Enum;

class Error extends Enum
{
    const DENIED = 'denied';
    const INVALID = 'invalid';
    const OVER_QUERY_LIMIT = 'over-query-limit';
    const ZERO_RESULTS = 'zero-results';
    const SERVER = 'server';
    const UNKNOWN = 'unknown';
}
