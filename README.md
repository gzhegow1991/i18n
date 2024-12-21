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

## Установка / Setup

```
> composer require gzhegow/i18n
```

## Пример / The Example

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';


// > настраиваем PHP
ini_set('memory_limit', '32M');


// > настраиваем обработку ошибок
error_reporting(E_ALL);
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    if (error_reporting() & $errno) {
        throw new \ErrorException($errstr, -1, $errno, $errfile, $errline);
    }
});
set_exception_handler(function (\Throwable $e) {
    // require_once getenv('COMPOSER_HOME') . '/vendor/autoload.php';
    // dd($e);

    $current = $e;
    do {
        echo "\n";

        echo \Gzhegow\Lib\Lib::debug_var_dump($current) . PHP_EOL;
        echo $current->getMessage() . PHP_EOL;

        $file = $current->getFile() ?? '{file}';
        $line = $current->getLine() ?? '{line}';
        echo "{$file} : {$line}" . PHP_EOL;

        foreach ( $e->getTrace() as $traceItem ) {
            $file = $traceItem[ 'file' ] ?? '{file}';
            $line = $traceItem[ 'line' ] ?? '{line}';

            echo "{$file} : {$line}" . PHP_EOL;
        }

        echo PHP_EOL;
    } while ( $current = $current->getPrevious() );

    die();
});


// > добавляем несколько функция для тестирования
function _debug(...$values) : void
{
    $lines = [];
    foreach ( $values as $value ) {
        $lines[] = \Gzhegow\Lib\Lib::debug_type_id($value);
    }

    echo implode(' | ', $lines) . PHP_EOL;
}

function _dump(...$values) : void
{
    $lines = [];
    foreach ( $values as $value ) {
        $lines[] = \Gzhegow\Lib\Lib::debug_value($value);
    }

    echo implode(' | ', $lines) . PHP_EOL;
}

function _dump_array($value, int $maxLevel = null, bool $multiline = false) : void
{
    $content = $multiline
        ? \Gzhegow\Lib\Lib::debug_array_multiline($value, $maxLevel)
        : \Gzhegow\Lib\Lib::debug_array($value, $maxLevel);

    echo $content . PHP_EOL;
}

function _assert_output(
    \Closure $fn, string $expect = null
) : void
{
    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

    \Gzhegow\Lib\Lib::assert_resource_static(STDOUT);
    \Gzhegow\Lib\Lib::assert_output($trace, $fn, $expect);
}


// >>> ЗАПУСКАЕМ!

// > сначала всегда фабрика
$factory = new \Gzhegow\I18n\I18nFactory();

// > создаем репозиторий, который будет получать переводы из удаленного источника
// > в пакете поставляются несколько готовых репозиториев: JSON, PHP, YAML, и всегда можно написать свой собственный
$repositoryPhp = new \Gzhegow\I18n\Repository\File\PhpFileRepository($langDir = __DIR__ . '/storage/resource/lang');
// $repositoryJson = new \Gzhegow\I18n\Repo\File\JsonFileRepository($langDir = __DIR__ . '/storage/resource/lang');
// $repositoryYaml = new \Gzhegow\I18n\Repo\File\YamlFileRepository($langDir = __DIR__ . '/storage/resource/lang');

// создаем и регистрируем менеджер типов (он определяет синтаксис для ключевых слов, отличие групп от слов, компоновку их в виде строки и так далее)
$typeManager = new \Gzhegow\I18n\Type\I18nTypeManager();
\Gzhegow\I18n\Type\I18nType::setInstance($typeManager);

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
    // $rotatingFileHandler = new \Monolog\Handler\RotatingFileHandler(__DIR__ . '/var/log/lang.log', 0);
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
$i18n = new \Gzhegow\I18n\I18n(
    $factory,
    $repositoryPhp,
    $config
);


// > TEST
// > Получаем часть пути, который подставляется при генерации URL, для языка по-умолчанию должен быть NULL
$fn = function () use ($i18n) {
    _dump('TEST 1');

    $before = $i18n->getLangDefault();

    $i18n->setLangDefault('en');

    $result = $i18n->getLangForUrl('en');
    _dump($result);

    $result = $i18n->getLangForUrl('ru');
    _dump($result);

    $i18n->setLangDefault($before);

    echo '';
};
_assert_output($fn, <<<HEREDOC
"TEST 1"
NULL
"ru"
""
HEREDOC
);


