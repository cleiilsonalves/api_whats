<?php
 class whatsAppBot{
            //specify instance URL and token
    var $APIurl = 'https://eu28.chat-api.com/instance93157/';
    var $token = '5ra1llp4mj4ajsuw';

    public function __construct(){
    //get the JSON body from the instance
        $json = file_get_contents('php://input');
        $decoded = json_decode($json,true);

        //write parsed JSON-body to the file for debugging
        ob_start();
        var_dump($decoded);
        $input = ob_get_contents();
        ob_end_clean();
        file_put_contents('input_requests.log',$input.PHP_EOL,FILE_APPEND);

            if(isset($decoded['messages'])){
            //check every new message
                foreach($decoded['messages'] as $message){
                //delete excess spaces and split the message on spaces. The first word in the message is a command, other words are parameters
                $text = explode(' ',trim($message['body']));
                //current message shouldn't be send from your bot, because it calls recursion
                    if(!$message['fromMe']){
                    //check what command contains the first word and call the function
                        switch(mb_strtolower($text[0],'UTF-8')){
                        case 'hi':  {$this->welcome($message['chatId'],false); break;}
                            case 'chatid': {$this->showChatId($message['chatId']); break;}
                            case 'time':   {$this->time($message['chatId']); break;}
                            case 'me':     {$this->me($message['chatId'],$message['senderName']); break;}
                            case 'file':   {$this->file($message['chatId'],$text[1]); break;}
                            case 'ptt':     {$this->ptt($message['chatId']); break;}
                            case 'geo':    {$this->geo($message['chatId']); break;}
                            case 'group':  {$this->group($message['author']); break;}
                            default:        {$this->welcome($message['chatId'],true); break;}
                            }
                    }
            }
        }
    }

    //this function calls function sendRequest to send a simple message
    //@param $chatId [string] [required] - the ID of chat where we send a message
    //@param $text [string] [required] - text of the message
    public function welcome($chatId, $noWelcome = false){
        $welcomeString = ($noWelcome) ? "Incorrect command\n" : "WhatsApp Demo Bot PHP\n";
        $this->sendMessage($chatId, 
        $welcomeString.
        "Commands:\n".
        "1. chatid - show ID of the current chat\n".
        "2. time - show server time\n".
        "3. me - show your nickname\n".
        "4. file [format] - get a file. Available formats: doc/gif/jpg/png/pdf/mp3/mp4\n".
        "5. ptt - get a voice message\n".
        "6. geo - get a location\n".
        "7. group - create a group with the bot"
        );
        //return $response;
    }

    //sends Id of the current chat. it is called when the bot gets the command "chatId"
    //@param $chatId [string] [required] - the ID of chat where we send a message
    public function showChatId($chatId){
        $response =  $this->sendMessage($chatId,'ChatID: '.$chatId);
        return $response;

    }
    //sends current server time. it is called when the bot gets the command "time"
    //@param $chatId [string] [required] - the ID of chat where we send a message
    public function time($chatId){
        $response = $this->sendMessage($chatId,date('d.m.Y H:i:s'));
        return $response;
    }
    //sends your nickname. it is called when the bot gets the command "me"
    //@param $chatId [string] [required] - the ID of chat where we send a message
    //@param $name [string] [required] - the "senderName" property of the message
    public function me($chatId,$name){
        $response = $this->sendMessage($chatId,$name);
        return $response;
    
    }
    //sends a file. it is called when the bot gets the command "file"
    //@param $chatId [string] [required] - the ID of chat where we send a message
    //@param $format [string] [required] - file format, from the params in the message body (text[1], etc)
    public function file($chatId,$format){
        $availableFiles = array(
        'doc' => 'document.doc',
        'gif' => 'gifka.gif',
        'jpg' => 'jpgfile.jpg',
        'png' => 'pngfile.png',
        'pdf' => 'presentation.pdf',
        'mp4' => 'video.mp4',
        'mp3' => 'mp3file.mp3'
        );

            if(isset($availableFiles[$format])){
            $data = array(
            'chatId'=>$chatId,
            'body'=>'https://domain.com/PHP/'.$availableFiles[$format],
            'filename'=>$availableFiles[$format],
            'caption'=>'Get your file '.$availableFiles[$format]
            );
            $response = $this->sendRequest('sendFile',$data);

        }
        return $response;
    }

    //sends a voice message. it is called when the bot gets the command "ptt"
    //@param $chatId [string] [required] - the ID of chat where we send a message
    public function ptt($chatId){
        $data = array(
        'audio'=>'https://domain.com/PHP/ptt.ogg',
        'chatId'=>$chatId
        );
        $response= $this->sendRequest('sendAudio',$data);
        return $response;
    }

    //sends a location. it is called when the bot gets the command "geo"
    //@param $chatId [string] [required] - the ID of chat where we send a message
    public function geo($chatId){
        $data = array(
        'lat'=>51.51916,
        'lng'=>-0.139214,
        'address'=>'Ваш адрес',
        'chatId'=>$chatId
        );
        $response=  $this->sendRequest('sendLocation',$data);
        return $response;
    }

    //creates a group. it is called when the bot gets the command "group"
    //@param chatId [string] [required] - the ID of chat where we send a message
    //@param author [string] [required] - "author" property of the message
    public function group($author, $text){
        $phone = str_replace('@c.us','',$author);
        $data = array(
        'groupName'=>'DESENV - IEPTB-BR',
        'phones'=>array($phone),
        'messageText'=> $text
        );
        $response=   $this->sendRequest('group',$data);
        return  $response;
    }

    public function sendMessage($chatId, $text){
        $data = array(
            'phone'=>$chatId,
            'body'=>$text
        );
        $response= $this->sendRequest('message',$data);
        return  $response;
    }

    public function sendRequest($method,$data){
        $url = $this->APIurl.$method.'?token='.$this->token;
        if(is_array($data)){ 
            $data = json_encode($data);
        }
        $options = stream_context_create(['http' => [
        'method'  => 'POST',
        'header'  => 'Content-type: application/json',
        'content' => $data]]);
        $response = file_get_contents($url,false,$options);
        file_put_contents('requests.log',$response.PHP_EOL,FILE_APPEND);
        return  $response; 
        
    }


}
//execute the class when this file is requested by the instance
$a = new whatsAppBot();

$chatId ='5511986586232';
$atuor  ='5511986586232';
$i = 7;
$test='nao e meu numero';
//echo $a->group($atuor, $test);
//echo $a->sendMessage($chatId, $test);
 //echo $a->welcome($chatId);
 // echo $a->geo($chatId);
 echo $a->showChatId($chatId);
                  