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

/**
 * ページを取得
 *
 * @param string $url 取得URL
 * @param array $cookies クッキー
 * @return string $content ページの内容
 */
function iGetPage($url, $cookies, $data)
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
	return file_get_contents($url, false, $context);
}

// ログインデータ
$data = array(
		'login'    => '3776481',
		'password' => 'yr2y0n5j0e'
);
// ログインクッキーを取得
$cookies = iLogin($data);

//URLはリストにしてforeach
$url = 'http://ryushare.com/2f6279feda2c/DVDriparearaw.comh_m_may.part1.rar';

// 実際はcontntsからhidden要素を集めてきてPOST用のvalueを生成(一回とれば使い回し出来る？idのみ可変か？)
$data = array(
		'down_direct' => 	1,
		'id' => 	'2f6279feda2c',
		'method_free'  => '',
		'method_premium'	 => 1,
		'op'	 => 'download2',
		'rand'	 => 'adhcfyahxijfrir3fswr2er5qwmb7itk6e77shi',
		'referer' => 	'http://ryushare.com/login.python',
);
$page1 = iGetPage($url, $cookies, $data);

//このページからDLリンクを抽出する
echo $page1;
