<?php
/*
 * This file is part of the Xcrypto Security package.
 *
 * (c) Acellam Guy <abiccel@yahoo.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Simbacode\Xcrypto;

use Cake\Network\Http\Client;
use Simbacode\Xcrypto\Crypt\RSA;
use Simbacode\Xcrypto\Crypt\AES;
use Cake\Utility\Time;
use Cake\Event\Event;

/**
 * This is main Server encryption class
 *
 * @author Acellam Guy
 * @version 0.0.1
 */
class Server {

    private $AESMessage = "";
    private $PrivateKeyFileString = "";
    private $PublicKeyFile = "";
    private $key = "";
    private $iv = "";

    public function __construct($PRKeyFile = null, $PUKeyFile = null) {

        $this->PrivateKeyFileString = $PRKeyFile;
        $this->PublicKeyFile = $PUKeyFile;
        
    }

    /**
     * The remote user is requesting a public certificate for use to communicate
     * to the server.
     * 
     */
    public function GetPublicKey() {
        if (isset($_POST['getkey'])) {
            echo file_get_contents($this->PublicKeyFile);
            exit;
        }
    }

    /**
     * The remote user is sending an encrypted AES key and iv to use for
     * encryption. Verify the that the keys are genuine using RSA.
     * 
     */
    public function GetKeyIV() {
       
        if (isset($_POST['key']) && isset($_POST['iv']) && !isset($_POST['data'])) {


            $rsa = new RSA();
            $rsa->setEncryptionMode(RSA_ENCRYPTION_PKCS1);
            $rsa->loadKey($this->PrivateKeyFileString);

            $this->key = $this->Base64UrlEncode($rsa->decrypt($this->Base64UrlDecode($_POST['key'])));
            $this->iv = $this->Base64UrlEncode($rsa->decrypt($this->Base64UrlDecode($_POST['iv'])));
            $this->SendEncryptedResponse("AES OK");
        }
    }

    /**
     * The remote user is sending an AES encrypted message. Decrypt it
     * 
     * @return type
     */
    public function GetEncryptedMessage() {
          
        $rsa = new RSA();
        $rsa->setEncryptionMode(RSA_ENCRYPTION_PKCS1);
        $rsa->loadKey($this->PrivateKeyFileString);

        $this->key = $this->Base64UrlEncode($rsa->decrypt($this->Base64UrlDecode($_POST['key'])));
        $this->iv = $this->Base64UrlEncode($rsa->decrypt($this->Base64UrlDecode($_POST['iv'])));


        if ((isset($this->key) && isset($this->iv)) && isset($_POST['data'])) {
            $aes = new AES(AES_MODE_CBC);

            $aes->setKeyLength(256);
            $aes->setKey($this->Base64UrlDecode($this->key));
            $aes->setIV($this->Base64UrlDecode($this->iv));
            $aes->enablePadding(); // This is PKCS

            $this->AESMessage = $aes->decrypt($this->Base64UrlDecode($_POST['data']));

            return $this->AESMessage;
        }
    }

    /**
     * used to URL decode the text
     * 
     * @param type $x
     * @return type
     */
    function Base64UrlDecode($x) {
        return base64_decode(str_replace(array('_', '-'), array('/', '+'), $x));
    }

    /**
     * used to URL encode the text
     * @param type $x
     * @return type
     */
    function Base64UrlEncode($x) {
        return str_replace(array('/', '+'), array('_', '-'), base64_encode($x));
    }

    /**
     * Use this function to save your private key in a php file so that people 
     * cannot download it. The on including the file it is stored in the variable
     * $PrivateRSAKey
     * 
     * @param type $private
     * @param type $file
     * @return string
     */
    function GeneratePHPKeyFile($private, $file) {
        $rsa = file_get_contents($private);
        $rsa = "<?php \$PrivateRSAKey = \"" . $rsa . "\"; ?>";
        file_put_contents($file, $rsa);
        $str = "The private key file has been stored in a php file. After you verify it works, \n" .
                "make sure you DELETE the private key (.key file) and DO NOT UPLOAD the .key file to your server.";
        return $str;
    }

    /**
     *  Encrypts the message to be sent using AES.
     *  Make sure you have not output any other text before or after calling this.
     * 
     * @param String $message The message to be transported online
     */
    function SendEncryptedResponse($message) {
        $aes = new AES(AES_MODE_CBC);

        $aes->setKeyLength(256);
        $aes->setKey($this->Base64UrlDecode($this->key));
        $aes->setIV($this->Base64UrlDecode($this->iv));
        $aes->enablePadding(); // This is PKCS
        echo $this->Base64UrlEncode($aes->encrypt($message));
        exit;
    }

    /**
     * This method is used to listen to encryptived messages from the client and
     * it is also used to verify secure connections from the client. The response
     * sent back to the client will depend on the data provided upon request.
     */
    public function init() {
        $this->GetPublicKey();
        $this->GetKeyIV();
        $this->GetEncryptedMessage();
    }

}
