<?php
require_once('VoiceText.php');
require_once(dirname(__FILE__).'/../autoload.php');
use Twilio\Rest\Client;
use Twilio\TwiML\VoiceResponse;
App::import('Model', 'AppModel');

class Call_Center extends AppModel{
	
	public $PluginName;
	
	public $responsePath;
	
	public $planOrder;
	
	public $wavPath;
	
	public $siteUrl;
	
	public $callId;
	
	public $locate;
	
	public $addMp3;
	
	public $text1;
	public $text2;
	public $text3;
	public $text4;
	
	
	public function basicResponse(){
		//$response = new VoiceResponse;
		$response = new VoiceResponse;
		$text = $this->text1;
		if(!empty($this->text2)) $text .= $this->text2;
		if(!empty($this->text3)) $text .= $this->text3;
		if(!empty($this->text4)) $text .= $this->text4;
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
		$response = new VoiceResponse;
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
		$text .= $this->text1;
		$text_array = $this->textToVoice($text);
		foreach($text_array as $wav_name){
			$gather->play($this->wavPath.$wav_name);
			$gather->pause(array("length" => 1));
		}
		return $response;
	}
	
	public function premiumResponse2(){
		$response = new VoiceResponse;
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
		$response = new VoiceResponse;
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
	
	public function confResponse(){
		$response = new VoiceResponse;
		$gather = $response->gather(array(
			'numDigits' => 2,
			'timeout' => 10,
			'finishOnKey' => '#',
			'action' => $this->responsePath.$this->planOrder.'/'.$this->callId
		));
		$text = $this->text1;
		if(!empty($this->text2)) $text .= $this->text2;
		if(!empty($this->locate)){
			$ret = $this->tenki($this->locate);
			$text .= $ret['city_name'].$ret['description'];
		}
		if(!empty($this->text3)) $text .= $this->text3;
		if(!empty($this->text4)) $text .= $this->text4;
		$text_array = $this->textToVoice($text);
		foreach($text_array as $wav_name){
			$gather->play($this->wavPath.$wav_name);
			$gather->pause(array("length" => 1));
		}
		return $response;
	}
	
	// Nos number_only用
	public function longConfResponse(){
		$response = new VoiceResponse;
		if(empty($this->callId) && empty($this->planOrder)){
			$action_url = $this->responsePath;
		}elseif(empty($this->callId) && !empty($this->planOrder)){
			$action_url = $this->responsePath.$this->planOrder;
		}else{
			$action_url = $this->responsePath.$this->planOrder.'/'.$this->callId;
		}
		$gather = $response->gather(array(
			'numDigits' => 8,
			'timeout' => 30,
			'finishOnKey' => '#',
			'action' => $action_url
		));
		$text = $this->text1;
		if(!empty($this->text2)) $text .= $this->text2;
		if(!empty($this->locate)){
			$ret = $this->tenki($this->locate);
			$text .= $ret['city_name'].$ret['description'];
		}
		if(!empty($this->text3)) $text .= $this->text3;
		if(!empty($this->text4)) $text .= $this->text4;
		$text_array = $this->textToVoice($text);
		foreach($text_array as $wav_name){
			$gather->play($this->wavPath.$wav_name);
			$gather->pause(array("length" => 1));
		}
		return $response;
	}
	
	public function onceResponse(){
		$response = new VoiceResponse;
		$text = $this->text1;
		if(!empty($this->text2)) $text .= $this->text2;
		if(!empty($this->locate)){
			$ret = $this->tenki($this->locate);
			$text .= $ret['city_name'].$ret['description'];
		}
		if(!empty($this->text3)) $text .= $this->text3;
		if(!empty($this->text4)) $text .= $this->text4;
		$text_array = $this->textToVoice($text);
		$text_count = count($text_array);
		foreach($text_array as $wav_name){
			$response->play($this->wavPath.$wav_name);
			$text_count--;
			if($text_count <> 0) $response->pause(array("length" => 1));
		}
		return $response;
	}
	
	public function noResponse(){
		$response = new VoiceResponse;
		$gather = $response->gather(array(
			'timeout' => 1,
		));
		return $response;
	}
	
	public function callOut($response_url, $to){
		if(Configure::read('MccPlugin.TEST_MODE')){
			$test_numbers = Configure::read('MccPlugin.TEST_NUMBER');
			if(array_search($to, $test_numbers) === false) return 'test_mode';
		}
		$sid = Configure::read('MccPlugin.TwilioSid');
		$token = Configure::read('MccPlugin.TwilioToken');
		$fromNumber = Configure::read('MccPlugin.TwilioFromNumber');
		$twilio = new Client($sid, $token);
		$int_to = $this->toInternational($to);
		$url = $this->responsePath.$response_url;
		
		//timeout 入れなくとも１分くらいは鳴ってる。電話機によるんだろうけど。
		$call = $twilio->calls->create($int_to, $fromNumber, ['url' => $url, 'timeout' => 40]);
		return $call->sid;
	}
	
	public function getTwilioLog($call_sid){
		if(Configure::read('MccPlugin.TEST_MODE')){
			if($call_sid == 'test_mode') return 'test_mode';
		}
		$sid = Configure::read('MccPlugin.TwilioSid');
		$token = Configure::read('MccPlugin.TwilioToken');
		$twilio = new Client($sid, $token);
		
		//ログ取得
		$call = $twilio->calls($call_sid)->fetch();
		
		//ログ整形
		$to = $this->toDomestic($call->to);
		if($call->status == 'completed'){
			$status = '応答あり';
		}elseif($call->status == 'no-answer'){
			$status = '応答なし';
		}elseif($call->status == 'busy'){
			$status = 'ビジー信号';
		}else{
			$status = $call->status;
		}
		$call->startTime->setTimezone(new DateTimeZone('Asia/Tokyo'));
		$startTime = $call->startTime->format('Y-m-d H:i:s');
		$duration = $call->duration;
		
		$log = '開始:'.$startTime.' To:'.$to.' ステータス:'.$status.' 通話時間:'.$duration.'秒'."\n";
		return $log;
	}
	
	
	public function textToVoice($text = null){
		if(Configure::read('MccPlugin.TEST_MODE')){
			return ['test1.wav', 'test2.wav'];
		}
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
						$this->log('Call-Center textToVoice error. :'.print_r($result, true));
					}
					$return_arr[] = $enc_tex.'.wav';
				}
			}
		}
		return $return_arr;
	}
	
	// ログ組立
	public function logAssembly($call){
		if($call['status'] == 'up'){
			$wake = '確認済。';
		}else{
			$wake = '未確認、確認できませんでした。';
		}
		$log_data = 'Plan:'.$call['call_plan'].' '.$wake."\n";
		
		if(!empty($call['callsid1'])){
			$log_data .= $this->getTwilioLog($call['callsid1']);
		}
		if(!empty($call['callsid2'])){
			$log_data .= $this->getTwilioLog($call['callsid2']);
		}
		if(!empty($call['callsid3'])){
			$log_data .= $this->getTwilioLog($call['callsid3']);
		}
		if(!empty($call['callsid4'])){
			$log_data .= $this->getTwilioLog($call['callsid4']);
		}
		if(!empty($call['callsid5'])){
			$log_data .= $this->getTwilioLog($call['callsid5']);
		}
		if(!empty($call['callsid6'])){
			$log_data .= $this->getTwilioLog($call['callsid6']);
		}
		if(!empty($call['callsid7'])){
			$log_data .= $this->getTwilioLog($call['callsid7']);
		}
		
		if($call['call_plan'] == 'premium'){
			if($call['result'] == 'correct'){
				$result = '正解';
			}elseif($call['result'] == 'incorrect'){
				$result = '不正解';
			}else{
				$result = '無';
			}
			$log_data .= '押された番号:'.$call['digits'].' '.$result."\n";
		}
		return $log_data;
	}
	
	public function tenki($city_number){
		$ret = $this->weatherYumake($city_number);
		if($ret == false){
			$ret = $this->weatherLivedoor($city_number);
		}elseif($ret == false){
			$ret['city_name'] = '天気予報停止中。';
			$ret['description'] = '申し訳ありません。';
		}
		return $ret;
	}
	
	public function weatherYumake($city_number){
		$ret = Cache::read('weather_yumake'.$city_number, 'MccCacheOneHour');
		if(!$ret){
			$key = Configure::read('MccPlugin.YUMAKE_TODAY_API_KEY');
			if(empty($key)) return false;
			$ret = [];
			$ret['description'] = '';
			//yumake用の呼び出しコード、頭2桁。
			$code = substr($city_number, 0, 2);
			if($code == '01' || $code == '47'){
				//北海道、沖縄は3桁
				$code = substr($city_number, 0, 3);
			}
			$req = 'https://api.yumake.jp/1.1/forecastPref.php?code='.$code.'&key='.$key.'&format=json';
			$file = file_get_contents($req);
			$json = json_decode($file, true);
			if(empty($json)) return false;
			if($json['status'] != 'success') return false;
			foreach($json['area'] as $area){
				if($area['areaCode'] == $city_number){
					$ret['city_name'] = $area['areaName'].'、';
					$ret['description'] .= $area['forecastDateName'][0].'は、';
					$ret['description'] .= $area['weather'][0].'、';
					$ret['description'] .= $area['windDirection'][0].'、';
					$ret['description'] .= '降水確率、';
					$precipitationName0 = str_replace('００', '０', $area['precipitationName'][0]);
					$precipitationName1 = str_replace('００', '０', $area['precipitationName'][1]);
					$ret['description'] .= $precipitationName0.$area['precipitation'][0].'パーセント、';
					$ret['description'] .= $precipitationName1.$area['precipitation'][1].'パーセント、';
					
				}
			}
			//areaCodeBelong が重複することがあるみたいなので一つだけとったら終わりで、braek
			foreach($json['temperatureStation'] as $temp){
				if($temp['areaCodeBelong'] == $city_number){
					if(empty($temp['type'][2]) || empty($temp['type'][3])){
						return false;
					}
					$ret['description'] .= $temp['type'][2].$temp['temperature'][2].'度、';
					$ret['description'] .= $temp['type'][3].$temp['temperature'][3].'度、';
					break;
				}
			}
			if(empty($ret['city_name']) || empty('description')){
				return false;
			}
			$ret['description'] .= 'の予報です。';
			Cache::write('weather_yumake'.$city_number, $ret, 'MccCacheOneHour');
		}
		return $ret;
	}
	
	public function weatherLivedoor($city_number){
		$ret = Cache::read('weather_livedoor'.$city_number, 'MccCacheOneHour');
		if(!$ret){
			$ret = array();
			$req = "http://weather.livedoor.com/forecast/webservice/json/v1";
			$req .= "?city=".$city_number."&day=today";
			$file = file_get_contents($req);
			$json = json_decode($file, true);
			//入っていなかったら停止アナウンス
			if(empty($json['forecasts'][0]['telop'])){
				return false;
			}
			$ret['description'] = '今日は'.$json['forecasts'][0]['telop'].'、';
			if(!empty($json['forecasts'][0]['temperature']['max']['celsius'])){
				$ret['description'] .= '最高気温'.$json['forecasts'][0]['temperature']['max']['celsius'].'度、';
			}
			if(!empty($json['forecasts'][0]['temperature']['min']['celsius'])){
				$ret['description'] .= '最低気温'.$json['forecasts'][0]['temperature']['min']['celsius'].'度、';
			}
			$ret['description'] .= 'の予報です。';
			$ret['city_name'] = $json['title'].'、';
			Cache::write('weather_livedoor'.$city_number, $ret, 'MccCacheOneHour');
		}
		return $ret;
	}
	
	//天気予報の地域を返す
	public function weatherArea(){
		$area = Cache::read('weather_area', 'MccCache');
		if(!$area){
			$xml = simplexml_load_file('http://weather.livedoor.com/forecast/rss/primary_area.xml');
			$rss = $xml->xpath('/rss/channel/ldWeather:source/pref');
			$area = [];
			foreach($rss as $value){
				$cate = (string)$value->attributes()->title;
				foreach($value->city as $city){
					$area[$cate][(string)$city->attributes()->id] = (string)$city->attributes()->title;
				}
			}
			Cache::write('weather_area', $area, 'MccCache');
		}
		return $area;
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
	
	//祝日（振替含む）年末年始
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
		$start = strtotime($this->firstDate($ym));
		$end = strtotime($this->lastDate($ym));
		$ret = array();
		$temp = $end;
		while($temp >= $start){
		  $ret[] = date('Y-m-d', $temp);
		  $temp = strtotime('-1 day', $temp);
		}// end while
		sort($ret);
		return $ret;
	}
	
	//指定月の最初の日をY-m-dで返す
	public function firstDate($ym = null){
		if($ym){
			$year = substr($ym, 0, 4);
			$month = substr($ym, 4, 2);
		}else{
			$year = date('Y');
			$month = date('m');	
		}
		$firstDate = date('Y-m-d', strtotime('first day of ' . $year.'-'.$month));
		return $firstDate;
	}
	
	//指定月の最後の日をY-m-dで返す
	public function lastDate($ym = null){
		if($ym){
			$year = substr($ym, 0, 4);
			$month = substr($ym, 4, 2);
		}else{
			$year = date('Y');
			$month = date('m');	
		}
		$lastDate = date('Y-m-d', strtotime('last day of ' . $year.'-'.$month));
		return $lastDate;
	}
	
	//http://qiita.com/kakk_a/items/19e4eeb5a6bca36bd51a
	public function toInternational($number){
		if (preg_match('/^[0-9]+$/', $number) && (strlen($number) == 10 || strlen($number) == 11)) {
            return preg_replace( '/^0/', '+81', $number);
        } else if (preg_match('/^\+81[0-9]+$/', $number) && (strlen($number) == 12 || strlen($number) == 13)) {
        	$this->log('Call-Center toInternational no convert. : '.$number);
            return $number;
        } else {
        	$this->log('Call-Center toInternational error. : '.$number);
            return false;
        }
	}
	
	public function toDomestic($number) {
        if (preg_match('/^\+81[0-9]+$/', $number) && (strlen($number) == 12 || strlen($number) == 13)) {
            return preg_replace( '/^\+81/', '0', $number);
        } else if (preg_match('/^[0-9]+$/', $number) && (strlen($number) == 10 || strlen($number) == 11)) {
        	$this->log('Call-Center toDomestic no convert. : '.$number);
            return $number;
        } else {
        	$this->log('Call-Center toDomestic error. : '.$number);
            return false;
        }
    }
    
}