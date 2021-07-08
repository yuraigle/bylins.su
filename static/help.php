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
    $qu = mb_strtoupper($q);

    $n = 0;
    if (preg_match('/^(\d+)\.(.+)$/', $qu, $matches)) {
        $n = $matches[1];
        $qu = $matches[2];
    }

    // read all files TODO: caching?
    foreach ($allFiles as $f) {
        $ss = file_get_contents('./help_files/' . $f . '.hlp');
        $ss = mb_convert_encoding($ss, 'UTF-8', 'KOI8-R');
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
}

function renderContent($str) {
    $resultStr = '';
    $seeAlso = '';

    foreach (preg_split('/\r?\n/', $str) as $line) {
        $line = preg_replace('/^\+-{10}.*$/', '', $line);
        $line = preg_replace('/^\s*\|\s*/', '', $line);
        $line = preg_replace('/\s*\|\s*$/', '', $line);

        $line = preg_replace_callback('/(\$COLOR|&)([A-Za-z])/', function ($m) {
            $c = strtolower($m[2]);
            return "</span><span class='clr-$c'>";
        }, $line);

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
            $qq = urlencode(mb_strtolower($ww));
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
        .clr-w {
            color: #000000;
            font-weight: bold;
        }

        .clr-g {
            color: #00C000;
            font-weight: bold;
        }

        .clr-c {
            color: #00C0C0;
            font-weight: bold;
        }

        .clr-r {
            color: #C00000;
            font-weight: bold;
        }
    </style>
</head>

<body>
<?php require_once './header_inc.php'; ?>
<div class="content">
    <article>
        <p class='text-center'><strong><i class="letter letter-s">С</i>правка</strong></p>

        <form method='get' action='/help.php' enctype="application/x-www-form-urlencoded" class='mt-4 mb-4 text-center'>
            <label for="search_q"></label>
            <input type="text" id="search_q" name="q" value="<?php echo $q ?>"/>
            <button type="submit">Искать</button>
        </form>

        <?php if (!$q) : ?>
            <p class="text-center">Введите команду или первые символы для поиска</p>
        <?php elseif ($cntFound === 0) : ?>
            <p class="text-center">Ничего не найдено</p>
        <?php elseif ($cntFound === 1) : ?>
            <strong><?= $shownTitle ?></strong><br/>
            <?= renderContent($shownContent) ?>
        <?php elseif ($cntFound > 1) : ?>
            <?php $i = 0; ?>
            <?php foreach ($foundWordsExact as $w) : ?>
                <a href="/help.php?q=<?= ++$i . "." . urlencode(mb_strtolower($w)) ?>"><?= $w ?></a><br/>
            <?php endforeach; ?>
            <?php foreach ($foundWords as $w) : ?>
                <a href="/help.php?q=<?= urlencode(mb_strtolower($w)) ?>"><?= $w ?></a><br/>
            <?php endforeach; ?>
        <?php endif; ?>
    </article>

    <?php require_once './footer_inc.php'; ?>
</div>
</body>
</html>
