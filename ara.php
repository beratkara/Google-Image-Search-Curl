<?php 
header('Access-Control-Allow-Origin: http://beratkara.com/'); 
ini_set('xdebug.max_nesting_level', 999999);
set_time_limit(0);
require_once "../wp-config.php";

function hedefConnect($hedef){$bot = curl_init();
	$hc = "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.86 Safari/537.36'";
	curl_setopt($bot,CURLOPT_FOLLOWLOCATION,true);
	curl_setopt($bot, CURLOPT_URL, $hedef);
	curl_setopt($bot, CURLOPT_USERAGENT, $hc);
	curl_setopt($bot, CURLOPT_RETURNTRANSFER, 1);
	$hedef = curl_exec($bot);
	curl_close($bot);
	return $hedef;
}

$Sonuclar = array();

$width;
$height;
$counter = 0;
$total = 20;

function googleAra($sorgu)
{
	global $width;
	global $height;
	$re = '/"ou":"(.*?)","ow"/mi';
	$url = "https://www.google.com.tr/images?q=".urlencode($sorgu)."&tbs=isz:ex,iszw:".$width.",iszh:".$height."";
	$str = hedefConnect($url);
	preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);
	return $matches;
}

function yandexAra($sorgu)
{
	global $width;
	global $height;
	$re = '/"ou":"(.*?)","ow"/mi';
	$url = "https://www.google.com.tr/images?q=".urlencode($sorgu)."&tbs=isz:ex,iszw:".$width.",iszh:".$height."";
	$str = hedefConnect($url);
	preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);
	return $matches;
	/*$Say = 0;
	$Son = count($matches) >= 20 ? 20 : count($matches);
	while ($Say < $Son) {
		$RND = rand(0,count($matches)-1);
		if (isset($Sonuclar["snc_".$RND])){
			continue;
		}
		$Sonuclar["snc_".$RND] = $matches[$RND][1];
		$Say++;
	}*/

	/*foreach ($matches as $match) {
		echo $match[1]."<br>";
	}*/
}

function dosyaOku($txt)
{
	$file = fopen($txt,"r");
	$txtinfo = array();
	while(! feof($file))
		array_push($txtinfo,fgets($file));

	fclose($file);
	return $txtinfo;
}

function wordpressEkle($baslik,$type)
{
	global $counter;
	global $total;
	
	$my_post = array();
	$my_post['post_title'] = $baslik;
	$my_post['post_content'] = "";
	
	if($type == "google")
		$matches = googleAra($baslik);
	elseif($type == "yandex")
		$matches = yandexAra($baslik);
	
	while ($counter<$total){
		flush();
		ob_get_contents();
		if (!isset($matches[$counter][1])){
			break;
		}
		echo '';
		$my_post['post_content'] .= "<img src=\"".$matches[$counter][1]."\" title=\"".$baslik."\"><br/>";
		$counter++;
	}
	$total += 20;
	$my_post['post_status'] = 'publish'; 
	$my_post['post_type'] = 'post'; 
	$kategoriler = get_categories();
	$my_post['post_category'] = array($kategoriler[rand(0,count($kategoriler)-1)]);
	$id = wp_insert_post( $my_post );
	echo '<a href="'.get_permalink($id).'">'.$baslik.'</a><br>';
}

$basliklar = dosyaOku("basliklar.txt");
$extra = dosyaOku("extra.txt");
$arancaksite = dosyaOku("arancaksite.txt");

if(count($basliklar) > 0)
{
	if(count($extra) > 0) //extra.txt içeriği örnek -> 600x900
	{
		$extras = explode("x",$extra[0]);
		$width = $extras[0];
		$height = $extras[1];
	}
	
	if(count($arancaksite) != 1) //arancaksite.txt içeriği boşsa dur -> google veya yandex yazılacak sadece
		return;
	
	for($i = 0; $i < count($basliklar); $i++)
	{
		$counter = 0;
		$total = 20;
		flush();
		ob_get_contents();
		$baslik = $basliklar[$i];
		for ($j=0; $j < 5; $j++) {
			flush();
			ob_get_contents();
			wordpressEkle($baslik,$arancaksite[0]);
		}
	}
}
?>