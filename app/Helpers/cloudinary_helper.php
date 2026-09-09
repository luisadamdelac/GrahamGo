<?php

if (! function_exists('cloudinary_configured')) {
    /**
     * True once all three Cloudinary env vars are set — until then,
     * uploads fall back to local disk (see save_avatar_upload/
     * save_product_image_upload), so local XAMPP development never needs
     * a Cloudinary account.
     *
     * All-caps underscore names on purpose: unlike CodeIgniter's own
     * Config classes (which fall back to an underscore form
     * automatically — see system/Config/BaseConfig.php's
     * getEnvValue()), the plain env() helper does a literal key lookup
     * with no such fallback. Dotted names (cloudinary.cloudName) turned
     * out not to survive Railway's env var injection into the
     * container at all — only the underscore form does.
     */
    function cloudinary_configured(): bool
    {
        return (bool) env('CLOUDINARY_CLOUD_NAME') && (bool) env('CLOUDINARY_API_KEY') && (bool) env('CLOUDINARY_API_SECRET');
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
        $cloudName = env('CLOUDINARY_CLOUD_NAME');
        $apiKey    = env('CLOUDINARY_API_KEY');
        $apiSecret = env('CLOUDINARY_API_SECRET');

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
