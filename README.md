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

Language package for plain PHP (without frameworks)

Tasks:

- Language URLs and routes
- Interpolation (substitution) of parameters in strings
- Retrieving translations from various sources (files, databases, and others)
- Saving translations to various sources
- Retrieving translations (including multiple keys per request)
- Using keys from memory without constant source queries
- Applying ->choice() to change the plural form according to number and language
- Using a "default language" to display untranslated phrases in the main language
```

## Установить

```
composer require gzhegow/i18n
```

## Запустить тесты

```
php test.php
```

## Примеры и тесты

```php
<?php

require_once __DIR__ . '/../vendor/autoload.php';


// > настраиваем PHP
\Gzhegow\Lib\Lib::entrypoint()
    ->setDirRoot(__DIR__ . '/..')
    //
    ->useErrorReporting()
    ->useMemoryLimit()
    ->useTimeLimit()
    ->useErrorHandler()
    ->useExceptionHandler()
;


// > добавляем несколько функция для тестирования
$ffn = new class {
    function root() : string
    {
        return realpath(__DIR__ . '/..');
    }


    function value_array($value, int $maxLevel = null, array $options = []) : string
    {
        return \Gzhegow\Lib\Lib::debug()->value_array($value, $maxLevel, $options);
    }

    function value_array_multiline($value, int $maxLevel = null, array $options = []) : string
    {
        return \Gzhegow\Lib\Lib::debug()->value_array_multiline($value, $maxLevel, $options);
    }


    function values($separator = null, ...$values) : string
    {
        return \Gzhegow\Lib\Lib::debug()->values([], $separator, ...$values);
    }


    function print(...$values) : void
    {
        echo $this->values(' | ', ...$values) . PHP_EOL;
    }

    function print_array($value, int $maxLevel = null, array $options = []) : void
    {
        echo $this->value_array($value, $maxLevel, $options) . PHP_EOL;
    }

    function print_array_multiline($value, int $maxLevel = null, array $options = []) : void
    {
        echo $this->value_array_multiline($value, $maxLevel, $options) . PHP_EOL;
    }


    function test(\Closure $fn, array $args = []) : \Gzhegow\Lib\Modules\Test\TestRunner\TestRunner
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        return \Gzhegow\Lib\Lib::test()->test()
            ->fn($fn, $args)
            ->trace($trace)
        ;
    }
};



// >>> ЗАПУСКАЕМ!

// > сначала всегда фабрика
$factory = new \Gzhegow\I18n\I18nFactory();

// > создаем репозиторий, который будет получать переводы из удаленного источника
// > в пакете поставляются несколько готовых репозиториев: JSON, PHP, YAML, и всегда можно написать свой собственный
$repositoryPhp = new \Gzhegow\I18n\Repository\File\PhpFileRepository($langDir = $ffn->root() . '/storage/resource/lang');
// $repositoryJson = new \Gzhegow\I18n\Repo\File\JsonFileRepository($langDir = $ffn->root() . '/storage/resource/lang');
// $repositoryYaml = new \Gzhegow\I18n\Repo\File\YamlFileRepository($langDir = $ffn->root() . '/storage/resource/lang');

