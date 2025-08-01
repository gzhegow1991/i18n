<?php

// > настраиваем PHP
\Gzhegow\Lib\Lib::entrypoint()
    ->setDirRoot(__DIR__ . '/..')
    ->useAllRecommended()
;



// > добавляем несколько функция для тестирования
$ffn = new class {
    function root() : string
    {
        return realpath(__DIR__ . '/..');
    }


    function value_array($value, ?int $maxLevel = null, array $options = []) : string
    {
        return \Gzhegow\Lib\Lib::debug()->dump_value_array($value, $maxLevel, $options);
    }

    function value_array_multiline($value, ?int $maxLevel = null, array $options = []) : string
    {
        return \Gzhegow\Lib\Lib::debug()->dump_value_array_multiline($value, $maxLevel, $options);
    }


    function values($separator = null, ...$values) : string
    {
        return \Gzhegow\Lib\Lib::debug()->dump_values([], $separator, ...$values);
    }


    function print(...$values) : void
    {
        echo $this->values(' | ', ...$values) . PHP_EOL;
    }

    function print_array($value, ?int $maxLevel = null, array $options = []) : void
    {
        echo $this->value_array($value, $maxLevel, $options) . PHP_EOL;
    }

    function print_array_multiline($value, ?int $maxLevel = null, array $options = []) : void
    {
        echo $this->value_array_multiline($value, $maxLevel, $options) . PHP_EOL;
    }


    function test(\Closure $fn, array $args = []) : \Gzhegow\Lib\Modules\Test\TestCase
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        return \Gzhegow\Lib\Lib::test()->newTestCase()
            ->fn($fn, $args)
            ->trace($trace)
        ;
    }
};



// >>> ЗАПУСКАЕМ!

// > сначала всегда фабрика
$factory = new \Gzhegow\I18n\I18nFactory();

// > создаем конфигурацию
$config = new \Gzhegow\I18n\Config\I18nConfig();
$config->configure(
    function (\Gzhegow\I18n\Config\I18nConfig $config) {
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
        $config->langCurrent = 'ru';

        // > устанавливаем язык (по-умолчанию) - фразы из этого языка будут выдаваться, если на текущем языке текста нет
        $config->langDefault = 'en';

        // > можно (и желательно) установить логгер, чтобы вовремя исправлять проблемы с отсутствием переводов и неверными переводами в группах
        // $rotatingFileHandler = new \Monolog\Handler\RotatingFileHandler($ffn->root() . '/var/log/lang.log', 0);
        // $rotatingFileHandler->setFilenameFormat('{date}-{filename}', 'Y/m/d');
        // $logger = new \Monolog\Logger('lang', [ $rotatingFileHandler ]);
        // $config->logger = $logger;

        // > и указать уровень логирования для ошибок, которые регистрирует модуль
        // $config->loggables = [
        //     // \Gzhegow\I18n\I18n::E_EXCLUDED_GROUP  => \Psr\Log\LogLevel::WARNING,
        //     // \Gzhegow\I18n\I18n::E_FORGOTTEN_GROUP => \Psr\Log\LogLevel::WARNING,
        //     // \Gzhegow\I18n\I18n::E_MISSING_WORD    => \Psr\Log\LogLevel::WARNING,
        //     // \Gzhegow\I18n\I18n::E_WRONG_AWORD     => \Psr\Log\LogLevel::WARNING,
        // ];
    }
);

// > создаем репозиторий, который будет получать переводы из удаленного источника
// > в пакете поставляются несколько готовых репозиториев: JSON, PHP, YAML, и всегда можно написать свой собственный
// > задаем папку, где лежат переводы
$langDir = $ffn->root() . '/disc/i18n';
// > создаем нужный объект
// $repositoryJson = new \Gzhegow\I18n\Repository\File\I18nJsonFileRepository($langDir);
$repositoryJsonc = new \Gzhegow\I18n\Repository\File\I18nJsoncFileRepository($langDir);
// $repositoryPhp = new \Gzhegow\I18n\Repository\File\I18nI18nPhpFileRepository($langDir);
// $repositoryYaml = new \Gzhegow\I18n\Repository\File\I18nYamlFileRepository($langDir);

// > создаем пул, который будет хранить запрошенные из репозитория переводы в виде пригодном для повторного запроса из оперативной памяти
$poolMemory = new \Gzhegow\I18n\Pool\I18nMemoryPool();

// > создаем мост между пулом и репозиторием, который управляет очередью вызовов
$poolManager = new \Gzhegow\I18n\PoolManager\I18nPoolManager(
    $poolMemory,
    $repositoryJsonc,
);

