# I18n

## Что это? / What's this?

### RU

```
Языковой пакет для установки на чистый PHP (без фреймворков).

Задачи:

- языковые URL и маршруты (роуты)
- интерполяция (подстановка) параметров в строки
- получение переводов из различных источников (файлы, БД и другие)
- сохранение переводов в различные источники
- получение переводов (в том числе несколько ключей за запрос)
- использование ключей из оперативной памяти без постоянных запросов в источник
- применение ->choice(), чтобы изменять форму множественного числа под языки
- использование "языка по-умолчанию", чтобы выводить ещё не переведенную фразу на главном языке
```

### EN
```

Language package for installation on plain PHP (without frameworks).

Tasks:

Language URLs and routes
Interpolation (substitution) of parameters in strings
Retrieving translations from various sources (files, databases, and others)
Saving translations to various sources
Retrieving translations (including multiple keys per request)
Using keys from memory without constant source queries
Applying ->choice() to change the plural form according to languages
Using a "default language" to display untranslated phrases in the main language
```

## Установка / Setup

```
> composer require gzhegow/i18n
```

### Результат примера / Result of the example

### RU

```
> php test.php

TEST - Получаем часть пути, который подставляется при генерации URL, для языка по-умолчанию должен быть NULL
OK
OK

TEST - Строим регулярное выражение, которое подключается в роутер для SEO оптимизации
OK
OK

TEST - Интерполяция (подстановка) строк
OK

TEST - Получаем фразу (обратите внимание, что фраза до перевода начинаются с `@`, чтобы избежать повторного перевода)
OK
OK
OK

TEST - Получаем из памяти переводы (несколько) и подставляем в них аргументы (рекомендую в имени ключа указывать число аргументов)
OK

TEST - Проверка фразы, которая есть только в русском языке (ещё не переведена переводчиком)
OK
OK

TEST - Проверка выбора фразы по количеству / EN
OK
OK
OK
OK

TEST - Проверка выбора фразы по количеству / RU
OK
OK
OK

TEST - Проверка выбора фразы по количеству / EN-RU
OK
OK

TEST - Проверяем наличие групп напрямую в репозитории
OK

TEST - Проверяем наличие переводов в репозитории
OK

TEST - Получаем переводы напрямую из репозитория
OK

TEST - Проверяем наличие переводов в памяти без запроса в репозиторий
OK

TEST - Получаем переводы из памяти без запроса в репозиторий
OK

TEST - Копируем имеющийся перевод в другой язык (если нам прислали переведенный файл)
OK
OK
```

### EN

```
TEST - Get the part of the path that is substituted when generating a URL; for the default language, it should be NULL
OK
OK

TEST - Construct a regular expression that is integrated into the router for SEO optimization
OK
OK

TEST - String interpolation (substitution)
OK

TEST - Get the phrase (note that the phrase before translation starts with `@` to avoid re-translation)
OK
OK
OK

TEST - Retrieve translations from memory (multiple) and substitute arguments into them (it is recommended to indicate the number of arguments in the key name)
OK

TEST - Check the phrase that exists only in Russian (not yet translated by the translator)
OK
OK

TEST - Check phrase selection by quantity / EN
OK
OK
OK
OK

TEST - Check phrase selection by quantity / RU
OK
OK
OK

TEST - Check phrase selection by quantity / EN-RU
OK
OK

TEST - Verify the presence of groups directly in the repository
OK

TEST - Verify the presence of translations in the repository
OK

TEST - Retrieve translations directly from the repository
OK

TEST - Verify the presence of translations in memory without querying the repository
OK

TEST - Retrieve translations from memory without querying the repository
OK

TEST - Copy an existing translation to another language (if a translated file was sent to us)
OK
OK
```


## Пример / The Example

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';


// > PHP config
ini_set('memory_limit', '32M');

// > error_reporting
error_reporting(E_ALL);
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    if (error_reporting() & $errno) {
        throw new \ErrorException($errstr, -1, $errno, $errfile, $errline);
    }
});
set_exception_handler(function ($e) {
    var_dump(\Gzhegow\I18n\Lib::php_dump($e));
    var_dump($e->getMessage());
    var_dump(($e->getFile() ?? '{file}') . ': ' . ($e->getLine() ?? '{line}'));

    die();
});


