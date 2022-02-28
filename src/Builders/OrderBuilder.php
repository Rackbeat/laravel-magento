<?php

namespace KgBot\Magento\Builders;

use KgBot\Magento\Models\Order;

class OrderBuilder extends Builder
{
    protected $entity = 'orders';
    protected $model  = Order::class;

    public function createShipmentForOrder($orderId, $data = [])
    {
        return $this->request->handleWithExceptions( function () use ( $orderId, $data ) {
            $response = $this->request->client->post( "order/" . urlencode($orderId) . "/ship", [
                'json' => $data,
            ] );

            $responseData = json_decode( (string) $response->getBody() );

            return new $this->model( $responseData );
        } );
    }
}