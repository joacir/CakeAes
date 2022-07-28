<?php
declare(strict_types=1);

namespace CakeAes\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Http\Response;
use Cake\Utility\Security;
use function assert;
use function is_string;

/**
 * Encrypt component
 */
class EncryptComponent extends Component
{
    /**
     * Decrypt file and download
     *
     * @param string $pathFileName Path and name of file
     * @return \Cake\Http\Response file download
     */
    public function decryptDownload(string $pathFileName): Response
    {
        return $this->decryptRender($pathFileName, true);
    }

    /**
     * Decrypt file and render content on browser
     *
     * @param string $pathFileName Path and name of file
     * @param bool $download Do download or not do
     * @return \Cake\Http\Response file content
     */
    public function decryptRender(string $pathFileName, bool $download = false): Response
    {
        $response = $this->getController()
            ->getResponse()
            ->withHeader('Access-Control-Allow-Origin', '*');
        $content = $this->getDecryptedContent($pathFileName);
        if (!empty($content)) {
            $type = $this->getFileType($pathFileName);
            if (!empty($type)) {
                $response = $response->withType($type)
                    ->withStringBody($content);
                if ($download) {
                    $response = $response->withDownload(basename($pathFileName));
                }
            }
        }

        return $response;
    }

    /**
     * Get the decrypted file content
     *
     * @param string $pathFileName Path and name of file
     * @return null|string Decrypted file content
     */
    public function getDecryptedContent(string $pathFileName): ?string
    {
        $decrypted = null;
        if (file_exists($pathFileName)) {
            $content = @file_get_contents($pathFileName);
            if (!empty($content)) {
                $key = Configure::read('Security.key');
                assert(is_string($key));
                $decrypted = Security::decrypt($content, $key);
            }
        }

        return $decrypted;
    }

    /**
     * Get file extension
     *
     * @param string $pathFileName Path and name of file
     * @return null|string File extension
     */
    public function getFileType(string $pathFileName): ?string
    {
        $type = null;
        $fileName = basename($pathFileName);
        if (strpos($fileName, '.') !== false) {
            [$file, $type] = explode('.', strtolower($fileName));
        }

        return $type;
    }
}
