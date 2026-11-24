<?php
namespace App\Enums;

enum ProductStatus: string {
    case ACTIVE = 'ACTIVE';
    case RETIRED = 'RETIRED';
    case PLANNING = 'PLANNING';
}