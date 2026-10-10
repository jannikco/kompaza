<?php

namespace App\Services\Crypto;

/**
 * Thrown when a secret cannot be encrypted or decrypted.
 *
 * Deliberately carries no ciphertext or key material in its message — these are
 * logged, and a log line is not a safe place for either.
 */
class DecryptionException extends \RuntimeException
{
}
