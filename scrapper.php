<?php
//https://retropie.org.uk/about/systems/
$allowed_extension["3do"] = array("iso");
$allowed_extension["amiga"] = array("adf");
$allowed_extension["amstradcpc"] = array("dsk", "cpc");
$allowed_extension["atari2600"] = array("a26", "bin", "rom");
$allowed_extension["atari5200"] = array("a52", "bin", "bas", "xex");
$allowed_extension["atari7800"] = array("a78", "bin");
$allowed_extension["psx"] = array("cue", "img", "ccd");
$allowed_extension["nes"] = array("nes");
$allowed_extension["snes"] = array("smc", "zip", "sfc");
$allowed_extension["sega32x"] = array("bin", "x32", "smd", "md");
$allowed_extension["scummvm"] = array("exe");
$allowed_extension["gb"] = array(".gb");
$allowed_extension["gba"] = array("gba");
$allowed_extension["gbc"] = array("gbc");
$allowed_extension["mastersystem"] = array("sms");
$allowed_extension["gamegear"] = array("gg");
$allowed_extension["megadrive"] = array("smd", "bin", "md", "iso");
$allowed_extension["genesis"] = array("smd", "bin", "md", "iso");
$allowed_extension["MAME"] = array("zip");

function getURL($url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,2);
	curl_setopt($ch, CURLOPT_TIMEOUT, 2);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'authority: www.google.com',
			'cache-control: max-age=0',
			'upgrade-insecure-requests: 1',
			'user-agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.88 Safari/537.36',
			'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
			'sec-fetch-site: same-origin',
			'sec-fetch-mode: navigate',
			'sec-fetch-user: ?1',
			'sec-fetch-dest: document'
		));
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$output = curl_exec($ch);
	curl_close($ch);
	return $output;
}

function getCoverURL($search_keyword, $game, $emulator_dir) {
	$search_keyword=str_replace(' ','+',$search_keyword);
	$url = "https://www.google.com/search?q=".$search_keyword."&tbm=isch&oq=mort&gs_lcp=CgNpbWcQARgAMgQIIxAnMgIIADICCAAyAggAMgIIADICCAAyAggAMgIIADICCAAyAggAUOWiAVjWqAFgh7ABaABwAHgAgAFRiAHpAZIBATSYAQCgAQGqAQtnd3Mtd2l6LWltZ8ABAQ&sclient=img&ei=JjLwX-bYGsrpkgXW74-YCw&bih=800&biw=600&hl=en";

	$output = getURL($url);
	$result_image_source = explode('.jpg',$output); //like i said, pragmatic

	foreach ($result_image_source as $s) {
		$data = explode('http', $s);
		$imgURL = "http".$data[count($data) - 1].".jpg";

		//Download the image anyways cause getimagesize coudlnt handle ssl on my system
		$data = getURL($imgURL);
		$img_tmp = $emulator_dir."/tmp.png";
		file_put_contents($img_tmp, $data);
		$size = @getimagesize($img_tmp);

		if ($size[0] > 100) {
			$img = $emulator_dir."/".$game.".png";
			rename($img_tmp, $img);
			echo chr(9)."Found cover with size:".$size[0]."x".$size[1].chr(10);
			return 1;
			break;
		}  else {
			echo "*";
		}
	}
	return 0;
}

function listFolderFiles($rom_dir){
	$ffs = scandir($rom_dir);
	unset($ffs[array_search('.', $ffs, true)]);
	unset($ffs[array_search('..', $ffs, true)]);
	unset($ffs[array_search('.DS_Store', $ffs, true)]);
	return $ffs;
}

$rom_dirs["dir_images"] = "downloaded_images";
$rom_dirs["dir_gamelists"] = "gamelists";

foreach($rom_dirs as $rom_directory) {
	if (!is_dir($rom_directory)) {
		mkdir($rom_directory);
	}
}

$rom_dir = "/home/pi/RetroPie/roms";
$emulators = listFolderFiles($rom_dir);

foreach($emulators as $emulator){
	$freshGameList = array();
	$emulatorXML = '<?xml version="1.0"?>
	   	<gameList>';

	$games = listFolderFiles($rom_dir."/".$emulator);
	foreach($games as $game){
		$game_extention = explode(".", $game);
		if (isset($allowed_extension[$emulator])) {
			if (in_array($game_extention[count($game_extention)-1], $allowed_extension[$emulator])) {
				array_push($freshGameList, $game);
			}
		}
	}

	if (count($freshGameList) == 0) {
		continue;
	}

	echo chr(10)."Found ".$emulator." games: ".count($freshGameList).chr(10);

	$i = 0;
	foreach($freshGameList as $game){

		$i++;
		echo "$i / ".count($freshGameList)." :".$emulator." - ".$game;
		if (strlen($game) < 1) {
			continue;
		}

		$emulator_dir = $rom_dirs["dir_images"]."/".$emulator;
		if (!is_dir($emulator_dir)) {
			mkdir($emulator_dir);
		}

		$search =  $emulator." - ".$game." game cover";
		if (getCoverURL($search, $game, $emulator_dir) == 0) {
			continue;
		}

		$emulatorXML .= "<game>
						<path>./$game</path>
						<name>$game</name>
						<image>~/.emulationstation/downloaded_images/$emulator/".$game.".png</image>
						<rating></rating>
						<releasedate></releasedate>
						<developer></developer>
						<publisher></publisher>
						<genre></genre>
						<players></players>
						<favorite>true</favorite>
						<playcount></playcount>
						<lastplayed></lastplayed>
					</game>";
	}
	$emulatorXML .= "</gameList>";

	$gamelists_xml_dir = $rom_dirs["dir_gamelists"]."/".$emulator;
	if (!is_dir($gamelists_xml_dir)) {
		mkdir($gamelists_xml_dir);
	}

	$gamelist = $gamelists_xml_dir."/gamelist.xml";
	file_put_contents($gamelist, $emulatorXML);
}
?>
