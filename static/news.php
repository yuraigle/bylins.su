<?php

function get10($file) {
    $ss = file_get_contents($file);
//    $ss = mb_convert_encoding($ss, 'UTF-8', 'KOI8-R');
    $lines = preg_split('/\r?\n/', $ss);

    $news = array();
    $curr = -1;
    foreach ($lines as $line) {
        if (preg_match("|^Message: (\d+)|", $line, $m)) {
            $curr = $m[1];
        } elseif ($curr >= 0) {
            $news[$curr] .= $line . PHP_EOL;
        }
    }

    ksort($news);
    return array_slice($news, 0, 10);
}

$news = get10('./help_files/_news.board');
$notices = get10('./help_files/_notice.board');
$all = array_merge($news, $notices);

for ($i = 0; $i < count($all); $i++) {
    $arr = preg_split("/\r?\n/", $all[$i]);
    if (preg_match("|^(.+)\s+\d+\s+\d+\s+(\d+)\s+\d+$|", $arr[0], $m)) {
        $tt = strftime("%Y-%m-%d %H:%M", $m[2]);
        $who = $m[1];
        $arr[0] = "$tt <strong>$who</strong>";
    }
    if (preg_match("|^(.+)~$|", $arr[1], $m)) {
        $what = $m[1];
        $arr[0] .= " :: <u><i>$what</i></u>";
        $arr[1] = "~";
    }

    $str = "";
    foreach ($arr as $line) {
        if ($line == '~') { continue; }

        $line = preg_replace("|[~_]|", "", $line);
        $line = preg_replace("|&[A-Za-z]|", "", $line);
        if (preg_match("|^\s*$|", $line)) { continue; }
        $line = preg_replace("|^\s{2,4}|", "&nbsp;&nbsp;&nbsp;", $line);
        $line = preg_replace("|(https?://)([^\s]+)|", "<a href='$1$2'>$1$2</a>", $line);
        $str .= $line . "<br/>";
    }

    $all[$i] = $str;
}

arsort($all);
$all = array_splice($all, 0, 10);

header('Content-type: application/json');
echo json_encode($all);
