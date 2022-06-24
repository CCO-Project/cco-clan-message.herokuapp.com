<?php
date_default_timezone_set('Asia/Taipei');
$response = new \stdClass;

try {
    $clanId = $_POST["c"];
    $number = (int) $_POST["n"];

    if (!is_int($number) || $number < 32 || $number > 1500) {
        throw new Error("invaild input (n)");
    }

    // Generated by curl-to-PHP: http://incarnate.github.io/curl-to-php/
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "https://mfrgjyclqnoizevhylbh.supabase.in/rest/v1/chat_messages?select=*&channel=in.%28{$clanId}%29&order=time.desc.nullslast&limit={$number}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

    curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

    $headers = array();
    $headers[] = 'Authority: mfrgjyclqnoizevhylbh.supabase.in';
    $headers[] = 'Accept: */*';
    $headers[] = 'Accept-Language: zh-TW,zh;q=0.9,en-US;q=0.8,en;q=0.7';
    $headers[] = 'Accept-Profile: public';
    $headers[] = 'Apikey: eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJyb2xlIjoiYW5vbiIsImlhdCI6MTYxODEyNjQ2NywiZXhwIjoxOTMzNzAyNDY3fQ.9bTzNinSET3aNueYx1z1-gXGuiS55MjSFaGC05w-sek';
    $headers[] = 'Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJyb2xlIjoiYW5vbiIsImlhdCI6MTYxODEyNjQ2NywiZXhwIjoxOTMzNzAyNDY3fQ.9bTzNinSET3aNueYx1z1-gXGuiS55MjSFaGC05w-sek';
    $headers[] = 'Origin: https://cybercodeonline.com';
    $headers[] = 'Referer: https://cybercodeonline.com/';
    $headers[] = 'X-Client-Info: supabase-js/1.35.3';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        throw new Error(curl_error($ch));
    }
    curl_close($ch);

    $response->data = json_decode($result);
    $response->success = true;
} catch (Error $e) {
    $response->success = false;
    $response->message = $e->getMessage();
} catch (Exception $e) {
    $response->success = false;
    $response->message = $e->getMessage();
} finally {
    $response->time = $_SERVER['REQUEST_TIME'];
    echo json_encode($response);
}