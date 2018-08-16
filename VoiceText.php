<?php

/**
 * VoiceText Web API 用ライブラリ
 * 
 *
 */
class VoiceText
{
    /**
     * 音声変換対象文字列の上限値
     * 
     * @var int
     */
    const TEXT_MAX_LENGTH = 200;
    
    /**
     * 感情レベルの下限値
     * 
     * @var int
     */
    const EMOTION_LEVEL_MIN = 1;
    
    /**
     * 感情レベルの上限値
     * 
     * @var int
     */
    const EMOTION_LEVEL_MAX = 2;
    
    /**
     * 音の高低の下限値
     * 
     * @var int
     */
    const PITCH_MIN = 50;
    
    /**
     * 音の高低の上限値
     * 
     * @var int
     */
    const PITCH_MAX = 200;
    
    /**
     * 話す速度の下限値
     * 
     * @var int
     */
    const SPEED_MIN = 50;
    
    /**
     * 話す速度の上限値
     * 
     * @var int
     */
    const SPEED_MAX = 200;
    
    /**
     * 音量の下限値
     * 
     * @var int
     */
    const VOLUME_MIN = 50;
    
    /**
     * 音量の上限値
     * 
     * @var int
     */
    const VOLUME_MAX = 200;
    
    /**
     * パラメータのキー - ファイル名
     * 
     * @var unknown_type
     */
    const PARAMETER_FILE = 'file';
    
    /**
     * パラメータのキー - 音声変換対象文字列
     * 
     * @var string
     */
    const PARAMETER_TEXT = 'text';
    
    /**
     * パラメータのキー - 話者名
     * 
     * @var string
     */
    const PARAMETER_SPEAKER = 'speaker';
    
    /**
     * パラメータのキー - 感情カテゴリ
     * 
     * @var string
     */
    const PARAMETER_EMOTION = 'emotion';
    
    /**
     * パラメータのキー - 感情レベル
     * 
     * @var string
     */
    const PARAMETER_EMOTION_LEVEL = 'emotion_level';
    
    /**
     * パラメータのキー - 音の高低
     * 
     * @var string
     */
    const PARAMETER_PITCH = 'pitch';
    
    /**
     * パラメータのキー - 話す速度
     * 
     * @var string
     */
    const PARAMETER_SPEED = 'speed';
    
    /**
     * パラメータのキー - 音量
     * 
     * @var string
     */
    const PARAMETER_VOLUME = 'volume';
    
    /**
     * オプションのキー - API KEY
     * 
     * @var string
     */
    const OPTION_API_KEY = 'API_KEY';
    
    /**
     * オプションのキー - API URL
     * 
     * @var string
     */
    const OPTION_API_URL = 'API_URL';
    
    /**
     * オプションのキー - 接続のタイムアウト秒
     * 
     * @var string
     */
    const OPTION_CONNECTTIMEOUT = 'CONNECTTIMEOUT';
    
    /**
     * オプションのキー - cURL関数のタイムアウト秒
     * 
     * @var string
     */
    const OPTION_TIMEOUT = 'TIMEOUT';
    
    /**
     * 設定出来る話者名の配列
     * 
     * @var array
     */
    public $speakers = array(
        'show',
        'haruka',
        'hikari',
        'takeru',
        'santa',
        'bear',
    );
    
    /**
     * 設定出来る感情カテゴリの配列
     * 
     * @var array
     */
    public $emotions = array(
        'happiness',
        'anger',
        'sadness',
    );
    
    /**
     * cURLにて接続時のオプション
     * 
     * @var array
     */
    private $options = array(
        self::OPTION_API_KEY          => 'DUMMY KEY',
        self::OPTION_API_URL          => 'https://api.voicetext.jp/v1/tts',
        self::OPTION_CONNECTTIMEOUT   => 30,
        self::OPTION_TIMEOUT          => 30,
    );
    
    /**
     * エラー内容
     * 
     * @var array
     */
    private $errors = array(
        'file'      => '出力するファイル名を必ず入力して下さい',
        'write'     => '対象のフォルダが存在しないか書き込み権限が不足しています',
        'required'  => 'パラメータ[%s]:必ず入力して下さい',
        'incorrect' => 'パラメータ[%s]:は%sから指定して下さい',
        'maxlength' => 'パラメータ[%s]:は%s文字以内で入力して下さい',
        'range'     => 'パラメータ[%s]:は%s～%sを指定して下さい',
    );
    
    public function __construct($options = array())
    {
        $this->options = array_merge($this->options, $options);
    }
    
