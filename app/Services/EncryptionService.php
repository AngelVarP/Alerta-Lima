<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;

/**
 * Servicio de Cifrado para Datos Sensibles
 *
 * CORRECCIÓN: Cifra SOLO datos sensibles (DNI, teléfono, direcciones exactas)
 * NO cifra: descripción de denuncias, títulos, categorías (para permitir búsquedas)
 *
 * Uso:
 * - Cifrar DNI al guardar usuario
 * - Cifrar teléfono si es requerido por ley
 * - NO cifrar campos de búsqueda
 */
class EncryptionService
{
    /**
     * Cifrar DNI para almacenamiento seguro
     * IMPORTANTE: Solo cifrar si el negocio lo requiere, impide búsquedas
     */
    public function encryptDni(?string $dni): ?string
    {
        if (empty($dni)) {
            return null;
        }

        return Crypt::encryptString($dni);
    }

    /**
     * Descifrar DNI
     */
    public function decryptDni(?string $encryptedDni): ?string
    {
        if (empty($encryptedDni)) {
            return null;
        }

        try {
            return Crypt::decryptString($encryptedDni);
        } catch (\Exception $e) {
            // Log error pero no exponer detalles
            \Log::error('Error descifrando DNI', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Cifrar teléfono (opcional, según requerimientos de privacidad)
     */
    public function encryptPhone(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        return Crypt::encryptString($phone);
    }

    /**
     * Descifrar teléfono
     */
    public function decryptPhone(?string $encryptedPhone): ?string
    {
        if (empty($encryptedPhone)) {
            return null;
        }

        try {
            return Crypt::decryptString($encryptedPhone);
        } catch (\Exception $e) {
            \Log::error('Error descifrando teléfono', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Hash de archivo para integridad (SHA-256)
     * Usado para detectar modificaciones de archivos adjuntos
     */
    public function hashFile(string $filePath): string
    {
        return hash_file('sha256', $filePath);
    }

    /**
     * Verificar integridad de archivo
     */
    public function verifyFileIntegrity(string $filePath, string $expectedHash): bool
    {
        $actualHash = $this->hashFile($filePath);

        return hash_equals($expectedHash, $actualHash);
    }

    /**
     * Sanitizar input para prevenir XSS (adicional a la sanitización de Laravel)
     */
    public function sanitizeInput(string $input): string
    {
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }
}
