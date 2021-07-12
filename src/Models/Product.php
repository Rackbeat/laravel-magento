<?php

namespace KgBot\Magento\Models;

use KgBot\Magento\Utils\Model;

class Product extends Model
{
    protected $entity     = 'products';
    protected $primaryKey = 'sku';
}
