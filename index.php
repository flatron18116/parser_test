<?php 
//config
$data = "";

if($_POST['mode'] === "analyze") {
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
	
	//storageのconfig情報を取得
	include_once('config/'.$g_storage.'.php');
	// ログインクッキーを取得
	$iCookies = iLogin($id);
	
	//proc
	//URL数分ループ
	foreach ($g_url as $d_url ) {
		$d_data = array();
		$d_html;
		$d_host;//host名
		$d_title;//title
	
		//host名取得
		$tmp_url = parse_url($d_url);
		$d_host = $tmp_url['host'];//host名でパースルールを振り分ける
	
		//subumit用のparamを取得
		$param = iGetParam($d_url, $iCookies);
		//DLurlを取得
		$d_data[] = iGetUrl($d_url, $iCookies, $param);
	
		//結果表示配列にアサイン
		$data[] = $d_data;
	}
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>DLtool3</title>
	<style>
	</style>
</head>
<body>
	<div id="header">
		<div class="row">
			<div id="logo"></div>
		</div>
	</div>
	<div class="container">
	<?php if($data !== "") {?>
	<div class="result"><pre>
	<?php 
	foreach($data as $row) {
		foreach($row as $col) {
			echo $col . '<br />';
		}
	}?>
	</pre></div>
	<?php }?>
		<div class="row">
<form action="index.php" method="post"> 
<select name="storage">
	<option>ryushare</option>
	<option>rapidgator</option>
</select>
<textarea name="urllist" style="width: 900px; height: 400px;">
</textarea>
<input type="hidden" name="mode" value="analyze" />
<input type="submit" name="submit_btn" value="Analyze Now" />
</form>
		</div>
		<footer>
			<p>
				ABC lisence
			</p>
		</footer>
	</div>
</body>
</html>
<?php 

/**
 * ログイン
 *
 * @param array $data ログインデータ
 * @return array $cookies クッキー
 */

function iLogin($data)
{
	$data = http_build_query($data + array('op' => 'login','redirect' => 'http://ryushare.com/'), '', '&');
	$context = stream_context_create(array(
			'http' => array(
					'method'  => 'POST',
					'header'  => implode("\r\n", array(
							'Content-Type: application/x-www-form-urlencoded',
							'Content-Length: ' . strlen($data)
					)),
					'content' => $data
			)
	));
	file_get_contents('http://ryushare.com/', false, $context);
	$cookies = array();
	foreach ($http_response_header as $r) {
		if (strpos($r, 'Set-Cookie') === false) {
			continue;
		}
		$c = explode(' ', $r);
		$c = str_replace(';', '', $c[1]);
		$cookies[] = $c;
	}
	return $cookies;
}

function iGetParam($url, $cookies)
{
	//$data = http_build_query($data, '', '&');
	$context = stream_context_create(array(
			'http' => array(
					'method'  => 'GET',
					'header'  => implode("\r\n", array(
							'Cookie: ' . implode('; ', $cookies)
					))
			)
	));
	$html = file_get_html($url, false, $context);
	return getParamHtml($html);
}

function iGetUrl($url, $cookies, $data)
{
	$data = http_build_query($data, '', '&');
	$context = stream_context_create(array(
			'http' => array(
					'method'  => 'POST',
					'header'  => implode("\r\n", array(
							'Cookie: ' . implode('; ', $cookies),
							'Content-Type: application/x-www-form-urlencoded',
							'Content-Length: ' . strlen($data)
					)),
					'content' => $data
			)
	));
	$html = file_get_html($url, false, $context);

	return getUrlHtml($html);
}

//paramを抽出して返す
function getParamHtml(&$html,$g_storage = null){
	$param = array();

	//if(preg_match('/newidols\.net/', $host)){//http://newidols.net/
		foreach($html->find('input[type="hidden"]') as $e) {
			$param[$e->name] = $e->value;
		}
	//} else if(preg_match('/idolex\.net/', $host)) {//http://idolex.net/
		//poc

	//} else if(preg_match('/javbest\.net/', $host)) {//http://javbest.net/
		//poc

	//}
	//var_dump($param);
	$html->clear();
	return $param;
}

//DLurlを抽出して返す
function getUrlHtml(&$html,$g_storage = null){
	$url = "";
	//if(preg_match('/newidols\.net/', $host)){//http://newidols.net/
	foreach($html->find('#content a') as $e) {
		$url = $e->href;
	}
	//} else if(preg_match('/idolex\.net/', $host)) {//http://idolex.net/
		//poc

	//} else if(preg_match('/javbest\.net/', $host)) {//http://javbest.net/
	//poc

	//}
	$html->clear();
	return $url;
}
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