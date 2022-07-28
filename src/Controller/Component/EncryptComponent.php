<?php
declare(strict_types=1);

namespace CakeAes\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Core\Configure;
use Cake\Http\Response;
use Cake\Utility\Security;

/**
 * Encrypt component
 */
class EncryptComponent extends Component
{
    /**
     * Descriptografa arquivo e força download
     *
     * @param string $pathFileName Caminho e nome do arquivo
     * @return Response response configurado para download do arquivo
     */
    public function decryptDownload(string $pathFileName): Response
    {
        return $this->decryptRender($pathFileName, true);
    }

    /**
     * Descriptografa arquivo e renderiza no browser
     *
     * @param string $pathFileName Caminho e nome do arquivo
     * @param bool $download Força ou não o download do arquivo, padrão false
     * @return Response response configurado para rendererização do arquivo
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
     * Retorna o conteúdo do arquivo descriptografado
     *
     * @param string $pathFileName Caminho e nome do arquivo
     * @return null|string conteúdo do arquivo descriptografado
     */
    public function getDecryptedContent(string $pathFileName): ?string
    {
		$decrypted = null;
        if (file_exists($pathFileName)) {
            $content = @file_get_contents($pathFileName);
            if (!empty($content)) {
                /** @var string $key */
                $key = Configure::read('Security.key');
                $decrypted = Security::decrypt($content, $key);
            }
        }

		return $decrypted;
	}

    /**
     * Retorna o tipo do arquivo criptografado
     *
     * @param string $pathFileName Caminho e nome do arquivo
     * @return null|string tipo do arquivo criptografado
     */
    public function getFileType(string $pathFileName): ?string
    {
        $type = null;
        $fileName = basename($pathFileName);
        if (strpos($fileName, '.') !== false) {
            list($file, $type) = explode('.', strtolower($fileName));
        }

        return $type;
    }
}
