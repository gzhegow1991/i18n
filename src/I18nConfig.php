<?php
/**
 * @noinspection PhpUndefinedNamespaceInspection
 * @noinspection PhpUndefinedClassInspection
 */

namespace Gzhegow\I18n;

use Gzhegow\Lib\Lib;
use Gzhegow\I18n\Choice\RuChoice;
use Gzhegow\Lib\Config\AbstractConfig;
use Gzhegow\I18n\Choice\DefaultChoice;
use Gzhegow\I18n\Exception\LogicException;
use Gzhegow\I18n\Choice\I18nChoiceInterface;


/**
 * @property array<string, array{
 *     0: string,
 *     1: string,
 *     2: string,
 *     3: string
 * }>                                               $languages
 * @property array<string, I18nChoiceInterface>     $choices
 *
 * @property array<string, array<string, string[]>> $phpLocales
 *
 * @property string                                 $lang
 * @property string                                 $langDefault
 *
 * @property \Psr\Log\LoggerInterface               $logger
 * @property array<int, int>                        $loggables
 */
class I18nConfig extends AbstractConfig
{
    /**
     * @var array<string, array{
     *      0: string,
     *      1: string,
     *      2: string,
     *      3: string
     *  }>
     */
    protected $languages = [
        'en' => [ 'en_GB', 'Latn', 'English', 'English' ],
        'ru' => [ 'ru_RU', 'Cyrl', 'Russian', 'Русский' ],

        // 'aa'      => [ 'aa_ER', 'Latn', 'Afar', 'Qafar' ],
        // 'af'      => [ 'af_ZA', 'Latn', 'Afrikaans', 'Afrikaans' ],
        // 'ak'      => [ 'ak_GH', 'Latn', 'Akan', 'Akan' ],
        // 'am'      => [ 'am_ET', 'Ethi', 'Amharic', 'አማርኛ' ],
        // 'an'      => [ 'an_ES', 'Latn', 'Aragonese', 'Aragonés' ],
        // 'ar'      => [ 'ar_AE', 'Arab', 'Arabic', 'العربية' ],
        // 'as'      => [ 'as_IN', 'Beng', 'Assamese', 'অসমীয়া' ],
        // 'ay'      => [ 'ay_PE', 'Latn', 'Aymara', 'Aymar aru' ],
        // 'az'      => [ 'az_AZ', 'Latn', 'Azerbaijani (Latin)', 'Azərbaycanca' ],
        // 'be'      => [ 'be_BY', 'Cyrl', 'Belarusian', 'Беларуская' ],
        // 'bem'     => [ 'bem_ZM', 'Latn', 'Bemba', 'Ichibemba' ],
        // 'bg'      => [ 'bg_BG', 'Cyrl', 'Bulgarian', 'Български' ],
        // 'bn'      => [ 'bn_BD', 'Beng', 'Bengali', 'বাংলা' ],
        // 'bo'      => [ 'bo_IN', 'Tibt', 'Tibetan', 'པོད་སྐད་' ],
        // 'br'      => [ 'br_FR', 'Latn', 'Breton', 'Brezhoneg' ],
        // 'brx'     => [ 'brx_IN', 'Deva', 'Bodo', 'बड़ो' ],
        // 'bs'      => [ 'bs_BA', 'Latn', 'Bosnian', 'Bosanski' ],
        // 'byn'     => [ 'byn_ER', 'Ethi', 'Blin', 'ብሊን' ],
        // 'ca'      => [ 'ca_ES', 'Latn', 'Catalan', 'Català' ],
        // 'ce'      => [ 'ce_RU', 'Cyrl', 'Chechen', 'Нохчийн мотт' ],
        // 'cs'      => [ 'cs_CZ', 'Latn', 'Czech', 'Čeština' ],
        // 'cv'      => [ 'cv_RU', 'Cyrl', 'Chuvash', 'Чӑваш чӗлхи' ],
        // 'cy'      => [ 'cy_GB', 'Latn', 'Welsh', 'Cymraeg' ],
        // 'da'      => [ 'da_DK', 'Latn', 'Danish', 'Dansk' ],
        // 'de-AT'   => [ 'de_AT', 'Latn', 'Austrian German', 'Österreichisches Deutsch' ],
        // 'de-CH'   => [ 'de_CH', 'Latn', 'Swiss High German', 'Schweizer Hochdeutsch' ],
        // 'de'      => [ 'de_DE', 'Latn', 'German', 'Deutsch' ],
        // 'doi'     => [ 'doi_IN', 'Deva', 'Dogri', 'डोगरी' ],
        // 'dv'      => [ 'dv_MV', 'Thaa', 'Divehi', 'ދިވެހިބަސް' ],
        // 'dz'      => [ 'dz_BT', 'Tibt', 'Dzongkha', 'རྫོང་ཁ' ],
        // 'el'      => [ 'el_GR', 'Grek', 'Greek', 'Ελληνικά' ],
        // 'en-AU'   => [ 'en_AU', 'Latn', 'Australian English', 'Australian English' ],
        // 'en-CA'   => [ 'en_CA', 'Latn', 'Canadian English', 'Canadian English' ],
        // 'en-GB'   => [ 'en_GB', 'Latn', 'British English', 'British English' ],
        // 'en' => [ 'en_GB', 'Latn', 'English', 'English' ],
        // 'en-US'   => [ 'en_US', 'Latn', 'U.S. English', 'U.S. English' ],
        // 'es'      => [ 'es_ES', 'Latn', 'Spanish', 'Español' ],
        // 'et'      => [ 'et_EE', 'Latn', 'Estonian', 'Eesti' ],
        // 'eu'      => [ 'eu_ES', 'Latn', 'Basque', 'Euskara' ],
        // 'fa'      => [ 'fa_IR', 'Arab', 'Persian', 'فارسی' ],
        // 'ff'      => [ 'ff_SN', 'Latn', 'Fulah', 'Pulaar' ],
        // 'fi'      => [ 'fi_FI', 'Latn', 'Finnish', 'Suomi' ],
        // 'fil'     => [ 'fil_PH', 'Latn', 'Filipino', 'Filipino' ],
        // 'fo'      => [ 'fo_FO', 'Latn', 'Faroese', 'Føroyskt' ],
        // 'fr-CA'   => [ 'fr_CA', 'Latn', 'Canadian French', 'Français canadien' ],
        // 'fr'      => [ 'fr_FR', 'Latn', 'French', 'Français' ],
        // 'fur'     => [ 'fur_IT', 'Latn', 'Friulian', 'Furlan' ],
        // 'fy'      => [ 'fy_DE', 'Latn', 'Western Frisian', 'Frysk' ],
        // 'ga'      => [ 'ga_IE', 'Latn', 'Irish', 'Gaeilge' ],
        // 'gd'      => [ 'gd_GB', 'Latn', 'Scottish Gaelic', 'Gàidhlig' ],
        // 'gl'      => [ 'gl_ES', 'Latn', 'Galician', 'Galego' ],
        // 'gu'      => [ 'gu_IN', 'Gujr', 'Gujarati', 'ગુજરાતી' ],
        // 'gv'      => [ 'gv_GB', 'Latn', 'Manx', 'Gaelg' ],
        // 'ha'      => [ 'ha_NG', 'Latn', 'Hausa', 'Hausa' ],
        // 'he'      => [ 'he_IL', 'Hebr', 'Hebrew', 'עברית' ],
        // 'hi'      => [ 'hi_IN', 'Deva', 'Hindi', 'हिन्दी' ],
        // 'hr'      => [ 'hr_HR', 'Latn', 'Croatian', 'Hrvatski' ],
        // 'ht'      => [ 'ht_HT', 'Latn', 'Haitian', 'Kreyòl ayisyen' ],
        // 'hu'      => [ 'hu_HU', 'Latn', 'Hungarian', 'Magyar' ],
        // 'hy'      => [ 'hy_AM', 'Armn', 'Armenian', 'Հայերեն' ],
        // 'ia'      => [ 'ia_FR', 'Latn', 'Interlingua', 'Interlingua' ],
        // 'id'      => [ 'id_ID', 'Latn', 'Indonesian', 'Bahasa Indonesia' ],
        // 'ig'      => [ 'ig_NG', 'Latn', 'Igbo', 'Igbo' ],
        // 'ik'      => [ 'ik_CA', 'Latn', 'Inupiaq', 'Iñupiaq' ],
        // 'is'      => [ 'is_IS', 'Latn', 'Icelandic', 'Íslenska' ],
        // 'it'      => [ 'it_IT', 'Latn', 'Italian', 'Italiano' ],
        // 'iu'      => [ 'iu_CA', 'Cans', 'Inuktitut (Canadian Aboriginal Syllabics)', 'ᐃᓄᒃᑎᑐᑦ' ],
        // 'iu-Latn' => [ 'iu_CA', 'Latn', 'Inuktitut (Latin)', 'Inuktitut' ],
        // 'ja'      => [ 'ja_JP', 'Jpan', 'Japanese', '日本語' ],
        // 'ka'      => [ 'ka_GE', 'Geor', 'Georgian', 'ქართული' ],
        // 'kab'     => [ 'kab_DZ', 'Latn', 'Kabyle', 'Taqbaylit' ],
        // 'kk'      => [ 'kk_KZ', 'Cyrl', 'Kazakh', 'Қазақ тілі' ],
        // 'kl'      => [ 'kl_GL', 'Latn', 'Kalaallisut', 'Kalaallisut' ],
        // 'km'      => [ 'km_KH', 'Khmr', 'Khmer', 'ភាសាខ្មែរ' ],
        // 'kn'      => [ 'kn_IN', 'Knda', 'Kannada', 'ಕನ್ನಡ' ],
        // 'ko'      => [ 'ko_KR', 'Hang', 'Korean', '한국어' ],
        // 'kok'     => [ 'kok_IN', 'Deva', 'Konkani', 'कोंकणी' ],
        // 'ks'      => [ 'ks_IN', 'Arab', 'Kashmiri (Arabic)', 'کأشُر' ],
        // 'ks-Deva' => [ 'ks_IN', 'Deva', 'Kashmiri (Devaganari)', 'कॉशुर' ],
        // 'ku'      => [ 'ku_TR', 'Arab', 'Kurdish', 'کوردی' ],
        // 'kw'      => [ 'kw_GB', 'Latn', 'Cornish', 'Kernewek' ],
        // 'ky'      => [ 'ky_KG', 'Cyrl', 'Kyrgyz', 'Кыргыз' ],
        // 'lb'      => [ 'lb_LU', 'Latn', 'Luxembourgish', 'Lëtzebuergesch' ],
        // 'lg'      => [ 'lg_UG', 'Latn', 'Ganda', 'Luganda' ],
        // 'li'      => [ 'li_BE', 'Latn', 'Limburgish', 'Limburgs' ],
        // 'lo'      => [ 'lo_LA', 'Laoo', 'Lao', 'ລາວ' ],
        // 'lt'      => [ 'lt_LT', 'Latn', 'Lithuanian', 'Lietuvių' ],
        // 'lv'      => [ 'lv_LV', 'Latn', 'Latvian', 'Latviešu' ],
        // 'mai'     => [ 'mai_IN', 'Tirh', 'Maithili', 'मैथिली' ],
        // 'mg'      => [ 'mg_MG', 'Latn', 'Malagasy', 'Malagasy' ],
        // 'mh'      => [ 'mh_MH', 'Latn', 'Marshallese', 'Kajin M̧ajeļ' ],
        // 'mi'      => [ 'mi_NZ', 'Latn', 'Māori', 'Māori' ],
        // 'mk'      => [ 'mk_MK', 'Cyrl', 'Macedonian', 'Македонски' ],
        // 'ml'      => [ 'ml_IN', 'Mlym', 'Malayalam', 'മലയാളം' ],
        // 'mn'      => [ 'mn_MN', 'Cyrl', 'Mongolian (Cyrillic)', 'Монгол' ],
        // 'mn-Mong' => [ 'mn_MN', 'Mong', 'Mongolian (Mongolian)', 'ᠮᠣᠨᠭᠭᠣᠯ ᠬᠡᠯᠡ' ],
        // 'mni'     => [ 'mni_IN', 'Beng', 'Manipuri', 'মৈতৈ' ],
        // 'mr'      => [ 'mr_IN', 'Deva', 'Marathi', 'मराठी' ],
        // 'ms'      => [ 'ms_MY', 'Latn', 'Malay', 'Bahasa Melayu' ],
        // 'mt'      => [ 'mt_MT', 'Latn', 'Maltese', 'Malti' ],
        // 'my'      => [ 'my_MM', 'Mymr', 'Burmese', 'မြန်မာဘာသာ' ],
        // 'nb'      => [ 'nb_NO', 'Latn', 'Norwegian Bokmål', 'Bokmål' ],
        // 'nds'     => [ 'nds_DE', 'Latn', 'Low German', 'Plattdüütsch' ],
        // 'nl'      => [ 'nl_NL', 'Latn', 'Dutch', 'Nederlands' ],
        // 'nn'      => [ 'nn_NO', 'Latn', 'Norwegian Nynorsk', 'Nynorsk' ],
        // 'nr'      => [ 'nr_ZA', 'Latn', 'South Ndebele', 'IsiNdebele' ],
        // 'nso'     => [ 'nso_ZA', 'Latn', 'Northern Sotho', 'Sesotho sa Leboa' ],
        // 'oc'      => [ 'oc_FR', 'Latn', 'Occitan', 'Occitan' ],
        // 'om'      => [ 'om_ET', 'Latn', 'Oromo', 'Oromoo' ],
        // 'or'      => [ 'or_IN', 'Orya', 'Oriya', 'ଓଡ଼ିଆ' ],
        // 'os'      => [ 'os_RU', 'Cyrl', 'Ossetic', 'Ирон' ],
        // 'pa-Arab' => [ 'pa_IN', 'Arab', 'Punjabi (Arabic)', 'پنجاب' ],
        // 'pa'      => [ 'pa_IN', 'Guru', 'Punjabi (Gurmukhi)', 'ਪੰਜਾਬੀ' ],
        // 'pl'      => [ 'pl_PL', 'Latn', 'Polish', 'Polski' ],
        // 'ps'      => [ 'ps_AF', 'Arab', 'Pashto', 'پښتو' ],
        // 'pt-BR'   => [ 'pt_BR', 'Latn', 'Brazilian Portuguese', 'Português do Brasil' ],
        // 'pt'      => [ 'pt_PT', 'Latn', 'Portuguese', 'Português' ],
        // 'ro'      => [ 'ro_RO', 'Latn', 'Romanian', 'Română' ],
        // 'ru' => [ 'ru_RU', 'Cyrl', 'Russian', 'Русский' ],
        // 'rw'      => [ 'rw_RW', 'Latn', 'Kinyarwanda', 'Kinyarwanda' ],
        // 'sa'      => [ 'sa_IN', 'Deva', 'Sanskrit', 'संस्कृतम्' ],
        // 'sc'      => [ 'sc_IT', 'Latn', 'Sardinian', 'Sardu' ],
        // 'sd'      => [ 'sd_IN', 'Arab', 'Sindhi', 'سنڌي' ],
        // 'se'      => [ 'se_NO', 'Latn', 'Northern Sami', 'Davvisámegiella' ],
        // 'si'      => [ 'si_LK', 'Sinh', 'Sinhala', 'සිංහල' ],
        // 'sid'     => [ 'sid_ET', 'Latn', 'Sidamo', 'Sidaamu Afo' ],
        // 'sk'      => [ 'sk_SK', 'Latn', 'Slovak', 'Slovenčina' ],
        // 'sl'      => [ 'sl_SI', 'Latn', 'Slovene', 'Slovenščina' ],
        // 'so'      => [ 'so_SO', 'Latn', 'Somali', 'Soomaali' ],
        // 'sq'      => [ 'sq_AL', 'Latn', 'Albanian', 'Shqip' ],
        // 'sr'      => [ 'sr_RS', 'Cyrl', 'Serbian (Cyrillic)', 'Српски' ],
        // 'sr-Latn' => [ 'sr_RS', 'Latn', 'Serbian (Latin)', 'Srpski' ],
        // 'ss'      => [ 'ss_ZA', 'Latn', 'Swati', 'Siswati' ],
        // 'st'      => [ 'st_ZA', 'Latn', 'Southern Sotho', 'Sesotho' ],
        // 'sv'      => [ 'sv_SE', 'Latn', 'Swedish', 'Svenska' ],
        // 'sw'      => [ 'sw_KE', 'Latn', 'Swahili', 'Kiswahili' ],
        // 'ta'      => [ 'ta_IN', 'Taml', 'Tamil', 'தமிழ்' ],
        // 'te'      => [ 'te_IN', 'Telu', 'Telugu', 'తెలుగు' ],
        // 'tg-Arab' => [ 'tg_TJ', 'Arab', 'Tajik (Arabic)', 'تاجیکی' ],
        // 'tg'      => [ 'tg_TJ', 'Cyrl', 'Tajik (Cyrillic)', 'Тоҷикӣ' ],
        // 'tg-Latn' => [ 'tg_TJ', 'Latn', 'Tajik (Latin)', 'Tojikī' ],
        // 'th'      => [ 'th_TH', 'Thai', 'Thai', 'ไทย' ],
        // 'ti'      => [ 'ti_ET', 'Ethi', 'Tigrinya', 'ትግርኛ' ],
        // 'tig'     => [ 'tig_ER', 'Ethi', 'Tigre', 'ትግረ' ],
        // 'tk'      => [ 'tk_TM', 'Cyrl', 'Turkmen', 'Түркменче' ],
        // 'tl'      => [ 'tl_PH', 'Latn', 'Tagalog', 'Tagalog' ],
        // 'tn'      => [ 'tn_ZA', 'Latn', 'Tswana', 'Setswana' ],
        // 'tr'      => [ 'tr_TR', 'Latn', 'Turkish', 'Türkçe' ],
        // 'ts'      => [ 'ts_ZA', 'Latn', 'Tsonga', 'Xitsonga' ],
        // 'tt'      => [ 'tt_RU', 'Cyrl', 'Tatar', 'Татар теле' ],
        // 'ug'      => [ 'ug_CN', 'Arab', 'Uyghur', 'ئۇيغۇرچە' ],
        // 'uk'      => [ 'uk_UA', 'Cyrl', 'Ukrainian', 'Українська' ],
        // 'ur'      => [ 'ur_PK', 'Arab', 'Urdu', 'اردو' ],
        // 'az-Cyrl' => [ 'uz_UZ', 'Cyrl', 'Azerbaijani (Cyrillic)', 'Азәрбајҹан' ],
        // 'uz'      => [ 'uz_UZ', 'Cyrl', 'Uzbek (Cyrillic)', 'Ўзбек' ],
        // 'uz-Latn' => [ 'uz_UZ', 'Latn', 'Uzbek (Latin)', 'Oʼzbekcha' ],
        // 'lu'      => [ 've_ZA', 'Latn', 'Luba-Katanga', 'Tshiluba' ],
        // 'vi'      => [ 'vi_VN', 'Latn', 'Vietnamese', 'Tiếng Việt' ],
        // 'wa'      => [ 'wa_BE', 'Latn', 'Walloon', 'Walon' ],
        // 'wae'     => [ 'wae_CH', 'Latn', 'Walser', 'Walser' ],
        // 'wal'     => [ 'wal_ET', 'Ethi', 'Wolaytta', 'ወላይታቱ' ],
        // 'wo'      => [ 'wo_SN', 'Latn', 'Wolof', 'Wolof' ],
        // 'xh'      => [ 'xh_ZA', 'Latn', 'Xhosa', 'IsiXhosa' ],
        // 'yi'      => [ 'yi_US', 'Hebr', 'Yiddish', 'ייִדיש' ],
        // 'yo'      => [ 'yo_NG', 'Latn', 'Yoruba', 'Èdè Yorùbá' ],
        // 'yue'     => [ 'yue_HK', 'Hant', 'Yue', '廣州話' ],
        // 'zh'      => [ 'zh_CN', 'Hans', 'Chinese (Simplified)', '简体中文' ],
        // 'zh-Hant' => [ 'zh_CN', 'Hant', 'Chinese (Traditional)', '繁體中文' ],
        // 'zu'      => [ 'zu_ZA', 'Latn', 'Zulu', 'IsiZulu' ],

        // 'ab'          => [ 'ab', 'Cyrl', 'Abkhazian', 'Аҧсуа' ],
        // 'ace'         => [ 'ace', 'Latn', 'Achinese', 'Aceh' ],
        // 'ady'         => [ 'ady', 'Cyrl', 'Adyghe', 'Адыгэбзэ' ],
        // 'ae'          => [ 'ae', 'Latn', 'Avestan', 'Avesta' ],
        // 'agq'         => [ 'agq', 'Latn', 'Aghem', 'Aghem' ],
        // 'ale'         => [ 'ale', 'Latn', 'Aleut', 'Unangax tunuu' ],
        // 'ang'         => [ 'ang', 'Runr', 'Old English', 'Old English' ],
        // 'asa'         => [ 'asa', 'Latn', 'Kipare', 'Kipare' ],
        // 'av'          => [ 'av', 'Cyrl', 'Avaric', 'Авар мацӀ' ],
        // 'ba'          => [ 'ba', 'Cyrl', 'Bashkir', 'Башҡорт теле' ],
        // 'bas'         => [ 'bas', 'Latn', 'Basa', 'Ɓàsàa' ],
        // 'bez'         => [ 'bez', 'Latn', 'Bena', 'Hibena' ],
        // 'bh'          => [ 'bh', 'Latn', 'Bihari', 'Bihari' ],
        // 'bi'          => [ 'bi', 'Latn', 'Bislama', 'Bislama' ],
        // 'bm'          => [ 'bm', 'Latn', 'Bambara', 'Bamanakan' ],
        // 'bra'         => [ 'bra', 'Deva', 'Braj', 'ब्रज भाषा' ],
        // 'ca-valencia' => [ 'ca-valencia', 'Latn', 'Valencian', 'Valencià' ],
        // 'cch'         => [ 'cch', 'Latn', 'Atsam', 'Atsam' ],
        // 'cgg'         => [ 'cgg', 'Latn', 'Chiga', 'Rukiga' ],
        // 'ch'          => [ 'ch', 'Latn', 'Chamorro', 'Chamoru' ],
        // 'chr'         => [ 'chr', 'Cher', 'Cherokee', 'ᏣᎳᎩ' ],
        // 'co'          => [ 'co', 'Latn', 'Corsican', 'Corsu' ],
        // 'cr'          => [ 'cr', 'Cans', 'Cree', 'ᓀᐦᐃᔭᐍᐏᐣ' ],
        // 'cu'          => [ 'cu', 'Cyrl', 'Church Slavic', 'Ѩзыкъ словѣньскъ' ],
        // 'dav'         => [ 'dav', 'Latn', 'Dawida', 'Kitaita' ],
        // 'dje'         => [ 'dje', 'Latn', 'Zarma', 'Zarmaciine' ],
        // 'dua'         => [ 'dua', 'Latn', 'Duala', 'Duálá' ],
        // 'dyo'         => [ 'dyo', 'Latn', 'Jola-Fonyi', 'Joola' ],
        // 'ebu'         => [ 'ebu', 'Latn', 'Kiembu', 'Kĩembu' ],
        // 'ee'          => [ 'ee', 'Latn', 'Ewe', 'Eʋegbe' ],
        // 'eo'          => [ 'eo', 'Latn', 'Esperanto', 'Esperanto' ],
        // 'ewo'         => [ 'ewo', 'Latn', 'Ewondo', 'Ewondo' ],
        // 'fj'          => [ 'fj', 'Latn', 'Fijian', 'Vosa Vakaviti' ],
        // 'gaa'         => [ 'gaa', 'Latn', 'Ga', 'Ga' ],
        // 'gn'          => [ 'gn', 'Latn', 'Guaraní', 'Avañe’ẽ' ],
        // 'gsw'         => [ 'gsw', 'Latn', 'Swiss German', 'Schwiizertüütsch' ],
        // 'guz'         => [ 'guz', 'Latn', 'Ekegusii', 'Ekegusii' ],
        // 'haw'         => [ 'haw', 'Latn', 'Hawaiian', 'ʻŌlelo Hawaiʻi' ],
        // 'ho'          => [ 'ho', 'Latn', 'Hiri Motu', 'Hiri Motu' ],
        // 'hz'          => [ 'hz', 'Latn', 'Herero', 'Otjiherero' ],
        // 'ii'          => [ 'ii', 'Yiii', 'Sichuan Yi', 'ꆈꌠꉙ' ],
        // 'io'          => [ 'io', 'Latn', 'Ido', 'Ido' ],
        // 'jmc'         => [ 'jmc', 'Latn', 'Machame', 'Kimachame' ],
        // 'jv'          => [ 'jv', 'Latn', 'Javanese (Latin)', 'Basa Jawa' ],
        // 'jv-Java'     => [ 'jv-Java', 'Java', 'Javanese (Javanese)', 'ꦧꦱꦗꦮ' ],
        // 'kaj'         => [ 'kaj', 'Latn', 'Jju', 'Kaje' ],
        // 'kam'         => [ 'kam', 'Latn', 'Kamba', 'Kikamba' ],
        // 'kcg'         => [ 'kcg', 'Latn', 'Tyap', 'Katab' ],
        // 'kde'         => [ 'kde', 'Latn', 'Makonde', 'Chimakonde' ],
        // 'kea'         => [ 'kea', 'Latn', 'Kabuverdianu', 'Kabuverdianu' ],
        // 'kg'          => [ 'kg', 'Latn', 'Kongo', 'Kikongo' ],
        // 'khq'         => [ 'khq', 'Latn', 'Koyra Chiini', 'Koyra ciini' ],
        // 'ki'          => [ 'ki', 'Latn', 'Kikuyu', 'Gikuyu' ],
        // 'kj'          => [ 'kj', 'Latn', 'Kuanyama', 'Kwanyama' ],
        // 'kln'         => [ 'kln', 'Latn', 'Kalenjin', 'Kalenjin' ],
        // 'kr'          => [ 'kr', 'Latn', 'Kanuri', 'Kanuri' ],
        // 'ksb'         => [ 'ksb', 'Latn', 'Shambala', 'Kishambaa' ],
        // 'ksf'         => [ 'ksf', 'Latn', 'Bafia', 'Rikpa' ],
        // 'ksh'         => [ 'ksh', 'Latn', 'Kölsch', 'Kölsch' ],
        // 'kv'          => [ 'kv', 'Cyrl', 'Komi', 'Коми кыв' ],
        // 'la'          => [ 'la', 'Latn', 'Latin', 'Latine' ],
        // 'lag'         => [ 'lag', 'Latn', 'Langi', 'Kɨlaangi' ],
        // 'lah'         => [ 'lah', 'Latn', 'Lahnda', 'Lahnda' ],
        // 'ln'          => [ 'ln', 'Latn', 'Lingala', 'Lingála' ],
        // 'luo'         => [ 'luo', 'Latn', 'Luo', 'Dholuo' ],
        // 'luy'         => [ 'luy', 'Latn', 'Oluluyia', 'Luluhia' ],
        // 'mas'         => [ 'mas', 'Latn', 'Masai', 'Ɔl-Maa' ],
        // 'mer'         => [ 'mer', 'Latn', 'Kimîîru', 'Kĩmĩrũ' ],
        // 'mfe'         => [ 'mfe', 'Latn', 'Morisyen', 'Kreol morisien' ],
        // 'mgh'         => [ 'mgh', 'Latn', 'Makhuwa-Meetto', 'Makua' ],
        // 'mtr'         => [ 'mtr', 'Latn', 'Mewari', 'Mewari' ],
        // 'mua'         => [ 'mua', 'Latn', 'Mundang', 'Mundang' ],
        // 'na'          => [ 'na', 'Latn', 'Nauru', 'Ekakairũ Naoero' ],
        // 'naq'         => [ 'naq', 'Latn', 'Nama', 'Khoekhoegowab' ],
        // 'nd'          => [ 'nd', 'Latn', 'North Ndebele', 'IsiNdebele' ],
        // 'ne'          => [ 'ne', 'Deva', 'Nepali', 'नेपाली' ],
        // 'ng'          => [ 'ng', 'Latn', 'Ndonga', 'OshiNdonga' ],
        // 'nmg'         => [ 'nmg', 'Latn', 'Kwasio', 'Ngumba' ],
        // 'nus'         => [ 'nus', 'Latn', 'Nuer', 'Thok Nath' ],
        // 'nv'          => [ 'nv', 'Latn', 'Navajo', 'Diné bizaad' ],
        // 'ny'          => [ 'ny', 'Latn', 'Chewa', 'ChiCheŵa' ],
        // 'nyn'         => [ 'nyn', 'Latn', 'Nyankole', 'Runyankore' ],
        // 'oj'          => [ 'oj', 'Cans', 'Ojibwa', 'ᐊᓂᔑᓈᐯᒧᐎᓐ' ],
        // 'pi'          => [ 'pi', 'Latn', 'Pahari-Potwari', 'Pāli' ],
        // 'pra'         => [ 'pra', 'Deva', 'Prakrit', 'प्राकृत' ],
        // 'qu'          => [ 'qu', 'Latn', 'Quechua', 'Runa Simi' ],
        // 'raj'         => [ 'raj', 'Deva', 'Rajasthani', 'राजस्थानी' ],
        // 'rm'          => [ 'rm', 'Latn', 'Romansh', 'Rumantsch' ],
        // 'rn'          => [ 'rn', 'Latn', 'Rundi', 'Ikirundi' ],
        // 'rof'         => [ 'rof', 'Latn', 'Rombo', 'Kihorombo' ],
        // 'rwk'         => [ 'rwk', 'Latn', 'Rwa', 'Kiruwa' ],
        // 'sah'         => [ 'sah', 'Cyrl', 'Yakut', 'Саха тыла' ],
        // 'saq'         => [ 'saq', 'Latn', 'Samburu', 'Kisampur' ],
        // 'sbp'         => [ 'sbp', 'Latn', 'Sileibi', 'Ishisangu' ],
        // 'seh'         => [ 'seh', 'Latn', 'Sena', 'Sena' ],
        // 'ses'         => [ 'ses', 'Latn', 'Songhay', 'Koyraboro senni' ],
        // 'sg'          => [ 'sg', 'Latn', 'Sango', 'Sängö' ],
        // 'sh'          => [ 'sh', 'Latn', 'Serbo-Croatian', 'Srpskohrvatski' ],
        // 'shi'         => [ 'shi', 'Latn', 'Tachelhit (Latin)', 'Tashelhit' ],
        // 'shi-Tfng'    => [ 'shi-Tfng', 'Tfng', 'Tachelhit (Tifinagh)', 'ⵜⴰⵎⴰⵣⵉⵖⵜ' ],
        // 'sm'          => [ 'sm', 'Latn', 'Samoan', 'Gagana fa’a Sāmoa' ],
        // 'sn'          => [ 'sn', 'Latn', 'Shona', 'ChiShona' ],
        // 'ssy'         => [ 'ssy', 'Latn', 'Saho', 'Saho' ],
        // 'su'          => [ 'su', 'Latn', 'Sundanese', 'Basa Sunda' ],
        // 'swc'         => [ 'swc', 'Latn', 'Congo Swahili', 'Kiswahili ya Kongo' ],
        // 'teo'         => [ 'teo', 'Latn', 'Teso', 'Kiteso' ],
        // 'to'          => [ 'to', 'Latn', 'Tongan', 'Lea fakatonga' ],
        // 'trv'         => [ 'trv', 'Latn', 'Taroko', 'Seediq' ],
        // 'tw'          => [ 'tw', 'Latn', 'Twi', 'Twi' ],
        // 'twq'         => [ 'twq', 'Latn', 'Tasawaq', 'Tasawaq senni' ],
        // 'ty'          => [ 'ty', 'Latn', 'Tahitian', 'Reo Māohi' ],
        // 'tzm'         => [ 'tzm', 'Tfng', 'Central Atlas Tamazight (Tifinagh)', 'ⵜⴰⵎⴰⵣⵉⵖⵜ' ],
        // 'tzm-Latn'    => [ 'tzm-Latn', 'Latn', 'Central Atlas Tamazight (Latin)', 'Tamazight' ],
        // 'uz-Arab'     => [ 'uz-Arab', 'Arab', 'Uzbek (Arabic)', 'اۉزبېک' ],
        // 'vai'         => [ 'vai', 'Vaii', 'Vai (Vai)', 'ꕙꔤ' ],
        // 'vai-Latn'    => [ 'vai-Latn', 'Latn', 'Vai (Latin)', 'Viyamíĩ' ],
        // 've'          => [ 've', 'Latn', 'Venda', 'Tshivenḓa' ],
        // 'vo'          => [ 'vo', 'Latn', 'Volapük', 'Volapük' ],
        // 'wen'         => [ 'wen', 'Latn', 'Sorbian', 'Wendic' ],
        // 'xog'         => [ 'xog', 'Latn', 'Soga', 'Olusoga' ],
        // 'yav'         => [ 'yav', 'Latn', 'Yangben', 'Nuasue' ],
    ];
    /**
     * @var array<string, I18nChoiceInterface>
     */
    protected $choices = [
        'en' => null,
        'ru' => null,
    ];

