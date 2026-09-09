<?php

if (! function_exists('save_product_image_upload')) {
    /**
     * Stores a validated uploaded product photo and returns what to save
     * in the DB `image` column. Same Cloudinary-first, local-fallback
     * behavior as save_avatar_upload() — see avatar_helper.php for why.
     */
    function save_product_image_upload(?\CodeIgniter\HTTP\Files\UploadedFile $file, ?string $oldFilename = null): ?string
    {
        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return null;
        }

        if (cloudinary_configured()) {
            $url = cloudinary_upload_image($file->getTempName(), 'grahamgo/products');
            if ($url) {
                return $url;
            }
        }

        $newName = $file->getRandomName();
        $file->move(FCPATH . 'uploads/products', $newName);

        if ($oldFilename && ! str_starts_with($oldFilename, 'http')) {
            $oldPath = FCPATH . 'uploads/products/' . $oldFilename;
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }

        return $newName;
    }
}

if (! function_exists('product_image_url')) {
    function product_image_url(?string $filename): ?string
    {
        if (! $filename) {
            return null;
        }

        if (str_starts_with($filename, 'http://') || str_starts_with($filename, 'https://')) {
            return $filename;
        }

        return base_url('uploads/products/' . $filename);
    }
}

if (! function_exists('product_chip')) {
    /**
     * A small rounded thumbnail for product list rows — the uploaded
     * photo if there is one, otherwise a themed icon square (same solid-
     * badge treatment as the dashboard's stat icons) so every row still
     * has a visual anchor instead of leaving blank space.
     */
    function product_chip(?string $imageFile, int $size = 36): string
    {
        if ($imageFile) {
            return '<img src="' . esc(product_image_url($imageFile)) . '" alt="" class="rounded" '
                . 'style="width:' . $size . 'px;height:' . $size . 'px;object-fit:cover;flex-shrink:0;">';
        }

        return '<div class="rounded d-inline-flex align-items-center justify-content-center text-white flex-shrink-0" '
            . 'style="width:' . $size . 'px;height:' . $size . 'px;background:var(--gg-secondary);font-size:' . round($size * .5) . 'px;">'
            . '<i class="bi bi-box-seam-fill"></i></div>';
    }
}
