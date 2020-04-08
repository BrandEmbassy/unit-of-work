<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

use Exception;
use Throwable;

final class UnableToProcessOperationException extends Exception
{
    public static function byOther(Throwable $exception): self
    {
        $message = 'Unable to process operation because: ' . $exception->getMessage();

        return new self($message, $exception->getCode(), $exception);
    }
}