    /**
     * @var array<string, array<string, string[]>>
     */
    protected $phpLocales = [
        'ru' => [
            // > пример
            // LC_COLLATE  => [ $unix = 'ru_RU.UTF-8', $unix = 'ru_RU', $windows = 'Russian_Russia.1251', $windows = 'ru-RU' ],
            //
            LC_COLLATE  => [ 'ru_RU.UTF-8', 'ru_RU', 'Russian_Russia.1251', 'ru-RU' ],
            LC_CTYPE    => [ 'ru_RU.UTF-8', 'ru_RU', 'Russian_Russia.1251', 'ru-RU' ],
            LC_TIME     => [ 'ru_RU.UTF-8', 'ru_RU', 'Russian_Russia.1251', 'ru-RU' ],
            LC_MONETARY => [ 'ru_RU.UTF-8', 'ru_RU', 'Russian_Russia.1251', 'ru-RU' ],
            //
            // > рекомендую использовать `C` в качестве локали для цифр, иначе можно столкнуться с запятой вместо десятичной точки
            LC_NUMERIC  => [ 'C' ],
            //
            // > если вы скомпилировали PHP с поддержкой `libintl`, можно LC_MESSAGES тоже указать
            // LC_MESSAGES => [ 'ru_RU.UTF-8', 'ru_RU', 'Russian_Russia.1251', 'ru-RU' ],
        ],
        'en' => [
            LC_COLLATE  => [ 'en_US', 'en-US' ],
            LC_CTYPE    => [ 'en_US', 'en-US' ],
            LC_TIME     => [ 'en_US', 'en-US' ],
            LC_MONETARY => [ 'en_US', 'en-US' ],
            LC_NUMERIC  => [ 'C' ],
        ],
    ];