// > "The factory always going first" (c)
$factory = new \Gzhegow\I18n\I18nFactory();

// > create `Repository`
// > It is a [type of classes] that is responsive to contact with remote/local translation storage.
// > There's 3 bundled implementations of FileRepository: JSON, PHP, YAML
// > It's highly recommended to write your own repositories to work with Database or RAM, or even both
$repoPhp = new \Gzhegow\I18n\Repo\File\PhpFileRepo($langDir = __DIR__ . '/storage/resource/lang');
// $repoJson = new \Gzhegow\I18n\Repo\File\JsonFileRepo($langDir = __DIR__ . '/storage/resource/lang');
// $repoYaml = new \Gzhegow\I18n\Repo\File\YamlFileRepo($langDir = __DIR__ . '/storage/resource/lang');

// > package config file contains a set of languages
// > initially enabled languages is: `ru` and `en`
// > when you're configuring this package you have to copy this config file, uncomment required languages, then use it following way:
$languages = require __DIR__ . '/config/languages.php';

// > write your config
$config = [];
$config[ 'languages' ] = $languages;
// $config[ 'lang' ] = null;
// $config[ 'lang_default' ] = null;
// $config[ 'logger' ] = null;
// $config[ 'loggables' ][ \Gzhegow\I18n\I18nInterface::E_FORGOTTEN_GROUP ] = \Psr\Log\LogLevel::WARNING;
// $config[ 'loggables' ][ \Gzhegow\I18n\I18nInterface::E_MISSING_WORD    ] = \Psr\Log\LogLevel::WARNING;
// $config[ 'loggables' ][ \Gzhegow\I18n\I18nInterface::E_WRONG_AWORD     ] = \Psr\Log\LogLevel::WARNING;

// > create instance of i18n (manager)
$i18n = $factory->newI18n($repoPhp, $config);

// > if you want to change parsing/validation behavior of internal package types, you can write your own type manager
// \Gzhegow\I18n\Type\Type::setInstance(new \Gzhegow\I18n\Type\TypeManager());

// > Here you can register lists of locales that will change when the language is switched
// > This step is optional. If you do not specify locales, they will not switch, and everything will continue to work fine.
$phpLocales = [
    'en' => [
        LC_COLLATE  => [ $unix = 'en_US', $windows = 'en-US' ],
        LC_CTYPE    => [ $unix = 'en_US', $windows = 'en-US' ],
        LC_TIME     => [ $unix = 'en_US', $windows = 'en-US' ],
        LC_MONETARY => [ $unix = 'en_US', $windows = 'en-US' ],
        //
        // > I recommend keeping LC_NUMERIC as 'C', because changing the locale may cause a comma to be expected instead of a dot in decimal numbers
        LC_NUMERIC  => 'C',
        //
        // > if your PHP build supports `libintl`
        // LC_MESSAGES => [ $unix = 'ru_RU.UTF-8', $unix = 'ru_RU', $windows = 'Russian_Russia.1251', $windows = 'ru-RU' ],
    ],
    'ru' => [
        LC_COLLATE  => [ $unix = 'ru_RU.UTF-8', $unix = 'ru_RU', $windows = 'Russian_Russia.1251', $windows = 'ru-RU' ],
        LC_CTYPE    => [ $unix = 'ru_RU.UTF-8', $unix = 'ru_RU', $windows = 'Russian_Russia.1251', $windows = 'ru-RU' ],
        LC_TIME     => [ $unix = 'ru_RU.UTF-8', $unix = 'ru_RU', $windows = 'Russian_Russia.1251', $windows = 'ru-RU' ],
        LC_MONETARY => [ $unix = 'ru_RU.UTF-8', $unix = 'ru_RU', $windows = 'Russian_Russia.1251', $windows = 'ru-RU' ],
        //
        // > I recommend keeping LC_NUMERIC as 'C', because changing the locale may cause a comma to be expected instead of a dot in decimal numbers
        LC_NUMERIC  => 'C',
        //
        // > if your PHP build supports `libintl`
        // LC_MESSAGES => [ $unix = 'ru_RU.UTF-8', $unix = 'ru_RU', $windows = 'Russian_Russia.1251', $windows = 'ru-RU' ],
    ],
];
$i18n->registerPhpLocales('en', $phpLocales[ 'en' ]);
$i18n->registerPhpLocales('ru', $phpLocales[ 'ru' ]);

