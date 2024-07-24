<?php

declare(strict_types=1);

namespace ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\Model\WebpaySdk;

class Signer
{
    /** @var string */
    private $privateKey;

    /** @var \OpenSSLAsymmetricKey|null */
    private $privateKeyResource;

    /** @var string */
    private $privateKeyPassword;

    /** @var string */
    private $publicKey;

    /** @var \OpenSSLAsymmetricKey|null */
    private $publicKeyResource;

    public function __construct(string $privateKey, string $privateKeyPassword, string $publicKey)
    {
        if (!file_exists($publicKey) || !is_readable($publicKey)) {
            throw new SignerException("Public key ({$publicKey}) not exists or not readable!");
        }

        $this->privateKey = $privateKey;
        $this->privateKeyPassword = $privateKeyPassword;
        $this->publicKey = $publicKey;
    }

    /**
     * @throws SignerException
     */
    private function getPrivateKeyResource(): \OpenSSLAsymmetricKey
    {
        if ($this->privateKeyResource) {
            return $this->privateKeyResource;
        }

        $privateKeyResource = openssl_pkey_get_private($this->privateKey, $this->privateKeyPassword);
        if (!$privateKeyResource) {
            throw new SignerException("'{$this->privateKey}' is not valid PEM private key (or passphrase is incorrect).");
        }

        $this->privateKeyResource = $privateKeyResource;

        return $this->privateKeyResource;
    }

    public function sign(array $params): string
    {
        $digestText = implode('|', $params);
        openssl_sign($digestText, $digest, $this->getPrivateKeyResource());

        return base64_encode($digest);
    }

    /**
     * @param string $digest
     *
     * @return bool
     *
     * @throws SignerException
     */
    public function verify(array $params, $digest)
    {
        $data = implode('|', $params);
        $digest = base64_decode($digest);
        assert($digest !== false);

        $ok = openssl_verify($data, $digest, $this->getPublicKeyResource());

        if ($ok !== 1) {
            throw new SignerException('Digest is not correct!');
        }

        return true;
    }

    /**
     * @throws SignerException
     */
    private function getPublicKeyResource(): \OpenSSLAsymmetricKey
    {
        if ($this->publicKeyResource) {
            return $this->publicKeyResource;
        }

        $fp = fopen($this->publicKey, 'rb');
        assert($fp !== false);
        $filesize = filesize($this->publicKey);
        assert($filesize !== false);
        $key = fread($fp, $filesize);
        fclose($fp);
        assert($key !== false);

        $publicKeyResource = openssl_pkey_get_public($key);
        if (!$publicKeyResource) {
            throw new SignerException("'{$this->publicKey}' is not valid PEM public key.");
        }
        $this->publicKeyResource = $publicKeyResource;

        return $this->publicKeyResource;
    }
}