// > создаем конфигурацию
$config = new \Gzhegow\I18n\I18nConfig();
$config->configure(function (\Gzhegow\I18n\I18nConfig $config) {
    // > посмотрите на класс конфига, чтобы увидеть примеры заполнения

    // > можно добавить другие поддерживаемые языки
    // $config->languages = [
    //     'en' => [ 'en_GB', 'Latn', 'English', 'English' ],
    //     'ru' => [ 'ru_RU', 'Cyrl', 'Russian', 'Русский' ],
    // ];

    // > можно добавить локали, которые будут автоматически активироваться при смене языка
    // $config->phpLocales = [
    //     'ru' => [
    //         // > пример
    //         // LC_COLLATE  => [ $unix = 'ru_RU.UTF-8', $unix = 'ru_RU', $windows = 'Russian_Russia.1251', $windows = 'ru-RU' ],
    //         //
    //         LC_COLLATE  => [ 'ru_RU.UTF-8', 'ru_RU', 'Russian_Russia.1251', 'ru-RU' ],
    //         LC_CTYPE    => [ 'ru_RU.UTF-8', 'ru_RU', 'Russian_Russia.1251', 'ru-RU' ],
    //         LC_TIME     => [ 'ru_RU.UTF-8', 'ru_RU', 'Russian_Russia.1251', 'ru-RU' ],
    //         LC_MONETARY => [ 'ru_RU.UTF-8', 'ru_RU', 'Russian_Russia.1251', 'ru-RU' ],
    //         //
    //         // > рекомендую использовать `C` в качестве локали для цифр, иначе можно столкнуться с запятой вместо десятичной точки
    //         LC_NUMERIC  => 'C',
    //         //
    //         // > если вы скомпилировали PHP с поддержкой `libintl`, можно LC_MESSAGES тоже указать
    //         // LC_MESSAGES => [ 'ru_RU.UTF-8', 'ru_RU', 'Russian_Russia.1251', 'ru-RU' ],
    //     ],
    //     'en' => [
    //         LC_COLLATE  => [ 'en_US', 'en-US' ],
    //         LC_CTYPE    => [ 'en_US', 'en-US' ],
    //         LC_TIME     => [ 'en_US', 'en-US' ],
    //         LC_MONETARY => [ 'en_US', 'en-US' ],
    //         LC_NUMERIC  => 'C',
    //     ],
    // ];

    // > можно установить выборку числительных для языков
    // $config->choices = [];
    // $config->choices['en'] = new \Gzhegow\I18n\Choice\DefaultChoice();
    // $config->choices['ru'] = new \Gzhegow\I18n\Choice\RuChoice();

    // > устанавливаем язык (текущий)
    $config->lang = 'ru';

    // > устанавливаем язык (по-умолчанию) - фразы из этого языка будут выдаваться, если на текущем языке текста нет
    $config->langDefault = 'en';

    // > можно (и желательно) установить логгер, чтобы вовремя исправлять проблемы с отсутствием переводов и неверными переводами в группах
    // $rotatingFileHandler = new \Monolog\Handler\RotatingFileHandler($ffn->root() . '/var/log/lang.log', 0);
    // $rotatingFileHandler->setFilenameFormat('{date}-{filename}', 'Y/m/d');
    // $logger = new \Monolog\Logger('lang', [ $rotatingFileHandler ]);
    // $config->logger = $logger;

    // > и указать уровень логирования для ошибок, которые регистрирует модуль
    // $config->loggables = [
    //     // \Gzhegow\I18n\I18nInterface::E_FORGOTTEN_GROUP => \Psr\Log\LogLevel::WARNING,
    //     // \Gzhegow\I18n\I18nInterface::E_MISSING_WORD    => \Psr\Log\LogLevel::WARNING,
    //     // \Gzhegow\I18n\I18nInterface::E_WRONG_AWORD     => \Psr\Log\LogLevel::WARNING,
    // ];
});

// > создаем основной модуль
$i18n = new \Gzhegow\I18n\I18nFacade(
    $factory,
    $repositoryPhp,
    $config
);

// > устанавливаем фасад, если удобно пользоваться статически
\Gzhegow\I18n\I18n::setFacade($i18n);



// >>> ТЕСТЫ

