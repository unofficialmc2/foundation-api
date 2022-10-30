# Foundation API

foundation-api est une collection de class pour faciliter la création et l'utilisation d'une APIA avec le framework Slim v4

## Installation

```shell
composer require fzed51/foundation-api
```

## Utilisation

### App Factory

C'est une class static qui a la méthode `create` qui prend en paramètre un tableau de settings

#### settings

```php
<?php
return [
    "logger" => [
        "name" => "nom du projet", // [obligatoire] necessaire pour les logs multi projets
        "path" => "chemin/vers/dossier/de/log"
    ]
];
```

L'exemple ci-dessus est le strict minimum pour les settings de l'app

### create

```php
<?php
$settings = [...];
$app = \Api\Factory::create($settings);
```