<?php

declare(strict_types=1);

namespace MangoSylius\SyliusGPWebpayPaymentGatewayPlugin\Model\WebpaySdk;

class Signer
{
	/** @var string */
	private $privateKey;

	/** @var resource|null */
	private $privateKeyResource;

	/** @var string */
	private $privateKeyPassword;

	/** @var string */
	private $publicKey;

	/** @var resource|null */
	private $publicKeyResource;

	public function __construct(string $privateKey, string $privateKeyPassword, string $publicKey)
	{
		if (!file_exists($privateKey) || !is_readable($privateKey)) {
			throw new SignerException("Private key ({$privateKey}) not exists or not readable!");
		}

		if (!file_exists($publicKey) || !is_readable($publicKey)) {
			throw new SignerException("Public key ({$publicKey}) not exists or not readable!");
		}

		$this->privateKey = $privateKey;
		$this->privateKeyPassword = $privateKeyPassword;
		$this->publicKey = $publicKey;
	}

	/**
	 * @return resource
	 *
	 * @throws SignerException
	 */
	private function getPrivateKeyResource()
	{
		if ($this->privateKeyResource) {
			return $this->privateKeyResource;
		}

		$key = file_get_contents($this->privateKey);
		assert($key !== false);

		$privateKeyResource = openssl_pkey_get_private($key, $this->privateKeyPassword);
		if (!$privateKeyResource) {
			throw new SignerException("'{$this->privateKey}' is not valid PEM private key (or passphrase is incorrect).");
		}

		$this->privateKeyResource = $privateKeyResource;

		return $this->privateKeyResource;
	}

	/**
	 * @param array $params
	 *
	 * @return string
	 */
	public function sign(array $params): string
	{
		$digestText = implode('|', $params);
		openssl_sign($digestText, $digest, $this->getPrivateKeyResource());
		$digest = base64_encode($digest);

		return $digest;
	}

	/**
	 * @param array $params
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
	 * @return resource
	 *
	 * @throws SignerException
	 */
	private function getPublicKeyResource()
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
