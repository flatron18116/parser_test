<?php
$g_title = "[ZEUSB-001] 水城るな プリンセスオールスター Blu-ray";

$g_prefix = '[IV]';
$g_rule = array();
$g_rule[] = '/^(\[.+?\]) (.+) \– (.+)/u';
$g_rule[]  = '/^(\[.+?\]) (.+)/u';//名前とタイトルの区切りがない場合

echo $g_title . '<br />';

//preg_match_all('/[一-龠]+|[ぁ-ん]+|[ァ-ヴー]+|[a-zA-Z0-9]+|[ａ-ｚＡ-Ｚ０-９]+/u', $g_title, $m);

if(preg_match_all($g_rule[0], $g_title, $m)) {
	$g_title = '['.$m[2][0] . '] ' . $m[3][0] . ' ' . $m[1][0];
} else if(preg_match_all($g_rule[1], $g_title, $m)) {
	//nameの要素がない場合
	$g_title = $m[2][0] . ' ' . $m[1][0];
}//正規表現にマッチしない場合はそのまま出力
echo $g_prefix.$g_title . '<br />';
?>