// > создаем интерполятор, задача - подставлять значения в переведенные строки
$interpolator = new \Gzhegow\I18n\Interpolator\I18nInterpolator();

// > создаем основной модуль
$i18n = new \Gzhegow\I18n\I18nFacade(
    $factory,
    $poolManager,
    $interpolator,
    $config
);

// > устанавливаем фасад, если удобно пользоваться статически
\Gzhegow\I18n\I18n::setFacade($i18n);

// > есть более короткая форма фасадов, где часто применяемые методы сокращены до нескольких букв (можете свой создать по примеру)
// > применять -> L::ppd([ word, word ]) или L::p('word')
// > вместо -> I18n::phrasesOrDefault([ word, word ]) или I18n::phrase('word')
// \Gzhegow\I18n\L::setFacade($i18n);



// >>> ТЕСТЫ

// > TEST
// > Строим регулярное выражение, которое подключается в роутер для SEO оптимизации
$fn = function () use ($i18n, $ffn) {
    $ffn->print('TEST 1');
    echo PHP_EOL;


    $result = $i18n->getLangsRegex();
    $ffn->print($result);

    $result = $i18n->getLangsRegex('/');
    $ffn->print($result);

    $result = $i18n->getLangsRegex('', '/');
    $ffn->print($result);

    $result = $i18n->getLangsRegex('/', '/');
    $ffn->print($result);

    echo "\n";

    $result = $i18n->getLangsRegex(
        '', '',
        $regexGroupName = 'lang',
        $regexBraces = '/',
        $regexFlags = 'iu'
    );
    $ffn->print($result);
};
$test = $ffn->test($fn);
$test->expectStdout('
"TEST 1"

"/(?:(en|ru))/"
"/(?:\/(en|ru))/"
"/(?:(en|ru)\/)/"
"/(?:\/(en|ru)\/)/"

"/(?:(?<lang>en|ru))/iu"
');
$test->run();


// > TEST
// > Получаем часть пути, который подставляется при генерации URL, для языка по-умолчанию должен быть NULL
$fn = function () use ($i18n, $ffn) {
    $ffn->print('TEST 2');
    echo PHP_EOL;


    $langDefaultBefore = $i18n->setLangDefault('en');
    $langCurrentBefore = $i18n->setLangCurrent('ru');

    $result = $i18n->getLangUrlFor('en');
    $ffn->print($result);

    $result = $i18n->getLangUrlFor('ru');
    $ffn->print($result);

    $i18n->setLangCurrent($langCurrentBefore);
    $i18n->setLangDefault($langDefaultBefore);
};
$test = $ffn->test($fn);
$test->expectStdout('
"TEST 2"

""
"ru"
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
// > Проверяем наличие в памяти без запроса в репозиторий
$fn = function () use ($i18n, $ffn) {
    $ffn->print('TEST 4');
    echo PHP_EOL;


    $words = [
        'main.title.apple',
        'main.title.apple_only_russian',
    ];

    $langDefaultBefore = $i18n->setLangDefault('ru');
    $langCurrentBefore = $i18n->setLangCurrent('en');

    $pool = $i18n->getPool();

    $i18n->resetUses();
    $i18n->useGroups([ 'main' ]);
    $i18n->loadUses();

    $result = $pool->has($words, $andGroupsIn = null, $andLangsIn = null);
    $ffn->print_array_multiline($result, 2);

    echo "\n";

    $poolItems = $pool->get($words, $andGroupsIn = null, $andLangsIn = null); // array[]
    $result = [];
    foreach ( $poolItems as $i => $poolItem ) {
        $result[ $i ] = $poolItem->getChoices();
    }
    $ffn->print_array($result, 2);

    $i18n->setLangCurrent($langCurrentBefore);
    $i18n->setLangDefault($langDefaultBefore);
};
$test = $ffn->test($fn);
$test->expectStdout('
"TEST 4"

###
[
  "en|main|title|apple" => "{ object # Gzhegow\I18n\Pool\PoolItem\I18nPoolItem }",
  "en|main|title|apple_only_russian" => NULL
]
###

[ "en|main|title|apple" => [ "apple", "apples" ] ]
');
$test->run();


// > TEST
// > Проверяем наличие напрямую в репозитории
$fn = function () use ($i18n, $ffn) {
    $ffn->print('TEST 5');
    echo PHP_EOL;


    $words = [
        'main.title.apple',
        'main.title.apple_only_russian',
    ];

    $repository = $i18n->getRepository();

    $result = $repository->hasGroups(
        $andGroupsIn = [ 'main' ],
        $andLangsIn = [ 'en' ]
    );
    $ffn->print_array($result, 2);

    echo "\n";

    $result = $repository->hasWords(
        $words,
        $andGroupsIn = [ 'main' ],
        $andLangsIn = [ 'en' ]
    );
    $ffn->print_array_multiline($result, 2);

    echo "\n";

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
"TEST 5"

[ [ "status" => TRUE, "group" => "main", "lang" => "en" ] ]

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

[ [ "apple", "apples" ] ]
');
$test->run();


// > TEST
// > Проверяем наличие переводов в памяти без запроса в репозиторий
// > Обратите внимание, что фраза до перевода начинаются с `@`, чтобы избежать повторного перевода и упростить поиск в массивах
$fn = function () use ($i18n, $ffn) {
    $ffn->print('TEST 6');
    echo PHP_EOL;


    $words = [
        '@main.title.apple',
        '@main.title.apple_only_russian',
    ];

    $langDefaultBefore = $i18n->setLangDefault('ru');
    $langCurrentBefore = $i18n->setLangCurrent('en');

    $i18n->resetUses();
    $i18n->useGroups([ 'main' ]);
    $i18n->loadUses();

    $poolItemsLists = $i18n->get($words, null, null, [ &$errors ]);
    $result = [];
    foreach ( $poolItemsLists as $i => $poolItemList ) {
        foreach ( $poolItemList as $ii => $poolItem ) {
            $result[ $i ][ $ii ] = $poolItem->toArray();
        }
    }
    $ffn->print_array_multiline($result, 4);
    echo "\n";
    $ffn->print_array_multiline($errors, 4);
    echo "\n";

    echo "\n";

    $poolItemsLists = $i18n->getOrDefault($words, null, null, [ &$errors ]);
    $result = [];
    foreach ( $poolItemsLists as $i => $poolItemList ) {
        foreach ( $poolItemList as $ii => $poolItem ) {
            $result[ $i ][ $ii ] = $poolItem->toArray();
        }
    }
    $ffn->print_array_multiline($result, 4);
    echo "\n";
    $ffn->print_array_multiline($errors, 4);
    echo "\n";

    $i18n->setLangCurrent($langCurrentBefore);
    $i18n->setLangDefault($langDefaultBefore);
};
$test = $ffn->test($fn);
$test->expectStdout('
"TEST 6"

###
[
  [
    "en|main|title|apple" => [
      "lang" => "en",
      "word" => [
        "value" => "main.title.apple",
        "group" => "main",
        "section" => "title",
        "key" => "apple"
      ],
      "phrase" => "apple",
      "choices" => [
        "apple",
        "apples"
      ]
    ]
  ]
]
###

###
[
  1 => [
    [
      2,
      "The word is missing in dictionary: [:index:] / [:langs:]",
      [
        "index" => "[ en|main|title|apple_only_russian ]",
        "langs" => "[ en ]"
      ]
    ]
  ]
]
###


###
[
  [
    "en|main|title|apple" => [
      "lang" => "en",
      "word" => [
        "value" => "main.title.apple",
        "group" => "main",
        "section" => "title",
        "key" => "apple"
      ],
      "phrase" => "apple",
      "choices" => [
        "apple",
        "apples"
      ]
    ]
  ],
  [
    "ru|main|title|apple_only_russian" => [
      "lang" => "ru",
      "word" => [
        "value" => "main.title.apple_only_russian",
        "group" => "main",
        "section" => "title",
        "key" => "apple_only_russian"
      ],
      "phrase" => "яблоко",
      "choices" => [
        "яблоко",
        "яблока",
        "яблок"
      ]
    ]
  ]
]
###

###
[
  1 => [
    [
      2,
      "The word is missing in dictionary: [:index:] / [:langs:]",
      [
        "index" => "[ en|main|title|apple_only_russian ]",
        "langs" => "[ en ]"
      ]
    ]
  ]
]
###
');
$test->run();


// > TEST
// > Получаем первую из доступных фразу по параметрам
// > Обратите внимание, что фраза до перевода начинаются с `@`, чтобы избежать повторного перевода и упростить поиск в массивах
$fn = function () use ($i18n, $ffn) {
    $ffn->print('TEST 7');
    echo PHP_EOL;

    $langDefaultBefore = $i18n->setLangDefault('en');
    $langCurrentBefore = $i18n->setLangCurrent('ru');

    $i18n->resetUses();
    $i18n->useGroups([ 'main' ]);

    // > загрузка групп и слов из очереди будет вызвана при запросе фразы
    // $i18n->loadUses();

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

    $i18n->setLangCurrent($langCurrentBefore);
    $i18n->setLangDefault($langDefaultBefore);
};
$test = $ffn->test($fn);
$test->expectStdout('
"TEST 7"

"Привет"
NULL
"123"
"[ CATCH ] The word is missing in dictionary: [ ru|main|message|missing ] / [ ru ]" | "tests/test.php" | 565
');
$test->run();


// > TEST
// > Получаем из памяти переводы (несколько) и подставляем в них аргументы
// > Рекомендуется помечать в имени ключа число возможных аргументов для дальнейшей поддержки
$fn = function () use ($i18n, $ffn) {
    $ffn->print('TEST 8');
    echo PHP_EOL;

    $langDefaultBefore = $i18n->setLangDefault('en');
    $langCurrentBefore = $i18n->setLangCurrent('ru');

    $i18n->resetUses();
    $i18n->useGroups([ 'main' ]);

    // > загрузка групп и слов из очереди будет вызвана при запросе фразы
    // $i18n->loadUses();

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

    $i18n->setLangCurrent($langCurrentBefore);
    $i18n->setLangDefault($langDefaultBefore);
};
$test = $ffn->test($fn);
$test->expectStdout('
"TEST 8"

[ "Привет, Андрей", "Привет, Вася и Валера" ]
');
$test->run();


// > TEST
// > Проверка фразы, которая есть только в русском языке (ещё не переведена переводчиком)
$fn = function () use ($i18n, $ffn) {
    $ffn->print('TEST 9');
    echo PHP_EOL;

    $langDefaultBefore = $i18n->setLangDefault('ru');
    $langCurrentBefore = $i18n->setLangCurrent('en');

    $i18n->resetUses();
    $i18n->useGroups([ 'main' ]);

    // > загрузка групп и слов из очереди будет вызвана при запросе фразы
    // $i18n->loadUses();

    $result = $i18n->phrase('@main.title.apple_only_russian', [ null ]);
    $ffn->print($result);

    $result = $i18n->phraseOrDefault('@main.title.apple_only_russian');
    $ffn->print($result);

    $i18n->setLangCurrent($langCurrentBefore);
    $i18n->setLangDefault($langDefaultBefore);
};
$test = $ffn->test($fn);
$test->expectStdout('
"TEST 9"

NULL
"яблоко"
');
$test->run();


// > TEST
// > Проверка выбора фразы по количеству / EN
$fn = function () use ($i18n, $ffn) {
    $ffn->print('TEST 10');
    echo PHP_EOL;

    $langDefaultBefore = $i18n->setLangDefault('ru');
    $langCurrentBefore = $i18n->setLangCurrent('en');

    $i18n->resetUses();
    $i18n->useGroups([ 'main' ]);

    // > загрузка групп и слов из очереди будет вызвана при запросе фразы
    // $i18n->loadUses();

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

    $i18n->setLangCurrent($langCurrentBefore);
    $i18n->setLangDefault($langDefaultBefore);
};
$test = $ffn->test($fn);
$test->expectStdout('
"TEST 10"

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
    $ffn->print('TEST 11');
    echo PHP_EOL;

    $langDefaultBefore = $i18n->setLangDefault('en');
    $langCurrentBefore = $i18n->setLangCurrent('ru');

    $i18n->resetUses();
    $i18n->useGroups([ 'main' ]);

    // > загрузка групп и слов из очереди будет вызвана при запросе фразы
    // $i18n->loadUses();

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

    $i18n->setLangCurrent($langCurrentBefore);
    $i18n->setLangDefault($langDefaultBefore);
};
$test = $ffn->test($fn);
$test->expectStdout('
"TEST 11"

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
// > Копируем имеющийся перевод в другой язык (если нам прислали переведенный файл)
$fn = function () use ($i18n, $langDir, $ffn) {
    $ffn->print('TEST 12');
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
    $ffn->print(is_file($langDir . '/by/main.jsonc'));

    /** @var \Generator $gen */
    $repository->delete($poolItemsCloned);
    $ffn->print(! file_exists($langDir . '/by/main.jsonc'));

    $it = new \DirectoryIterator($langDir . '/by');
    foreach ( $it as $spl ) {
        if ($spl->isDot()) continue;
        if ($spl->isDir()) continue;
        if ('.gitignore' === $spl->getFilename()) continue;

        unlink($spl->getRealPath());
    }
};
$test = $ffn->test($fn);
$test->expectStdout('
"TEST 12"

TRUE
TRUE
');
$test->run();
