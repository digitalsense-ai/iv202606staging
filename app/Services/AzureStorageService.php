<?php

namespace App\Services;

use DateTime;
use DateTimeZone;
use Exception;
use Illuminate\Support\Facades\Storage;

use MicrosoftAzure\Storage\Blob\BlobSharedAccessSignatureHelper;
use MicrosoftAzure\Storage\Common\Internal\Resources;

class AzureStorageService
{
    protected string $account;
    protected string $key;
    protected string $container;
    protected string $sv; // SAS version

    public function __construct()
    {
        $this->account   = config('filesystems.disks.azure.name');
        $this->key       = config('filesystems.disks.azure.key');
        $this->container = config('filesystems.disks.azure.container');
        $this->sv        = '2024-11-04'; // SAS version matching portal
    }

    /**
     * Upload a file to Azure Blob Storage
     *
     * @param string $localPath Absolute local file path
     * @param string $remotePath Relative path in container (e.g., "invoices/file.pdf")
     * @return void
     */
    public function uploadFile(string $localPath, string $remotePath): string
    {        
        if (!file_exists($localPath)) {
            throw new Exception("File does not exist: " . $localPath);
        }

        $disk = Storage::disk('azure');

        if ($disk->exists($remotePath)) {
            $info = pathinfo($remotePath);
            $baseName = preg_replace(
                '/_\d{8}_\d{6}_[a-zA-Z0-9]{4}$/',
                '',
                $info['filename']
            );
            $timestamp = date('Ymd_His') . '_' . substr(uniqid(), -4);

            $dirname = $info['dirname'] !== '.' ? $info['dirname'] . '/' : '';
            $extension = isset($info['extension']) ? '.' . $info['extension'] : '';

            $remotePath = $dirname . $baseName . '_' . $timestamp . $extension;
        }

        $stream = fopen($localPath, 'r');

        if (!$stream) {
            throw new Exception("Unable to open file: " . $localPath);
        }

        try {
            if (!$disk->put($remotePath, $stream)) {
                throw new Exception("Failed to upload file to Azure: " . $remotePath);
            }
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        return $remotePath;
    }
    // public function uploadFile(string $localPath, string $remotePath): string
    // {        
    //     if (!file_exists($localPath)) {
    //         throw new Exception("File does not exist: " . $localPath);
    //     }

    //     $stream = fopen($localPath, 'r');

    //     Storage::disk('azure')->put($remotePath, $stream);

    //     if (is_resource($stream)) {
    //         fclose($stream);
    //     }

    //     return $remotePath;
    // }

    /**
     * Generate a read-only SAS URL for a blob
     *
     * @param string $blobPath Relative path in container (no leading slash)
     * @param int $minutes Validity duration in minutes
     * @return string
     * @throws Exception
     */
    public function generateSasUrl(string $blobPath, int $hours = 1): string
    {       
        $sasHelper = new BlobSharedAccessSignatureHelper($this->account, $this->key);

        $expiry = now()->addHours($hours);

        // Generate the token
        $sasToken = $sasHelper->generateBlobServiceSharedAccessSignatureToken(
            Resources::RESOURCE_TYPE_BLOB, // 'b' for blob
            "$this->container/$blobPath",
            'r',                           // 'r' for Read, 'w' for Write
            $expiry // Expiry
        );

        $fullUrl = "https://{$this->account}.blob.core.windows.net/{$this->container}/{$blobPath}?{$sasToken}";

        return $fullUrl;
    }

    public function deleteFile(string $blobPath): bool
    {        
        if (Storage::disk('azure')->exists($blobPath)) {         
            $result =  Storage::disk('azure')->delete($blobPath);
            return $result;
        }

        return false;
    }

    public function checkFile(string $remotePath): bool
    {                
        $disk = Storage::disk('azure');

        if ($disk->exists($remotePath))
            return true;

        return false;
    }
}
    
