<?php

namespace App\Doctrine;

use App\Security\Cifrado;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

/**
 * Columna de texto cifrada de forma transparente (AES-256-GCM) al guardar/leer.
 * El getter/setter de la entidad sigue trabajando en texto plano; solo lo que
 * queda en la base de datos es el valor cifrado.
 *
 * IMPORTANTE: al ser cifrado con IV aleatorio, esta columna NO sirve para
 * WHERE = ni LIKE. Para buscar por este campo hay que usar la columna
 * "_hash" asociada (ver App\Security\Cifrado::hash()).
 */
class EncryptedStringType extends Type
{
    public const NAME = 'encrypted_string';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getVarcharTypeDeclarationSQL(array_merge($column, ['length' => 512]));
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return Cifrado::encrypt($value);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return Cifrado::decrypt($value);
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
