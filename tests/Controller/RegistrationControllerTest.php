<?php declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\User;
use App\Entity\Trick;
use App\Security\EmailVerifier;
use PHPUnit\Framework\TestCase;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\RegistrationController;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Iterator\CsvFileIterator;

class RegistrationControllerTest extends TestCase
{

    public function testItWorks(): void
    {
        self::assertEquals(42, 42);
    }

    // function to test that when a user is instanciated, there is no error
    public function testUserIsInstanciated(): void
    {
        $user = new User();
        self::assertInstanceOf(User::class, $user);
    }

    public function testTrickIsInstanciated(): void
    {
        $user = new Trick();
        self::assertInstanceOf(Trick::class, $user);
    }


    public function testOne(): void
    {
        $this->assertTrue(true);
    }

    // test one point five
    public function testOnePointFive(): void
    {
        $this->assertTrue(true);
    }

    /**
     * @depends testOne
     * @depends testOnePointFive
     */
    public function testTwo(): void
    {
        $this->assertTrue(true);
    }

    /**
     * @dataProvider additionProviderCSV
     * @testdox Test $a + $b = $expected
     */
    public function testAdd(int $a, int $b, int $expected): void
    {
        $this->assertSame($expected, $a + $b);
    }

    // public function additionProvider(): array
    // {
    //     return [
    //         [0, 0, 0],
    //         [0, 1, 1],
    //         [1, 0, 1],
    //         [0, 3, 3]
    //     ];
    // }

    /**
     * @dataProvider additionProviderCSV
     * @testdox Test $a + $b = $expected csv
     */
    // public function additionProviderCSV(): CsvFileIterator
    public function additionProviderCSV(): void
    {
        $iterator = new CsvFileIterator('data.csv');
        dump($iterator);
        // return new CsvFileIterator('data.csv');
    }

}
