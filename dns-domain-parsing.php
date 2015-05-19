<?php

function get_domain($domain, $debug = false)
{
	$original = $domain = strtolower($domain);
	if (filter_var($domain, FILTER_VALIDATE_IP)) { return $domain; }
	$debug ? print('<strong style="color:green">&raquo;</strong> Parsing: '.$original) : false;
	$arr = array_slice(array_filter(explode('.', $domain, 4), function($value){
		return $value !== 'www';
	}), 0); //rebuild array indexes
	if (count($arr) > 2)
	{
		$count = count($arr);
		$_sub = explode('.', $count === 4 ? $arr[3] : $arr[2]);
		$debug ? print(" (parts count: {$count})") : false;
		if (count($_sub) === 2) // two level TLD
		{
			$removed = array_shift($arr);
			if ($count === 4) // got a subdomain acting as a domain
			{
				$removed = array_shift($arr);
			}
			$debug ? print("<br>\n" . '[*] Two level TLD: <strong>' . join('.', $_sub) . '</strong> ') : false;
		}
		elseif (count($_sub) === 1) // one level TLD
		{
			$removed = array_shift($arr); //remove the subdomain
			if (strlen($_sub[0]) === 2 && $count === 3) // TLD domain must be 2 letters
			{
				array_unshift($arr, $removed);
			}
			else
			{
				// non country TLD according to IANA
				$tlds = array(
					'aero',
					'arpa',
					'asia',
					'biz',
					'cat',
					'com',
					'coop',
					'edu',
					'gov',
					'info',
					'jobs',
					'mil',
					'mobi',
					'museum',
					'name',
					'net',
					'org',
					'post',
					'pro',
					'tel',
					'travel',
					'xxx',
				);
				if (count($arr) > 2 && in_array($_sub[0], $tlds) !== false) //special TLD don't have a country
				{
					array_shift($arr);
				}
			}
			$debug ? print("<br>\n" .'[*] One level TLD: <strong>'.join('.', $_sub).'</strong> ') : false;
		}
		else // more than 3 levels, something is wrong
		{
			for ($i = count($_sub); $i > 1; $i--)
			{
				$removed = array_shift($arr);
			}
			$debug ? print("<br>\n" . '[*] Three level TLD: <strong>' . join('.', $_sub) . '</strong> ') : false;
		}
	}
	elseif (count($arr) === 2)
	{
		$arr0 = array_shift($arr);

		if (strpos(join('.', $arr), '.') === false
			&& in_array($arr[0], array('localhost','test','invalid')) === false) // not a reserved domain
		{
			$debug ? print("<br>\n" .'Seems invalid domain: <strong>'.join('.', $arr).'</strong> re-adding: <strong>'.$arr0.'</strong> ') : false;
			// seems invalid domain, restore it
			array_unshift($arr, $arr0);
		}
	}
	$debug ? print("<br>\n".'<strong style="color:gray">&laquo;</strong> Done parsing: <span style="color:red">' . $original . '</span> as <span style="color:blue">'. join('.', $arr) ."</span><br>\n") : false;

	return join('.', $arr);
}

function get_subdomain($input){
	if (preg_match('/^[a-zA-Z\-\.0-9]+$/', $input)){
		$domain = get_domain($input);
		$domstart = strrpos($input, $domain);
		if ($domstart){
			return substr($input, 0, $domstart-1);
		} else {
			return '';
		}
	} else {
		return '';
	}
}

?><?php

//
// BELOW:
// TESTS FOR getDomainParts()
//

