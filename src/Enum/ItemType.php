<?php

namespace App\Enum;

enum ItemType: string
{
    case WEAPON = 'weapon';
    case ARMOR  = 'armor';
    case POTION = 'potion';
    case MISC   = 'misc';
}