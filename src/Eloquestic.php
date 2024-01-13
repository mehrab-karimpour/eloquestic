<?php

namespace mehrab\eloquestic;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Elasticsearch\Exception\AuthenticationException;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Log;
use mehrab\eloquestic\Exceptions\IndexNotSelectedException;
use mehrab\eloquestic\Exceptions\ToManyLargeException;
use function Symfony\Component\Translation\t;

class Eloquestic extends Facade
{
    use ElasticBuilder;

    public static function name(): string
    {
        return 'elastic';
    }

    public ?string $index = null;
    public ?array $searchables = null;
    private Client $client;

    /**
     * @throws AuthenticationException
     */
    public function __construct()
    {
        $this->client = ClientBuilder::create()->build();
    }

    public function setIndex(string $index): static
    {
        $this->index = $index;
        return $this;
    }

    public function setSearchables(array $searchables): static
    {
        $this->searchables = $searchables;
        return $this;
    }

    public function delete(string $index, string|int $id): bool
    {
        try {
            $this->client->delete([
                'index' => $index,
                'id' => $id
            ]);
            return true;
        } catch (\Exception $e) {
            Log::error($e);
        }
        return false;
    }

    /**
     * @throws AuthenticationException
     * @throws \Throwable
     * @throws ClientResponseException
     * @throws ServerResponseException
     * @throws MissingParameterException
     */
    public function index(array|Model $data, ?string $index = null): bool
    {
        $index = $index ?? $this->index;

        throw_if(!$index, IndexNotSelectedException::class);

        $this->client = ClientBuilder::create()->build();
        $params = [
            'index' => $index,
            'id' => $data['id'],
            'body' => $data
        ];
        $result = $this->client->index($params);
        return !!$result;
    }

    /**
     * @param array $data
     * @param string|null $index
     * @return bool
     * @throws AuthenticationException
     * @throws ClientResponseException
     * @throws MissingParameterException
     * @throws ServerResponseException
     * @throws \Throwable
     */
    public function create(array|Model $data, ?string $index = null): bool
    {
        return $this->index($data, $index);
    }

    /**
     * @param array $data
     * @param string|null $index
     * @return bool
     * @throws AuthenticationException
     * @throws ClientResponseException
     * @throws MissingParameterException
     * @throws ServerResponseException
     * @throws \Throwable
     */
    public function insert(array|Collection|Model $data, ?string $index = null): bool
    {
        throw_if(count($data) > 10000, ToManyLargeException::class);

        if ($data instanceof Collection && !isset($data->id, $data['id'])) {
            foreach ($data as $datum) {
                $this->index($datum, $index);
            }
        } else {
            $this->index($data, $index);
        }


        return true;
    }


    private function cleanResult($res): array
    {
        $cleanedData = [];
        foreach ($res->asArray()['hits']['hits'] as $hit) {
            $cleanedData[] = $hit['_source'];
        };
        return $cleanedData;
    }
}
