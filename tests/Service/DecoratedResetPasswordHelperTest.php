<?php

namespace App\Tests\Service;

use PHPUnit\Framework\TestCase;
use App\Service\DecoratedResetPasswordHelper;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

class DecoratedResetPasswordHelperTest extends TestCase {

    public function testConstruct() {

        // create a mock for the ResetPasswordHelperInterface
        $mock = $this->createMock(ResetPasswordHelperInterface::class);

        // create a new instance of DecoratedResetPasswordHelper
        $decoratedResetPasswordHelper = new DecoratedResetPasswordHelper($mock);

        // assert that the instance of DecoratedResetPasswordHelper is not null
        $this->assertNotNull($decoratedResetPasswordHelper);
    }

}
