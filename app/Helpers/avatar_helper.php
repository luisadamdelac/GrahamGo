<?php

if (! function_exists('save_avatar_upload')) {
    /**
     * Stores a validated uploaded avatar and returns what to save in the
     * DB `avatar` column. When Cloudinary is configured (see
     * cloudinary_helper.php), that's a full https:// URL — deploy hosts
     * like Railway have an ephemeral filesystem, so anything saved to
     * local disk there is gone on the next redeploy/restart. Without
     * Cloudinary configured (e.g. local XAMPP dev), falls back to moving
     * the file into public/uploads/avatars and returning just the
     * filename, same as before.
     */
    function save_avatar_upload(?\CodeIgniter\HTTP\Files\UploadedFile $file, ?string $oldFilename = null): ?string
    {
        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return null;
        }

        if (cloudinary_configured()) {
            $url = cloudinary_upload_image($file->getTempName(), 'grahamgo/avatars');
            if ($url) {
                return $url;
            }
            // Upload failed (network hiccup, bad credentials, ...) — fall
            // through to local storage rather than losing the file.
        }

        $newName = $file->getRandomName();
        $file->move(FCPATH . 'uploads/avatars', $newName);

        if ($oldFilename && ! str_starts_with($oldFilename, 'http')) {
            $oldPath = FCPATH . 'uploads/avatars/' . $oldFilename;
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }

        return $newName;
    }
}

if (! function_exists('avatar_url')) {
    function avatar_url(?string $filename): ?string
    {
        if (! $filename) {
            return null;
        }

        // Already a full Cloudinary URL — nothing to resolve.
        if (str_starts_with($filename, 'http://') || str_starts_with($filename, 'https://')) {
            return $filename;
        }

        return base_url('uploads/avatars/' . $filename);
    }
}

if (! function_exists('avatar_chip')) {
    /**
     * A small round avatar for list rows (customer/reservation tables) —
     * the uploaded photo if there is one, otherwise a colored circle with
     * the person's initials. The color is picked deterministically from
     * the name so the same person always gets the same color, and
     * different people in the same list get visual variety (the "colorful
     * avatar list" look of the Elegant reference template) without
     * needing a real photo on file.
     */
    function avatar_chip(string $name, ?string $avatarFile = null, int $size = 36): string
    {
        if ($avatarFile) {
            // 2x the on-screen size so it still looks sharp on high-DPI
            // screens, without shipping a multi-hundred-KB original for
            // what's often a 24-36px circle in a list.
            $src = cloudinary_resized(avatar_url($avatarFile), $size * 2);

            return '<img src="' . esc($src) . '" alt="" class="rounded-circle" loading="lazy" '
                . 'style="width:' . $size . 'px;height:' . $size . 'px;object-fit:cover;flex-shrink:0;">';
        }

        $colors = ['--gg-primary-dark', '--gg-info', '--gg-success', '--gg-warning', '--gg-danger', '--gg-secondary'];
        $color  = $colors[crc32($name) % count($colors)];

        $parts    = preg_split('/\s+/', trim($name));
        $initials = strtoupper(mb_substr($parts[0] ?? '', 0, 1) . mb_substr($parts[count($parts) - 1] ?? '', 0, 1));

        return '<div class="rounded-circle d-inline-flex align-items-center justify-content-center text-white fw-semibold flex-shrink-0" '
            . 'style="width:' . $size . 'px;height:' . $size . 'px;background:var(' . $color . ');font-size:' . round($size * .38) . 'px;">'
            . esc($initials) . '</div>';
    }
}
