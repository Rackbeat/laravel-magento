<?php

namespace KgBot\Magento\Builders;

use KgBot\Magento\Models\Customer;
use KgBot\Magento\Models\CustomerAddress;

class CustomerBuilder extends Builder
{
    protected $entity = 'customers';
    protected $model  = Customer::class;

    public function get( $filters = [] )
    {
        $urlFilters = $this->parseFilters( $filters );

        return $this->request->handleWithExceptions( function () use ( $urlFilters ) {
            $response     = $this->request->client->get( "{$this->entity}/search{$urlFilters}" );
            $responseData = json_decode( (string) $response->getBody() );
            $items        = $this->parseResponse( $responseData );

            return $items;
        } );
    }

    public function getBillingAddress($id)
    {
        return $this->request->handleWithExceptions( function () use ($id) {
            $address      = $this->request->client->get( "{$this->entity}/{$id}/billingAddress" );
            $responseData = json_decode( (string) $address->getBody() );

            if ( ! empty( $responseData ) ) {
                return new CustomerAddress( $responseData );
            }

            return null;
        } );
    }

    public function getShippingAddress($id)
    {
        return $this->request->handleWithExceptions( function () use ($id) {
            $address      = $this->request->client->get( "{$this->entity}/{$id}/shippingAddress" );
            $responseData = json_decode( (string) $address->getBody() );

            if ( ! empty( $responseData ) ) {
                return new CustomerAddress( $responseData );
            }

            return null;
        } );
    }
}