// > To use substitutions based on quantity (choice), you should register converters
$i18n->registerChoice('ru', new \Gzhegow\I18n\Choice\RuChoice());

// > Enable the required language at the start of the application (locales registered earlier will also be changed)
// > If the user of the application switches the language, change this parameter
$i18n->setLang('ru');

// > Set the default language. In practice, there are situations when a translation is missing, and business requires displaying it in the "main" language, for example,
$i18n->setLangDefault('en');

// > It is recommended to enable logging to address translation issues during usage
// $rotatingFileHandler = new \Monolog\Handler\RotatingFileHandler(__DIR__ . '/var/log/lang.log', 0);
// $rotatingFileHandler->setFilenameFormat('{date}-{filename}', 'Y/m/d');
// $logger = new \Monolog\Logger('lang', [ $rotatingFileHandler ]);
// $i18n->setLogger($logger);
// $i18n->setLoggables([
//     \Gzhegow\I18n\I18nInterface::E_FORGOTTEN_GROUP => \Monolog\Logger::WARNING,
//     \Gzhegow\I18n\I18nInterface::E_MISSING_WORD    => \Monolog\Logger::WARNING,
//     \Gzhegow\I18n\I18nInterface::E_WRONG_AWORD     => \Monolog\Logger::WARNING,
// ]);


// > ТЕСТЫ

$fnAssert = function (bool $bool) {
    if (! $bool) {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[ 0 ];

        throw new \ErrorException(
            'FAIL',
            null,
            null,
            $trace[ 'file' ],
            $trace[ 'line' ]
        );
    }

    echo 'OK' . PHP_EOL;
};

echo PHP_EOL;
echo 'TEST - Получаем часть пути, который подставляется при генерации URL, для языка по-умолчанию должен быть NULL' . PHP_EOL;
$before = $i18n->getLangDefault();
$i18n->setLangDefault('en');
$fnAssert(null === $i18n->getLangForUrl('en'));
$fnAssert('ru' === $i18n->getLangForUrl('ru'));
$i18n->setLangDefault($before);

echo PHP_EOL;
echo 'TEST - Строим регулярное выражение, которое подключается в роутер для SEO оптимизации' . PHP_EOL;
$fnAssert("/(\/|en\/|ru\/)/" === $i18n->getLangsRegexForRoute());
$fnAssert(
    "/(?<lang>\/|en\/|ru\/)/iu" === $i18n->getLangsRegexForRoute(
        $regexGroupName = 'lang',
        $regexBraces = '/',
        $regexFlags = 'iu'
    )
);

echo PHP_EOL;
echo 'TEST - Интерполяция (подстановка) строк' . PHP_EOL;
$fnAssert(
    'Здесь был Вася. И ниже кто-то дописал: сосед' === $i18n->interpolate(
        $phrase = "Здесь был [:name:]. И ниже кто-то дописал: [:name2:]",
        $placeholders = [ 'name' => 'Вася', 'name2' => 'сосед' ]
    )
);

echo PHP_EOL;
echo 'TEST - Получаем фразу (обратите внимание, что фраза до перевода начинаются с `@`, чтобы избежать повторного перевода)' . PHP_EOL;
$langBefore = $i18n->getLang();
$i18n->setLang('ru');
$i18n->clearUsesLoaded();
$i18n->useGroups([ 'main' ]);
$fnAssert('Привет' === $i18n->phrase('@main.message.hello'));
$fnAssert(null === $i18n->phrase('@main.message.missing', $fallback = [ null ]));
$fnAssert('123' === $i18n->phrase('@main.message.missing', $fallback = [ 123 ]));
// $i18n->phrase('@main.message.missing', $fallback = []); // throws exception
$i18n->setLang($langBefore);

