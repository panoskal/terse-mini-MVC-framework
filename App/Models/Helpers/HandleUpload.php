<?php

/**
 * HandleUpload model
 *
 * PHP version 7.4
 */

namespace App\Models\Helpers;

class HandleUpload
{

    private static $uploadError;

    /**
     * [[Description]]
     * @param  [[Type]] iterable $file     [[Description]]
     * @param  [[Type]] string $field      [[Description]]
     * @param  [[Type]] int $applicationId [[Description]]
     * @param  [[Type]] string $newName    [[Description]]
     * @return string [[Description]]
     */
    public static function storeFile(iterable $file, string $field, string $newName)
    {
        try {
            // Undefined | Multiple Files | $_FILES Corruption Attack
            // If this request falls under any of them, treat it invalid.
            if (
                !isset($file['error']) ||
                is_array($file['error'])
            ) {
                throw new \RuntimeException("Invalid values in field");
            }

            // Check $file['error'] value.
            switch ($file['error']) {
                case UPLOAD_ERR_OK:
                    break;
                case UPLOAD_ERR_NO_FILE:
                    throw new \RuntimeException("File is required");
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    throw new \RuntimeException("File is too large");
                default:
                    throw new \RuntimeException("The was an error while uploading file");
            }

            // You should also check filesize here.
            if ($file['size'] > 5242880) {
                throw new \RuntimeException("File is larger than 5ΜΒ");
            }

            // DO NOT TRUST $file['mime'] VALUE !!
            // Check MIME Type by yourself.
            $finfo = new \finfo(FILEINFO_MIME_TYPE);

            if (false === $ext = array_search(
                $finfo->file($file['tmp_name']),
                [
                    'jpg'   => 'image/jpeg',
                    'jpeg'  => 'image/jpeg',
                    'png'   => 'image/png',
                ],
                true
            )) {
                throw new \RuntimeException("The file must be a jpeg, jpg or png");
            }

            // You should name it uniquely.
            // DO NOT USE $file['name'] WITHOUT ANY VALIDATION !!
            // On this example, obtain safe unique name from its binary data.

            $path = MEDIAS_FOLDER;
            $hashedName = sprintf( "%s.%s", hash('md5', $newName), $ext );

            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            if (!move_uploaded_file( $file['tmp_name'], sprintf( "$path%s", $hashedName))) {
                throw new \RuntimeException("Παρουσιάστηκε κάποιο πρόβλημα στην επισύναψη του αρχείου για το πεδίο '" . constant(strtoupper($field)) . "'");
            }

            return $hashedName;

        } catch (\RuntimeException $e) {
            self::$uploadError = $e->getMessage();
            return self::$uploadError;
        }

    }

}
