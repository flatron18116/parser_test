<?php
require_once("Benchmark/Timer.php");

$timer = new Benchmark_Timer;
$timer->start();
$timer->setMarker(開始);

//config
include_once('lib/simplehtmldom_1_5/simple_html_dom.php');
$data = array();
$g_url = array();
$g_storage;

//input
$g_storage = $_POST['storage'];
//改行文字で分割
$g_url = preg_split('/(\r\n|\r|\n)/', $_POST['urllist']);
//空行削除
$g_url = array_merge(array_diff($g_url,array("")));

//proc
//URL数分ループ
foreach ($g_url as $d_url ) {
	$d_data = array();
	$d_html;
	$d_host;//host名
	$d_title;//title
	$timer->setMarker(a);
	//host名取得
	$tmp_url = parse_url($d_url);
	$d_host = $tmp_url['host'];//host名でパースルールを振り分ける
	$timer->setMarker(b);
	//解析用のhtmlを取得
	$d_html   = file_get_html($d_url);
	$timer->setMarker(c);
	//src
	$d_data[] = '<a href="'.$d_url.'">src</a>';
	//title
	$d_data[] = makeTitleString($d_host,$d_html);
	//link
	$d_data[] = makeLinkHtml($d_host,$d_html,$g_storage);

	//結果表示配列にアサイン
	$data[] = $d_data;
	$timer->setMarker(d);
	//コンテキストの解放
	$d_html->clear();
}
$timer->stop();
$timer->display();

echo $time2 . '<br />';
//ページタイトルを整形して返す
function makeTitleString($host,&$html){
	//config
	$prefix = "";
	$rule = array();

	//input
	$title = $html->find('title',0)->innertext;//title

	if(preg_match('/newidols\.net/', $host)){//http://newidols.net/
		//rule
		$rule[] = '/^(\[.+?\]) (.+) \– (.+)/u';
		$rule[]  = '/^(\[.+?\]) (.+)/u';//名前とタイトルの区切りがない場合

		//prefix判定
		$prefix = '[IV]';
		//titleの整形
		if(preg_match_all($rule[0], $title, $m)) {
			$title = '['.$m[2][0] . '] ' . $m[3][0] . ' ' . $m[1][0];
		} else if(preg_match_all($rule[1], $title, $m)) {
			//nameの要素がない場合
			$title = $m[2][0] . ' ' . $m[1][0];
		}//正規表現にマッチしない場合はそのまま出力
	} else if(preg_match('/idolex\.net/', $host)) {//http://idolex.net/
		//poc

	} else if(preg_match('/javbest\.net/', $host)) {//http://javbest.net/
		//poc

	}
	return $prefix.$title;
}

//リンクを抽出して返す
function makeLinkHtml($host,&$html,$g_storage){
	$link = "";
	if(preg_match('/newidols\.net/', $host)){//http://newidols.net/
		foreach($html->find('div.entry-content blockquote pre a[href*="'.$g_storage.'"]') as $e) {
			$link .= '<a href="' . $e->href . '">' . $e->href . '</a>' . '<br />';
		}
	} else if(preg_match('/idolex\.net/', $host)) {//http://idolex.net/
		//poc

	} else if(preg_match('/javbest\.net/', $host)) {//http://javbest.net/
		//poc

	}

	return $link;
}
?>