$urls = array(
	'www.example.com' => 'example.com',
	'example.com' => 'example.com',
	'example.com.br' => 'example.com.br',
	'www.example.com.br' => 'example.com.br',
	'www.example.gov.br' => 'example.gov.br',
	'localhost' => 'localhost',
	'www.localhost' => 'localhost',
	'subdomain.localhost' => 'localhost',
	'www.subdomain.example.com' => 'example.com',
	'subdomain.example.com' => 'example.com',
	'subdomain.example.com.br' => 'example.com.br',
	'www.subdomain.example.com.br' => 'example.com.br',
	'www.subdomain.example.biz.br' => 'example.biz.br',
	'subdomain.example.biz.br' => 'example.biz.br',
	'subdomain.example.net' => 'example.net',
	'www.subdomain.example.net' => 'example.net',
	'www.subdomain.example.co.kr' => 'example.co.kr',
	'subdomain.example.co.kr' => 'example.co.kr',
	'example.co.kr' => 'example.co.kr',
	'example.jobs' => 'example.jobs',
	'www.example.jobs' => 'example.jobs',
	'subdomain.example.jobs' => 'example.jobs',
	'insane.subdomain.example.jobs' => 'example.jobs',
	'insane.subdomain.example.com.br' => 'example.com.br',
	'www.doubleinsane.subdomain.example.com.br' => 'example.com.br',
	'www.subdomain.example.jobs' => 'example.jobs',
	'test' => 'test',
	'www.test' => 'test',
	'subdomain.test' => 'test',
	'www.detran.sp.gov.br' => 'sp.gov.br',
	'www.mp.sp.gov.br' => 'sp.gov.br',
	'ny.library.museum' => 'library.museum',
	'www.ny.library.museum' => 'library.museum',
	'ny.ny.library.museum' => 'library.museum',
	'www.library.museum' => 'library.museum',
	'info.abril.com.br' => 'abril.com.br',
	'127.0.0.1' => '127.0.0.1',
	'::1' => '::1',
);

$suburls = array(
	'www.example.com' => 'www',
	'example.com' => '',
	'example.com.br' => '',
	'www.example.com.br' => 'www',
	'www.example.gov.br' => 'www',
	'localhost' => '',
	'www.localhost' => 'www',
	'subdomain.localhost' => 'subdomain',
	'www.subdomain.example.com' => 'www.subdomain',
	'subdomain.example.com' => 'subdomain',
	'subdomain.example.com.br' => 'subdomain',
	'www.subdomain.example.com.br' => 'www.subdomain',
	'www.subdomain.example.biz.br' => 'www.subdomain',
	'subdomain.example.biz.br' => 'subdomain',
	'subdomain.example.net' => 'subdomain',
	'www.subdomain.example.net' => 'www.subdomain',
	'www.subdomain.example.co.kr' => 'www.subdomain',
	'subdomain.example.co.kr' => 'subdomain',
	'example.co.kr' => '',
	'example.jobs' => '',
	'www.example.jobs' => 'www',
	'subdomain.example.jobs' => 'subdomain',
	'insane.subdomain.example.jobs' => 'insane.subdomain',
	'insane.subdomain.example.com.br' => 'insane.subdomain',
	'www.doubleinsane.subdomain.example.com.br' => 'www.doubleinsane.subdomain',
	'www.subdomain.example.jobs' => 'www.subdomain',
	'test' => '',
	'www.test' => 'www',
	'subdomain.test' => 'subdomain',
	'www.detran.sp.gov.br' => 'www.detran',
	'www.mp.sp.gov.br' => 'www.mp',
	'ny.library.museum' => 'ny',
	'www.ny.library.museum' => 'www.ny',
	'ny.ny.library.museum' => 'ny.ny',
	'www.library.museum' => 'www',
	'info.abril.com.br' => 'info',
	'127.0.0.1' => '',
	'::1' => '',
);


echo '<h1>Domain Tests</h1>';

foreach ($urls as $goodinput => $goodoutput) {
	$myoutput = get_domain($goodinput);
	if ($myoutput == $goodoutput){
		echo $myoutput.' == '.$goodoutput.'? <b style="color:green;">YES</b><br>';
	} else {
		echo $myoutput.' == '.$goodoutput.'? <b style="color:red;">NOPE</b><br>';
	}
	echo '<br>';
}

echo '<h1>Subdomain Tests</h1>';

foreach ($suburls as $goodinput => $goodoutput) {
	$myoutput = get_subdomain($goodinput);
	if ($myoutput == $goodoutput){
		echo $myoutput.' == '.$goodoutput.'? <b style="color:green;">YES</b><br>';
	} else {
		echo $myoutput.' == '.$goodoutput.'? <b style="color:red;">NOPE</b><br>';
	}
	echo '<br>';
}

?>
<style>body{background: black;color: white;}</style>
