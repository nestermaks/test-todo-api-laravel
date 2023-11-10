<?php

namespace App\Enums;


enum TaskSorters: string
{
    case CompletedAt = 'completedAt';
    case CreatedAt = 'createdAt';
    case Priority = 'priority';
}
