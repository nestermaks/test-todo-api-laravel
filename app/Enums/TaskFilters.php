<?php

namespace App\Enums;


enum TaskFilters: string
{
    case Status = 'status';
    case Search = 'search';
    case Priority = 'priority';
}