echo PHP_EOL;
echo 'TEST - Получаем из памяти переводы (несколько) и подставляем в них аргументы (рекомендую в имени ключа указывать число аргументов)' . PHP_EOL;
$langBefore = $i18n->getLang();
$i18n->setLang('ru');
$i18n->clearUsesLoaded();
$i18n->useGroups([ 'main' ]);
$expected = [
    0 => 'Привет, Андрей',
    1 => 'Привет, Вася и Валера',
];
$fnAssert(
    $expected === $i18n->phrases(
        $awords = [
            '@main.message.hello_$',
            '@main.message.hello_$$',
        ],
        $fallbacks = null,
        $placeholders = [
            [ 'name' => 'Андрей' ],
            [ 'name1' => 'Вася', 'name2' => 'Валера' ],
        ]
    )
);
$i18n->setLang($langBefore);

echo PHP_EOL;
echo 'TEST - Проверка фразы, которая есть только в русском языке (ещё не переведена переводчиком)' . PHP_EOL;
$langBefore = $i18n->getLang();
$langDefaultBefore = $i18n->getLangDefault();
$i18n->setLang('en');
$i18n->setLangDefault('ru');
$i18n->clearUsesLoaded();
$i18n->useGroups([ 'main' ]);
$fnAssert(null === $i18n->phrase('@main.title.apple_only_russian', [ null ]));
$fnAssert('яблоко' === $i18n->phraseOrDefault('@main.title.apple_only_russian'));
$i18n->setLangDefault($langDefaultBefore);
$i18n->setLang($langBefore);

echo PHP_EOL;
echo 'TEST - Проверка выбора фразы по количеству / EN' . PHP_EOL;
$langBefore = $i18n->getLang();
$i18n->setLang('en');
$i18n->clearUsesLoaded();
$i18n->useGroups([ 'main' ]);
$fnAssert([ '1', 'apple' ] === $i18n->choice(1, '@main.title.apple'));
$fnAssert([ '2', 'apples' ] === $i18n->choice(2, '@main.title.apple'));
$fnAssert([ '1.5', 'apples' ] === $i18n->choice(1.5, '@main.title.apple'));
$expected = [
    [ '1', 'apple' ],
    [ '2', 'apples' ],
    [ '1.5', 'apples' ],
    [ '1', 'apple' ],
    [ '2', 'apples' ],
    [ '1.5', 'apples' ],
];
$fnAssert(
    $expected === $i18n->choices(
        $numbers = [ 1, 2, 1.5, '1', '2', '1.5' ],
        $awords = array_fill(0, count($numbers), '@main.title.apple')
    )
);
$i18n->setLang($langBefore);

echo PHP_EOL;
echo 'TEST - Проверка выбора фразы по количеству / RU' . PHP_EOL;
$langBefore = $i18n->getLang();
$i18n->setLang('ru');
$i18n->clearUsesLoaded();
$i18n->useGroups([ 'main' ]);
$fnAssert([ '1', 'яблоко' ] === $i18n->choice(1, '@main.title.apple'));
$fnAssert([ '2', 'яблока' ] === $i18n->choice(2, '@main.title.apple'));
$fnAssert([ '5', 'яблок' ] === $i18n->choice(5, '@main.title.apple'));
$expected = [
    [ '1', 'яблоко' ],
    [ '2', 'яблока' ],
    [ '5', 'яблок' ],
    [ '11', 'яблок' ],
    [ '21', 'яблоко' ],
    [ '1.5', 'яблока' ],
    [ '1', 'яблоко' ],
    [ '2', 'яблока' ],
    [ '5', 'яблок' ],
    [ '11', 'яблок' ],
    [ '21', 'яблоко' ],
    [ '1.5', 'яблока' ],
];
$fnAssert(
    $expected === $i18n->choices(
        $numbers = [ 1, 2, 5, 11, 21, 1.5, '1', '2', '5', '11', '21', '1.5' ],
        $awords = array_fill(0, count($numbers), '@main.title.apple')
    )
);
$i18n->setLang($langBefore);

