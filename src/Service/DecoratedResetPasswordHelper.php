<?php

namespace App\Service;

use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordToken;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;



class DecoratedResetPasswordHelper implements ResetPasswordHelperInterface {
    private $delegate;

    public function __construct(ResetPasswordHelperInterface $helper) {
        $this->delegate = $helper;
    }

    /**
    * @codeCoverageIgnore
    */
    public function generateFakeResetToken() {
        // If you want to override or add behavior, do it here.
        // For now, let's just delegate to the original implementation:
        return $this->delegate->generateFakeResetToken();
    }

    /**
    * @codeCoverageIgnore
    */
    public function generateResetToken(object $user): ResetPasswordToken {
        return $this->delegate->generateResetToken($user);
    }

    /**
    * @codeCoverageIgnore
    */
    public function validateTokenAndFetchUser(string $fullToken): object {
        return $this->delegate->validateTokenAndFetchUser($fullToken);
    }

    /**
    * @codeCoverageIgnore
    */
    public function removeResetRequest(string $fullToken): void {
        $this->delegate->removeResetRequest($fullToken);
    }

    /**
    * @codeCoverageIgnore
    */
    public function getTokenLifetime(): int {
        return $this->delegate->getTokenLifetime();
    }
}
