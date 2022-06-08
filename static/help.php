<?php
$allFiles = array(
    'clan', 'commands', 'feats', 'im', 'info', 'pk', 'rules',
    'skills_spells_list', 'skills', 'slang', 'spells', 'wizhelp', 'zones'
);
$allTopics = array(); // TITLE => CONTENT
$foundWords = $foundWordsExact = $exactMatchesStr = $exactMatchesTopic = array();

$cntFound = 0;
$shownTitle = '';
$shownContent = '';

$q = isset($_GET['q']) ? $_GET['q'] : '';
$q = htmlspecialchars($q);

if ($q) {
    $qu = mb_strtoupper($q, 'UTF-8');

    $n = 0;
    if (preg_match('/^(\d+)\.(.+)$/', $qu, $matches)) {
        $n = $matches[1];
        $qu = $matches[2];
    }

    // read all files TODO: caching?
    foreach ($allFiles as $f) {
        $ss = file_get_contents('./help_files/' . $f . '.hlp');
//        $ss = mb_convert_encoding($ss, 'UTF-8', 'KOI8-R');
        $lines = preg_split('/\r?\n/', $ss);
        $subj = '';

        $i = 0;
        foreach ($lines as $line) {
            if ($line === '$') {
                continue;
            } elseif (substr($line, 0, 1) === '#') {
                $subj = '';
            } elseif (!$subj) {
                $subj = $line;
            } else {
                $allTopics[$subj] .= $line . PHP_EOL;
            }
        }
    }

    foreach ($allTopics as $topic => $topicStr) {
        $matches = false;
        $topicWords = preg_split('/\s+/', $topic);
        foreach ($topicWords as $w) {
            if ("$w!" === $qu || $w === $qu) {
                array_push($foundWordsExact, $w);
                array_push($exactMatchesStr, $topicStr);
                array_push($exactMatchesTopic, $topic);
                $matches = true;
                break;
            } elseif (strpos($w, $qu) === 0) {
                array_push($foundWords, $w);
                $matches = true;
                break;
            }
        }

        if ($matches) {
            $shownTitle = $topic;
            $shownContent = $topicStr;
        }
    }

    $cntFound = count($foundWords) + count($foundWordsExact);

    if ($n > 0 && count($foundWordsExact) >= $n) {
        $cntFound = 1;
        $shownTitle = $exactMatchesTopic[$n - 1];
        $shownContent = $exactMatchesStr[$n - 1];
    }

    if ($cntFound === 0) {
        header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found", true, 404);
    }
}

