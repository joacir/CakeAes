# CakeAes
CakePHP plugin to encrypt/decrypt table fields using AES-256 algorithm on MySQL/MariaDB databases.
This branch is for use with CakePHP 5.0+. Use branch "main-cake4" to use with CakePHP 4.0+

## Install
Install it as require dependency:
```
composer require joacir/CakeAes
```

## Setup
Enable the plugin in your Application.php or call
```
bin/cake plugin load CakeAes
```

Configure a new security hash in *app_local.php*:
```
'Security' => [
    'key' => '34601affc03a12d4b963b6123ab3afcb',
],
```
- This will be the key used by the plugin for encryption.
- NEVER use the same key for different apps.
- Once generated, NEVER change the key.
- The key must contain at least 32 digits in hexadecimal.

Change the type field of the fields you want to encrypt on your tables to a binary type:
```
char(20) -> blob
vachar(200) -> varbinary(200)
text -> blob
```
- Only *string* types works. Use only *VarBinary* or *Blob* types.

Load the behavior on your table *initialize()* method:
```
$this->addBehavior('CakeAes.Encrypt', [
    'fields' => ['name', 'card', 'phone']
]);
```

## Usage

### To encrypt fields

Nothing is necessary, the EncryptBehavior do the encryption automatically when you set the *fields* in settings.

### To decrypt fields, conditions, order and contain

The *find()* ou *get()* works without changes, in complex queries you can use *decryptField()* or *decryptString()*.
```
$new = $this->Temps->get($temp->id, ['fields' => [
    'id',
    'name' => $this->Temps->decryptField('Temps.name')
]]);

$temp = $this->Temps->find()
    ->select(['name' => $this->Temps->decryptField('Temps.name')])
    ->where(['id' => 2])
    ->first();
```

You can use *decryptEq()* in *conditions*:
```
$temp = $this->Temps->find()
    ->select(['name' => $this->Temps->decryptField('Temps.name')])
    ->where([$this->Temps->decryptEq('Temps.name', $name)])
    ->first();

$temp = $this->Temps->find()
    ->select([
        'id',
        'name' => $this->Temps->decryptField('Temps.name')
    ])
    ->where([$this->Temps->decryptLike('Temps.name', '%Sa%')])
    ->first();
```

In *updateAll()* you can use *encrypt()* to encrypt:
```
$name = $this->Temps->encrypt("José");
$fields = ['name' => $name];
$conditions = [
    $this->Temps->decryptEq('Temps.name', 'Maria')
];
$this->Temps->updateAll($fields, $conditions);
```

### To encrypt/decrypt a file

```
$imageFile = dirname(__FILE__) . DS . 'imagem.jpg';
$Temps->encryptFile($imageFile);

$imageFile = dirname(__FILE__) . DS . 'imagem_crypted.jpg';
$Temps->decryptFile($imageFile);
```

### To decrypt a file in a controller

Load de Encrypt Component:
```
public function initialize(): void
{
    $this->loadComponent('CakeAes.Encrypt');
}
```

To decrypt and render the content:
```
$imageFile = dirname(__FILE__) . DS . 'imagem_encrypted.jpg';

return $this->Encrypt->decryptRender($imageFile);
```

To decrypt and download a file:
```
$imageFile = dirname(__FILE__) . DS . 'imagem_encrypted.jpg';

return $this->Encrypt->decryptDownload($imageFile);
```
