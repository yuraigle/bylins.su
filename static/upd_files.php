<?php

$gh_base = 'https://raw.githubusercontent.com/bylins/mud/master/';

$files = array(
    'lib/text/help/clan.hlp' => './help_files/clan.hlp',
    'lib/text/help/commands.hlp' => './help_files/commands.hlp',
    'lib/text/help/feats.hlp' => './help_files/feats.hlp',
    'lib/text/help/im.hlp' => './help_files/im.hlp',
    'lib/text/help/info.hlp' => './help_files/info.hlp',
    'lib/text/help/pk.hlp' => './help_files/pk.hlp',
    'lib/text/help/rules.hlp' => './help_files/rules.hlp',
    'lib/text/help/skills.hlp' => './help_files/skills.hlp',
    'lib/text/help/skills_spells_list.hlp' => './help_files/skills_spells_list.hlp',
    'lib/text/help/slang.hlp' => './help_files/slang.hlp',
    'lib/text/help/socials.hlp' => './help_files/socials.hlp',
    'lib/text/help/spells.hlp' => './help_files/spells.hlp',
    'lib/text/help/wizhelp.hlp' => './help_files/wizhelp.hlp',
    'lib/text/help/zones.hlp' => './help_files/zones.hlp',
    'lib/etc/board/news.board' => './help_files/_news.board',
    'lib/etc/board/notice.board' => './help_files/_notice.board',
);

foreach ($files as $remote => $local) {
    $txt = file_get_contents($gh_base . $remote, true);
    if ($txt === false) {
        echo "fail: $remote <br/>" . PHP_EOL;
    } else {
        file_put_contents('./' . $local, $txt);
        echo "ok: $remote <br/>" . PHP_EOL;
    }
}

echo "all done";