echo PHP_EOL;
echo 'TEST - Проверяем наличие переводов в памяти без запроса в репозиторий' . PHP_EOL;
$words = [
    'main.title.apple',
    'main.title.apple_only_russian',
];
$langBefore = $i18n->getLang();
$i18n->setLang('en');
$i18n->clearUsesLoaded();
$i18n->useGroups([ 'main' ]);
$i18n->loadUses();
$expected = [
    0 => [
        'status' => true,
        'word'   => 'main.title.apple',
        'group'  => 'main',
        'lang'   => 'en',
    ],
    1 => [
        'status' => false,
        'word'   => 'main.title.apple_only_russian',
        'group'  => 'main',
        'lang'   => 'en',
    ],
];
$pool = $i18n->getPool();
$fnAssert($expected === $pool->has($words));
$i18n->setLang($langBefore);

echo PHP_EOL;
echo 'TEST - Получаем переводы из памяти без запроса в репозиторий' . PHP_EOL;
$words = [
    'main.title.apple',
    'main.title.apple_only_russian',
];
$langBefore = $i18n->getLang();
$i18n->setLang('en');
$i18n->clearUsesLoaded();
$i18n->useGroups([ 'main' ]);
$i18n->loadUses();
$pool = $i18n->getPool();
$poolItems = $pool->get($words, $andGroupsIn = null, $andLangsIn = null); // array[]
$result = [];
foreach ( $poolItems as $i => $poolItem ) {
    $result[ $i ] = $poolItem->getChoices();
}
$expected = [
    0 => [
        0 => 'apple',
        1 => 'apples',
    ],
];
$fnAssert($expected === $result);
$i18n->setLang($langBefore);

echo PHP_EOL;
echo 'TEST - Проверяем наличие групп напрямую в репозитории' . PHP_EOL;
$expected = [
    0 => [
        'status' => true,
        'group'  => 'main',
        'lang'   => 'en',
    ],
];
$fnAssert(
    $expected === $repoPhp->hasGroups(
        $andGroupsIn = [ 'main' ],
        $andLangsIn = [ 'en' ]
    )
);

echo PHP_EOL;
echo 'TEST - Проверяем наличие переводов в репозитории' . PHP_EOL;
$words = [
    'main.title.apple',
    'main.title.apple_only_russian',
];
$expected = [
    0 => [
        'status' => true,
        'word'   => 'main.title.apple',
        'group'  => 'main',
        'lang'   => 'en',
    ],
    1 => [
        'status' => false,
        'word'   => 'main.title.apple_only_russian',
        'group'  => 'main',
        'lang'   => 'en',
    ],
];
$fnAssert(
    $expected === $repoPhp->hasWords(
        $words,
        $andGroupsIn = [ 'main' ],
        $andLangsIn = [ 'en' ]
    )
);

echo PHP_EOL;
echo 'TEST - Получаем переводы напрямую из репозитория' . PHP_EOL;
$words = [
    'main.title.apple',
    'main.title.apple_only_russian',
];
$repo = $repoPhp;
$it = $repo->getWords(
    $words,
    $andGroupsIn = [ 'main' ],
    $andLangsIn = [ 'en' ]
);
$result = [];
foreach ( $it as $poolItemsBatch ) {
    foreach ( $poolItemsBatch as $poolItem ) {
        $result[] = $poolItem->getChoices();
    }
}
$expected = [
    0 => [
        0 => 'apple',
        1 => 'apples',
    ],
];
$fnAssert($expected === $result);

echo PHP_EOL;
echo 'TEST - Копируем имеющийся перевод в другой язык (если нам прислали переведенный файл)' . PHP_EOL;
$words = [
    'main.title.apple',
    'main.title.apple_only_russian',
];
$repo = $repoPhp;
$it = $repo->getWords(
    $words,
    $andGroupsIn = [ 'main' ],
    $andLangsIn = [ 'en' ]
);
$poolItemsCloned = [];
foreach ( $it as $poolItemsBatch ) {
    foreach ( $poolItemsBatch as $poolItem ) {
        $itemClone = clone $poolItem;

        (function () {
            $this->lang = 'by';
        })->call($itemClone);

        $poolItemsCloned[] = $itemClone;
    }
}
//
$gen = $repo->save($poolItemsCloned);
// execute generator
foreach ( $gen as $i => $v ) {
}
$fnAssert(is_file($langDir . '/by/main.php'));
//
$gen = $repo->delete($poolItemsCloned);
// execute generator
foreach ( $gen as $i => $v ) {
}
$fnAssert(! file_exists($langDir . '/by/main.php'));
```