// > TEST
// > Получаем часть пути, который подставляется при генерации URL, для языка по-умолчанию должен быть NULL
$fn = function () use ($i18n, $ffn) {
    $ffn->print('TEST 1');
    echo PHP_EOL;

    $before = $i18n->getLangDefault();

    $i18n->setLangDefault('en');

    $result = $i18n->getLangForUrl('en');
    $ffn->print($result);

    $result = $i18n->getLangForUrl('ru');
    $ffn->print($result);

    $i18n->setLangDefault($before);
};
$test = $ffn->test($fn);
$test->expectStdout('
"TEST 1"

NULL
"ru"
');
$test->run();


// > TEST
// > Строим регулярное выражение, которое подключается в роутер для SEO оптимизации
$fn = function () use ($i18n, $ffn) {
    $ffn->print('TEST 2');
    echo PHP_EOL;

    $result = $i18n->getLangsRegexForRoute();
    $ffn->print($result);

    $result = $i18n->getLangsRegexForRoute(
        $regexGroupName = 'lang',
        $regexBraces = '/',
        $regexFlags = 'iu'
    );
    $ffn->print($result);
};
$test = $ffn->test($fn);
$test->expectStdout('
"TEST 2"

"/(\/|en\/|ru\/)/"
"/(?<lang>\/|en\/|ru\/)/iu"
');
$test->run();


// > TEST
// > Интерполяция (подстановка) строк
$fn = function () use ($i18n, $ffn) {
    $ffn->print('TEST 3');
    echo PHP_EOL;

    $result = $i18n->interpolate(
        $phrase = "Здесь был [:name:]. И ниже кто-то дописал: [:name2:]",
        $placeholders = [ 'name' => 'Вася', 'name2' => 'сосед' ]
    );
    $ffn->print($result);
};
$test = $ffn->test($fn);
$test->expectStdout('
"TEST 3"

"Здесь был Вася. И ниже кто-то дописал: сосед"
');
$test->run();


// > TEST
// > Получаем фразу (обратите внимание, что фраза до перевода начинаются с `@`, чтобы избежать повторного перевода)
$fn = function () use ($i18n, $ffn) {
    $ffn->print('TEST 4');
    echo PHP_EOL;

    $langBefore = $i18n->getLang();

    $i18n->setLang('ru');

    $i18n->clearUsesLoaded();
    $i18n->useGroups([ 'main' ]);

    $result = $i18n->phrase('@main.message.hello');
    $ffn->print($result);

    $result = $i18n->phrase('@main.message.missing', $fallback = [ null ]);
    $ffn->print($result);

    $result = $i18n->phrase('@main.message.missing', $fallback = [ 123 ]);
    $ffn->print($result);

    try {
        // > throws exception cause of no fallback provided
        $i18n->phrase('@main.message.missing', $fallback = []);
    }
    catch ( \Gzhegow\I18n\Exception\RuntimeException $e ) {
        $ffn->print(
            '[ CATCH ] ' . $e->getMessage(),
            $e->getFileOverride($ffn->root()),
            $e->getLine()
        );
    }

    $i18n->setLang($langBefore);
};
$test = $ffn->test($fn);
$test->expectStdout('
"TEST 4"

"Привет"
NULL
"123"
"[ CATCH ] This word is missing in the dictionary for languages: main.message.missing / ( ru ) / { object(stringable) # Gzhegow\I18n\Struct\I18nAword }" | "tests/test.php" | 254
');
$test->run();


// > TEST
// > Получаем из памяти переводы (несколько) и подставляем в них аргументы (рекомендую в имени ключа указывать число аргументов)
$fn = function () use ($i18n, $ffn) {
    $ffn->print('TEST 5');
    echo PHP_EOL;

    $langBefore = $i18n->getLang();

    $i18n->setLang('ru');

    $i18n->clearUsesLoaded();
    $i18n->useGroups([ 'main' ]);

    $result = $i18n->phrases(
        $awords = [
            '@main.message.hello_$',
            '@main.message.hello_$$',
        ],
        $fallbacks = [],
        $placeholders = [
            [ 'name' => 'Андрей' ],
            [ 'name1' => 'Вася', 'name2' => 'Валера' ],
        ]
    );
    $ffn->print($result);

    $i18n->setLang($langBefore);
};
$test = $ffn->test($fn);
$test->expectStdout('
"TEST 5"

[ "Привет, Андрей", "Привет, Вася и Валера" ]
');
$test->run();


// > TEST
// > Проверка фразы, которая есть только в русском языке (ещё не переведена переводчиком)
$fn = function () use ($i18n, $ffn) {
    $ffn->print('TEST 6');
    echo PHP_EOL;

    $langBefore = $i18n->getLang();
    $langDefaultBefore = $i18n->getLangDefault();

    $i18n->setLang('en');
    $i18n->setLangDefault('ru');

    $i18n->clearUsesLoaded();
    $i18n->useGroups([ 'main' ]);

    $result = $i18n->phrase('@main.title.apple_only_russian', [ null ]);
    $ffn->print($result);

    $result = $i18n->phraseOrDefault('@main.title.apple_only_russian');
    $ffn->print($result);

    $i18n->setLangDefault($langDefaultBefore);
    $i18n->setLang($langBefore);
};
$test = $ffn->test($fn);
$test->expectStdout('
"TEST 6"

NULL
"яблоко"
');
$test->run();


// > TEST
// > Проверка выбора фразы по количеству / EN
$fn = function () use ($i18n, $ffn) {
    $ffn->print('TEST 7');
    echo PHP_EOL;

    $langBefore = $i18n->getLang();

    $i18n->setLang('en');

    $i18n->clearUsesLoaded();
    $i18n->useGroups([ 'main' ]);

    $result = $i18n->choice(1, '@main.title.apple');
    $ffn->print($result);

    $result = $i18n->choice(2, '@main.title.apple');
    $ffn->print($result);

    $result = $i18n->choice(1.5, '@main.title.apple');
    $ffn->print($result);

    $result = $i18n->choices(
        $numbers = [ 1, 2, 1.5, '1', '2', '1.5' ],
        $awords = array_fill(0, count($numbers), '@main.title.apple')
    );
    $ffn->print_array_multiline($result, 2);

    $i18n->setLang($langBefore);
};
$test = $ffn->test($fn);
$test->expectStdout('
"TEST 7"

[ "1", "apple" ]
[ "2", "apples" ]
[ "1.5", "apples" ]
###
[
  [
    "1",
    "apple"
  ],
  [
    "2",
    "apples"
  ],
  [
    "1.5",
    "apples"
  ],
  [
    "1",
    "apple"
  ],
  [
    "2",
    "apples"
  ],
  [
    "1.5",
    "apples"
  ]
]
###
');
$test->run();


// > TEST
// > Проверка выбора фразы по количеству / RU
$fn = function () use ($i18n, $ffn) {
    $ffn->print('TEST 8');
    echo PHP_EOL;

    $langBefore = $i18n->getLang();

    $i18n->setLang('ru');

    $i18n->clearUsesLoaded();
    $i18n->useGroups([ 'main' ]);

    $result = $i18n->choice(1, '@main.title.apple');
    $ffn->print($result);

    $result = $i18n->choice(2, '@main.title.apple');
    $ffn->print($result);

    $result = $i18n->choice(5, '@main.title.apple');
    $ffn->print($result);

    $result = $i18n->choices(
        $numbers = [ 1, 2, 5, 11, 21, 1.5, '1', '2', '5', '11', '21', '1.5' ],
        $awords = array_fill(0, count($numbers), '@main.title.apple')
    );
    $ffn->print_array_multiline($result, 2);

    $i18n->setLang($langBefore);
};
$test = $ffn->test($fn);
$test->expectStdout('
"TEST 8"

[ "1", "яблоко" ]
[ "2", "яблока" ]
[ "5", "яблок" ]
###
[
  [
    "1",
    "яблоко"
  ],
  [
    "2",
    "яблока"
  ],
  [
    "5",
    "яблок"
  ],
  [
    "11",
    "яблок"
  ],
  [
    "21",
    "яблоко"
  ],
  [
    "1.5",
    "яблока"
  ],
  [
    "1",
    "яблоко"
  ],
  [
    "2",
    "яблока"
  ],
  [
    "5",
    "яблок"
  ],
  [
    "11",
    "яблок"
  ],
  [
    "21",
    "яблоко"
  ],
  [
    "1.5",
    "яблока"
  ]
]
###
');
$test->run();


// > TEST
// > Проверяем наличие переводов в памяти без запроса в репозиторий
$fn = function () use ($i18n, $ffn) {
    $ffn->print('TEST 9');
    echo PHP_EOL;

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

    $result = $pool->has($words);
    $ffn->print_array_multiline($result, 2);

    $i18n->setLang($langBefore);
};
$test = $ffn->test($fn);
$test->expectStdout('
"TEST 9"

###
[
  [
    "status" => TRUE,
    "word" => "main.title.apple",
    "group" => "main",
    "lang" => "en"
  ],
  [
    "status" => FALSE,
    "word" => "main.title.apple_only_russian",
    "group" => "main",
    "lang" => "en"
  ]
]
###
');
$test->run();


// > TEST
// > Получаем переводы из памяти без запроса в репозиторий
$fn = function () use ($i18n, $ffn) {
    $ffn->print('TEST 10');
    echo PHP_EOL;

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
    $ffn->print_array($result, 2);

    $i18n->setLang($langBefore);
};
$test = $ffn->test($fn);
$test->expectStdout('
"TEST 10"

[ [ "apple", "apples" ] ]
');
$test->run();


// > TEST
// > Проверяем наличие групп напрямую в репозитории
$fn = function () use ($i18n, $ffn) {
    $ffn->print('TEST 11');
    echo PHP_EOL;

    $repository = $i18n->getRepository();

    $result = $repository->hasGroups(
        $andGroupsIn = [ 'main' ],
        $andLangsIn = [ 'en' ]
    );
    $ffn->print_array($result, 2);
};
$test = $ffn->test($fn);
$test->expectStdout('
"TEST 11"

[ [ "status" => TRUE, "group" => "main", "lang" => "en" ] ]
');
$test->run();


// > TEST
// > Проверяем наличие переводов в репозитории
$fn = function () use ($i18n, $ffn) {
    $ffn->print('TEST 12');
    echo PHP_EOL;

    $repository = $i18n->getRepository();

    $words = [
        'main.title.apple',
        'main.title.apple_only_russian',
    ];

    $result = $repository->hasWords(
        $words,
        $andGroupsIn = [ 'main' ],
        $andLangsIn = [ 'en' ]
    );
    $ffn->print_array_multiline($result, 2);
};
$test = $ffn->test($fn);
$test->expectStdout('
"TEST 12"

###
[
  [
    "status" => TRUE,
    "word" => "main.title.apple",
    "group" => "main",
    "lang" => "en"
  ],
  [
    "status" => FALSE,
    "word" => "main.title.apple_only_russian",
    "group" => "main",
    "lang" => "en"
  ]
]
###
');
$test->run();


// > TEST
// > Получаем переводы напрямую из репозитория
$fn = function () use ($i18n, $ffn) {
    $ffn->print('TEST 13');
    echo PHP_EOL;

    $repository = $i18n->getRepository();

    $words = [
        'main.title.apple',
        'main.title.apple_only_russian',
    ];

    $it = $repository->getWordsIt(
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
    $ffn->print_array($result, 2);
};
$test = $ffn->test($fn);
$test->expectStdout('
"TEST 13"

[ [ "apple", "apples" ] ]
');
$test->run();


// > TEST
// > Копируем имеющийся перевод в другой язык (если нам прислали переведенный файл)
$fn = function () use ($i18n, $langDir, $ffn) {
    $ffn->print('TEST 14');
    echo PHP_EOL;

    $repository = $i18n->getRepository();

    $andWordsIn = [
        'main.title.apple',
        'main.title.apple_only_russian',
    ];
    $andGroupsIn = [ 'main' ];
    $andLangsIn = [ 'en' ];

    $it = $repository->getWordsIt(
        $andWordsIn,
        $andGroupsIn,
        $andLangsIn
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

    /** @var \Generator $gen */
    $repository->save($poolItemsCloned);
    $ffn->print(is_file($langDir . '/by/main.php'));

    /** @var \Generator $gen */
    $repository->delete($poolItemsCloned);
    $ffn->print(! file_exists($langDir . '/by/main.php'));
};
$test = $ffn->test($fn);
$test->expectStdout('
"TEST 14"

TRUE
TRUE
');
$test->run();
```

