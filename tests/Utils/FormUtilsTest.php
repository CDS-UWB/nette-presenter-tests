<?php

declare(strict_types=1);

namespace Tests\Utils;

use Cds\NettePresenterTests\Utils\FormUtils;
use Nette\Forms\Form;
use Nette\Http\FileUpload;
use Nette\InvalidArgumentException;
use Nette\Utils\DateTime;
use Nette\Utils\Html;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use stdClass;
use Tests\TestCase;

/**
 * @internal
 */
#[CoversClass(FormUtils::class)]
final class FormUtilsTest extends TestCase
{
    /**
     * Data normalization test.
     */
    #[Test]
    public function normalizePostData(): void
    {
        $result = FormUtils::normalizePostData([
            'boolTrue' => true,
            'boolFalse' => false,
            'intZero' => 0,
            'intValue' => 642,
            'floatZero' => 0.0,
            'floatValue' => 43.154,
            'text' => 'Hello',
            'null' => null,
            'date' => DateTime::fromParts(2023, 3, 1),
            'html' => Html::el('a'),
            'array' => [
                'intValue' => 3,
            ],
            'intArray' => [
                0 => 1,
            ],
        ]);

        self::assertEquals([
            'boolTrue' => '1',
            'boolFalse' => '',
            'intZero' => '0',
            'intValue' => '642',
            'floatZero' => '0',
            'floatValue' => '43.154',
            'text' => 'Hello',
            'null' => '',
            'date' => '2023-03-01T00:00:00',
            'html' => '<a></a>',
            'array' => [
                'intValue' => '3',
            ],
            'intArray' => [
                '0' => '1',
            ],
        ], $result);
    }

    /**
     * Data normalization test - unsupported type.
     */
    #[Test]
    public function normalizePostDataUnsupported(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Value of 'value' cannot be converted to string (type: object)");

        FormUtils::normalizePostData([
            'value' => new stdClass(),
        ]);
    }

    /**
     * Value normalization test.
     */
    #[Test]
    #[DataProvider('dataNormalizePostValue')]
    public function normalizePostValue(mixed $value, mixed $expected): void
    {
        $result = FormUtils::normalizePostValue('key', $value);

        self::assertEquals($expected, $result);
    }

    /**
     * @return array<array{mixed, mixed}>
     */
    public static function dataNormalizePostValue(): array
    {
        return [
            [true, '1'],
            [false, ''],
            [0, '0'],
            [642, '642'],
            [0.0, '0'],
            [43.154, '43.154'],
            ['Hello', 'Hello'],
            [null, ''],
            [DateTime::fromParts(2023, 3, 1), '2023-03-01T00:00:00'],
            [Html::el('a'), '<a></a>'],
            [['intValue' => 3], ['intValue' => '3']],
            [[0 => 1], ['0' => '1']],
        ];
    }

    /**
     * Value normalization test - unsupported type.
     */
    #[Test]
    #[DataProvider('dataNormalizePostValueUnsupported')]
    public function normalizePostValueUnsupported(mixed $value): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Value of 'value' cannot be converted to string (type: object)");

        FormUtils::normalizePostValue('value', $value);
    }

    /**
     * @return array<array{mixed}>
     */
    public static function dataNormalizePostValueUnsupported(): array
    {
        return [
            [new stdClass()],
        ];
    }

    /**
     * Test building upload file.
     */
    #[Test]
    public function buildUploadFile(): void
    {
        $file = FormUtils::buildUploadFile('test.txt', 'Hello World!');

        self::assertTrue($file->ok);
        self::assertEquals('test.txt', $file->name);
        self::assertEquals('Hello World!', $file->contents);
    }

    /**
     * Test building upload file - failed.
     */
    #[Test]
    public function buildUploadFileFailed(): void
    {
        $file = FormUtils::buildUploadFile('test.txt', 'Hello World!', UPLOAD_ERR_FORM_SIZE);

        self::assertFalse($file->ok);
    }

    /**
     * Test splitting form data.
     *
     * @param array<string, mixed> $data
     * @param array<string, mixed> $expectedPost
     * @param array<string, mixed> $expectedFiles
     */
    #[Test]
    #[DataProvider('dataSplitData')]
    public function splitData(array $data, array $expectedPost, array $expectedFiles): void
    {
        [$post, $files] = FormUtils::splitData($data);

        self::assertEquals($expectedPost, $post);
        self::assertEquals($expectedFiles, $files);
    }

    /**
     * @return array<array{array<string, mixed>, array<string, mixed>, array<string, mixed>}>
     */
    public static function dataSplitData(): array
    {
        return [
            [[], [], []],
            [
                ['key1' => 'value1', 'key2' => 'value2'],
                ['key1' => 'value1', 'key2' => 'value2'],
                [],
            ],
            [
                ['key1' => 'value1', 'key2' => new FileUpload([])],
                ['key1' => 'value1'],
                ['key2' => new FileUpload([])]],
            [
                ['array' => ['key1' => 'value1', 'key2' => new FileUpload([])], 'key2' => new FileUpload([])],
                ['array' => ['key1' => 'value1']],
                ['array' => ['key2' => new FileUpload([])], 'key2' => new FileUpload([])],
            ],
        ];
    }

    /**
     * Test building error message from erroneous container.
     */
    #[Test]
    public function buildContainerError(): void
    {
        $form = new Form();

        $form->addError('Error 0');

        $form->addText('value1')->addError('Error 1');
        $form->addText('value2')->addError('Error 2');
        $form->addText('value3');
        $formGroup = $form->addContainer('group1');
        $formGroup->addText('value1');
        $formGroup->addText('value2')->addError('Error 3');
        $formGroup->addText('value3')->addError('Error 4');

        $result = FormUtils::buildInvalidContainerMessage($form);

        self::assertEquals(
            <<<'ERROR'
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

            ERROR,
            $result,
        );
    }
}
