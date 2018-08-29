<?php
namespace ValuePad\Core\Customer\Enums;
use Ascope\Libraries\Enum\Enum;

class Rule extends Enum
{
    const REQUIRE_ENV = 'requireEnv';

    const CLIENT_ADDRESS_1 = 'clientAddress1';
    const CLIENT_ADDRESS_2 = 'clientAddress2';
    const CLIENT_STATE = 'clientState';
    const CLIENT_CITY = 'clientCity';
    const CLIENT_ZIP = 'clientZip';

    const CLIENT_DISPLAYED_ON_REPORT_ADDRESS_1 = 'clientDisplayedOnReportAddress1';
    const CLIENT_DISPLAYED_ON_REPORT_ADDRESS_2 = 'clientDisplayedOnReportAddress2';
    const CLIENT_DISPLAYED_ON_REPORT_STATE = 'clientDisplayedOnReportState';
    const CLIENT_DISPLAYED_ON_REPORT_CITY = 'clientDisplayedOnReportCity';
    const CLIENT_DISPLAYED_ON_REPORT_ZIP = 'clientDisplayedOnReportZip';

    const DISPLAY_FDIC = 'displayFdic';
}