// > TEST
// > Строим регулярное выражение, которое подключается в роутер для SEO оптимизации
$fn = function () use ($i18n) {
    _dump('TEST 2');

    $result = $i18n->getLangsRegexForRoute();
    _dump($result);

    $result = $i18n->getLangsRegexForRoute(
        $regexGroupName = 'lang',
        $regexBraces = '/',
        $regexFlags = 'iu'
    );
    _dump($result);

    echo '';
};
_assert_output($fn, <<<HEREDOC
"TEST 2"
"/(\/|en\/|ru\/)/"
"/(?<lang>\/|en\/|ru\/)/iu"
""
HEREDOC
);


// > TEST
// > Интерполяция (подстановка) строк
$fn = function () use ($i18n) {
    _dump('TEST 3');

    $result = $i18n->interpolate(
        $phrase = "Здесь был [:name:]. И ниже кто-то дописал: [:name2:]",
        $placeholders = [ 'name' => 'Вася', 'name2' => 'сосед' ]
    );
    _dump($result);

    echo '';
};
_assert_output($fn, <<<HEREDOC
"TEST 3"
"Здесь был Вася. И ниже кто-то дописал: сосед"
""
HEREDOC
);


// > TEST
// > Получаем фразу (обратите внимание, что фраза до перевода начинаются с `@`, чтобы избежать повторного перевода)
$fn = function () use ($i18n) {
    _dump('TEST 4');

    $langBefore = $i18n->getLang();

    $i18n->setLang('ru');

    $i18n->clearUsesLoaded();
    $i18n->useGroups([ 'main' ]);

    $result = $i18n->phrase('@main.message.hello');
    _dump($result);

    $result = $i18n->phrase('@main.message.missing', $fallback = [ null ]);
    _dump($result);

    $result = $i18n->phrase('@main.message.missing', $fallback = [ 123 ]);
    _dump($result);

    try {
        // throws exception
        $i18n->phrase('@main.message.missing', $fallback = []);
    }
    catch ( \Throwable $e ) {
        _dump($e);
    }

    $i18n->setLang($langBefore);

    echo '';
};
_assert_output($fn, <<<HEREDOC
"TEST 4"
"Привет"
NULL
123
{ object # Gzhegow\I18n\Exception\RuntimeException }
""
HEREDOC
);


// > TEST
// > Получаем из памяти переводы (несколько) и подставляем в них аргументы (рекомендую в имени ключа указывать число аргументов)
$fn = function () use ($i18n) {
    _dump('TEST 5');

    $langBefore = $i18n->getLang();

    $i18n->setLang('ru');

    $i18n->clearUsesLoaded();
    $i18n->useGroups([ 'main' ]);

    $result = $i18n->phrases(
        $awords = [
            '@main.message.hello_$',
            '@main.message.hello_$$',
        ],
        $fallbacks = null,
        $placeholders = [
            [ 'name' => 'Андрей' ],
            [ 'name1' => 'Вася', 'name2' => 'Валера' ],
        ]
    );
    _dump($result);

    $i18n->setLang($langBefore);

    echo '';
};
_assert_output($fn, <<<HEREDOC
"TEST 5"
[ "Привет, Андрей", "Привет, Вася и Валера" ]
""
HEREDOC
);


// > TEST
// > Проверка фразы, которая есть только в русском языке (ещё не переведена переводчиком)
$fn = function () use ($i18n) {
    _dump('TEST 6');

    $langBefore = $i18n->getLang();
    $langDefaultBefore = $i18n->getLangDefault();

    $i18n->setLang('en');
    $i18n->setLangDefault('ru');

    $i18n->clearUsesLoaded();
    $i18n->useGroups([ 'main' ]);

    $result = $i18n->phrase('@main.title.apple_only_russian', [ null ]);
    _dump($result);

    $result = $i18n->phraseOrDefault('@main.title.apple_only_russian');
    _dump($result);

    $i18n->setLangDefault($langDefaultBefore);
    $i18n->setLang($langBefore);

    echo '';
};
_assert_output($fn, <<<HEREDOC
"TEST 6"
NULL
"яблоко"
""
HEREDOC
);


// > TEST
// > Проверка выбора фразы по количеству / EN
$fn = function () use ($i18n) {
    _dump('TEST 7');

    $langBefore = $i18n->getLang();

    $i18n->setLang('en');

    $i18n->clearUsesLoaded();
    $i18n->useGroups([ 'main' ]);

    $result = $i18n->choice(1, '@main.title.apple');
    _dump($result);

    $result = $i18n->choice(2, '@main.title.apple');
    _dump($result);

    $result = $i18n->choice(1.5, '@main.title.apple');
    _dump($result);

    $result = $i18n->choices(
        $numbers = [ 1, 2, 1.5, '1', '2', '1.5' ],
        $awords = array_fill(0, count($numbers), '@main.title.apple')
    );
    echo \Gzhegow\Lib\Lib::debug_array_multiline($result) . PHP_EOL;

    $i18n->setLang($langBefore);

    echo '';
};
_assert_output($fn, <<<HEREDOC
"TEST 7"
[ "1", "apple" ]
[ "2", "apples" ]
[ "1.5", "apples" ]
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
""
HEREDOC
);


// > TEST
// > Проверка выбора фразы по количеству / RU
$fn = function () use ($i18n) {
    _dump('TEST 8');

    $langBefore = $i18n->getLang();

    $i18n->setLang('ru');

    $i18n->clearUsesLoaded();
    $i18n->useGroups([ 'main' ]);

    $result = $i18n->choice(1, '@main.title.apple');
    _dump($result);

    $result = $i18n->choice(2, '@main.title.apple');
    _dump($result);

    $result = $i18n->choice(5, '@main.title.apple');
    _dump($result);

    $result = $i18n->choices(
        $numbers = [ 1, 2, 5, 11, 21, 1.5, '1', '2', '5', '11', '21', '1.5' ],
        $awords = array_fill(0, count($numbers), '@main.title.apple')
    );
    echo \Gzhegow\Lib\Lib::debug_array_multiline($result) . PHP_EOL;

    $i18n->setLang($langBefore);

    echo '';
};
_assert_output($fn, <<<HEREDOC
"TEST 8"
[ "1", "яблоко" ]
[ "2", "яблока" ]
[ "5", "яблок" ]
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
""
HEREDOC
);


// > TEST
// > Проверяем наличие переводов в памяти без запроса в репозиторий
$fn = function () use ($i18n) {
    _dump('TEST 9');

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
    echo \Gzhegow\Lib\Lib::debug_array_multiline($result) . PHP_EOL;

    $i18n->setLang($langBefore);

    echo '';
};
_assert_output($fn, <<<HEREDOC
"TEST 9"
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
""
HEREDOC
);


// > TEST
// > Получаем переводы из памяти без запроса в репозиторий
$fn = function () use ($i18n) {
    _dump('TEST 10');

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
    echo \Gzhegow\Lib\Lib::debug_array($result) . PHP_EOL;

    $i18n->setLang($langBefore);

    echo '';
};
_assert_output($fn, <<<HEREDOC
"TEST 10"
[ [ "apple", "apples" ] ]
""
HEREDOC
);


// > TEST
// > Проверяем наличие групп напрямую в репозитории
$fn = function () use ($i18n) {
    _dump('TEST 11');

    $repository = $i18n->getRepository();

    $result = $repository->hasGroups(
        $andGroupsIn = [ 'main' ],
        $andLangsIn = [ 'en' ]
    );
    echo \Gzhegow\Lib\Lib::debug_array($result) . PHP_EOL;

    echo '';
};
_assert_output($fn, <<<HEREDOC
"TEST 11"
[ [ "status" => TRUE, "group" => "main", "lang" => "en" ] ]
""
HEREDOC
);


// > TEST
// > Проверяем наличие переводов в репозитории
$fn = function () use ($i18n) {
    _dump('TEST 12');

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
    echo \Gzhegow\Lib\Lib::debug_array_multiline($result) . PHP_EOL;

    echo '';
};
_assert_output($fn, <<<HEREDOC
"TEST 12"
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
""
HEREDOC
);


// > TEST
// > Получаем переводы напрямую из репозитория
$fn = function () use ($i18n) {
    _dump('TEST 13');

    $repository = $i18n->getRepository();

    $words = [
        'main.title.apple',
        'main.title.apple_only_russian',
    ];

    $it = $repository->getWords(
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
    echo \Gzhegow\Lib\Lib::debug_array($result) . PHP_EOL;

    echo '';
};
_assert_output($fn, <<<HEREDOC
"TEST 13"
[ [ "apple", "apples" ] ]
""
HEREDOC
);


// > TEST
// > Копируем имеющийся перевод в другой язык (если нам прислали переведенный файл)
$fn = function () use ($i18n, $langDir) {
    _dump('TEST 14');

    $repository = $i18n->getRepository();

    $words = [
        'main.title.apple',
        'main.title.apple_only_russian',
    ];

    $it = $repository->getWords(
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

    /** @var \Generator $gen */
    $gen = $repository->save($poolItemsCloned);
    while ( $gen->valid() ) {
        $gen->next();
    }
    _dump(is_file($langDir . '/by/main.php'));

    /** @var \Generator $gen */
    $gen = $repository->delete($poolItemsCloned);
    while ( $gen->valid() ) {
        $gen->next();
    }
    _dump(! file_exists($langDir . '/by/main.php'));

    echo '';
};
_assert_output($fn, <<<HEREDOC
"TEST 14"
TRUE
TRUE
""
HEREDOC
);
```