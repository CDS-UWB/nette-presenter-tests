<?php

declare(strict_types=1);

namespace Tests\Traits;

use Cds\NettePresenterTests\Traits\PresenterRequest;
use Cds\NettePresenterTests\Utils\FormSubmitMethod;
use Cds\NettePresenterTests\Utils\Utils;
use Nette\Application\Request;
use Nette\Application\Responses\ForwardResponse;
use Nette\Application\UI\Presenter;
use Nette\Forms\Form;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception as MockException;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

/**
 * Tests for presenter requests.
 *
 * @internal
 */
final class PresenterRequestTest extends TestCase
{
    use PresenterRequest;

    protected Presenter&MockObject $presenter;

    private Form $testForm;

    /**
     * @throws MockException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->presenter = $this->createMock(Presenter::class);

        $this->presenter
            ->method('offsetGet')
            ->willReturnMap([
                ['testForm', $this->testForm = new Form()],
            ])
        ;
    }

    /**
     * Tests the request.
     */
    #[Test]
    public function testRequest(): void
    {
        $this->expectRequest(['action' => 'default']);

        $this->request('default');
    }

    /**
     * Tests the request with parameters.
     */
    #[Test]
    public function testRequestWithParameters(): void
    {
        $this->expectRequest(['action' => 'add', 'id' => 3]);

        $this->request('add', ['id' => 3]);
    }

    /**
     * Tests a form submission request.
     */
    #[Test]
    public function testSubmitForm(): void
    {
        $this->expectRequest(['action' => 'default'], ['_do' => 'testForm-submit', 'key1' => 'value1']);

        $this->testForm->addText('key1');

        $this->submitForm('default', [], [
            'key1' => 'value1',
        ], 'testForm');
    }

    /**
     * Tests a form submission request.
     */
    #[Test]
    public function testSubmitFormWithParameters(): void
    {
        $this->expectRequest(['action' => 'edit', 'id' => 12], ['_do' => 'testForm-submit', 'key1' => 'value1']);

        $this->testForm->addText('key1');

        $this->submitForm('edit', ['id' => 12], [
            'key1' => 'value1',
        ], 'testForm');
    }

    /**
     * Tests a form submission request.
     */
    #[Test]
    public function testSubmitFormMethodGet(): void
    {
        $this->expectRequest(['action' => 'default', '_do' => 'testForm-submit', 'key1' => 'value1']);

        $this->testForm->addText('key1');

        $this->submitForm('default', [], [
            'key1' => 'value1',
        ], 'testForm', method: FormSubmitMethod::Get);
    }

    /**
     * Tests a form submission request.
     */
    #[Test]
    public function testSubmitFormSuccess(): void
    {
        $this->expectRequest(['action' => 'default'], ['_do' => 'testForm-submit', 'key1' => 'value1']);

        $this->testForm->addText('key1');

        $this->submitFormSuccess('default', [], [
            'key1' => 'value1',
        ], 'testForm');
    }

    /**
     * Tests a form submission request.
     */
    #[Test]
    public function testSubmitFormSuccessWithParameters(): void
    {
        $this->expectRequest(['action' => 'open', 'id' => 73], ['_do' => 'testForm-submit', 'key1' => 'values']);

        $this->testForm->addText('key1');

        $this->submitFormSuccess('open', ['id' => 73], [
            'key1' => 'values',
        ], 'testForm');
    }

    /**
     * Tests a form submission request.
     */
    #[Test]
    public function testSubmitFormSuccessMethodGet(): void
    {
        $this->expectRequest(['action' => 'default', '_do' => 'testForm-submit', 'key1' => 'value1']);

        $this->testForm->addText('key1');

        $this->submitFormSuccess('default', [], [
            'key1' => 'value1',
        ], 'testForm', method: FormSubmitMethod::Get);
    }

    /**
     * Tests a form submission request - error result.
     */
    #[Test]
    public function testSubmitFormError(): void
    {
        $this->expectRequest(['action' => 'default'], ['_do' => 'testForm-submit', 'key1' => 'value1']);

        $this->testForm->addText('key1');

        $this->testForm->addError('Error');

        $errors = $this->submitFormError('default', [], [
            'key1' => 'value1',
        ], 'testForm');

        self::assertEquals(['Error'], $errors);
    }

    /**
     * Tests a form submission request - error result.
     */
    #[Test]
    public function testSubmitFormErrorWithParameters(): void
    {
        $this->expectRequest(['action' => 'close', 'id' => 63], ['_do' => 'testForm-submit', 'reason' => 'DD']);

        $this->testForm->addText('reason');

        $this->testForm->addError('Error');

        $errors = $this->submitFormError('close', ['id' => 63], [
            'reason' => 'DD',
        ], 'testForm');

        self::assertEquals(['Error'], $errors);
    }

    /**
     * Tests a form submission request - error result.
     */
    #[Test]
    public function testSubmitFormErrorMethodGet(): void
    {
        $this->expectRequest(['action' => 'default', '_do' => 'testForm-submit', 'key1' => 'value1']);

        $this->testForm->addText('key1');

        $this->testForm->addError('Error');

        $errors = $this->submitFormError('default', [], [
            'key1' => 'value1',
        ], 'testForm', method: FormSubmitMethod::Get);

        self::assertEquals(['Error'], $errors);
    }

    /**
     * Tests handling of a forward response.
     */
    #[Test]
    public function testRunForwardResponse(): void
    {
        $this->expectRequest(['action' => 'default']);

        $fwdResponse = new ForwardResponse(new Request(
            Utils::buildNameFromPresenter(get_class($this->presenter)),
            null,
            ['action' => 'default'],
        ));

        $this->runForwardResponse($fwdResponse);
    }

    /**
     * Tests handling of a forward response - to a different presenter.
     */
    #[Test]
    public function testRunForwardResponseDifferentPresenter(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage("Unable to run request for presenter 'OtherPresenter' on presenter '{$this->presenter->getName()}'");

        $fwdResponse = new ForwardResponse(new Request(
            'OtherPresenter',
            null,
            ['action' => 'default'],
        ));

        $this->runForwardResponse($fwdResponse);
    }

    /**
     * Sets the expectation for the request.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $post
     */
    private function expectRequest(array $parameters, array $post = []): void
    {
        $this->presenter
            ->expects($this->once())
            ->method('run')
            ->with(self::callback(static function (Request $request) use ($parameters, $post) {
                self::assertEquals($parameters, $request->parameters);
                self::assertEquals($post, $request->post);

                return true;
            }))
        ;
    }
}