function renderContent($str) {
    $resultStr = '';
    $seeAlso = '';

    foreach (preg_split('/\r?\n/', $str) as $line) {
        $line = preg_replace('/^\+-{10}.*$/', '', $line);
        $line = preg_replace('/^\s*\|\s*/', '', $line);
        $line = preg_replace('/\s*\|\s*$/', '', $line);
        $line = preg_replace('/(\$COLOR|&)([A-Za-z])/', "</span><span class='clr-$2'>", $line);
        $line = preg_replace('/(\$COLOR|&)COLOR[A-Za-z]/', '', $line);

        if (preg_match('/^См\. также ?:?(.+)/', $line)) {
            $seeAlso = $line;
        } elseif ($seeAlso && preg_match('/^\s+/', $line)) {
            $seeAlso .= $line;
        } else {
            $resultStr .= '<span>' . $line . '</span><br/>' . PHP_EOL;
        }
    }

    if ($seeAlso && preg_match('/^См. также ?:?(.+)/', $seeAlso, $matches)) {
        $line = 'См. также: ';
        $also = preg_replace('/<\/?span[^>]*>/', '', $matches[1]);
        $also = preg_replace('/[,.\s]/', ' ', $also);
        foreach (preg_split('/\s+/', $also) as $ww) {
            $qq = urlencode(mb_strtolower($ww, 'UTF-8'));
            $line .= " <a href='/help.php?q=$qq'>$ww</a> ";
        }
        $resultStr .= '<span>' . $line . '</span><br/>' . PHP_EOL;
    }

    return $resultStr;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="/css/style.css">
    <link type="image/x-icon" rel="shortcut icon" href="/favicon.ico">
    <link type="image/png" sizes="16x16" rel="icon" href="/favicon-16.png">
    <link type="image/png" sizes="32x32" rel="icon" href="/favicon-32.png">
    <link type="image/png" sizes="120x120" rel="icon" href="/favicon-120.png">
    <title>Справка | МПМ Былины - бесплатная текстовая игра онлайн по русским сказкам</title>
    <meta name="description" content="МАД/МУД(MUD) Былины - бесплатная онлайновая текстовая игра по русским сказкам">
    <meta name="keywords" content="mud, мад, муд, мпм, былины, онлайн, текстовая игра, бесплатная, русские сказки">

    <style>
        .clr-w { color: #000000; font-weight: bold; }
        .clr-g { color: #00C000; font-weight: bold; }
        .clr-c { color: #00C0C0; font-weight: bold; }
        .clr-r { color: #C00000; font-weight: bold; }
        .clr-W { color: #000000; font-weight: bold; }
        .clr-G { color: #00C000; font-weight: bold; }
        .clr-C { color: #00C0C0; font-weight: bold; }
        .clr-R { color: #C00000; font-weight: bold; }

        .col-idx {
            display: inline-block;
            vertical-align: top;
            width: 245px;
            margin-right: 10px;
        }

        @media only screen and (max-width: 760px) {
            .col-idx {
                width: 100%;
            }
        }
    </style>
</head>

<body>
<?php require_once './header_inc.php'; ?>
<div class="content">
    <article>
        <p class='text-center'>
            <strong><a href="/help.php"><i class="letter letter-s">С</i>правка</a></strong>
        </p>

        <form method='get' action='/help.php' enctype="application/x-www-form-urlencoded" class='mt-4 mb-4 text-center'>
            <label for="search_q"></label>
            <input type="text" id="search_q" name="q" value="<?php echo $q ?>"/>
            <button type="submit">Искать</button>
        </form>

        <?php if (!$q) : ?>
            <p class="text-center">Введите команду или первые символы для поиска</p>

        <div>
            <div>
                <div class="col-idx">
                    <h4>ПЕРЕМЕЩЕНИЯ И ИНФОРМАЦИЯ</h4>
                    <a href='?q=север!'>север</a> <a href='?q=юг'>юг</a> <a href='?q=восток'>восток</a>
                    <a href='?q=запад!'>запад</a> <a href='?q=вверх'>вверх</a> <a href='?q=вниз'>вниз</a>
                    <a href='?q=смотреть'>смотреть</a> <a href='?q=выходы'>выходы</a> <a href='?q=войти'>войти</a>
                    <a href='?q=спать'>спать</a> <a href='?q=отдых'>отдыхать</a> <a href='?q=сесть'>сесть</a>
                    <a href='?q=встать'>встать</a> <a href='?q=открыть'>открыть</a> <a href='?q=закрыть'>закрыть</a>
                    <a href='?q=отпереть'>отпереть</a> <a href='?q=запереть'>запереть</a>
                </div>

                <div class="col-idx">
                    <h4>ОБЩЕНИЕ</h4>
                    <a href='?q=говорить'>говорить</a> <a href='?q=сказать'>сказать</a>
                    <a href='?q=спросить'>спросить</a> <a href='?q=шептать'>шептать</a> <a href='?q=болтать'>болтать</a>
                    <a href='?q=кричать'>кричать</a> <a href='?q=орать'>орать</a> <a href='?q=гговорить'>гговорить</a>
                    <a href='?q=состояние'>состояние</a> <a href='?q=писать'>писать</a> <a href='?q=читать'>читать</a>
                    <a href='?q=почта'>почта</a>
                </div>
            </div>

            <div>
                <div class="col-idx">
                    <h4>ПРЕДМЕТЫ</h4>
                    <a href='?q=взять'>взять</a> <a href='?q=бросить'>бросить</a> <a href='?q=положить'>положить</a>
                    <a href='?q=надеть'>надеть</a> <a href='?q=снять!'>снять</a> <a href='?q=убрать'>убрать</a>
                    <a href='?q=держать!'>держать</a> <a href='?q=вооружиться'>вооружиться</a>
                    <a href='?q=есть'>есть</a> <a href='?q=пить'>пить</a> <a href='?q=наполнить'>наполнить</a>
                    <a href='?q=лить'>лить</a>
                </div>

                <div class="col-idx">
                    <h4>ИНФОРМАЦИЯ</h4>
                    <a href='?q=очки'>очки</a> <a href='?q=помощь'>помощь</a> <a href='?q=инфо'>инфо</a>
                    <a href='?q=время'>время</a> <a href='?q=погода'>погода</a> <a href='?q=кто!'>кто</a>
                    <a href='?q=уровни'>уровни</a> <a href='?q=оценить'>оценить</a> <a href='?q=смотреть'>смотреть</a>
                    <a href='?q=осмотреть'>осмотреть</a> <a href='?q=использованиесправки'>использованиесправки</a>
                    <a href='?q=дружины'>дружины</a>
                </div>
            </div>

            <div>
                <div class="col-idx">
                    <h4>БИТВА</h4>
                    <a href='?q=убить'>убить</a> <a href='?q=атаковать'>атаковать</a> <a href='?q=спасти'>спасти</a>
                    <a href='?q=бежать'>бежать</a> <a href='?q=пнуть'>пнуть</a> <a href='?q=сбить'>сбить</a>
                    <a href='?q=обезоружить'>обезоружить</a> <a href='?q=заколоть'>заколоть</a>
                    <a href='?q=колдовать'>колдовать</a> <a href='?q=уклониться'>уклониться</a>
                    <a href='?q=парировать'>парировать</a> <a href='?q=блокироватьщитом'>блокировать</a>
                    <a href='?q=перехватить'>перехватить</a> <a href='?q=прикрыть'>прикрыть</a>
                    <a href='?q=подножка'>подножка</a>
                </div>

                <div class="col-idx">
                    <h4>ОБЩИЕ</h4>
                    <a href='?q=1.%21'>!</a> <a href='?q=эмоция'>эмоция</a> <a href='?q=ошибка'>ошибка</a>
                    <a href='?q=опечатка'>опечатка</a> <a href='?q=режим!'>режим</a> <a href='?q=статус'>статус</a>
                    <a href='?q=социалы'>социалы</a> <a href='?q=команды'>команды</a> <a href='?q=слава'>слава</a>
                </div>
            </div>
        </div>
        <?php elseif ($cntFound === 0) : ?>
            <p class="text-center">Ничего не найдено</p>
        <?php elseif ($cntFound === 1) : ?>
            <strong><?= $shownTitle ?></strong><br/>
            <?= renderContent($shownContent) ?>
        <?php elseif ($cntFound > 1) : ?>
            <?php $i = 0; ?>
            <?php foreach ($foundWordsExact as $w) : ?>
                <a href="/help.php?q=<?= ++$i . "." . urlencode(mb_strtolower($w, 'UTF-8')) ?>"><?= $w ?></a><br/>
            <?php endforeach; ?>
            <?php foreach ($foundWords as $w) : ?>
                <a href="/help.php?q=<?= urlencode(mb_strtolower($w, 'UTF-8')) ?>"><?= $w ?></a><br/>
            <?php endforeach; ?>
        <?php endif; ?>
    </article>

    <?php require_once './footer_inc.php'; ?>
</div>
</body>
</html>
