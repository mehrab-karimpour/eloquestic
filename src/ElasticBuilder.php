<?php

namespace mehrab\eloquestic;


use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use mehrab\eloquestic\Exceptions\IndexNotSelectedException;

trait ElasticBuilder
{
    public array $queryWhere = [];
    public array $queryOrWhere = [];
    public ?string $sort = null;
    public ?string $order = null;
    public int $size = 10;
    public string $q = '';


    public function orWhere(string $column, string $operator, mixed $value): static
    {
        if ($value === null) return $this;

        $operator = $this->getOperator($operator);
        if ($operator === '=') {
            $this->queryOrWhere[] = [
                "term" => [
                    $column => $value
                ]
            ];
            return $this;
        }

        $this->queryOrWhere[] = [
            "range" => [
                $column => [
                    $operator => $value
                ]
            ]
        ];
        return $this;

    }

    public function where(string $column, string $operator, mixed $value): static
    {
        if ($value === null) return $this;
        $operator = $this->getOperator($operator);
        if ($operator === '=') {
            $this->queryWhere[] = [
                "term" => [
                    $column => $value
                ]
            ];
            return $this;
        }

        $this->queryWhere[] = [
            "range" => [
                $column => [
                    $operator => $value
                ]
            ]
        ];
        return $this;
    }

    public function whereIn(string $column, array $values, string $operator = '='): static
    {
        $this->queryWhere[] = [
            "terms" => [
                $column => $values
            ]
        ];
        return $this;
    }

    public function limit(int $value): static
    {
        $this->size = $value;
        return $this;
    }

    public function orderBy(string $field, $order = 'asc'): static
    {
        $orderAbles = ['asc', 'desc'];
        if (!in_array($order, $orderAbles)) {
            $order = 'asc';
        }
        $this->order = $order;
        $this->sort = $field;
        return $this;
    }

    public function search($q): static
    {
        if ($q) $this->q = $q;
        return $this;
    }

    /**
     * @throws ServerResponseException
     * @throws ClientResponseException
     * @throws IndexNotSelectedException
     */
    public function get(array|string $columns = []): array
    {
        if (!$this->index) {
            throw new IndexNotSelectedException();
        }
        $params = [
            'index' => $this->index,
            'body' => [
                'query' => [
                    'bool' => [
                        'should' => $this->queryOrWhere,
                        'must' => [
                            ...$this->queryWhere,
                            [
                                'query_string' => [
                                    'query' => '*' . $this->q . '*',
                                    'fields' => $this->searchables
                                ]
                            ],
                        ],
                    ],
                ],
                '_source' => is_array($columns) ? $columns : [$columns],
                'size' => $this->size
            ],
        ];
        if ($this->order) {
            $params['body']['sort'] = [$this->sort => $this->order];
        }
        $res = $this->client->search($params);
        return $this->cleanResult($res);
    }

    private function getOperator(string $operator): string
    {
        return match ($operator) {
            '>' => 'gt',
            '>=' => 'gte',
            '<' => 'lt',
            '<=' => 'lte',
            default => '=',
        };
    }
}
