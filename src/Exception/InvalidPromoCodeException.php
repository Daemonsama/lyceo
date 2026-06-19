<?php

namespace App\Exception;

final class InvalidPromoCodeException extends \RuntimeException
{
    public function __construct(string $code, ?string $detail = null)
    {
        $message = sprintf('Le code promo « %s » est invalide ou non disponible pour ce module.', $code);

        if ($detail !== null) {
            $message .= ' '.$detail;
        }

        parent::__construct($message);
    }
}
