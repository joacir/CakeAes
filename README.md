# CakeAes
Plugin do CakePHP para criptografia de campos usando AES-256

## Instalação

Inclua o plugin no composer.json do app:
```
    "require": {
        "joacir/cake-aes": "^1.0",
    },
    "autoload": {
        "psr-4": {
            "CakeAes\\": "plugins/CakeAes/src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "CakeAes\\Test\\": "plugins/CakeAes/tests/"
        }
    }

```

Execute o composer dumpautoload:
```
php composer.phar dumpautoload
```

Carregue o plugin no Application.php do app:
```
public function bootstrap(): void
{
    $this->addPlugin('CakeAes');
}
```

## Para gravar/recuperar uma informação criptografada

1 - Configure o hash de segurança no *app_local.php* do app:
```
'Security' => [
    'key' => '34601affc03a12d4b963b6123ab3afcb',
],
```
- Essa será a chave usada pelo plugin para criptografia.
- NUNCA usar a mesma chave para apps diferentes.
- Depois de gerada, NUNCA alterar a chave.
- A chave deve conter no mínimo 32 digitos em hexadecimal.

2 - Altere o tipo de dados dos campos na table do MySQL para binary:
```
char(20) -> blob
vachar(200) -> blob
text -> blob
```

3 - Registre o custom Data Type Aes no *bootstrap.php*:
```
use Cake\Database\TypeFactory;

TypeFactory::map('aes', 'CakeAes\Model\Database\Type\AesType');
```

4 - Inicializa o schema do banco de dados para sobrescrever os tipos binários para os tipos aes_encrypt dentro do seu Table:
```
protected function _initializeSchema(TableSchemaInterface $schema): TableSchemaInterface
{
    $schema->setColumnType('nome', 'aes');

    return $schema;
}
```

5 - Configure o behavior do plugin no Table:
```
$this->addBehavior('CakeAes.Encrypt', [
    'fields' => ['nome', 'cpf']
]);
```
*fields*: só informe campos do tipo "string", todos os campos informados aqui deverão estar na table como "VarBinary" ou "Blob".

## Para criptografar/descriptografar em fields, conditions, order e contain

O funcionamento do *save()* é transparente, ou seja, na maioria das vezes não é preciso fazer nenhuma mudança para funcionar.

- *find()* ou *get()* funciona transparente na maioria dos casos, para casos mais complexos pode ser usado o método *decryptField()* para solicitar a descriptografia no mysql.
- o decryptField pode ser usando na *contain*, no *fields*, no *select*, ou em qualquer lugar que necessite descriptografia.
- Exemplos:
```
$new = $this->Temps->get($temp->id, ['fields' => [
    'id',
    'nome' => $this->Temps->decryptField('Temps.nome')
]]);

$temp = $this->Temps->find()
    ->select(['nome' => $this->Temps->decryptField('Temps.nome')])
    ->where(['id' => 2])
    ->first();
```

- Para *conditions* com campos criptografados, pode usar funções de comparação:
```
$temp = $this->Temps->find()
    ->select(['nome' => $this->Temps->decryptField('Temps.nome')])
    ->where([$this->Temps->decryptEq('Temps.nome', $nome)])
    ->first();

$temp = $this->Temps->find()
    ->select([
        'id',
        'nome' => $this->Temps->decryptField('Temps.nome')
    ])
    ->where([$this->Temps->decryptLike('Temps.nome', '%Sa%')])
    ->first();
```

- *updateAll()* é preciso chamar o método *encrypt()*:
```
$nome = $this->Temps->encrypt("José");
$fields = ['nome' => $nome];
$conditions = [
    $this->Temps->decryptEq('Temps.nome', 'Maria')
];
$this->Temps->updateAll($fields, $conditions);
```

## Para criptografar/descriptografar um arquivo já gravado no disco

1 - Para criptografar um arquivo já gravado no disco:
```
// Use sempre o path + name completo do arquivo, exemplo:
$imageFile = dirname(__FILE__) . DS . 'imagem.jpg';
$Temps->encryptFile($imageFile);
```

2 - Para descriptografar um arquivo criptografado já gravado no disco:
```
// Use sempre o path + name completo do arquivo, exemplo:
$imageFile = dirname(__FILE__) . DS . 'imagem_crypted.jpg';
$Temps->decryptFile($imageFile);
```

### Para descriptografar e exibir o conteúdo de um arquivo criptografado

1 - Configure o component do plugin no Controller:
```
public function initialize(): void
{
    $this->loadComponent('CakeAes.Encrypt');
}
```

2 - Para descriptografar e mostrar o conteúdo do arquivo no browser:
```
// Use sempre o path + name completo do arquivo, exemplo:
$imageFile = dirname(__FILE__) . DS . 'imagem_encrypted.jpg';

return $this->Encrypt->decryptRender($imageFile);
```

3 - Para descriptografar e fazer o download do conteúdo do arquivo no browser:
```
// Use sempre o path + name completo do arquivo, exemplo:
$imageFile = dirname(__FILE__) . DS . 'imagem_encrypted.jpg';

return $this->Encrypt->decryptDownload($imageFile);
```

### Testes

Para testar a visualização e download no app use as urls:

- http://[URL_APP]/cake-aes/encrypt-tests/image

- http://[URL_APP]/cake-aes/encrypt-tests/download

Testes Unitários:

```
phpunit .\plugins\CakeAes\tests\TestCase\Model\Table\TempsTableTest.php
```
