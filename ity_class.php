<?php

class ityim {

public $login;
public $apikey;
public $format;
public $longurl;
public $disable_inter;    
public $output;
public $object;
public $returned;

function GetElementByName ($xml, $start, $end) {

   $startpos = strpos($xml, $start);
   if ($startpos === false) {
       return false;
   }
   $endpos = strpos($xml, $end);

   $endpos = $endpos+strlen($end);    
   $this->pos = $endpos;
   $endpos = $endpos-$startpos;
   $endpos = $endpos - strlen($end);
   $tag = substr ($xml, $startpos, $endpos);
   $tag = substr ($tag, strlen($start));
    return $tag;

}
function xmltagvalue($outArray,$needle) {

for($i=0;$i<count($outArray);$i++){
            if($outArray[$i]['tag']==strtoupper($needle)){
                $tagValue    =    $outArray[$i]['value'];
            }
        }
        return $tagValue;

}


function apiconnect($apiurl) {

if  (function_exists(‘curl_version’)) {
	$curl_handle=curl_init();
    curl_setopt($curl_handle,CURLOPT_URL,"$apiurl");
    curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
    curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
    $buffer = curl_exec($curl_handle);
    curl_close($curl_handle);
	return $buffer;
	}
	else{
	
	$timeout = stream_context_create(array(
    'http' => array(
        'timeout' => 2
        )
    )
   );
   return @file_get_contents("$apiurl", 0, $timeout); 
	}
}


function shrinkurl() {
$apiurl="http://api.ity.im/v1/shorten.php?";
$apiurl.="login={$this->login}&";
$apiurl.="apikey={$this->apikey}&";
$apiurl.="disable_inter={$this->disable_inter}&";
$apiurl.="long_url=".urlencode($this->longurl)."&";
$apiurl.="format={$this->format}";

$this->output=$this->apiconnect($apiurl);


if($this->format=="json") {
$this->returned=json_decode($this->output);
} else {


$this->Nodes = array();
$data=$this->output;
$this->count = 0;
$this->pos = 0;
while ($node = $this->GetElementByName($data, "<response>", "</response>")) {
   $this->Nodes[$this->count] = $node;
   $this->count++;
   $data = substr($data, $this->pos);
}

for ($i=0; $i<$this->count; $i++) {
$this->returned->status_code=$this->GetElementByName($this->Nodes[$i], "<status_code>", "</status_code>");
$this->returned->status_txt=$this->GetElementByName($this->Nodes[$i], "<status_txt>", "</status_txt>");
$this->returned->data->url=$this->GetElementByName($this->Nodes[$i], "<url>", "</url>");
$this->returned->data->hash=$this->GetElementByName($this->Nodes[$i], "<hash>", "</hash>");
$this->returned->data->long_url=$this->GetElementByName($this->Nodes[$i], "<long_url>", "</long_url>");
}

}
}

function expand() {
$apiurl="http://api.ity.im/v1/expand.php?";
$apiurl.="login={$this->login}&";
$apiurl.="apikey={$this->apikey}&";

if($this->hash!='') {

 if(strpos($this->hash,',') === FASLE) {
 $apiurl.="hash={$this->hash}&";
 } else {
$array=explode(',',$this->hash);
for($i=0; $i<count($array); $i++) {
$apiurl.="hash[]={$array[$i]}&";
}
} 
}
if($this->shorturl!='') {

 if(strpos($this->shorturl,',')=== FALSE) {
 $apiurl.="shorturl[]={$this->shorturl}&";
 } else {
$array=explode(',',$this->shorturl);

for($i=0; $i<count($array); $i++) {
$apiurl.="shorturl={$array[$i]}&";
}
} 
}
$apiurl.="format={$this->format}";


$this->output=$this->apiconnect($apiurl);


if($this->format=="json") {
$this->returned=json_decode($this->output);
} else {


$this->Nodes = array();
$data=$this->output;
$this->count = 0;
$this->pos = 0;
while ($node = $this->GetElementByName($data, "<response>", "</response>")) {
   $this->Nodes[$this->count] = $node;
   $this->count++;
   $data = substr($data, $this->pos);
}

for ($i=0; $i<$this->count; $i++) {
$this->returned->status_code=$this->GetElementByName($this->Nodes[$i], "<status_code>", "</status_code>");
$this->returned->status_txt=$this->GetElementByName($this->Nodes[$i], "<status_txt>", "</status_txt>");
}

$this->Nodes = array();
$data=$this->output;
$this->count = 0;
$this->pos = 0;
while ($node = $this->GetElementByName($data, "<entry>", "</entry>")) {
   $this->Nodes[$this->count] = $node;
   $this->count++;
   $data = substr($data, $this->pos);
}

for ($i=0; $i<$this->count; $i++) {
$this->returned->data->expand[$i]->long_url=$this->GetElementByName($this->Nodes[$i], "<short_url>", "</short_url>");
$this->returned->data->expand[$i]->hash=$this->GetElementByName($this->Nodes[$i], "<hash>", "</hash>");
$this->returned->data->expand[$i]->short_url=$this->GetElementByName($this->Nodes[$i], "<long_url>", "</long_url>");
$this->returned->data->expand[$i]->clicks_today=$this->GetElementByName($this->Nodes[$i], "<clicks_today>", "</clicks_today>");
$this->returned->data->expand[$i]->clicks_month=$this->GetElementByName($this->Nodes[$i], "<clicks_month>", "</clicks_month>");
$this->returned->data->expand[$i]->clicks_overall=$this->GetElementByName($this->Nodes[$i], "<clicks_overall>", "</clicks_overall>");
}





}


}


}


