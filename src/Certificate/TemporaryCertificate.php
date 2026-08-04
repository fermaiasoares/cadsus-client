<?php

declare(strict_types=1);

namespace SolucaoInternet\Cadsus\Certificate;

use SolucaoInternet\Cadsus\Exceptions\CadsusException;

final class TemporaryCertificate
{
    private function __construct(public readonly string $path) {}

    public static function create(string $contents, ?string $password): self
    {
        if (!str_contains($contents, '-----BEGIN')) {
            $parts = [];
            if (!openssl_pkcs12_read($contents, $parts, $password ?? '')) {
                return self::fromLegacyPkcs12($contents, $password ?? '');
            }
            $contents = ($parts['cert'] ?? '') . "\n" . ($parts['pkey'] ?? '') . "\n" . implode("\n", $parts['extracerts'] ?? []);
        }
        $path = tempnam(sys_get_temp_dir(), 'cadsus-');
        if ($path === false || file_put_contents($path, $contents, LOCK_EX) === false || !chmod($path, 0600)) {
            if (is_string($path) && is_file($path)) {
                @unlink($path);
            }
            throw new CadsusException('Não foi possível preparar o certificado digital.', 'certificate');
        }
        return new self($path);
    }

    private static function fromLegacyPkcs12(string $contents, string $password): self
    {
        if (!function_exists('proc_open')) {
            throw new CadsusException('O certificado usa criptografia legada não suportada pelo PHP.', 'certificate');
        }
        $input = tempnam(sys_get_temp_dir(), 'cadsus-pfx-');
        $output = tempnam(sys_get_temp_dir(), 'cadsus-pem-');
        if ($input === false || $output === false) {
            throw new CadsusException('Não foi possível preparar o certificado digital.', 'certificate');
        }
        chmod($input, 0600);
        chmod($output, 0600);
        $success = false;
        try {
            if (file_put_contents($input, $contents, LOCK_EX) === false) {
                throw new CadsusException('Não foi possível preparar o certificado digital.', 'certificate');
            }
            $pipes = [];
            $process = proc_open(
                ['openssl', 'pkcs12', '-legacy', '-in', $input, '-passin', 'stdin', '-nodes', '-out', $output],
                [['pipe', 'r'], ['file', '/dev/null', 'w'], ['pipe', 'w']],
                $pipes,
            );
            if (!is_resource($process)) {
                throw new CadsusException('Não foi possível processar o certificado digital.', 'certificate');
            }
            fwrite($pipes[0], $password . "\n");
            fclose($pipes[0]);
            stream_get_contents($pipes[2]);
            fclose($pipes[2]);
            if (proc_close($process) !== 0 || filesize($output) === 0) {
                throw new CadsusException('Certificado digital ou senha inválidos.', 'certificate');
            }
            $success = true;
            return new self($output);
        } finally {
            if (is_file($input)) {
                @unlink($input);
            }
            if (!$success && is_file($output)) {
                @unlink($output);
            }
        }
    }

    public function remove(): void
    {
        if (is_file($this->path) && !unlink($this->path)) {
            throw new CadsusException('Não foi possível remover o certificado temporário.', 'certificate');
        }
    }
}