    /**
     * @var string
     */
    protected $lang;
    /**
     * @var string
     */
    protected $langDefault;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    /**
     * @see I18nInterface::E_LIST
     * @see \Psr\Log\LogLevel::WARNING
     *
     * @var array<int, int>
     */
    protected $loggables = [
        // \Gzhegow\I18n\I18nInterface::E_FORGOTTEN_GROUP => \Psr\Log\LogLevel::WARNING,
        // \Gzhegow\I18n\I18nInterface::E_MISSING_WORD    => \Psr\Log\LogLevel::WARNING,
        // \Gzhegow\I18n\I18nInterface::E_WRONG_AWORD     => \Psr\Log\LogLevel::WARNING,
    ];


    public function __construct()
    {
        $this->choices[ 'en' ] = new DefaultChoice();
        $this->choices[ 'ru' ] = new RuChoice();

        parent::__construct();
    }


    protected function validation(array &$context = []) : bool
    {
        foreach ( $this->languages as $array ) {
            [
                $locale,
                $script,
                $titleEnglish,
                $titleNative,
            ] = $array;

            if (null === Lib::parse()->string_not_empty($locale)) {
                throw new LogicException(
                    [
                        'The `locale` should be non-empty string',
                        $locale,
                    ]
                );
            }

            if (null === Lib::parse()->string_not_empty($script)) {
                throw new LogicException(
                    [
                        'The `script` should be non-empty string',
                        $locale,
                    ]
                );
            }

            if (null === Lib::parse()->string_not_empty($titleEnglish)) {
                throw new LogicException(
                    [
                        'The `titleEnglish` should be non-empty string',
                        $locale,
                    ]
                );
            }

            if (null === Lib::parse()->string_not_empty($titleNative)) {
                throw new LogicException(
                    [
                        'The `titleNative` should be non-empty string',
                        $locale,
                    ]
                );
            }
        }

        $phpLocalesIndex = [
            'LC_CTYPE'    => true,
            'LC_NUMERIC'  => true,
            'LC_TIME'     => true,
            'LC_COLLATE'  => true,
            'LC_MONETARY' => true,
            'LC_ALL'      => true,
            'LC_MESSAGES' => true,
        ];
        foreach ( $phpLocalesIndex as $constName => $bool ) {
            unset($phpLocalesIndex[ $constName ]);

            if (defined($constName)) {
                $phpLocalesIndex[ constant($constName) ] = $bool;
            }
        }
        foreach ( $this->phpLocales as $lang => $arr ) {
            if (! isset($this->languages[ $lang ])) {
                throw new LogicException(
                    [
                        'The `lang` should be existing language',
                        $lang,
                        $this,
                    ]
                );
            }

            foreach ( $arr as $phpLocale => $phpLocaleArr ) {
                if (! isset($phpLocalesIndex[ $phpLocale ])) {
                    throw new LogicException(
                        [
                            'The `phpLocale` should be existing PHP locale constant',
                            $phpLocale,
                            $this,
                        ]
                    );
                }

                while ( count($phpLocaleArr) ) {
                    $phpLocale = array_shift($phpLocaleArr);

                    if (null === Lib::parse()->string_not_empty($phpLocale)) {
                        throw new LogicException(
                            [
                                'The `phpLocale` should be non-empty string',
                                $phpLocale,
                                $this,
                            ]
                        );
                    }
                }
            }
        }

        foreach ( $this->choices as $lang => $choiceObject ) {
            if (! isset($this->languages[ $lang ])) {
                throw new LogicException(
                    [
                        'The `lang` should be existing language',
                        $lang,
                        $this,
                    ]
                );
            }

            if (! is_a($choiceObject, I18nChoiceInterface::class)) {
                throw new LogicException(
                    [
                        'Each of `choices` should be instance of: ' . I18nChoiceInterface::class,
                        $lang,
                        $this,
                    ]
                );
            }
        }

        if (null !== $this->lang) {
            if (! isset($this->languages[ $this->lang ])) {
                throw new LogicException(
                    [
                        'The `lang` should be existing language',
                        $this->lang,
                        $this,
                    ]
                );
            }
        }

        if (null !== $this->langDefault) {
            if (! isset($this->languages[ $this->langDefault ])) {
                throw new LogicException(
                    [
                        'The `lang_default` should be existing language',
                        $this->langDefault,
                        $this,
                    ]
                );
            }
        }

        if (null !== $this->logger) {
            if (! is_a($this->logger, '\Psr\Log\LoggerInterface')) {
                throw new LogicException(
                    [
                        'The `logger` should be instance of: \Psr\Log\LoggerInterface',
                        $this,
                    ]
                );
            }
        }

        foreach ( $this->loggables as $error => $errorLevel ) {
            if (isset(I18nInterface::E_LIST[ $error ])) {
                throw new LogicException(
                    [
                        'The `logger` should be instance of: \Psr\Log\LoggerInterface',
                        $this,
                    ]
                );
            }
        }

        return true;
    }
}
