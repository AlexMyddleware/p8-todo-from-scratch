<?php

namespace App\Tests\Form;

use App\Form\ChangePasswordFormType;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Validator\Validation;

class ChangePasswordFormTypeTest extends TypeTestCase
{
    protected function getExtensions(): array
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping(true)
            ->addDefaultDoctrineAnnotationReader()
            ->getValidator();

        return [
            new ValidatorExtension($validator),
        ];
    }

    public function testSubmitValidData(): void
    {
        $formData = [
            'first_options' => 'password123',
            'second_options' => 'password123'
        ];

        $form = $this->factory->create(ChangePasswordFormType::class);

        $form->submit($formData);

        // This check ensures there are no transformation failures
        $this->assertTrue($form->isSynchronized());

        // Assert data is mapped correctly
        $viewData = $form->getExtraData();

        $this->assertArrayHasKey('first_options', $viewData);
        $this->assertArrayHasKey('second_options', $viewData);
        $this->assertEquals('password123', $viewData['first_options']);
        $this->assertEquals('password123', $viewData['second_options']);
    }

    public function testCustomFormView(): void
    {
        $view = $this->factory->create(ChangePasswordFormType::class)->createView();

        $this->assertArrayHasKey('plainPassword', $view->children);

        $plainpassword = $view->children['plainPassword'];

        $firstoption = $plainpassword->children['first'];
        $secondoption = $plainpassword->children['second'];
        // asserts that first option has autocomplete attribute set to new-password
        $this->assertArrayHasKey('autocomplete', $firstoption->vars['attr']);
        $this->assertEquals('new-password', $firstoption->vars['attr']['autocomplete']);

        // asserts that second option has autocomplete attribute set to new-password
        $this->assertArrayHasKey('autocomplete', $secondoption->vars['attr']);
        $this->assertEquals('new-password', $secondoption->vars['attr']['autocomplete']);
    }
}
