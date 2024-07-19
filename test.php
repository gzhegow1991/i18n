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
set_exception_handler(function ($e) {
    var_dump(\Gzhegow\I18n\Lib::php_dump($e));
    var_dump($e->getMessage());
    var_dump(($e->getFile() ?? '{file}') . ': ' . ($e->getLine() ?? '{line}'));

    die();
});


// > сначала всегда факторка :)
$factory = new \Gzhegow\I18n\I18nFactory();

// > создаем репозиторий, в пакете есть три формата файлов, при желании - напишите свой репозиторий для работы с базой данных или любым другим хранилищем
$repoJson = new \Gzhegow\I18n\Repo\File\JsonFileRepo($langDir = __DIR__ . '/storage/resource/lang');
// $repoPhp = new \Gzhegow\I18n\Repo\File\I18nPhpFileRepo($factory, $langDir = __DIR__ . '/storage/resource/lang');
// $repoYaml = new \Gzhegow\I18n\Repo\File\I18nYamlFileRepo($factory, $langDir = __DIR__ . '/storage/resource/lang');

// > считываем конфигурацию, в ней перечислены множество языков, для проекта стоит скопировать себе встроенный файл и раскомментировать нужные языки, по-умолчанию активны 'en' и 'ru'
$languages = require __DIR__ . '/config/languages.php';

$config = [];
$config[ 'languages' ] = $languages;
// $config[ 'lang' ] = null;
// $config[ 'lang_default' ] = null;
// $config[ 'logger' ] = null;
// $config[ 'loggables' ][ \Gzhegow\I18n\I18nInterface::E_FORGOTTEN_GROUP ] = \Psr\Log\LogLevel::WARNING;
// $config[ 'loggables' ][ \Gzhegow\I18n\I18nInterface::E_MISSING_WORD    ] = \Psr\Log\LogLevel::WARNING;
// $config[ 'loggables' ][ \Gzhegow\I18n\I18nInterface::E_WRONG_AWORD     ] = \Psr\Log\LogLevel::WARNING;

$i18n = $factory->newI18n($repoJson, $config);

// > настраиваем типы, здесь их реализацию можно подменить
// \Gzhegow\I18n\Type\Type::setInstance(new \Gzhegow\I18n\Type\TypeManager());

// > для смены локалей можно зарегистрировать списки локалей на языки, локали будут меняться при смене языка
$phpLocales = [
    'en' => [
        LC_COLLATE  => [ $unix = 'en_US', $windows = 'en-US' ],
        LC_CTYPE    => [ $unix = 'en_US', $windows = 'en-US' ],
        LC_TIME     => [ $unix = 'en_US', $windows = 'en-US' ],
        LC_MONETARY => [ $unix = 'en_US', $windows = 'en-US' ],
        //
        // > рекомендую оставить LC_NUMERIC как 'C', поскольку изменение локали приводит к тому, что в дробных числах может ожидаться запятая вместо точки
        LC_NUMERIC  => 'C',
        //
        // > если ваша сборка PHP поддерживает libintl
        // LC_MESSAGES => [ $unix = 'ru_RU.UTF-8', $unix = 'ru_RU', $windows = 'Russian_Russia.1251', $windows = 'ru-RU' ],
    ],
    'ru' => [
        LC_COLLATE  => [ $unix = 'ru_RU.UTF-8', $unix = 'ru_RU', $windows = 'Russian_Russia.1251', $windows = 'ru-RU' ],
        LC_CTYPE    => [ $unix = 'ru_RU.UTF-8', $unix = 'ru_RU', $windows = 'Russian_Russia.1251', $windows = 'ru-RU' ],
        LC_TIME     => [ $unix = 'ru_RU.UTF-8', $unix = 'ru_RU', $windows = 'Russian_Russia.1251', $windows = 'ru-RU' ],
        LC_MONETARY => [ $unix = 'ru_RU.UTF-8', $unix = 'ru_RU', $windows = 'Russian_Russia.1251', $windows = 'ru-RU' ],
        //
        // > рекомендую оставить LC_NUMERIC как 'C', поскольку изменение локали приводит к тому, что в дробных числах может ожидаться запятая вместо точки
        LC_NUMERIC  => 'C',
        //
        // > если ваша сборка PHP поддерживает libintl
        // LC_MESSAGES => [ $unix = 'ru_RU.UTF-8', $unix = 'ru_RU', $windows = 'Russian_Russia.1251', $windows = 'ru-RU' ],
    ],
];
$i18n->registerPhpLocales('en', $phpLocales[ 'en' ]);
$i18n->registerPhpLocales('ru', $phpLocales[ 'ru' ]);

// > для использования подстановок по количеству (choice) стоит зарегистрировать преобразователи
$i18n->registerChoice('ru', new \Gzhegow\I18n\Choice\RuChoice());

// > включаем нужный язык при старте приложения (также меняются локали на зарегистрированные ранее)
// > если пользователь приложения переключает язык - менять этот параметр
$i18n->setLang('ru');

// > устанавливаем язык по-умолчанию. На практике бывают ситуации, когда перевода нет, и бизнес требует вывести на "основном" языке, например, на английском.
$i18n->setLangDefault('en');

