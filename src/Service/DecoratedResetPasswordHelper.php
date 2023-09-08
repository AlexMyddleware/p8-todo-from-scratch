<?php

use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordToken;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;


class DecoratedResetPasswordHelper implements ResetPasswordHelperInterface {
    private $delegate;

    public function __construct(ResetPasswordHelperInterface $helper) {
        $this->delegate = $helper;
    }

    public function generateFakeResetToken() {
        // If you want to override or add behavior, do it here.
        // For now, let's just delegate to the original implementation:
        return $this->delegate->generateFakeResetToken();
    }

    // Delegate other methods similarly:
    
    public function generateResetToken(object $user): ResetPasswordToken {
        return $this->delegate->generateResetToken($user);
    }

    // validateTokenAndFetchUser', 'removeResetRequest', 'getTokenLifetime'
    public function validateTokenAndFetchUser(string $fullToken): object {
        return $this->delegate->validateTokenAndFetchUser($fullToken);
    }

    public function removeResetRequest(string $fullToken): void {
        $this->delegate->removeResetRequest($fullToken);
    }

    public function getTokenLifetime(): int {
        return $this->delegate->getTokenLifetime();
    }
}
