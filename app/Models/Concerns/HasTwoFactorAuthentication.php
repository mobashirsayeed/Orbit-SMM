<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Crypt;
use PragmaRX\TwoFactor\Models\TwoFactorAuthentication;

trait HasTwoFactorAuthentication
{
    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_confirmed_at !== null;
    }

    public function enableTwoFactorAuthentication(): void
    {
        $this->forceFill([
            'two_factor_secret' => Crypt::encryptString($this->generateTwoFactorSecret()),
            'two_factor_recovery_codes' => Crypt::encryptString(json_encode($this->generateRecoveryCodes())),
            'two_factor_confirmed_at' => now(),
        ])->save();
    }

    public function disableTwoFactorAuthentication(): void
    {
        $this->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();
    }

    public function getTwoFactorSecret(): ?string
    {
        return $this->two_factor_secret ? Crypt::decryptString($this->two_factor_secret) : null;
    }

    public function getTwoFactorRecoveryCodes(): array
    {
        return $this->two_factor_recovery_codes 
            ? json_decode(Crypt::decryptString($this->two_factor_recovery_codes), true) 
            : [];
    }

    public function generateTwoFactorSecret(): string
    {
        return base32_encode(random_bytes(16));
    }

    public function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(substr(str_replace('-', '', uuid_create()), 0, 8));
        }
        return $codes;
    }

    public function verifyTwoFactorCode(string $code): bool
    {
        if (!$this->hasTwoFactorEnabled()) {
            return true;
        }

        // Check recovery codes
        $recoveryCodes = $this->getTwoFactorRecoveryCodes();
        if (in_array(strtoupper($code), $recoveryCodes)) {
            $this->removeRecoveryCode($code);
            return true;
        }

        // Check TOTP
        return $this->verifyTOTP($code);
    }

    private function verifyTOTP(string $code): bool
    {
        $secret = $this->getTwoFactorSecret();
        if (!$secret) {
            return false;
        }

        $timestamp = floor(time() / 30);
        
        for ($i = -1; $i <= 1; $i++) {
            $computedCode = $this->generateTOTPCode($secret, $timestamp + $i);
            if (hash_equals($computedCode, $code)) {
                return true;
            }
        }

        return false;
    }

    private function generateTOTPCode(string $secret, int $timestamp): string
    {
        $time = pack('N', $timestamp);
        $time = str_pad($time, 8, chr(0), STR_PAD_LEFT);
        $hmac = hash_hmac('sha1', $time, base32_decode($secret), true);
        $offset = ord(substr($hmac, -1)) & 0x0F;
        $hashpart = substr($hmac, $offset, 4);
        $value = unpack('N', $hashpart)[1];
        $value = $value & 0x7FFFFFFF;
        $modulo = pow(10, 6);
        return str_pad($value % $modulo, 6, '0', STR_PAD_LEFT);
    }

    private function removeRecoveryCode(string $code): void
    {
        $codes = $this->getTwoFactorRecoveryCodes();
        $codes = array_filter($codes, fn($c) => strtoupper($c) !== strtoupper($code));
        
        $this->forceFill([
            'two_factor_recovery_codes' => Crypt::encryptString(json_encode($codes)),
        ])->save();
    }
}

if (!function_exists('base32_encode')) {
    function base32_encode($data) {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $binary = '';
        foreach (str_split($data) as $char) {
            $binary .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }
        $base32 = '';
        foreach (str_split($binary, 5) as $chunk) {
            $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
            $base32 .= $alphabet[bindec($chunk)];
        }
        return rtrim($base32, '=');
    }
}

if (!function_exists('base32_decode')) {
    function base32_decode($data) {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $binary = '';
        foreach (str_split(strtoupper($data)) as $char) {
            $pos = strpos($alphabet, $char);
            if ($pos !== false) {
                $binary .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
            }
        }
        $output = '';
        foreach (str_split($binary, 8) as $chunk) {
            if (strlen($chunk) === 8) {
                $output .= chr(bindec($chunk));
            }
        }
        return $output;
    }
}
