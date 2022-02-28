<?php

namespace KgBot\Magento\Builders;

use KgBot\Magento\Models\Order;

class OrderBuilder extends Builder
{
    protected $entity = 'orders';
    protected $model  = Order::class; 
}