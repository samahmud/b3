<?php
/*
 * Copyright (c) 2025 @NezukoChk0wner
 *
 * This script is licensed under private usage only.
 * Redistribution, modification, or resale of this script is strictly prohibited.
 * You may use this script for personal or educational purposes only.
 * Proper credit must be given to "@NezukoChk0wner".
 *
 * Unauthorized distribution or commercial use will result in legal actions.
 */
$productionKey = "your Bearer or production key";
$tokenizationUrl = "https://payments.braintree-api.com/graphql";
$jsonFile = "bins.json";
$maxConcurrentRequests = 20;

$binsData = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];
$existingBins = array_column($binsData, 'bin');

function getTokenizationPayload($bin) {
    $fakeCardNumber = $bin . str_pad(rand(0, 999999999), 9, '0', STR_PAD_LEFT);
    return json_encode([
        "clientSdkMetadata" => [
            "source" => "client",
            "integration" => "custom",
            "sessionId" => uniqid()
        ],
        "query" => "mutation TokenizeCreditCard(\$input: TokenizeCreditCardInput!) { 
            tokenizeCreditCard(input: \$input) { 
                token 
                creditCard { 
                    bin 
                    brandCode 
                    last4 
                    cardholderName 
                    expirationMonth 
                    expirationYear 
                    binData { 
                        prepaid 
                        healthcare 
                        debit 
                        durbinRegulated 
                        commercial 
                        payroll 
                        issuingBank 
                        countryOfIssuance 
                        productId 
                    } 
                } 
            } 
        }",
        "variables" => [
            "input" => [
                "creditCard" => [
                    "number" => $fakeCardNumber,
                    "expirationMonth" => "12",
                    "expirationYear" => "2026",
                    "cvv" => "123"
                ],
                "options" => ["validate" => false]
            ]
        ],
        "operationName" => "TokenizeCreditCard"
    ]);
}

$mh = curl_multi_init();
$handles = [];
$binQueue = range(300000, 999999);
$activeRequests = 0;

while (!empty($binQueue) || !empty($handles)) {
    while ($activeRequests < $maxConcurrentRequests && !empty($binQueue)) {
        $bin = array_shift($binQueue);
        $binStr = str_pad($bin, 6, '0', STR_PAD_LEFT);
        if (in_array($binStr, $existingBins)) continue;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $tokenizationUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, getTokenizationPayload($binStr));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer $productionKey",
            "Braintree-Version: 2023-01-01"
        ]);

        $handles[(int) $ch] = ['handle' => $ch, 'bin' => $binStr];
        curl_multi_add_handle($mh, $ch);
        $activeRequests++;
    }

    do {
        $status = curl_multi_exec($mh, $running);
    } while ($status == CURLM_CALL_MULTI_PERFORM);

    while ($info = curl_multi_info_read($mh)) {
        $ch = $info['handle'];
        $chId = (int) $ch;

        if (!isset($handles[$chId])) continue;

        $binStr = $handles[$chId]['bin'];
        $response = curl_multi_getcontent($ch);
        curl_multi_remove_handle($mh, $ch);
        unset($handles[$chId]);
        $activeRequests--;

        $binInfo = json_decode($response, true);
        if (isset($binInfo['data']['tokenizeCreditCard']['creditCard'])) {
            $binData = $binInfo['data']['tokenizeCreditCard']['creditCard'];
            $binsData[] = [
                "bin" => $binData['bin'],
                "brand" => $binData['brandCode'],
                "issuingBank" => $binData['binData']['issuingBank'],
                "country" => $binData['binData']['countryOfIssuance'],
                "prepaid" => $binData['binData']['prepaid'],
                "debit" => $binData['binData']['debit'],
                "commercial" => $binData['binData']['commercial'],
                "payroll" => $binData['binData']['payroll'],
                "durbinRegulated" => $binData['binData']['durbinRegulated'],
                "healthcare" => $binData['binData']['healthcare']
            ];
            file_put_contents($jsonFile, json_encode($binsData, JSON_PRETTY_PRINT));
        }
        curl_close($ch);
    }
}

curl_multi_close($mh);

?>
