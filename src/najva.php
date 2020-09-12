<?php
class Najva {
    
    private $segments_url = "https://app.najva.com/api/v1/websites/<website-api-key>/segments/";
    private $accounts_url = "https://app.najva.com/api/v1/one-signals/";

    private $api_key;
    private $token;

    function __construct($api_key,$token){
        $this->api_key = $api_key;
        $this->token = $token;
    }

    function sendNotification($notification){
        $ch = curl_init($notification->destination);

        $headers = array(
            'cache-control: no-cache',
            'content-type: application/json',
            'authorization: Token '. $this->token
        );

        $body = $this->buildBody($notification);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);

        curl_close($ch);

        return $result;
    }

    function getSegmentsUrl(){
        return "https://app.najva.com/api/v1/websites/". $this->api_key ."/segments/";
    }
    
    function getSegments(){
        $ch = curl_init($this->getSegmentsUrl());
        
        $headers = array(
            'cache_control: no-cache',
            'content-type: application/json',
            'authorization: Token '. $this->token
        );
        
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);

        curl_close($ch);
        
        return $result;
    }
    
    function getAccounts(){
        $ch = curl_init($this->accounts_url);
        
        $headers = array(
            'cache_control: no-cache',
            'content-type: application/json',
            'authorization: Token '. $this->token
        );
        
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);

        curl_close($ch);
        
        return $result;
    }

    private function buildBody($notification){
        $body =  '{'.
            $this->buildItem("api_key",$this->api_key).','.
            $this->buildItem("title",$notification->title).','.
            $this->buildItem("body",$notification->body).','.
            $this->buildItem("url",$notification->url).','.
            $this->buildItem("content",$notification->content).','.
            $this->buildItem("image",$notification->image).','.
            $this->buildItem("icon",$notification->icon).','.
            $this->buildItem("onclick_action",$notification->onClickAction).','.
            $this->buildItem("sent_time",$notification->sentTime).',';
        if ($notification->sendToAll){
            $body .=
            $this->buildList("segments_include",$notification->segmentInclude).','.
            $this->buildList("segment_exclude", $notification->segmentExclude).','.
            $this->buildList("one_signal_accounts", $notification->oneSignalAccounts).',';
            if ($notification->oneSignalEnabled){
                $body .= $this->buildItem("one_signal_enabled", "true").',';
            } else {
                $body .= $this->buildItem("one_signal_enabled", "false").',';
            }
        } else {
            $body .=
            $this->buildTokens("subscriber_tokens", $notification->subscribersToken).',';
        }
        $body .= $this->buildJson("json",$notification->json).'}';
        return $body;
    }

    private function buildItem($key,$value){
        if(is_null($value)){
            return '';
        }
        return '"'.$key.'"'.':'.'"'.$value.'"';
    }

    private function buildList($key, $list){
        $body = '"'.$key.'"'.':';
        $body .= '[';
        for ($i=0;$i<count($list);$i++){
            if ($i != 0){
                $body .= ',';
            }
            $body .= $list[$i];
        }
        $body .= ']';
        return $body;
    }
    
    private function buildTokens($key, $list){
        $body = '"'.$key.'"'.':';
        $body .= '[';
        for ($i=0;$i<count($list);$i++){
            if ($i != 0){
                $body .= ',';
            }
            $body .= '"';
            $body .= $list[$i];
            $body .= '"';
        }
        $body .= ']';
        return $body;
    }

    private function buildJson($key,$json){
        $body = '"'.$key.'"'.':';
        $body .= '"{';
        $temp = false;
        foreach($json as $x => $x_value){
            if ($temp == true){
                $body .= ',';
            }
            $temp = true;
            $body .= '\"'.$x.'\"'.':'.'\"'.$x_value.'\"';
        }
        $body .= '}"';
        return $body;
    }

}

class Notification {

    public $destination;
    public $sendToAll;

    function __construct($sendToAll){
        $this->sendToAll = $sendToAll;
        if ($sendToAll == true){
            $this->destination = "https://app.najva.com/api/v1/notifications/";
        } else {
            $this->destination = "https://app.najva.com/notification/api/v1/notifications/";
        }
    }

    public $title;
    public $body;
    public $onClickAction;
    public $url;
    public $content;
    public $json;
    public $icon;
    public $image;
    public $sentTime;
    public $segmentInclude;
    public $segmentExclude;
    public $oneSignalEnabled = false;
    public $oneSignalAccounts;
    public $subscribersToken;
}


?>
