<?php

namespace KgBot\Magento\Builders;

use KgBot\Magento\Models\Product;

class ProductBuilder extends Builder
{
    protected $entity = 'products';
    protected $model  = Product::class;

    public function updateStockItem($productSku, $id, $data = [])
    {
        $data = [
            Str::singular( $this->entity ) => $data,
        ];

        return $this->request->handleWithExceptions( function () use ( $productSku, $id, $data ) {
            $response = $this->request->client->put( "{$this->entity}/" . urlencode($productSku) . "/stockItems/" . $id, [
                'json' => $data,
            ] );

            $responseData = json_decode( (string) $response->getBody() );

            return new $this->model( $responseData );
        } );
    }
}