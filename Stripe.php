<?php
define('BOT_TOKEN', '');
define('API_URL', 'https://api.telegram.org/bot'.BOT_TOKEN.'/');
define('CC_CHECK_API', 'https://test.infinitemsfeed.com/bots/Mod_By_Kamal/stripe.php?lista=');
define('CHECKING_STATUS_FILE', 'checking_status.json');

define('USERS_FILE', 'registered_users.txt');
define('TEMP_FOLDER', 'temp/');

if (!file_exists(TEMP_FOLDER)) {
    mkdir(TEMP_FOLDER, 0777, true);
}

function getUpdates() {
    $update = json_decode(file_get_contents('php://input'), true);
    return $update;
}

function sendMessage($chat_id, $text, $reply_to = null) {
    $data = [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => 'HTML'
    ];
    if ($reply_to) {
        $data['reply_to_message_id'] = $reply_to;
    }
    return apiRequest('sendMessage', $data);
}

function editMessage($chat_id, $message_id, $text) {
    return apiRequest('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => $text,
        'parse_mode' => 'HTML'
    ]);
}

function sendDocument($chat_id, $file_path, $caption = '') {
    $data = [
        'chat_id' => $chat_id,
        'caption' => $caption,
        'document' => new CURLFile($file_path)
    ];
    return apiRequest('sendDocument', $data);
}

function apiRequest($method, $data) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, API_URL . $method);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    $result = curl_exec($curl);
    curl_close($curl);
    return json_decode($result, true);
}


function isRegistered($user_id) {
    if (!file_exists(USERS_FILE)) return false;
    $users = file_get_contents(USERS_FILE);
    return strpos($users, "$user_id\n") !== false;
}


function registerUser($user_id) {
    file_put_contents(USERS_FILE, "$user_id\n", FILE_APPEND);
}


function getAllUsers() {
    if (!file_exists(USERS_FILE)) return [];
    return array_filter(explode("\n", file_get_contents(USERS_FILE)));
}


function isCheckingInProgress($chat_id) {
    if (!file_exists(CHECKING_STATUS_FILE)) return false;
    $status = json_decode(file_get_contents(CHECKING_STATUS_FILE), true) ?? [];
    return isset($status[$chat_id]) && $status[$chat_id]['checking'] &&
           (time() - $status[$chat_id]['timestamp'] < 3600); // 1 hour timeout
}

function setCheckingStatus($chat_id, $checking = true) {
    $status = json_decode(file_get_contents(CHECKING_STATUS_FILE), true) ?? [];
    if ($checking) {
        $status[$chat_id] = ['checking' => true, 'timestamp' => time()];
    } else {
        unset($status[$chat_id]);
    }
    file_put_contents(CHECKING_STATUS_FILE, json_encode($status));
}


function checkCC($cc_list, $chat_id, $message_id) {
    if (isCheckingInProgress($chat_id)) {
        editMessage($chat_id, $message_id, "⚠️ A checking session is already in progress. Please wait for it to finish.");
        return;
    }


    setCheckingStatus($chat_id, true);

    $total = count($cc_list);
    $processed = 0;
    $lives = 0;
    $live_results = [];

    try {
        $temp_file = TEMP_FOLDER . 'live_' . time() . '.txt';

        foreach ($cc_list as $cc) {
            $processed++;

            $progress = "⌬ 𝙏𝙤𝙩𝙖𝙡 𝘾𝙖𝙧𝙙𝙨: $total\n⌬ 𝙋𝙧𝙤𝙘𝙚𝙨𝙨𝙞𝙣𝙜: $processed/$total\n⌬ 𝙇𝙞𝙫𝙚𝙨: $lives";
            editMessage($chat_id, $message_id, $progress);

            $response = file_get_contents(CC_CHECK_API . urlencode(trim($cc)));
            $result = json_decode($response, true);

            if (strpos($result['Status'] ?? '', '✅') !== false) {
                $lives++;
                file_put_contents($temp_file, $response . "\n", FILE_APPEND);
            }

            sleep(2);
        }

        if ($lives > 0) {
            sendDocument($chat_id, $temp_file, "Found $lives live cards");
        } else {
            sendMessage($chat_id, "No live cards found.");
        }

        if (file_exists($temp_file)) {
            unlink($temp_file);
        }
    } catch (Exception $e) {
        sendMessage($chat_id, "❌ Error during checking: " . $e->getMessage());
    } finally {
        setCheckingStatus($chat_id, false);
    }
}

$update = getUpdates();

if (isset($update['message'])) {
    $message = $update['message'];
    $chat_id = $message['chat']['id'];
    $user_id = $message['from']['id'];
    $text = $message['text'] ?? '';

    if (strpos($text, '/start') === 0) {
        $response = "Welcome to CC Checker Bot!\n\nCommands:\n/register - Register to use the bot\n/txt - Reply to a CC list file to check cards\n/broadcast - Send message to all users (Admin only)";
        sendMessage($chat_id, $response);
    }

    elseif (strpos($text, '/register') === 0) {
        if (!isRegistered($user_id)) {
            registerUser($user_id);
            sendMessage($chat_id, "✅ Registration successful! You can now use the bot.");
        } else {
            sendMessage($chat_id, "You are already registered!");
        }
    }


    elseif (strpos($text, '/broadcast') === 0) {
        if ($user_id == 'ADMIN_USER_ID') {
            $broadcast_msg = substr($text, 10);
            $users = getAllUsers();
            foreach ($users as $user) {
                sendMessage($user, $broadcast_msg);
            }
            sendMessage($chat_id, "Broadcast sent to " . count($users) . " users.");
        }
    }

    elseif (strpos($text, '/txt') === 0) {
        if (!isRegistered($user_id)) {
            sendMessage($chat_id, "⚠️ Please register first using /register");
            return;
        }

        if (isset($message['reply_to_message']['document'])) {
            if (isCheckingInProgress($chat_id)) {
                sendMessage($chat_id, "⚠️ A checking session is already in progress. Please wait for it to finish.");
                return;
            }

            $file_id = $message['reply_to_message']['document']['file_id'];
            $file_info = apiRequest('getFile', ['file_id' => $file_id]);
            $file_path = $file_info['result']['file_path'];
            $file_content = file_get_contents('https://api.telegram.org/file/bot'.BOT_TOKEN.'/'.$file_path);

            $cc_list = array_filter(explode("\n", $file_content));

            $status_msg = sendMessage($chat_id, "Starting CC check...");
            checkCC($cc_list, $chat_id, $status_msg['result']['message_id']);
        } else {
            sendMessage($chat_id, "⚠️ Please reply to a file containing CC list");
        }
    }
}
?>