    /**
     * 
     * 
     * @param string $fileName 出力先のファイル名
     * @param array $parameters 音声情報のパラメータ
     * @return void|multitype:multitype: string |multitype:string |mixed
     */
    public function create($fileName, array $parameters)
    {
        if($errors = $this->__validate($fileName, $parameters))
        {
            return $errors;
        }
        
        // 実処理
        $fp = fopen($fileName, 'w');
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->options[self::OPTION_API_URL]);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $this->options[self::OPTION_API_KEY] . ':');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->options[self::OPTION_CONNECTTIMEOUT]);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->options[self::OPTION_TIMEOUT]);
        curl_setopt($ch, CURLOPT_FILE, $fp); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_exec($ch);

        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);
        fclose($fp);
        
        // cURLがエラーを返却した場合
        if(CURLE_OK !== $errno)
        {
            unlink($fileName);
            return array($errno => $error);
        }
        
        // レスポンスが正常終了以外の場合レスポンスコードを返却する
        if($code != 200)
        {
            $json = json_decode(file_get_contents($fileName));
            unlink($fileName);
            return array($code => $json->error->message);
        }
        
        return $code;
    }
    
    /**
     * パラメータチェック
     * 
     * @param string $fileName 出力先のファイル名
     * @param array $parameters 音声情報のパラメータ
     * @return multitype:multitype: string
     */
    private function __validate($fileName, array $parameters)
    {
        $errors = array();
        extract($parameters);
        
        if(!strlen($fileName))
        {
            $errors[self::PARAMETER_FILE] = $this->errors['file'];
        }
        else if(!is_writable(dirname($fileName)))
        {
            $errors[self::PARAMETER_FILE] = $this->errors['write'];
        }
        
        if(!isset($text) || !strlen($text))
        {
            $errors[self::PARAMETER_TEXT] = 
                sprintf($this->errors['required'], self::PARAMETER_TEXT);
        }
        else if(mb_strlen($text) > self::TEXT_MAX_LENGTH)
        {
            $errors[self::PARAMETER_TEXT] = 
                sprintf($this->errors['maxlength'], self::PARAMETER_TEXT, self::TEXT_MAX_LENGTH);
        }
        
        if(!isset($speaker) || !strlen($speaker))
        {
            $errors[self::PARAMETER_SPEAKER] = 
                sprintf($this->errors['required'], self::PARAMETER_SPEAKER);
        }
        else if(!in_array($speaker, $this->speakers))
        {
            $errors[self::PARAMETER_SPEAKER] = 
                sprintf($this->errors['incorrect'], self::PARAMETER_SPEAKER, implode(',', $this->speakers));
        }
        
        if(isset($emotion) && !in_array($emotion, $this->emotions))
        {
            $errors[self::PARAMETER_EMOTION] = 
                sprintf($this->errors['incorrect'], self::PARAMETER_EMOTION, implode(',', $this->emotions));
        }
        
        if(isset($emotion_level) && (!preg_match('/^[0-9]+$/', $emotion_level) || $emotion_level < self::EMOTION_LEVEL_MIN || $emotion_level > self::EMOTION_LEVEL_MAX))
        {
            $errors[self::PARAMETER_EMOTION_LEVEL] = 
                sprintf($this->errors['range'], self::PARAMETER_EMOTION_LEVEL, self::EMOTION_LEVEL_MIN, self::EMOTION_LEVEL_MAX);
        }
        
        if(isset($pitch) && (!preg_match('/^[0-9]+$/', $pitch) || $pitch < self::PITCH_MIN || $pitch > self::PITCH_MAX))
        {
            $errors[self::PARAMETER_PITCH] = 
                sprintf($this->errors['range'], self::PARAMETER_PITCH, self::PITCH_MIN, self::PITCH_MAX);
        }
        
        if(isset($speed) && (!preg_match('/^[0-9]+$/', $speed) || $speed < self::SPEED_MIN || $speed > self::SPEED_MAX))
        {
            $errors[self::PARAMETER_SPEED] = 
                sprintf($this->errors['range'], self::PARAMETER_SPEED, self::SPEED_MIN, self::SPEED_MAX);
        }
        
        if(isset($volume) && (!preg_match('/^[0-9]+$/', $volume) || $volume < self::VOLUME_MIN || $volume > self::VOLUME_MAX))
        {
            $errors[self::PARAMETER_VOLUME] = 
                sprintf($this->errors['range'], self::PARAMETER_VOLUME, self::VOLUME_MIN, self::VOLUME_MAX);
        }
        
        return $errors;
    }
}
