<?php

declare(strict_types=1);

namespace Cds\NettePresenterTests\Utils;

use DateTimeInterface;
use http\Exception\RuntimeException;
use Nette\Forms\Container;
use Nette\Forms\Control;
use Nette\Forms\Form;
use Nette\Http\FileUpload;
use Nette\InvalidArgumentException;
use Nette\StaticClass;

/**
 * Helper class for testing forms.
 */
final class FormUtils
{
    use StaticClass;

    /**
     * Convert input data into form required by POST.
     *
     * @param array<string|int, mixed> $data
     *
     * @return array<string, mixed>
     *
     * @throws InvalidArgumentException if any value of the array cannot be converted to string
     */
    public static function normalizePostData(array $data): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            $result[(string) $key] = self::normalizePostValue($key, $value);
        }

        return $result;
    }

    /**
     * Convert input value into form required by POST.
     *
     * @return array<string, mixed>|string
     *
     * @throws InvalidArgumentException if value cannot be converted to string
     */
    public static function normalizePostValue(string|int $key, mixed $value): array|string
    {
        $result = null;

        if (is_scalar($value)) {
            $result = (string) $value;
        } elseif (is_null($value)) {
            $result = '';
        } elseif (is_array($value)) {
            $result = self::normalizePostData($value);
        } elseif (is_object($value)) {
            if ($value instanceof DateTimeInterface) {
                $result = $value->format('Y-m-d\TH:i:s');
            } elseif (method_exists($value, '__toString')) {
                $result = (string) $value;
            }
        }

        if (!is_string($result) && !is_array($result)) {
            throw new InvalidArgumentException(
                "Value of '{$key}' cannot be converted to string (type: " . gettype($value) . ')',
            );
        }

        return $result;
    }

    /**
     * Build UploadFile for given content.
     *
     * @param string $name    uploaded filename
     * @param string $content uploaded file content
     * @param int    $error   file upload error
     */
    public static function buildUploadFile(string $name, string $content, int $error = UPLOAD_ERR_OK): FileUpload
    {
        if ($error === UPLOAD_ERR_OK) {
            $file = tmpfile();

            if ($file === false) {
                // @codeCoverageIgnoreStart
                throw new RuntimeException('Unable to create temporary file');
                // @codeCoverageIgnoreEnd
            }

            $metadata = stream_get_meta_data($file);
            if (!array_key_exists('uri', $metadata)) {
                // @codeCoverageIgnoreStart
                throw new RuntimeException('URI not found in metadata');
                // @codeCoverageIgnoreEnd
            }

            $tempFilePath = $metadata['uri'];
            file_put_contents($tempFilePath, $content);
            register_shutdown_function(static fn () => fclose($file));
        } else {
            $tempFilePath = null;
        }

        return new FileUpload([
            'name' => $name,
            'size' => strlen($content),
            'tmp_name' => $tempFilePath,
            'error' => $error,
        ]);
    }

    /**
     * Split form data containing POST and FILES into separate arrays.
     *
     * @param array<string, mixed> $data
     *
     * @return array{array<string, mixed>, array<string, mixed>}
     */
    public static function splitData(array $data): array
    {
        $post = [];
        $files = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                [$arrPost, $arrFiles] = self::splitData($value);

                if (!empty($arrPost)) {
                    $post[$key] = $arrPost;
                }

                if (!empty($arrFiles)) {
                    $files[$key] = $arrFiles;
                }
            } elseif ($value instanceof FileUpload) {
                $files[$key] = $value;
            } else {
                $post[$key] = $value;
            }
        }

        return [$post, $files];
    }

    /**
     * Builds a message for displaying an invalid form.
     */
    public static function buildInvalidFormMessage(Form $form): string
    {
        return "Form is not valid:\n" . self::buildInvalidContainerMessage($form, 1);
    }

    /**
     * Build message for invalid form container.
     *
     * @param Container $container container with errors
     */
    public static function buildInvalidContainerMessage(Container $container, int $level = 1): string
    {
        $indent = static fn ($repeat) => str_repeat(' ', ($level + $repeat) * 2);

        $message = '';

        foreach ($container->getErrors() as $error) {
            $message .= "{$indent(0)}- {$error}\n";
        }

        foreach ($container->getComponents() as $name => $control) {
            assert($control instanceof Control || $control instanceof Container);
            $controlErrors = $control->getErrors();

            if (!empty($controlErrors)) {
                $message .= "\n{$indent(0)}[{$name}]:\n";

                if ($control instanceof Container) {
                    $message .= self::buildInvalidContainerMessage($control, $level + 1);
                } else {
                    foreach ($controlErrors as $error) {
                        $message .= "{$indent(1)}- {$error}\n";
                    }
                }
            }
        }

        return $message;
    }

    /**
     * Recursively checks the existence of form fields for values in the form.
     *
     * @param array<non-empty-string, mixed> $values
     * @param list<non-empty-string>         $path
     */
    public static function checkFormFields(Container $container, array $values, array $path = []): void
    {
        foreach ($values as $name => $value) {
            $localPath = array_merge($path, [$name]);

            if (!isset($container[$name])) {
                trigger_error("Missing input element for value: '" . implode('.', $localPath) . "'");
            } elseif (is_array($value) && $container[$name] instanceof Container) {
                self::checkFormFields($container[$name], $value, $localPath);
            }
        }
    }
}
