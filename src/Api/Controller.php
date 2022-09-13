<?php
declare(strict_types=1);

namespace Api;

use HttpException\BadRequestException;
use JsonException;
use Psr\Container\ContainerExceptionInterface;
use RuntimeException;
use Slim\App;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class Controller
 * @package Api
 */
abstract class Controller
{
    use UseAnInstanceResolver;
    use UseALogger;
    use UseExceptionFormatter;

    protected ?ResponseFormatter $responseFormatter = null;
    protected Container $container;

    /**
     * Controller constructor.
     * @param Container $container
     */
    final public function __construct(Container $container)
    {
        $this->container = $container;

        if ($this->responseFormatter === null && !$this->container->has(ResponseFormatter::class)) {
            throw new RuntimeException("ResponseFormatter n'est pas initialisé");
        }

        if ($this->responseFormatter === null) {
            try {
                $this->responseFormatter = $this->container->get(ResponseFormatter::class);
            } catch (ContainerExceptionInterface $e) {
                $this->log()->debug(self::exceptionToString($e));
                throw new \RuntimeException("Impossible d'initialiser le ResponseFormatter");
            }
        }
    }

    /**
     * Enregistre les routes du controleur
     * @param \Slim\App $app
     * @param string $groupName
     * @return \Slim\App
     */
    public static function register(App $app, string $groupName): App
    {
        return $app;
    }

    /**
     * @param Response $response
     * @param mixed $data
     * @return Response
     */
    protected function returnSuccess(Response $response, $data = null): Response
    {
        return $this->responseFormatter->formatSuccess($response, $data);
    }

    /**
     * @param Response $response
     * @param string $newUrl
     * @param mixed $data
     * @return Response
     */
    protected function returnRedirect(Response $response, string $newUrl = "urlPortail", $data = null): Response
    {
        return $this->responseFormatter->formatRedirect($response, $newUrl, $data);
    }

    /**
     * @param Request $request
     * @param null|callable $validator
     * @phpstan-param  (null|callable(mixed $data): void) $validator
     * @return mixed
     * @throws BadRequestException
     */
    protected function readBodyJson(Request $request, ?callable $validator = null)
    {
        try {
            $rawBody = (string)$request->getBody();
            $data = json_decode($rawBody, false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $this->log()->debug(self::exceptionToString($e), ["body" => $rawBody ?? ""]);
            throw new BadRequestException("Le format du cops de la requète n'est pas valide.");
        }
        if (null !== $validator) {
            $validator($data);
        }
        return $data;
    }

    /**
     * @param Request $request
     * @param null|callable $validator
     * @phpstan-param  (null|callable(mixed $data): void) $validator
     * @return array<string,mixed>
     */
    protected function readParams(Request $request, ?callable $validator = null): array
    {
        /** @var array<string,mixed>|null $datas */
        $datas = $request->getParams();
        /** @var array<string,mixed> $datas */
        $datas ??= [];
        // netoyage de l'encodage URL
        foreach ($datas as $key => $data) {
            if (is_string($data) && ($m = preg_match('/%[0-9a-f]{2}/i', $data)) !== false && $m > 0) {
                $datas[$key] = urldecode($data);
            }
        }
        if (null !== $validator) {
            $validator($datas);
        }
        return $datas;
    }
}
