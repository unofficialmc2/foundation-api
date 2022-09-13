<?php
declare(strict_types=1);

namespace Api;

use DateTimeImmutable;
use Psr\Log\LoggerInterface;

/**
 * Class Query
 * @package Api
 */
abstract class Query
{
    use Valuable;
    use useALogger;

    /**
     * Query constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Execution de la query
     */
    abstract public function __invoke(): void;

    /**
     * prepareFetchAll
     * prépare des fetchs (venant de fetchAll) avec des callbacks
     *
     * @param array<array<string,mixed>> $fetchAll
     * @param callable[] $callbacks
     * @return array<array<string,mixed>>
     */
    protected function prepareFetchAll(array $fetchAll, array $callbacks = []): array
    {
        return array_map(function (array $fetch) use ($callbacks) {
            return $this->prepareFetch($fetch, $callbacks);
        }, $fetchAll);
    }

    /**
     * prepareFetch
     * prépare un fetch avec des callbacks
     * @param array<string,mixed>|null $fetch
     * @param callable[] $callbacks
     * @return array<string,mixed>
     */
    protected function prepareFetch(?array $fetch, array $callbacks = []): ?array
    {
        if ($fetch === null) {
            return null;
        }
        return array_reduce($callbacks, static function (array $oldFetch, callable $callback) {
            return $callback($oldFetch);
        }, $fetch);
    }

    /**
     * cleanerFetch
     * property > "a" => ["b", "str"]
     * [a]="1" --> [b]="1"
     * property > "c" => ["d", "int"]
     * [c]="9" --> [d]=9
     * @param array<string,string[]> $properties
     * @return callable
     */
    protected function cleanerFetch(array $properties): callable
    {
        return static function (array $fetch) use ($properties) {
            $newItem = [];
            foreach ($properties as $property => $info) {
                if (!isset($fetch[$property])) {
                    $newItem[$info[0]] = null;
                    continue;
                }
                switch (strtolower($info[1] ?? '')) {
                    case 'int':
                        $newItem[$info[0]] = (int)$fetch[$property];
                        break;
                    case 'timestamp':
                    case 'datetime':
                        $tmp = DateTimeImmutable::createFromFormat(DATE_ATOM, $fetch[$property]);
                        if (!is_a($tmp, DateTimeImmutable::class)) {
                            $tmp = new DateTimeImmutable($fetch[$property]);
                        }
                        $newItem[$info[0]] = $tmp->format(DATE_ATOM);
                        break;
                    case 'date':
                        $tmp = DateTimeImmutable::createFromFormat('Y-m-d', $fetch[$property]);
                        if (!is_a($tmp, DateTimeImmutable::class)) {
                            $tmp = new DateTimeImmutable($fetch[$property]);
                        }
                        $newItem[$info[0]] = $tmp->format('Y-m-d');
                        break;
                    case 'str':
                        //$tmp = htmlentities($fetch[$property], ENT_COMPAT | ENT_HTML401, 'cp1252');
                        //$newItem[$info[0]] = html_entity_decode($tmp, ENT_COMPAT | ENT_HTML401);
                        $newItem[$info[0]] = (string)$fetch[$property];
                        break;
                    case 'bool':
                    case 'boolean':
                        $newItem[$info[0]] = (bool)$fetch[$property];
                        break;
                    default:
                        $newItem[$info[0]] = $fetch[$property];
                }
            }
            return $newItem;
        };
    }

    /**
     * arborise
     * [a/b]=x ==> [a][b]=x
     * @return callable
     */
    protected function arborise(): callable
    {
        return function ($data) {
            $newData = [];
            foreach ($data as $key => $value) {
                if (false === strpos($key, '/')) {
                    $newData[$key] = $value;
                } else {
                    $keys = explode('/', $key);
                    $subData = &$newData;
                    foreach ($keys as $offset) {
                        if (!array_key_exists($offset, $subData)) {
                            $subData[$offset] = [];
                        }
                        $subData = &$subData[$offset];
                    }
                    $subData = $value;
                }
            }
            return $newData;
        };
    }
}
