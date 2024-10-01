<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Response;

use OxidSolutionCatalysts\TeleCash\IPG\API\AbstractResponse;

/**
 * Class Action
 */
abstract class Action extends AbstractResponse
{

    /**
     * @var bool $wasSuccessful
     */
    protected bool $wasSuccessful = false;

    /**
     * @var string $errorMessage
     */
    protected string $errorMessage = '';

    /**
     * @return bool
     */
    public function wasSuccessful(): bool
    {
        return $this->wasSuccessful;
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }
}