/*
NOTES: 
When using this api wrapper it is recommended to use the json format for speed and efficiency. 
A basic XML parser is included for use with the xml format.
*/
//initialize class--------------
$ityclass=new ityim();
$ityclass->login="apidemo@ity.im";  //your ity.im login email address
$ityclass->apikey="2147483647"; //your ity.im api key
$ityclass->format="xml"; //output format; json or xml
//------------------------------

//shrink a url***********************************************
$ityclass->longurl="http://sometestsite.com/?testing12354"; //provide a long url to be shortened
$ityclass->disable_inter="1"; // 0 to disable interstitial ads, 1 to enable ads.
$ityclass->shrinkurl(); //controller function

//Returned Object---------------
// $ityclass->returned->status_code - the status code
// $ityclass->returned->status_txt  - status description
// $ityclass->returned->data->url   - complete shortened url
// $ityclass->returned->data->hash  - hash only
// $ityclass->returned->data->long_url - the long url submitted

//------------------------------
//***********************************************************


//expand a url or hash includes click stats******************

$ityclass->shorturl="http://ity.im/09K"; //provide a short url to be expanded (seperated by commas for multiples)
$ityclass->hash="00y,00U"; // hash only (seperated by commas for multiples)
$ityclass->format="json"; //output format; json or xml
$ityclass->expand(); //controller function

//NOTE: to include multiple hash or shorturl's add them as a string seperated by commas. 
//example: $ityclass->hash="F4s3a,4Fsfg,85fDs,Cvse4";


//Returned Object---------------
// $ityclass->returned->status_code - the status code
// $ityclass->returned->status_txt  - status description
// $ityclass->returned->data->expand[0]->long_url   - long url assigned to hash
// $ityclass->returned->data->expand[0]->hash  - hash only
// $ityclass->returned->data->expand[0]->short_url - short url
// $ityclass->returned->data->expand[0]->clicks_today - clicks today
// $ityclass->returned->data->expand[0]->clicks_month - clicks this month
// $ityclass->returned->data->expand[0]->clicks_overall - clicks overall
//------------------------------

//NOTE: if multiple hash's or short urls were submitted the returned data object will be an array with multiple rows. A single row returned will
//be on row [0] of the array.
//***********************************************************




?>