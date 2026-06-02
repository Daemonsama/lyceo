<?php

namespace App\Exception;

final class InvalidPromoCodeException extends \RuntimeException
{
    public function __construct(string $code)
    {
        parent::__construct(sprintf('Le code promo « %s » est invalide ou expiré.', $code));
    }
}
