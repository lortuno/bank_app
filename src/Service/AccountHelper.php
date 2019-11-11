<?php


namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;


class AccountHelper
{
    /**
     * const integer DEFAULT_STATUS_CODE_ERROR Status http por defecto cuando hay error.
     */
    const DEFAULT_STATUS_CODE_ERROR = 404;

    /**
     * @param \Exception $e
     * @return JsonResponse
     */
    public static function getJsonErrorResponse(\Exception $e)
    {
        $code = ($e->getCode() > 0) ? $e->getCode() : self::DEFAULT_STATUS_CODE_ERROR;

        return new JsonResponse($e->getMessage(), $code );
    }

}