<?php

namespace App\Enum;

enum QuestStatus: string
{
    case AVAILABLE  = 'available';
    case ACCEPTED   = 'accepted';
    case COMPLETED  = 'completed';
}