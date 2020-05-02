<?php
class CURL
{
   public $url;
   public $method; # {{@es_comment_note: Either GET or POST}}
   public $headers;
   public $cookies;
   public $CURL_HANDLER;

   function __construct($url,$method="GET",$request_body="") 
   {
      $this->url = $url;
      $this->method = $method;
      $this->headers=array();
      $this->cookies="";

      if($this->method!="GET" && $this->method!="POST")
      {
         fprint("Either GET or POST methods are allowed.");
         iprint("Exiting...");
         exit();
      }

      $this->CURL_HANDLER = curl_init();

      curl_setopt($this->CURL_HANDLER, CURLOPT_URL, $url);
      curl_setopt($this->CURL_HANDLER, CURLOPT_POST, $method=="POST"?1:0);
      curl_setopt($this->CURL_HANDLER, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($this->CURL_HANDLER, CURLOPT_SSL_VERIFYHOST, false);
      curl_setopt($this->CURL_HANDLER, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($this->CURL_HANDLER, CURLOPT_HEADER, 1);
      // curl_setopt($this->CURL_HANDLER, CURLOPT_TIMEOUT, $GLOBALS["CURL_TIMEOUT"]);
      if($method=="POST") curl_setopt($this->CURL_HANDLER, CURLOPT_POSTFIELDS, $request_body);
      // global $PROXY_ON;
      // global $PROXY_IP;
      // global $PROXY_PORT;

      // if($PROXY_ON) $this->set_proxy($PROXY_IP.":".$PROXY_PORT);
   }

   function append_header($key,$value) 
   {
      $this->headers[] = $key.": ".$value;
   }
   
   function set_proxy($ip_and_port) 
   {
      $this->proxy=$ip_and_port;
      curl_setopt($this->CURL_HANDLER, CURLOPT_PROXY, $this->proxy);
   }

   function set_data($data) 
   {
      curl_setopt($this->CURL_HANDLER, CURLOPT_POSTFIELDS, $data);
   }

   function set_basic_auth($username,$password) 
   {
      curl_setopt($this->CURL_HANDLER, CURLOPT_USERPWD, "$username:$password");
   }

   function set_cookie($name,$value)
   {
      $this->cookies.=$name."=".$value."; ";
   }

   function send()
   {
      if(sizeof($this->headers)>0) curl_setopt($this->CURL_HANDLER, CURLOPT_HTTPHEADER, $this->headers);
      if($this->cookies!="") curl_setopt($this->CURL_HANDLER,CURLOPT_COOKIE,$this->cookies);

      $response_object = curl_exec($this->CURL_HANDLER);
      $curl_info=curl_getinfo($this->CURL_HANDLER);

      if (curl_errno($this->CURL_HANDLER))
      {
         fprint("Connection error");
         throw new Exception("Error Processing Request".curl_error($this->CURL_HANDLER), 1);
      }
      else
      {
         $header_size = curl_getinfo($this->CURL_HANDLER, CURLINFO_HEADER_SIZE);
         $response_headers = substr($response_object, 0, $header_size);
         $response_body = substr($response_object, $header_size);
         $response_headers = explode("\r\n", $response_headers);
         array_shift($response_headers);

         $response=array();

         $response["info"]=$curl_info;
         $response["headers"]=array();
         $response["content"]=$response_body;
         $response["status_code"]=$curl_info["http_code"];

         foreach($response_headers as $response_header)
         {
            if(strlen($response_header)>0)
            {
               $colons_index=strpos($response_header, ":");
               if(!$colons_index) continue;
               if(substr($response_header,$colons_index,2)==": ")
               {
                  $response_header=substr($response_header,0,$colons_index+1).substr($response_header,$colons_index+2);
               }

               $key=substr($response_header,0,$colons_index);
               $value=substr($response_header,$colons_index+1,strlen($response_header));

               // iprint($response_header." => [".$key."] - [".$value."]");
               $response["headers"][]=array("key"=>$key,"value"=>$value);
            } 
         }
      }
      curl_close($this->CURL_HANDLER);
      return $response;
   }
}
?>