<?php
declare(strict_types=1);

namespace UserLog\Controller;

use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use UserLog\Controller\AppController;

/**
 * EncryptTests Controller
 *
 * @method \UserLog\Model\Entity\EncryptTest[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class EncryptTestsController extends AppController
{
    public function initialize(): void
    {
        $this->loadComponent('RequestHandler');
        $this->loadComponent('UserLog.Encrypt');
        $this->loadComponent('Authentication.Authentication');
        $this->loadComponent('Authorization.Authorization');
        Configure::write('Security.key', 'f9a73f2770c52dc4e2ce3eec60dc296745a33bfbfd06d1d8a9472de3afb72bc3');
    }

    public function beforeFilter(EventInterface $event) 
    {
        $this->Authentication->allowUnauthenticated(['image', 'download']);
        $this->Authorization->skipAuthorization();
    }

    /**
     * Teste: Mostra imagem no browser
     */
    public function image() 
    {
        $imageFile = ROOT . DS . 'plugins' . DS . 'UserLog' . DS . 'tests' . DS . 'Fixture' . DS . 'imagem_encrypted.jpg';
        
        return $this->Encrypt->decryptRender($imageFile);
    }

    /**
     * Teste: Download de arquivo texto
     */
    public function download() 
    {
        $textFile = ROOT . DS . 'plugins' . DS . 'UserLog' . DS . 'tests' . DS . 'Fixture' . DS . 'texto_encrypted.txt';

        return $this->Encrypt->decryptDownload($textFile);
    }    
}
