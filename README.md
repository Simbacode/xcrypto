Xcrypto
========================

This an Encryption Library for cakephp.It was inspired by 
http://www.codeproject.com/Articles/223081/Encrypting-Communication-between-Csharp-and-PHP

## Requirements ##
* PHP 5.4.16+
* curl enabled
* cakePHP 3.0+


## Encryption Algorithms ##
* AES
* DES
* Hash
* RC4
* RSA
* Random
* Rijndael
* TripleDES 


## How to use ##
This a composer cakePHP library and hence it is easy to use.

### Using Composer ###

```
require: "simbacode/xcrypto": "dev-master"
```
### Git Clone ###
You can as well clone the library in your cakePHP vendor folder but remember to add this library to cakePHP autorun.
```
git clone https://github.com/Simbacode/xcrypto
```

##Code Example ##
```php
    $PrivateKeyFile = APP . "Controller".DS."Encryption".DS."private.php";
    $PublicKeyFile = APP . "Controller".DS."Encryption".DS."public.crt";

    include $PrivateKeyFile;
    $server = new Server($PrivateRSAKey, $PublicKeyFile);
    
    // listen to encryptived messages from the client and verify secure connections from the client.
    // It verifies the public key, the AES key, AES IV and then sends AES OK to affirm this
    $server->init();
    //used to retrieve any sent encrypted messages
    $data = $server->GetDecryptedAESMessage();
```
