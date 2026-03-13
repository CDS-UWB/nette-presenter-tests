<?php

declare(strict_types=1);

namespace Cds\NettePresenterTests\Traits;

use Cds\NettePresenterTests\Utils\FormSubmitMethod;
use Cds\NettePresenterTests\Utils\FormUtils;
use Cds\NettePresenterTests\Utils\Utils;
use Nette\Application\Request;
use Nette\Application\Response;
use Nette\Application\Responses\ForwardResponse;
use Nette\Application\UI\Presenter as NettePresenter;
use Nette\Forms\Form;
use Nette\Http\FileUpload;
use Nette\Http\Helpers;
use Nette\Utils\FileSystem;
use PHPUnit\Framework\AssertionFailedError;

/**
 * Trait for running tests on given presenter.
 */
trait PresenterRun
{
    /**
     * Run presenter.
     *
     * @param array<non-empty-string, mixed> $args presenter arguments
     */
    protected function runPresenter(NettePresenter $presenter, array $args = []): Response
    {
        $name = Utils::buildNameFromPresenter($presenter::class);

        // NOTE: the method is intentionally NULL. In case of GET or HEAD there is check for URL match
        //  and presenter redirects to canonical address.
        return $presenter->run(new Request($name, null, $args));
    }

    /**
     * Run presenter with form submit.
     *
     * @param NettePresenter                 $presenter the requested UI presenter
     * @param array<non-empty-string, mixed> $args      presenter arguments
     * @param array<non-empty-string, mixed> $data      form data including file uploads
     * @param string                         $formName  form name that was submitted
     */
    protected function runPresenterForm(
        NettePresenter $presenter,
        array $args,
        array $data,
        string $formName,
        FormSubmitMethod $method = FormSubmitMethod::Post,
    ): Response {
        if (!isset($_COOKIE[Helpers::StrictCookieName])) {
            $_COOKIE[Helpers::StrictCookieName] = '1';
        }

        $name = Utils::buildNameFromPresenter($presenter::class);

        $params = $args;
        $files = [];
        $post = [];

        if ($method === FormSubmitMethod::Get) {
            $params = [...$args, ...$this->addSubmitSignal($data, $formName)];
        } else {
            [$post, $files] = FormUtils::splitData($data);
            $post = $this->addSubmitSignal(FormUtils::normalizePostData($post), $formName);
        }

        $result = $presenter->run(new Request($name, $method->value, $params, $post, $files));

        $form = $presenter[$formName];
        assert($form instanceof Form);
        FormUtils::checkFormFields($form, $data);

        return $result;
    }

    /**
     * Run presenter with form submit with success check.
     *
     * @param NettePresenter                 $presenter the requested UI presenter
     * @param array<non-empty-string, mixed> $args      presenter arguments
     * @param array<non-empty-string, mixed> $data      form data including file uploads
     * @param string                         $formName  form name that was submitted
     *
     * @throws AssertionFailedError when form is not valid
     */
    protected function runPresenterFormSuccess(
        NettePresenter $presenter,
        array $args,
        array $data,
        string $formName,
        FormSubmitMethod $method = FormSubmitMethod::Post
    ): Response {
        $result = $this->runPresenterForm($presenter, $args, $data, $formName, $method);

        /** @var Form $form */
        $form = $presenter[$formName];

        if (!$form->isValid()) {
            self::fail(FormUtils::buildInvalidFormMessage($form));
        }

        return $result;
    }

    /**
     * Run presenter with form submit with error check.
     *
     * @param NettePresenter                 $presenter the requested UI presenter
     * @param array<non-empty-string, mixed> $args      presenter arguments
     * @param array<non-empty-string, mixed> $data      form data including file uploads
     * @param string                         $formName  form name that was submitted
     *
     * @return array<string> a list of form errors
     *
     * @throws AssertionFailedError when form is valid
     */
    protected function runPresenterFormError(
        NettePresenter $presenter,
        array $args,
        array $data,
        string $formName,
        FormSubmitMethod $method = FormSubmitMethod::Post,
    ): array {
        $this->runPresenterForm($presenter, $args, $data, $formName, $method);

        /** @var Form $form */
        $form = $presenter[$formName];

        if (!$form->hasErrors()) {
            self::fail('Form is valid, should be invalid');
        }

        return $form->getErrors();
    }

    /**
     * Run forward response on presenter.
     */
    protected function runForwardResponseOn(ForwardResponse $response, NettePresenter $presenter): Response
    {
        $request = $response->getRequest();

        if ((string) $presenter->getName() !== $request->getPresenterName()) {
            self::fail("Unable to run request for presenter '{$request->getPresenterName()}' on presenter '{$presenter->getName()}'");
        }

        return $presenter->run($request);
    }

    /**
     * Creates an object for uploading a file via form.
     *
     * @param non-empty-string $name
     * @param int              $error error when uploading file
     */
    protected function uploadFile(string $name, string $content, int $error = UPLOAD_ERR_OK): FileUpload
    {
        if ($error === UPLOAD_ERR_OK) {
            $tempFilePath = tempnam(sys_get_temp_dir(), 'TEST');
            FileSystem::write($tempFilePath, $content);
            register_shutdown_function(static fn () => FileSystem::delete($tempFilePath));
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
     * Create presenter request from parameters.
     *
     * @param string               $name   presenter name
     * @param array<string, mixed> $params presenter parameters
     * @param array<string, mixed> $post   POST parameters
     * @param array<string, mixed> $files  uploaded files
     */
    protected function createRequest(
        string $name,
        null|string $method = null,
        array $params = [],
        array $post = [],
        array $files = [],
    ): Request {
        return new Request($name, $method, $params, $post, $files);
    }

    /**
     * Add action to parameters.
     *
     * @param array<string, mixed> $params presenter parameters
     *
     * @return array<string, mixed>
     */
    protected function paramsWithAction(array $params, string $action): array
    {
        return array_merge(['action' => $action], $params);
    }

    /**
     * Add submit signal to form data.
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    protected function addSubmitSignal(array $data, string $formName): array
    {
        return array_merge(['_do' => "{$formName}-submit"], $data);
    }
}
