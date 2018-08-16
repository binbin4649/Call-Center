<?php
require_once('VoiceText.php');
require_once(dirname(__FILE__).'/../autoload.php');
use Twilio\Rest\Client;
use Twilio\Twiml;
App::import('Model', 'AppModel');

class Call_Center extends AppModel{
	
	public $PluginName;
	
	public $responsePath;
	
	public $planOrder;
	
	public $wavPath;
	
	public $siteUrl;
	
	public $callId;
	
	public $mypageName;
	
	public $locate;
	
	public $addMp3;
	
	public $text1;
	public $text2;
	public $text3;
	public $text4;
	
/*
	public function __construct($responsePath){
		$this->responsePath = $responsePath;
	}
*/
	
	public function test(){
		//$this->log('tukaeru?');
		var_dump(APP);
	}
	
	public function basicResponse(){
		$response = new Twiml;
		$text = $this->mypageName.$this->text1;
		$text_array = $this->textToVoice($text);
		$text_count = count($text_array);
		foreach($text_array as $wav_name){
			$response->play($this->wavPath.$wav_name);
			$text_count--;
			if($text_count <> 0) $response->pause(array("length" => 1));
		}
		$response->Redirect($this->responsePath.$this->planOrder.'/'.$this->callId);
		return $response;
	}
	
	public function basicResponse2(){
		$response = new Twiml;
		$datetext = date("n月d日");
		$timetext = date("H時i分");
		$gather = $response->gather(array(
			'numDigits' => 1,
			'timeout' => 2,
			'action' => $this->responsePath.$this->planOrder.'/'.$this->callId
		));
		if(!empty($this->locate)){
			//天気予報あり
			$ret = $this->tenki($this->locate);
			$text = $datetext.','.$timetext.',';
			$text .= $ret['city_name'].$ret['description'];
		}else{
			//天気予報なし
			$text = '今日は、'.$datetext.',時刻は、'.$timetext.'です。';
		}
		$text .= $this->mypageName.$this->text1;
		$text_array = $this->textToVoice($text);
		foreach($text_array as $wav_name){
			$gather->play($this->wavPath.$wav_name);
			$gather->pause(array("length" => 1));
		}
		return $response;
	}
	
	public function premiumResponse2(){
		$response = new Twiml;
		$datetext = date("n月d日");
		$timetext = date("H時i分");
		$gather = $response->gather(array(
			'numDigits' => 2,
			'timeout' => 20,
			'finishOnKey' => '#',
			'action' => $this->responsePath.$this->planOrder.'/'.$this->callId
		));
		if(!empty($this->locate)){
			//天気予報あり
			$ret = $this->tenki($this->locate);
			$text = $datetext.','.$timetext.',';
			$text .= $ret['city_name'].$ret['description'];
		}else{
			//天気予報なし
			$text = '今日は、'.$datetext.',時刻は、'.$timetext.'です。';
		}
		$text .= $this->text1;
		$text_array = $this->textToVoice($text);
		foreach($text_array as $wav_name){
			$gather->play($this->wavPath.$wav_name);
			$gather->pause(array("length" => 1));
		}
		return $response;
	}
	
	public function premiumResponse3(){
		$response = new Twiml;
		$gather = $response->gather(array(
			'numDigits' => 1,
			'timeout' => 2,
			'action' => $this->responsePath.$this->planOrder.'/'.$this->callId
		));
		if(!empty($this->addMp3)){
			$gather->play($this->addMp3);
		}
		$text = $this->text1;
		if(!empty($this->text2)){
			$text .= $this->text2;
		}
		$text_array = $this->textToVoice($text);
		foreach($text_array as $wav_name){
			$gather->play($this->wavPath.$wav_name);
			$gather->pause(array("length" => 1));
		}
		return $response;
	}
	
	
	public function noResponse(){
		$response = new Twiml;
		$gather = $response->gather(array(
			'timeout' => 1,
		));
		return $response;
	}
	
