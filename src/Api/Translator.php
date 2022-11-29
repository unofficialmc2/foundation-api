<?php
declare(strict_types=1);

namespace FoundationApi;

use Exception;
use RuntimeException;

/**
 * Class de traduction
 */
class Translator
{
    /** @var string Nom du fichier du dictionnaire de traduction */
    private string $filename;
    /** @var array<string,string> Dictionnaire de traduction */
    private array $translationDictionary = [];
    /** @var int Nombre de traductions dans le dictionnaire */
    private int $nbTranslation = 0;

    /**
     * Initialisation du translator
     * @param string $directory
     * @param string $lang
     */
    public function __construct(string $directory, string $lang)
    {
        $this->filename = self::getFileTranslation($directory, $lang);
        $this->loadTranslation();
    }

    /**
     * Donne le nom du fichier de traduction
     * @param string $directory
     * @param string $lang
     * @return string
     */
    private static function getFileTranslation(string $directory, string $lang): string
    {
        if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $directory));
        }
        return rtrim($directory, "/\\") . "/lang_$lang.json";
    }

    /**
     * Charge le dictionnaire de traduction dans l'instance
     * @return void
     */
    private function loadTranslation(): void
    {
        if (is_file($this->filename)) {
            $json = file_get_contents($this->filename);
            try {
                $content = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            } catch (Exception $e) {
                $content = [];
            }
            if (is_array($content)) {
                $this->translationDictionary = $content;
            }
            $this->nbTranslation = count($this->translationDictionary);
        }
    }

    /**
     * Fonction de traduction
     * @param string $text
     * @return string
     */
    public function __invoke(string $text): string
    {
        if (array_key_exists($text, $this->translationDictionary)) {
            $text = $this->translationDictionary[$text];
        } else {
            $this->translationDictionary[$text] = $text;
            $this->saveTranslation();
        }
        return $text;
    }

    /**
     * Sauvegarde les traductions dans le fichier de traduction
     * @return void
     */
    private function saveTranslation(): void
    {
        try {
            if (count($this->translationDictionary) !== $this->nbTranslation) {
                $json = json_encode($this->translationDictionary, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
                if (file_put_contents($this->filename, $json) === false) {
                    throw new RuntimeException("Erreur d'écriture dans " . $this->filename);
                }
                $this->nbTranslation = count($this->translationDictionary);
            }
        } catch (Exception $e) {
            throw new RuntimeException("Impossible d'enregistrer la traduction", $e->getCode(), $e);
        }
    }
}
