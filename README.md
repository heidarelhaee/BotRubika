# BotRubika – PHP Wrapper for Rubika Bot API

**BotRubika** یک کتابخانه‌ی ساده و بدون وابستگی برای کار با **Rubika Bot API** است.  
فقط یک فایل `botrubika.php` دارد که همه‌چیز در آن جمع شده و بدون نصب Composer یا وابستگی دیگر قابل استفاده است.

## ویژگی‌ها
- بدون نیاز به نصب کتابخانه جانبی
- پشتیبانی از متدهای اصلی Bot API روبیکا
- پشتیبانی از **Chat Keypad** و **Inline Keypad**
- کلاس‌های داخلی برای ساخت دکمه‌ها و کیبوردها
- کلاس **Webhook** برای پردازش داده‌های ورودی
- مناسب برای استفاده در پروژه‌های ساده و سریع

## نصب
فقط فایل `botrubika.php` را در پروژه خود قرار دهید و `require` کنید:

```php
require 'botrubika.php';
```

## استفاده سریع

```php
require 'botrubika.php';

// ایجاد شیء BotRubika با توکن
$bot = new BotRubika("YOUR_BOT_TOKEN");

// ارسال پیام ساده
$bot->sendMessage("CHAT_ID", "سلام از BotRubika!");
```

## نمونه ساخت کیبورد
```php
$chatKeypad = BotRubika_Keypad::new()
    ->setResizeKeyboard(true)
    ->addRow(
        BotRubika_KeypadRow::new()->addButton(
            BotRubika_Button::simple("100", "دکمه ۱")
        )
    );

$bot->sendMessage("CHAT_ID", "پیام با کیبورد", $chatKeypad, "New");
```

## پردازش وبهوک
```php
$payload = BotRubika_Webhook::readJsonBody();
$chatId = BotRubika_Webhook::getChatIdFromUpdate($payload);
$text = BotRubika_Webhook::getMessageText($payload);

if ($chatId && $text) {
    $bot->sendMessage($chatId, "پیام شما: " . $text);
}
```

## متدهای موجود
- `getMe()`
- `sendMessage()`
- `sendPoll()`
- `sendLocation()`
- `sendContact()`
- `getChat()`
- `getUpdates()`
- `forwardMessage()`
- `editMessageText()`
- `editInlineKeypad()`
- `deleteMessage()`
- `setCommands()`
- `updateBotEndpoints()`
- `editChatKeypad()`

## نیازمندی‌ها
- **PHP 7.4+**
- اکستنشن‌های `ext-curl` و `ext-json` فعال باشند.

## لایسنس
MIT – استفاده، تغییر و انتشار آزاد است.
