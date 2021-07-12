<?php

namespace KgBot\Magento\Builders;

use Generator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use KgBot\Magento\Exceptions\MagentoClientException;
use KgBot\Magento\Exceptions\MagentoRequestException;
use KgBot\Magento\Utils\Model;
use KgBot\Magento\Utils\Request;

class Builder
{
	protected $entity;
	/** @var Model */
	protected $model;
	protected $request;

	public function __construct( Request $request ) {
		$this->request = $request;
	}

	/**
	 * @param array $filters
	 *
	 * @return Collection|Model[]
	 * @throws MagentoClientException
	 * @throws MagentoRequestException
	 */
	public function get( $filters = [] ) {
		$urlFilters = $this->parseFilters( $filters );

		return $this->request->handleWithExceptions( function () use ( $urlFilters ) {

			$response     = $this->request->client->get( "{$this->entity}{$urlFilters}" );
			$responseData = json_decode( (string) $response->getBody() );

			return $this->parseResponse( $responseData );
		} );
	}

	protected function parseFilters( $filters ) {
		$urlFilters = '?searchCriteria';
		$count      = count( $filters );
		if ( $count > 0 ) {
			foreach ( $filters as $index => $filter ) {

				$conditionType = $filter['condition_type'] ?? 'eq';

				$urlFilters .= "[filter_groups][{$index}][filters][0][field]={$filter['field']}";
				$urlFilters .= '&searchCriteria';
				$urlFilters .= "[filter_groups][{$index}][filters][0][value]={$filter['value']}";
				$urlFilters .= '&searchCriteria';
				$urlFilters .= "[filter_groups][{$index}][filters][0][condition_type]={$conditionType}";
				$urlFilters .= ( $count > 1 && ( $index < $count - 1 ) ) ? '&searchCriteria' : '';
			}
		} else {
			$urlFilters .= '=[]';
		}

		return $urlFilters;
	}

	protected function parseResponse( $response ) {
		$fetchedItems = collect( $response->items );
		$items        = collect( [] );

		foreach ( $fetchedItems as $index => $item ) {
			/** @var Model $model */
			$model = new $this->model( $item );

			$items->push( $model );
		}

		return $items;
	}

    /**
     * It will iterate over all pages until it does not receive empty response, you can also set query parameters,
     * Return a Generator that you' handle first before quering the next offset
     *
     * @param $filters
     * @param int $chunkSize
     *
     * @return Generator
     * @throws MagentoClientException
     * @throws MagentoRequestException
     */
    public function allWithGenerator($filters, int $chunkSize = 50)
    {
        $page = 1;

        $response = function ($page) use ($filters, $chunkSize) {
            $urlFilters = $this->parseFilters($filters);
            $urlFilters .= '&searchCriteria[pageSize]='. $chunkSize;
            $urlFilters .= '&searchCriteria[currentPage]=' . $page;

            return $this->request->handleWithExceptions( function () use ( $urlFilters ) {
                $response = $this->request->client->get( "{$this->entity}{$urlFilters}" );
                $responseData = json_decode( (string) $response->getBody() );

                return [
                    'items' => $this->parseResponse( $responseData )
                ];
            });
        };

        do {
            $resp = $response($page);

            $countResults = count($resp->items);
            if ($countResults === 0) {
                break;
            }
            // make a generator of the results and return them
            // so the logic will handle them before the next iteration
            // in order to avoid memory leaks
            foreach ($resp->items as $result) {
                yield $result;
            }

            unset($resp);

            $page++;
        } while ($countResults === $chunkSize);
    }

    public function find( $id ) {
		return $this->request->handleWithExceptions( function () use ( $id ) {
			$response     = $this->request->client->get( "{$this->entity}/{$id}" );
			$responseData = json_decode( (string) $response->getBody() );

			return new $this->model( $responseData );
		} );
	}

	public function create( $data ) {
		$data = [
			Str::singular( $this->entity ) => $data,
		];

		return $this->request->handleWithExceptions( function () use ( $data ) {
			$response = $this->request->client->post( "{$this->entity}", [
				'json' => $data,
			] );

			$responseData = json_decode( (string) $response->getBody() );

			return new $this->model( $responseData );
		} );
	}

    public function update($id, $data = [])
    {
        $data = [
            Str::singular( $this->entity ) => $data,
        ];

        return $this->request->handleWithExceptions( function () use ( $id, $data ) {
            $response = $this->request->client->put( "{$this->entity}/" . urlencode( $id), [
                'json' => $data,
            ] );

            $responseData = json_decode( (string) $response->getBody() );

            return new $this->model( $responseData );
        } );
    }

    public function delete($id)
    {
        return $this->request->handleWithExceptions( function () use ($id) {
            return $this->request->client->delete( "{$this->entity}/" . urlencode( $id ) );
        } );
    }

	public function getEntity() {
		return $this->entity;
	}

	public function setEntity( $new_entity ) {
		$this->entity = $new_entity;

		return $this->entity;
	}
}