<?php
$update = json_decode(file_get_contents('php://input'));

// normalize update parts safely
$message = isset($update->message) ? $update->message : null;
$callback_query = isset($update->callback_query) ? $update->callback_query : null;

$chat_id = null;
if ($message && isset($message->chat->id)) {
    $chat_id = $message->chat->id;
} elseif ($callback_query && isset($callback_query->message->chat->id)) {
    $chat_id = $callback_query->message->chat->id;
}

$tc = $message && isset($message->chat->type)
    ? $message->chat->type
    : ($callback_query && isset($callback_query->message->chat->type) ? $callback_query->message->chat->type : null);

$message_id = null;
if ($message && isset($message->message_id)) {
    $message_id = $message->message_id;
} elseif ($callback_query && isset($callback_query->message->message_id)) {
    $message_id = $callback_query->message->message_id;
}

$from_id = null;
if ($message && isset($message->from->id)) {
    $from_id = $message->from->id;
} elseif ($callback_query && isset($callback_query->from->id)) {
    $from_id = $callback_query->from->id;
}

$text = null;
if ($message && isset($message->text)) {
    $text = $message->text;
} elseif ($callback_query && isset($callback_query->data)) {
    $text = $callback_query->data;
}

$textMessage = $message && isset($message->text) ? $message->text : null;

$first_name = $message && isset($message->from->first_name) ? $message->from->first_name : null;
$last_name = $message && isset($message->from->last_name) ? $message->from->last_name : null;
$user_name = $message && isset($message->from->username) ? $message->from->username : null;
$link = $from_id ? "<a href='tg://user?id=$from_id'>$from_id</a>" : null;

$sticker_id = $message && isset($message->sticker->file_id) ? $message->sticker->file_id : null;
$video_id = $message && isset($message->video->file_id) ? $message->video->file_id : null;
$voice_id = $message && isset($message->voice->file_id) ? $message->voice->file_id : null;
$file_id = $message && isset($message->document->file_id) ? $message->document->file_id : null;
$animation_id = $message && isset($message->animation->file_id) ? $message->animation->file_id : null;
$music_id = $message && isset($message->audio->file_id) ? $message->audio->file_id : null;
$photo0_id = $message && isset($message->photo[0]->file_id) ? $message->photo[0]->file_id : null;
$cap = $message && isset($message->caption) ? $message->caption : null;
//--------------------------------------

$reply = $message && isset($message->reply_to_message) ? $message->reply_to_message : null;
$reply_Message_id = $reply && isset($reply->message_id) ? $reply->message_id : null;
$reply_From_id = $reply && isset($reply->from->id) ? $reply->from->id : null;
$reply_First_name = $reply && isset($reply->from->first_name) ? $reply->from->first_name : null;
$reply_Username = $reply && isset($reply->from->username) ? $reply->from->username : null;
$reply_Text = $reply && isset($reply->text) ? $reply->text : null;
//--------------------------------------

if ($callback_query) {
    $data = isset($callback_query->data) ? $callback_query->data : null;
    $chatId = isset($callback_query->message->chat->id) ? $callback_query->message->chat->id : null;
    $fromId = isset($callback_query->from->id) ? $callback_query->from->id : null;
    $messageId = isset($callback_query->message->message_id) ? $callback_query->message->message_id : null;
    $firstName = isset($callback_query->from->first_name) ? $callback_query->from->first_name : null;
    $lastName = isset($callback_query->from->last_name) ? $callback_query->from->last_name : null;
    $username = isset($callback_query->from->username) ? $callback_query->from->username : null;
    $callback_query_id0 = isset($callback_query->id) ? $callback_query->id : null;
}

function bot($method, $datas = [])
{
    $url = "https://api.telegram.org/bot" . API_KEY . "/" . $method;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $datas);
    $res = curl_exec($ch);
    if (curl_error($ch)) {
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            error_log('Curl error: ' . curl_error($ch));
        }
        curl_close($ch);
        return null;
    }
    curl_close($ch);
    return json_decode($res);
}
function answerCallbackQuery($callback_query_id, $text, $show_alert)
{
    return bot('answerCallbackQuery', [
        'callback_query_id' => $callback_query_id,
        'text' => $text,
        'show_alert' => $show_alert
    ]);
}
function SendMessage($chat_id, $text, $mode = null, $reply = null, $keyboard = null)
{
    return bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => $mode,
        'reply_to_message_id' => $reply,
        'reply_markup' => $keyboard,
        'disable_web_page_preview' => true
    ]);
}

function EditMessageText($chat_id, $message_id, $text, $mode = null, $keyboard = null, $disable_web_page_preview = null)
{
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => $text,
        'parse_mode' => $mode,
        'reply_markup' => $keyboard,
        'disable_web_page_preview' => $disable_web_page_preview
    ]);
}

function sendphoto($chat_id, $photo, $caption)
{
    bot('sendPhoto', [
        'chat_id' => $chat_id,
        'photo' => $photo,
        'caption' => $caption,
    ]);
}


function sendAnimation($chat_id, $animation, $caption)
{
    return bot('sendAnimation', [
        'chat_id' => $chat_id,
        'animation' => $animation,
        'caption' => $caption
    ]);
}


function ForwardMessage($chat_id, $from_chat, $message_id)
{
    bot('forwardMessage', [
        'chat_id' => $chat_id,
        'from_chat_id' => $from_chat,
        'message_id' => $message_id
    ]);
}

function SendAudio($chat_id, $audio, $caption = null)
{
    bot('sendAudio', [
        'chat_id' => $chat_id,
        'audio' => $audio,
        'caption' => $caption
    ]);
}
function SendDocument($chat_id, $document, $caption = null, $reply = null)
{
    bot('sendDocument', [
        'chat_id' => $chat_id,
        'document' => $document,
        'caption' => $caption,
        'reply_to_message_id' => $reply
    ]);
}
function SendSticker($chat_id, $sticker, $reply = null)
{
    bot('sendSticker', [
        'chat_id' => $chat_id,
        'sticker' => $sticker,
        'reply_to_message_id' => $reply
    ]);
}
function SendVideo($chat_id, $video, $caption = null, $reply = null)
{
    bot('sendVideo', [
        'chat_id' => $chat_id,
        'video' => $video,
        'caption' => $caption,
        'reply_to_message_id' => $reply
    ]);
}
function SendVoice($chat_id, $voice, $caption = null)
{
    bot('SendVoice', [
        'chat_id' => $chat_id,
        'voice' => $voice,
        'caption' => $caption
    ]);
}