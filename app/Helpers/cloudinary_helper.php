<?php

if (! function_exists('cloudinary_configured')) {
    /**
     * True once all three Cloudinary env vars are set (cloudinary.cloudName,
     * cloudinary.apiKey, cloudinary.apiSecret) — until then, uploads fall
     * back to local disk (see save_avatar_upload/save_product_image_upload),
     * so local XAMPP development never needs a Cloudinary account.
     */
    function cloudinary_configured(): bool
    {
        return (bool) env('cloudinary.cloudName') && (bool) env('cloudinary.apiKey') && (bool) env('cloudinary.apiSecret');
    }
}

if (! function_exists('cloudinary_upload_image')) {
    /**
     * Uploads a local temp file to Cloudinary via a signed upload (no
     * dashboard "upload preset" setup needed) and returns the resulting
     * secure_url, or null if the upload failed — callers should fall back
     * to local storage in that case rather than losing the file entirely.
     */
    function cloudinary_upload_image(string $localFilePath, string $folder): ?string
    {
        $cloudName = env('cloudinary.cloudName');
        $apiKey    = env('cloudinary.apiKey');
        $apiSecret = env('cloudinary.apiSecret');

        $timestamp    = time();
        $paramsToSign = ['folder' => $folder, 'timestamp' => $timestamp];
        ksort($paramsToSign);

        $toSign = '';
        foreach ($paramsToSign as $key => $value) {
            $toSign .= ($toSign === '' ? '' : '&') . $key . '=' . $value;
        }
        $signature = sha1($toSign . $apiSecret);

        $ch = curl_init("https://api.cloudinary.com/v1_1/{$cloudName}/image/upload");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'file'      => new CURLFile($localFilePath),
            'api_key'   => $apiKey,
            'timestamp' => $timestamp,
            'signature' => $signature,
            'folder'    => $folder,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode !== 200 || ! $response) {
            log_message('error', 'Cloudinary upload failed (HTTP {code}): {error} {response}', [
                'code'     => $httpCode,
                'error'    => $curlError,
                'response' => $response,
            ]);

            return null;
        }

        $data = json_decode($response, true);

        return $data['secure_url'] ?? null;
    }
}