// > рекомендуется подключить логирование для устранения дефектов перевода в процессе использования
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
        throw new RuntimeException('FAIL');
    }

    echo 'OK' . PHP_EOL;
};

$fnAwait = function (iterable $it) : array {
    $result = [];

    foreach ( $it as $i => $v ) {
        $result[] = [ $i, $v ];
    }

    return $result;
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
$phrase = "Hello, [:name1:] and [:name2:]";
$phraseInterpolated = $i18n->interpolate(
    $phrase,
    [ 'name1' => 'John', 'name2' => 'Jason' ]
);
$fnAssert('Hello, John and Jason' === $phraseInterpolated);

echo PHP_EOL;
echo 'TEST - Получаем фразу (обратите внимание, что фраза до перевода начинаются с `@`, чтобы избежать повторного перевода)' . PHP_EOL;
$i18n->resetLoaded();
$i18n->useGroups([ 'main' ]);
$fnAssert('Привет' === $i18n->phrase('@main.message.hello'));
$fnAssert(null === $i18n->phrase('@main.message.missing', $fallback = [ null ]));
$fnAssert('123' === $i18n->phrase('@main.message.missing', $fallback = [ 123 ]));
// $i18n->phrase('@main.message.missing', $fallback = []); // throws exception

echo PHP_EOL;
echo 'TEST - Получаем из памяти переводы (несколько) и подставляем в них аргументы (рекомендую в имени ключа указывать число аргументов)' . PHP_EOL;
$i18n->resetLoaded();
$i18n->useGroups([ 'main' ]);
$awords = [];
$awords[ 0 ] = '@main.message.hello_$';
$awords[ 1 ] = '@main.message.hello_$$';
$placeholders[ 0 ] = [ 'name' => 'Andrew' ];
$placeholders[ 1 ] = [ 'name1' => 'John', 'name2' => 'Jason' ];
$expected = [
    0 => 'Привет, Andrew',
    1 => 'Привет, John и Jason',
];
$fnAssert($expected === $i18n->phrases($awords, $fallbacks = null, $placeholders));

echo PHP_EOL;
echo 'TEST - Проверка фразы, которая есть только в русском языке (ещё не переведена переводчиком)' . PHP_EOL;
$langBefore = $i18n->getLang();
$langDefaultBefore = $i18n->getLangDefault();
$i18n->setLang('en');
$i18n->setLangDefault('ru');
$i18n->resetLoaded();
$i18n->useGroups([ 'main' ]);
$fnAssert(null === $i18n->phrase('@main.message.only_russian', [ null ]));
$fnAssert('[:choice:] текст' === $i18n->phraseOrDefault('@main.message.only_russian'));
$i18n->setLangDefault($langDefaultBefore);
$i18n->setLang($langBefore);

echo PHP_EOL;
echo 'TEST - Проверка выбора фразы по количеству / EN' . PHP_EOL;
$langBefore = $i18n->getLang();
$i18n->setLang('en');
$i18n->resetLoaded();
$i18n->useGroups([ 'main' ]);
$fnAssert("1 apple" === $i18n->choice(1, '@main.choice.apple'));
$fnAssert("2 apples" === $i18n->choice(2, '@main.choice.apple'));
$fnAssert("1.5 apples" === $i18n->choice(1.5, '@main.choice.apple'));
$numbers = [ 1, 2, 5, '1', '2', '5', 1.5, '1.5' ];
$awords = array_fill(0, count($numbers), '@main.choice.apple');
$expected = [
    0 => '1 apple',
    1 => '2 apples',
    2 => '5 apples',
    3 => '1 apple',
    4 => '2 apples',
    5 => '5 apples',
    6 => '1.5 apples',
    7 => '1.5 apples',
];
$fnAssert($expected === $i18n->choices($numbers, $awords));
$i18n->setLang($langBefore);

echo PHP_EOL;
echo 'TEST - Проверка выбора фразы по количеству / RU' . PHP_EOL;
$langBefore = $i18n->getLang();
$i18n->setLang('ru');
$i18n->resetLoaded();
$i18n->useGroups([ 'main' ]);
$fnAssert("1 яблоко" === $i18n->choice(1, '@main.choice.apple'));
$fnAssert("2 яблока" === $i18n->choice(2, '@main.choice.apple'));
$fnAssert("5 яблок" === $i18n->choice(5, '@main.choice.apple'));
$i18n->setLang($langBefore);

echo PHP_EOL;
echo 'TEST - Проверка выбора фразы по количеству / EN-RU' . PHP_EOL;
$langBefore = $i18n->getLang();
$langDefaultBefore = $i18n->getLangDefault();
$i18n->setLang('en');
$i18n->setLangDefault('ru');
$i18n->resetLoaded();
$i18n->useGroups([ 'main' ]);
$fnAssert(null === $i18n->choice(5, '@main.message.only_russian', [ null ]));
$expected = [
    0 => '1 текст',
    1 => '2 текста',
    2 => '5 текстов',
];
$fnAssert(
    $expected === $i18n->choicesOrDefault(
        [ 1, 2, 5 ],
        [
            '@main.message.only_russian',
            '@main.message.only_russian',
            '@main.message.only_russian',
        ]
    )
);
$i18n->setLangDefault($langDefaultBefore);
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
$fnAssert($expected === $repoJson->hasGroups([ 'main' ], [ 'en' ]));

echo PHP_EOL;
echo 'TEST - Проверяем наличие переводов в репозитории' . PHP_EOL;
$words = [
    'main.choice.apple',
    'main.title.plural',
    'main.title.plural_multiline',
];
$expected = [
    0 => [
        'status' => true,
        'word'   => 'main.choice.apple',
        'group'  => 'main',
        'lang'   => 'en',
    ],
    1 => [
        'status' => true,
        'word'   => 'main.title.plural',
        'group'  => 'main',
        'lang'   => 'en',
    ],
    2 => [
        'status' => true,
        'word'   => 'main.title.plural_multiline',
        'group'  => 'main',
        'lang'   => 'en',
    ],
];
$fnAssert($expected === $repoJson->hasWords($words, [ 'main' ], [ 'en' ]));

echo PHP_EOL;
echo 'TEST - Получаем переводы напрямую из репозитория' . PHP_EOL;
$words = [
    'main.choice.apple',
    'main.title.plural',
    'main.title.plural_multiline',
];
$gen = $repoJson->getWords($words, [ 'main' ], [ 'en' ]); // generator
$result = [];
foreach ( $gen as $poolItemsBatch ) {
    foreach ( $poolItemsBatch as $poolItem ) {
        $result[] = $poolItem->getPhrase();
    }
}
$expected = [
    0 => 'Item',
    1 => 'Item
Subitem',
    2 => '[:choice:] apple',
];
$fnAssert($expected === $result);

echo PHP_EOL;
echo 'TEST - Проверяем наличие переводов в памяти без запроса в репозиторий' . PHP_EOL;
/** @var \Gzhegow\I18n\Pool\PoolInterface $pool */
$i18n->resetLoaded();
$i18n->useGroups([ 'main' ]);
$pool = (function () {
    /** @var \Gzhegow\I18n\I18n $this */

    $this->loadUses();

    return $this->{'pool'};
})->call($i18n);
$words = [
    'main.choice.apple',
    'main.title.plural',
    'main.title.plural_multiline',
];
$expected = [
    0 => [
        'status' => true,
        'word'   => 'main.choice.apple',
        'group'  => 'main',
        'lang'   => 'ru',
    ],
    1 => [
        'status' => true,
        'word'   => 'main.title.plural',
        'group'  => 'main',
        'lang'   => 'ru',
    ],
    2 => [
        'status' => true,
        'word'   => 'main.title.plural_multiline',
        'group'  => 'main',
        'lang'   => 'ru',
    ],
];
$fnAssert($expected === $pool->has($words));

echo PHP_EOL;
echo 'TEST - Получаем переводы из памяти без запроса в репозиторий' . PHP_EOL;
/** @var \Gzhegow\I18n\Pool\PoolInterface $pool */
$i18n->resetLoaded();
$i18n->useGroups([ 'main' ]);
$pool = (function () {
    /** @var \Gzhegow\I18n\I18n $this */

    $this->loadUses();

    return $this->{'pool'};
})->call($i18n);
$words = [
    'main.choice.apple',
    'main.title.plural',
    'main.title.plural_multiline',
];
$poolItems = $pool->get($words, $groups = null, $langs = null); // array[]
$result = [];
foreach ( $poolItems as $i => $poolItem ) {
    $result[ $i ] = $poolItem->getChoices();
}
$expected = [
    0 => [
        0 => '[:choice:] яблоко',
        1 => '[:choice:] яблока',
        2 => '[:choice:] яблок',
    ],
    1 => [
        0 => 'Штука',
        1 => 'Штуки',
        2 => 'Штук',
    ],
    2 => [
        0 => 'Штука
Предмет',
        1 => 'Штуки
Предмета',
        2 => 'Штук
Предметов',
    ],
];
$fnAssert($expected === $result);

echo PHP_EOL;
echo 'TEST - Копируем имеющийся перевод в другой язык (если нам прислали переведенный файл)' . PHP_EOL;
$words = [
    'main.choice.apple',
    'main.title.plural',
    'main.title.plural_multiline',
];
$gen = $repoJson->getWords($words, [ 'main' ], [ 'en' ]);
$poolItemsCloned = [];
foreach ( $gen as $poolItemsBatch ) {
    foreach ( $poolItemsBatch as $poolItem ) {
        $itemClone = clone $poolItem;

        (function () {
            $this->lang = 'by';
        })->call($itemClone);

        $poolItemsCloned[] = $itemClone;
    }

    break;
}
//
$gen = $repoJson->save($poolItemsCloned);
$fnAwait($gen);
$fnAssert(is_file($langDir . '/by/main.json'));
//
$gen = $repoJson->delete($poolItemsCloned);
$fnAwait($gen);
$fnAssert(! file_exists($langDir . '/by/main.json'));