	public function textToVoice($text = null){
		$return_arr = array();
		$key = Configure::read('MccPlugin.VoiceText_OPTION_API_KEY');
		$options = array(VoiceText::OPTION_API_KEY => $key);
		$manager = new VoiceText($options);
		
		$text = str_replace("、、", ",", $text);
		$text = str_replace("、", ",", $text);
		$text = str_replace("。。", ",", $text);
		$text = str_replace("。", ",", $text);
		$text = str_replace("？？", "?,", $text);
		$text = str_replace("？", "?,", $text);
		$text = str_replace("！！", "!,", $text);
		$text = str_replace("！", "!,", $text);
		$texts = explode(',', $text);

		foreach($texts as $tex){
			$tex = str_replace("　", " ", $tex);
			$tex = trim($tex);
			$judg = true; //音声に変換しても、あきらかに意味ないようなものは変換しない。
			if(mb_strlen($tex) == 1){
				if($tex == ',') $judg = false;
				if($tex == '?') $judg = false;
				if($tex == '!') $judg = false;
				if($tex == ' ') $judg = false;
			}
			if(empty($tex)) $judg = false;
			if($judg){
				$enc_tex = rtrim(strtr(base64_encode($tex), '+/', '-_'), '='); // urlで使える仕様のbase64_encode
				if(file_exists(APP.'Plugin/'.$this->pluginName.'/webroot/files/wav/'.$enc_tex.'.wav')){
					$return_arr[] = $enc_tex.'.wav';
				}else{
					$parameters = array( 
						'text' => $tex,
						'emotion' => 'happiness',
						'speaker' => 'hikari',
						'pitch' => '90',
						'speed' => '90',
						'volume' => '100'
					);
					$result = $manager->create(APP.'Plugin/'.$this->pluginName.'/webroot/files/wav/'.$enc_tex.'.wav', $parameters);
					if($result <> 200){
						$this->log('MccCall.php textToVoice error. :'.print_r($result, true));
					}
					$return_arr[] = $enc_tex.'.wav';
				}
			}
		}
		return $return_arr;
	}
	
	public function tenki($city_number){
		$ret = array();
		$req = "http://weather.livedoor.com/forecast/webservice/json/v1";
		$req .= "?city=".$city_number."&day=today";
		$file = file_get_contents($req);
		$json = json_decode($file, true);
		$ret['description'] = '今日は'.$json['forecasts'][0]['telop'].'、';
		if(!empty($json['forecasts'][0]['temperature']['max']['celsius'])){
			$ret['description'] .= '最高気温'.$json['forecasts'][0]['temperature']['max']['celsius'].'度、';
		}
		if(!empty($json['forecasts'][0]['temperature']['min']['celsius'])){
			$ret['description'] .= '最低気温'.$json['forecasts'][0]['temperature']['min']['celsius'].'度、';
		}
		$ret['description'] .= 'の予報です。';
		$ret['city_name'] = $json['title'].'、';
		return $ret;
	}
	
	//時間切り捨て、10分単位
	//https://qiita.com/Web_akira/items/d07ac8b418c9f7a97d96
	function floorPerTime($time, $per=10){
	    // 値がない時、単位が0の時は false を返して終了する
	    if( !isset($time) || !is_numeric($per) || ($per == 0 )) {
	        return false;
	    }else{
	        $deteObj = new DateTime($time);
	        // 指定された単位で切り捨てる
	        // フォーマット文字 i だと、 例えば1分が 2桁の 01 となる(1桁は無い）ので、整数に変換してから切り捨てる
	        $ceil_num = floor(sprintf('%d', $deteObj->format('i'))/$per) *$per;
	        $hour = $deteObj->format('H');
	        $have = $hour.sprintf( '%02d', $ceil_num );
	        return new DateTime($have);
	    }
	}
	
	// return to view/MccCall/index.php
	public function getBankHoliday(){
		$bankholiday = Cache::read('bank_holiday', 'MccCache');
		if(!$bankholiday){
			$bankholiday = [];
			$url = 'https://holidays-jp.github.io/api/v1/date.json';
			$json = file_get_contents($url);
			$json = mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
			$arry = json_decode($json,true);
			foreach($arry as $date=>$text){
				$year = substr($date, 0,4);
				$prev_year = $year -1;
				$md = substr($date, 5,6);
				if($md == '01-01'){
					$bankholiday[$prev_year.'-12-31'] = '年末年始';
					$bankholiday[$date] = $text;
					$bankholiday[$year.'-01-02'] = '年末年始';
					$bankholiday[$year.'-01-03'] = '年末年始';
				}else{
					$bankholiday[$date] = $text;
				}
			}
			Cache::write('bank_holiday', $bankholiday, 'MccCache');
		}
		return $bankholiday;
	}
	
	//指定月の日付を配列で返す
	public function ymdMonthArray($ym = null){
		if($ym){
			$year = substr($ym, 0, 4);
			$month = substr($ym, 4, 2);
		}else{
			$year = date('Y');
			$month = date('m');	
		}
		$firstDate = date('Y-m-d', strtotime('first day of ' . $year.'-'.$month));
		$lastDate = date('Y-m-d', strtotime('last day of ' . $year.'-'.$month));
		$start = strtotime($firstDate);
		$end = strtotime($lastDate);

		$ret = array();
		$temp = $end;
		while($temp >= $start){
		  $ret[] = date('Y-m-d', $temp);
		  $temp = strtotime('-1 day', $temp);
		}// end while
		sort($ret);
		return $ret;
	}
	
	
}