<?php

namespace Coff\Ticker;

enum Time: string
{
    case YEAR = 'y';
    case MONTH = 'n';
    case WEEK = 'W';
    case DAY = 'z';
    case HOUR = 'G';
    case MINUTE = 'i';
    case SECOND = 's';
    case SECOND_10TH = 's/10';
    case SECOND_100TH = 's/100';
    case SECOND_1000TH = 's/1000';
    case SECOND_10000TH = 's/10000';
    case MICROSECOND = 'u';
}