<?php
declare(strict_types=1);

namespace FoundationApi;

use DateTimeImmutable;
use Psr\Log\LoggerInterface;

/**
 * Class Query
 * @package Api
 */
abstract class Query
{
    use Valuable;
    use UseALogger;

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
                    case 'float':
                    case 'real':
                    case 'double':
                        $newItem[$info[0]] = (double)$fetch[$property];
                        break;
                    case 'int':
                    case 'integer':
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
                    case 'string':
                        if (is_resource($fetch[$property])) {
                            $newItem[$info[0]] = self::changeEncoding(stream_get_contents($fetch[$property]), "UTF-8");
                        } else {
                            $newItem[$info[0]] = (string)$fetch[$property];
                        }
                        break;
                    case 'bool':
                    case 'boolean':
                        $newItem[$info[0]] = (bool)$fetch[$property];
                        break;
                    case 'stream':
                    case 'resource':
                        $newItem[$info[0]] = $fetch[$property];
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

    /**
     * modifie l'encodage d'une chaine
     * @param string $value
     * @param string $newCharset
     * @return string
     */
    private static function changeEncoding(string $value, string $newCharset): string
    {
        $supportedCharset = [];
        // $supportedCharset[] = 'UTF-32';
        // $supportedCharset[] = 'UTF-16';
        $supportedCharset[] = 'UTF-8';
        $supportedCharset[] = 'CP1252';
        $supportedCharset[] = 'ISO-8859-15';
        $supportedCharset[] = 'ISO-8859-1';
        $supportedCharset[] = 'ASCII';
        $charset = mb_detect_encoding($value, $supportedCharset, true);
        if ($newCharset !== $charset) {
            return mb_convert_encoding($value, $newCharset, $charset);
        }
        return $value;
    }
}
