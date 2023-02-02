<?php
declare(strict_types=1);

namespace FoundationApi;

use HttpException\BadRequestException;
use HttpException\InternalServerException;
use Psr\Log\LoggerInterface;
use Respect\Validation\Exceptions\NestedValidationException as vException;
use Respect\Validation\Factory;
use Respect\Validation\Validatable;
use RuntimeException;

/**
 * Class Validator de base
 * @package Api
 */
abstract class Validator
{
    /** @var bool Flag pour savoir si le translator est initialisé */
    private static bool $translatorInitialised = false;

    /** @var Validatable|null Instance de validateur */
    protected ?Validatable $validator = null;

    /** @var \Psr\Log\LoggerInterface */
    private LoggerInterface $logger;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        /* :: exemple de code d'initialisation D'UN CONSTRUCTEUR
        parent::__construct($logger);
        try {
            $this->validator = self::isAssocArray(["nom?" => self::isText()]);
        } catch (ComponentException $e) {
            throw new \RuntimeException(__CLASS__ . " ne peut pas être initialisé");
        }
        */

        $this->logger = $logger;
        if (!self::$translatorInitialised) {
            $translator = new Translator('./translate', 'fr');
            Factory::setDefaultInstance(
                (new Factory())->withTranslator($translator)
            );
            self::$translatorInitialised = true;
        }
    }

    /**
     * Validator est un callableTranslator.php
     * @param mixed $data
     * @return boolean
     */
    public function test($data): bool
    {
        if ($this->validator === null) {
            return false;
        }
        return $this->validator->validate($data);
    }

    /**
     * Validator est un callable
     * @param mixed $data
     * @return boolean
     * @throws \HttpException\BadRequestException
     * @noinspection JsonEncodingApiUsageInspection
     */
    public function __invoke(mixed $data): bool
    {
        if ($this->validator === null) {
            throw new RuntimeException("Le validateur " . static::class . " n'est pas initialisé");
        }
        try {
            $this->validator->assert($data);
            return true;
        } catch (vException $e) {
            $errors = $e->getMessages();
            $this->logger->warning('Erreur de validation : ' . json_encode($errors, JSON_PRETTY_PRINT));
            $this->logger->debug('Données : ' . json_encode($data, JSON_PRETTY_PRINT));
            throw new BadRequestException('Les données de la requête ne sont pas valides');
        }
    }

    /**
     * Donne les erreurs générées lors de la validation
     * @param mixed $data
     * @return string[]
     */
    public function getErrors(mixed $data): array
    {
        if ($this->validator === null) {
            throw new RuntimeException("Le validateur " . static::class . " n'est pas initialisé");
        }
        try {
            $this->validator->assert($data);
        } catch (vException $e) {
            return $e->getMessages();
        }
        return [];
    }
}
