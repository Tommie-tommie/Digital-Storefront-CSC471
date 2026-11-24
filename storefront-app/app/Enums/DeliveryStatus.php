<?php
namespace App\Enums;

enum DeliveryStatus: string {
    case PENDING = 'PENDING';
    case PROCESSING = 'PROCESSING';
    case SHIPPED = 'SHIPPED';
    case DELIVERING = 'DELIVERING';
    case DELIVERED = 'DELIVERED';
    case CANCELED = 'CANCELED';
}