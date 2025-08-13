<!--
مدیریت ربات روبیکا با زبان PHP 
نوشته شده توسظ حیدر الهائی
درباره من : Heidarelhaee.ir
گیت هاب من : github.com/heidarelhaee
گیت هاب این سورس : github.com/heidarelhaee/botrubika
-->

<?php
class BotRubika
{
    private $token;
    private $baseUrl;
    private $timeout;
    private $userAgent;

    public function __construct($token, $timeout = 15, $userAgent = null)
    {
        $this->token = $token;
        $this->baseUrl = "https://botapi.rubika.ir/v3/";
        $this->timeout = $timeout;
        $this->userAgent = $userAgent;
    }

    private function call($method, $payload = [])
    {
        $url = $this->baseUrl . rawurlencode($this->token) . "/" . $method;
        $ch = curl_init($url);
        $headers = ["Content-Type: application/json"];
        $body = json_encode($payload, JSON_UNESCAPED_UNICODE);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_CONNECTTIMEOUT => $this->timeout,
            CURLOPT_TIMEOUT => $this->timeout,
        ]);
        if ($this->userAgent) {
            curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        }
        $resp = curl_exec($ch);
        if ($resp === false) {
            $err = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException("cURL error: " . $err);
        }
        $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        if ($status < 200 || $status >= 300) {
            throw new RuntimeException("HTTP $status: $resp");
        }
        $data = json_decode($resp, true);
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException("JSON decode error: " . json_last_error_msg() . " Body: " . $resp);
        }
        return $data;
    }

    public function getMe() {
        return $this->call("getMe");
    }

    public function sendMessage($chatId, $text, $chatKeypad = null, $chatKeypadType = null, $inlineKeypad = null, $disableNotification = false, $replyToMessageId = null) {
        $payload = [
            "chat_id" => $chatId,
            "text" => $text,
        ];
        if ($chatKeypadType !== null) $payload["chat_keypad_type"] = $chatKeypadType;
        if ($chatKeypad) $payload["chat_keypad"] = $chatKeypad->toArray();
        if ($inlineKeypad) $payload["inline_keypad"] = $inlineKeypad->toArray();
        if ($disableNotification) $payload["disable_notification"] = true;
        if ($replyToMessageId) $payload["reply_to_message_id"] = $replyToMessageId;
        return $this->call("sendMessage", $payload);
    }

    public function sendPoll($chatId, $question, $options) {
        return $this->call("sendPoll", [
            "chat_id" => $chatId,
            "question" => $question,
            "options" => array_values($options),
        ]);
    }

    public function sendLocation($chatId, $latitude, $longitude) {
        return $this->call("sendLocation", [
            "chat_id" => $chatId,
            "latitude" => $latitude,
            "longitude" => $longitude,
        ]);
    }

    public function sendContact($chatId, $firstName, $lastName, $phoneNumber) {
        return $this->call("sendContact", [
            "chat_id" => $chatId,
            "first_name" => $firstName,
            "last_name" => $lastName,
            "phone_number" => $phoneNumber,
        ]);
    }

    public function getChat($chatId) {
        return $this->call("getChat", ["chat_id" => $chatId]);
    }

    public function getUpdates($offsetId = null, $limit = null) {
        $payload = [];
        if ($offsetId !== null) $payload["offset_id"] = $offsetId;
        if ($limit !== null) $payload["limit"] = $limit;
        return $this->call("getUpdates", $payload);
    }

    public function forwardMessage($fromChatId, $messageId, $toChatId, $disableNotification = false) {
        $payload = [
            "from_chat_id" => $fromChatId,
            "message_id" => $messageId,
            "to_chat_id" => $toChatId,
        ];
        if ($disableNotification) $payload["disable_notification"] = true;
        return $this->call("forwardMessage", $payload);
    }

    public function editMessageText($chatId, $messageId, $text) {
        return $this->call("editMessageText", [
            "chat_id" => $chatId,
            "message_id" => $messageId,
            "text" => $text,
        ]);
    }

    public function editInlineKeypad($chatId, $messageId, $inlineKeypad) {
        return $this->call("editInlineKeypad", [
            "chat_id" => $chatId,
            "message_id" => $messageId,
            "inline_keypad" => $inlineKeypad->toArray(),
        ]);
    }

    public function deleteMessage($chatId, $messageId) {
        return $this->call("deleteMessage", [
            "chat_id" => $chatId,
            "message_id" => $messageId,
        ]);
    }

    public function setCommands($botCommands) {
        return $this->call("setCommands", ["bot_commands" => array_values($botCommands)]);
    }

    public function updateBotEndpoints($url, $type) {
        return $this->call("updateBotEndpoints", [
            "url" => $url,
            "type" => $type,
        ]);
    }

    public function editChatKeypad($chatId, $chatKeypadType, $chatKeypad = null) {
        $payload = [
            "chat_id" => $chatId,
            "chat_keypad_type" => $chatKeypadType,
        ];
        if ($chatKeypad) $payload["chat_keypad"] = $chatKeypad->toArray();
        return $this->call("editChatKeypad", $payload);
    }
}

