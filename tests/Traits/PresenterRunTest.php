<?php

declare(strict_types=1);

namespace Tests\Traits;

use Cds\NettePresenterTests\Traits\PresenterRun;
use Cds\NettePresenterTests\Utils\FormSubmitMethod;
use Cds\NettePresenterTests\Utils\Utils;
use Nette\Application\Request;
use Nette\Application\Responses\ForwardResponse;
use Nette\Application\UI\Presenter;
use Nette\Forms\Form;
use Nette\Http\FileUpload;
use Nette\InvalidArgumentException;
use Nette\Utils\DateTime;
use Nette\Utils\Html;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception as MockException;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Tests\TestCase;

/**
 * Tests for the presenter run trait.
 *
 * @internal
 */
final class PresenterRunTest extends TestCase
{
    use PresenterRun;

    protected Presenter&MockObject $presenter;

    private Form $testForm;
    private Form $normForm;

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
                ['normForm', $this->normForm = new Form()],
            ])
        ;
    }

    /**
     * Tests running the presenter.
     */
    #[Test]
    public function testRunPresenter(): void
    {
        $this->expectRequest([]);

        $this->runPresenter($this->presenter);
    }

    /**
     * Tests running the presenter with parameters.
     */
    #[Test]
    public function testRunPresenterWithParameters(): void
    {
        $this->expectRequest(['id' => 3]);

        $this->runPresenter($this->presenter, ['id' => 3]);
    }

    /**
     * Tests submitting a form.
     */
    #[Test]
    public function testRunPresenterForm(): void
    {
        $this->expectRequest([], ['_do' => 'testForm-submit', 'key1' => 'value1']);

        $this->testForm->addText('key1');

        $this->runPresenterForm($this->presenter, [], [
            'key1' => 'value1',
        ], 'testForm');
    }

    /**
     * Tests the form submission request.
     */
    #[Test]
    public function testSubmitFormWithParameters(): void
    {
        $this->expectRequest(['id' => 12], ['_do' => 'testForm-submit', 'key1' => 'value1']);

        $this->testForm->addText('key1');

        $this->runPresenterForm($this->presenter, ['id' => 12], [
            'key1' => 'value1',
        ], 'testForm');
    }

    /**
     * Tests submitting a form.
     */
    #[Test]
    public function testRunPresenterFormMethodGet(): void
    {
        $this->expectRequest(['_do' => 'testForm-submit', 'key1' => 'value1']);

        $this->testForm->addText('key1');

        $this->runPresenterForm($this->presenter, [], [
            'key1' => 'value1',
        ], 'testForm', method: FormSubmitMethod::Get);
    }

    /**
     * Tests form submission - detects extra input values not present in the form.
     */
    #[Test]
    public function testRunPresenterFormCheckInputs(): void
    {
        $this->expectRequest(['_do' => 'testForm-submit', 'key1' => 'value1', 'key2' => 'value2']);

        $this->testForm->addText('key1');

        $errors = self::captureErrors(function () {
            $this->runPresenterForm($this->presenter, [], [
                'key1' => 'value1',
                'key2' => 'value2',
            ], 'testForm', method: FormSubmitMethod::Get);
        });

        self::assertEquals([
            [E_USER_NOTICE, "Missing input element for value: 'key2'"],
        ], $errors);
    }

    /**
     * Tests form submission - detects extra input values in nested containers.
     */
    #[Test]
    public function testRunPresenterFormCheckInputsContainer(): void
    {
        $this->expectRequest([
            '_do' => 'testForm-submit',
            'key1' => 'value1',
            'group1' => [
                'key11' => 'value11',
                'key12' => 'value12',
            ],
            'group2' => [
                'group21' => [
                    'key211' => 'value211',
                    'key212' => 'value212',
                ],
            ],
            'key2' => 'value2',
        ]);

        $this->testForm->addText('key1');
        $group1 = $this->testForm->addContainer('group1');
        $group1->addText('key11');

        $group2 = $this->testForm->addContainer('group2');
        $group21 = $group2->addContainer('group21');
        $group21->addText('key211');

        $errors = self::captureErrors(function () {
            $this->runPresenterForm($this->presenter, [], [
                'key1' => 'value1',
                'group1' => [
                    'key11' => 'value11',
                    'key12' => 'value12',
                ],
                'group2' => [
                    'group21' => [
                        'key211' => 'value211',
                        'key212' => 'value212',
                    ],
                ],
                'key2' => 'value2',
            ], 'testForm', method: FormSubmitMethod::Get);
        });

        self::assertEquals([
            [E_USER_NOTICE, "Missing input element for value: 'group1.key12'"],
            [E_USER_NOTICE, "Missing input element for value: 'group2.group21.key212'"],
            [E_USER_NOTICE, "Missing input element for value: 'key2'"],
        ], $errors);
    }

    /**
     * Tests form submission - checks inputs when no container exists for provided data.
     */
    #[Test]
    public function testRunPresenterFormCheckInputsNoContainer(): void
    {
        $this->expectRequest([
            '_do' => 'testForm-submit',
            'key1' => 'value1',
            'group1' => [
                'key11' => 'value11',
            ],
        ]);

        $this->testForm->addText('key1');
        $this->testForm->addText('group1');

        $errors = self::captureErrors(function () {
            $this->runPresenterForm($this->presenter, [], [
                'key1' => 'value1',
                'group1' => [
                    'key11' => 'value11',
                ],
            ], 'testForm', method: FormSubmitMethod::Get);
        });

        self::assertEquals([], $errors);
    }

    /**
     * Tests form submission with a file upload.
     */
    #[Test]
    public function testRunPresenterFormUploadFile(): void
    {
        $this->expectRequest(
            [],
            ['_do' => 'testForm-submit', 'key1' => 'value1'],
            ['file' => ['test.txt', 'Hello World!', UPLOAD_ERR_OK]],
        );

        $this->testForm->addText('key1');
        $this->testForm->addUpload('file');

        $this->runPresenterForm($this->presenter, [], [
            'key1' => 'value1',
            'file' => $this->uploadFile('test.txt', 'Hello World!'),
        ], 'testForm');
    }

    /**
     * Tests form submission with file uploads in containers.
     */
    #[Test]
    public function testRunPresenterFormUploadFileContainers(): void
    {
        $this->expectRequest(
            [],
            ['_do' => 'testForm-submit', 'key1' => 'value1'],
            ['files' => [
                'file1' => ['test1.txt', 'Hello World 1!', UPLOAD_ERR_OK],
                'file2' => ['test2.txt', 'Hello World 2!', UPLOAD_ERR_OK],
            ]],
        );

        $this->testForm->addText('key1');
        $files = $this->testForm->addContainer('files');
        $files->addUpload('file1');
        $files->addUpload('file2');

        $this->runPresenterForm($this->presenter, [], [
            'key1' => 'value1',
            'files' => [
                'file1' => $this->uploadFile('test1.txt', 'Hello World 1!'),
                'file2' => $this->uploadFile('test2.txt', 'Hello World 2!'),
            ],
        ], 'testForm');
    }

    /**
     * Tests form submission with a file upload error.
     */
    #[Test]
    public function testRunPresenterFormUploadFileError(): void
    {
        $this->expectRequest(
            [],
            ['_do' => 'testForm-submit', 'key1' => 'value1'],
            // Unable to upload
            ['file' => [null, null, self::testOldNetteFileUpload() ? UPLOAD_ERR_NO_FILE : UPLOAD_ERR_CANT_WRITE]],
        );

        $this->testForm->addText('key1');
        $this->testForm->addUpload('file');

        $this->runPresenterForm($this->presenter, [], [
            'key1' => 'value1',
            'file' => $this->uploadFile('test.txt', 'Hello World!', UPLOAD_ERR_CANT_WRITE),
        ], 'testForm');
    }

    /**
     * Tests the form submission request (success case).
     */
    #[Test]
    public function testRunPresenterFormSuccess(): void
    {
        $this->expectRequest([], ['_do' => 'testForm-submit', 'key1' => 'value1']);

        $this->testForm->addText('key1');

        $this->runPresenterFormSuccess($this->presenter, [], [
            'key1' => 'value1',
        ], 'testForm');
    }

    /**
     * Tests the form submission request with parameters (success case).
     */
    #[Test]
    public function testRunPresenterFormSuccessWithParameters(): void
    {
        $this->expectRequest(['id' => 73], ['_do' => 'testForm-submit', 'key1' => 'values']);

        $this->testForm->addText('key1');

        $this->runPresenterFormSuccess($this->presenter, ['id' => 73], [
            'key1' => 'values',
        ], 'testForm');
    }

    /**
     * Tests the form submission request (GET method success case).
     */
    #[Test]
    public function testRunPresenterFormSuccessMethodGet(): void
    {
        $this->expectRequest(['_do' => 'testForm-submit', 'key1' => 'value1']);

        $this->testForm->addText('key1');

        $this->runPresenterFormSuccess($this->presenter, [], [
            'key1' => 'value1',
        ], 'testForm', method: FormSubmitMethod::Get);
    }

    /**
     * Tests the form submission request - invalid form.
     */
    #[Test]
    public function testRunPresenterFormSuccessInvalid(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage("Form is not valid:\n  - Error\n");

        $this->expectRequest([], ['_do' => 'testForm-submit', 'key1' => 'value1']);

        $this->testForm->addText('key1');

        $this->testForm->addError('Error');

        $this->runPresenterFormSuccess($this->presenter, [], [
            'key1' => 'value1',
        ], 'testForm');
    }

    /**
     * Tests the form submission request - complex invalid form errors.
     */
    #[Test]
    public function testRunPresenterFormSuccessInvalidComplex(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage(
            <<<'ERROR'
            Form is not valid:
              - Error 0
              - Error 1
              - Error 2
              - Error 3
              - Error 4
            
              [value1]:
                - Error 1
            
              [value2]:
                - Error 2
            
              [group1]:
                - Error 3
                - Error 4
            
                [value2]:
                  - Error 3
            
                [value3]:
                  - Error 4
            ERROR
        );

        $this->expectRequest([], ['_do' => 'testForm-submit', 'key1' => 'value1']);

        $this->testForm->addText('key1');

        $this->testForm->addError('Error 0');

        $this->testForm->addText('value1')->addError('Error 1');
        $this->testForm->addText('value2')->addError('Error 2');
        $this->testForm->addText('value3');
        $formGroup = $this->testForm->addContainer('group1');
        $formGroup->addText('value1');
        $formGroup->addText('value2')->addError('Error 3');
        $formGroup->addText('value3')->addError('Error 4');

        $this->runPresenterFormSuccess($this->presenter, [], [
            'key1' => 'value1',
        ], 'testForm');
    }

    /**
     * Tests the form submission request - error result.
     */
    #[Test]
    public function testRunPresenterFormError(): void
    {
        $this->expectRequest([], ['_do' => 'testForm-submit', 'key1' => 'value1']);

        $this->testForm->addText('key1');

        $this->testForm->addError('Error');

        $errors = $this->runPresenterFormError($this->presenter, [], [
            'key1' => 'value1',
        ], 'testForm');

        self::assertEquals(['Error'], $errors);
    }

    /**
     * Tests the form submission request - error result with parameters.
     */
    #[Test]
    public function testRunPresenterFormErrorWithParameters(): void
    {
        $this->expectRequest(['id' => 63], ['_do' => 'testForm-submit', 'reason' => 'DD']);

        $this->testForm->addText('reason');

        $this->testForm->addError('Error');

        $errors = $this->runPresenterFormError($this->presenter, ['id' => 63], [
            'reason' => 'DD',
        ], 'testForm');

        self::assertEquals(['Error'], $errors);
    }

    /**
     * Tests the form submission request - error result using GET method.
     */
    #[Test]
    public function testRunPresenterFormErrorMethodGet(): void
    {
        $this->expectRequest(['_do' => 'testForm-submit', 'key1' => 'value1']);

        $this->testForm->addText('key1');

        $this->testForm->addError('Error');

        $errors = $this->runPresenterFormError($this->presenter, [], [
            'key1' => 'value1',
        ], 'testForm', method: FormSubmitMethod::Get);

        self::assertEquals(['Error'], $errors);
    }

    /**
     * Tests the form submission request - expects an error when form is valid.
     */
    #[Test]
    public function testRunPresenterFormErrorSuccess(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Form is valid');

        $this->expectRequest([], ['_do' => 'testForm-submit', 'key1' => 'value1']);

        $this->testForm->addText('key1');

        $errors = $this->runPresenterFormError($this->presenter, [], [
            'key1' => 'value1',
        ], 'testForm');

        self::assertEquals(['Error'], $errors);
    }

    /**
     * Tests handling of a ForwardResponse.
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

        $this->runForwardResponseOn($fwdResponse, $this->presenter);
    }

    /**
     * Tests handling of a ForwardResponse that targets a different presenter.
     */
    #[Test]
    #[AllowMockObjectsWithoutExpectations]
    public function testRunForwardResponseDifferentPresenter(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage("Unable to run request for presenter 'OtherPresenter' on presenter '{$this->presenter->getName()}'");

        $fwdResponse = new ForwardResponse(new Request(
            'OtherPresenter',
            null,
            ['action' => 'default'],
        ));

        $this->runForwardResponseOn($fwdResponse, $this->presenter);
    }

    /**
     * Tests normalization of data for POST/GET sending.
     */
    #[Test]
    public function testNormalizePostData(): void
    {
        $this->expectRequest([], [
            '_do' => 'normForm-submit',
            'boolTrue' => '1',
            'boolFalse' => '',
            'intZero' => '0',
            'intValue' => '642',
            'floatZero' => '0',
            'floatValue' => '43.154',
            'text' => 'Hello',
            'null' => '',
            'date' => '2023-03-01T00:00:00',
            'stringable' => '<a></a>',
            'array' => [
                'intValue' => '3',
            ],
        ]);

        // Ignore warnings of missing form inputs.
        @$this->runPresenterForm($this->presenter, [], [
            'boolTrue' => true,
            'boolFalse' => false,
            'intZero' => 0,
            'intValue' => 642,
            'floatZero' => 0.0,
            'floatValue' => 43.154,
            'text' => 'Hello',
            'null' => null,
            'date' => DateTime::fromParts(2023, 3, 1),
            'stringable' => Html::el('a'),
            'array' => [
                'intValue' => 3,
            ],
        ], 'normForm');
    }

    /**
     * Tests normalization of post data - unsupported type.
     */
    #[Test]
    #[AllowMockObjectsWithoutExpectations]
    public function testNormalizePostDataUnsupported(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Value of 'value' cannot be converted to string (type: object)");

        $this->normForm->addText('value');

        $this->runPresenterForm($this->presenter, [], [
            'value' => new stdClass(),
        ], 'normForm');
    }

    /**
     * Sets the expectation on the request.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $post
     * @param array<string, mixed> $files
     */
    private function expectRequest(array $parameters, array $post = [], array $files = []): void
    {
        $this->presenter
            ->expects($this->once())
            ->method('run')
            ->with(self::callback(static function (Request $request) use ($parameters, $post, $files) {
                $reqFiles = self::convertFiles($request->files);

                self::assertEquals($parameters, $request->parameters);
                self::assertEquals($post, $request->post);
                self::assertEquals($files, $reqFiles);

                return true;
            }))
        ;
    }

    /**
     * Converts uploaded files into a format suitable for comparison.
     *
     * @param array<string, mixed> $files
     *
     * @return array<string, mixed>
     */
    private static function convertFiles(array $files): array
    {
        $result = [];

        foreach ($files as $key => $file) {
            if (is_array($file)) {
                $value = self::convertFiles($file);
            } elseif ($file->error === UPLOAD_ERR_OK) {
                $value = [$file->name, $file->contents, $file->error];
            } else {
                $value = [null, null, $file->error];
            }

            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * In Nette Http 3.3.1 the constructor behavior changed. In older versions, when data was missing the
     * error value was set to UPLOAD_ERR_NO_FILE regardless of the provided error; newer versions do not do this.
     */
    private static function testOldNetteFileUpload(): bool
    {
        $testUpload = new FileUpload(['error' => UPLOAD_ERR_NO_TMP_DIR]);

        return $testUpload->error === UPLOAD_ERR_NO_FILE;
    }

    /**
     * Calls the given function and captures reported errors via a custom error handler.
     *
     * @param callable(): void $function
     *
     * @return list<array{int, string}>
     */
    private static function captureErrors(callable $function): array
    {
        $errors = [];
        set_error_handler(static function (int $errno, string $errstr) use (&$errors): bool {
            $errors[] = [$errno, $errstr];

            return true;
        });

        try {
            $function();
        } finally {
            restore_error_handler();
        }

        return $errors;
    }
}
