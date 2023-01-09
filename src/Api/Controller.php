<?php
declare(strict_types=1);

namespace FoundationApi;

use HttpException\BadRequestException;
use JsonException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use RuntimeException;
use Slim\App;
use Slim\Psr7\Factory\ResponseFactory;

/**
 * Class Controller
 * @package Api
 */
abstract class Controller
{
    use UseAnInstanceResolver;
    use UseALogger;
    use UseExceptionFormatter;

    protected ?ResponseFormatterInterface $responseFormatter = null;

    /**
     * Controller constructor.
     * @param ContainerInterface $container
     */
    public function __construct(protected ContainerInterface $container)
    {
        if ($this->responseFormatter === null) {
            if (!$this->container->has('settings')) {
                throw new RuntimeException("Le container n'est pas correctement initialisé");
            }
            $settings = $this->container->has('settings');
            if (!isset($settings['ResponseFormatterClass'])) {
                throw new RuntimeException("ResponseFormatter n'est pas initialisé");
            }
            try {
                $responseFormatterClass = $this->container->get('ResponseFormatterClass');
                $this->responseFormatter = $this->resolve($responseFormatterClass);
            } catch (ContainerExceptionInterface $e) {
                $this->log()->debug(self::exceptionToString($e));
                throw new RuntimeException("Impossible d'initialiser le ResponseFormatter");
            }
        }
    }

    /**
     * Enregistre les routes du controleur
     * @param App $app
     * @param string $groupName
     * @return App
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
    protected function returnSuccess(Response $response, mixed $data = null): Response
    {
        return $this->responseFormatter->formatSuccess($response, $data);
    }

    /**
     * @param Response $response
     * @param string $newUrl
     * @param mixed $data
     * @return Response
     */
    protected function returnRedirect(Response $response, string $newUrl = "urlPortail", mixed $data = null): Response
    {
        return $this->responseFormatter->formatRedirect($response, $newUrl, $data);
    }

    /**
     * @param Request $request
     * @param null|callable|Validator $validator
     * @phpstan-param  null|(callable(mixed $data): void)|Validator $validator
     * @param bool $returnAssoc
     * @return mixed
     * @throws BadRequestException
     */
    protected function readBodyJson(Request $request, null|callable|Validator $validator = null, bool $returnAssoc = false): mixed
    {
        $rawBody = (string)$request->getBody();
        try {
            $data = json_decode($rawBody, $returnAssoc, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $this->log()->debug(self::exceptionToString($e), ["body" => $rawBody]);
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
        $datas = $request->getQueryParams();
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

    /**
     * Donne une réponse
     * @param int $code
     * @return Response
     */
    private function getResponse(int $code = 200): ResponseInterface
    {
        $factory = new ResponseFactory();
        return $factory->createResponse($code);
    }
}