class BotRubika_Keypad {
    private $rows = [];
    private $resizeKeyboard = null;
    private $onTimeKeyboard = null;

    public static function new() { return new self(); }
    public function addRow($row) { $this->rows[] = $row; return $this; }
    public function setResizeKeyboard($v) { $this->resizeKeyboard = $v; return $this; }
    public function setOnTimeKeyboard($v) { $this->onTimeKeyboard = $v; return $this; }

    public function toArray() {
        $rows = array_map(fn($r) => $r->toArray(), $this->rows);
        $out = ["rows" => $rows];
        if (!is_null($this->resizeKeyboard)) $out["resize_keyboard"] = $this->resizeKeyboard;
        if (!is_null($this->onTimeKeyboard)) $out["on_time_keyboard"] = $this->onTimeKeyboard;
        return $out;
    }
}

class BotRubika_KeypadRow {
    private $buttons = [];
    public static function new() { return new self(); }
    public function addButton($button) { $this->buttons[] = $button; return $this; }
    public function toArray() { return ["buttons" => array_map(fn($b) => $b->toArray(), $this->buttons)]; }
}

class BotRubika_Button {
    private $id;
    private $type;
    private $buttonText = null;

    public static function simple($id, $text) {
        $b = new self($id, "Simple");
        $b->buttonText = $text;
        return $b;
    }

    public function __construct($id, $type) {
        $this->id = $id;
        $this->type = $type;
    }

    public function toArray() {
        $arr = [
            "id" => $this->id,
            "type" => $this->type,
        ];
        if (!is_null($this->buttonText)) $arr["button_text"] = $this->buttonText;
        return $arr;
    }
}

class BotRubika_InlineKeypad {
    private $rows = [];
    public static function new() { return new self(); }
    public function addRow($row) { $this->rows[] = $row; return $this; }
    public function toArray() { return ["rows" => array_map(fn($r) => $r->toArray(), $this->rows)]; }
}

class BotRubika_Webhook {
    public static function readJsonBody() {
        $input = file_get_contents('php://input');
        if ($input === false) {
            throw new RuntimeException("Failed to read php://input");
        }
        $decoded = json_decode($input, true);
        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException("Invalid JSON: " . json_last_error_msg());
        }
        return $decoded;
    }

    public static function isReceiveUpdate($payload) {
        return isset($payload['update']);
    }
    public static function isReceiveInlineMessage($payload) {
        return isset($payload['inline_message']);
    }

    public static function getChatIdFromUpdate($payload) {
        if (isset($payload['update']['chat_id'])) {
            return (string)$payload['update']['chat_id'];
        }
        if (isset($payload['inline_message']['chat_id'])) {
            return (string)$payload['inline_message']['chat_id'];
        }
        return null;
    }

    public static function getMessageText($payload) {
        if (isset($payload['update']['new_message']['text'])) {
            return (string)$payload['update']['new_message']['text'];
        }
        if (isset($payload['inline_message']['text'])) {
            return (string)$payload['inline_message']['text'];
        }
        return null;
    }

    public static function getButtonId($payload) {
        $aux = $payload['update']['new_message']['aux_data'] ?? ($payload['inline_message']['aux_data'] ?? null);
        if (isset($aux['button_id'])) {
            return (string)$aux['button_id'];
        }
        return null;
    }
}
?>
