<?php

declare(strict_types=1);

namespace Cds\NettePresenterTests\Traits;

use Cds\NettePresenterTests\Utils\FormSubmitMethod;
use Nette\Application\IPresenter;
use Nette\Application\Response;
use Nette\Application\Responses\ForwardResponse;
use PHPUnit\Framework\AssertionFailedError;

/**
 * More user-friendly interface for presenter testing.
 *
 * @property IPresenter $presenter
 */
trait PresenterRequest
{
    use PresenterRun;

    /**
     * Request presenter's action with given parameters.
     *
     * @param string                         $action presenter action name
     * @param array<non-empty-string, mixed> $params presenter parameters
     */
    protected function request(string $action, array $params = []): Response
    {
        return $this->runPresenter(
            presenter: $this->presenter,
            args: $this->mergeActionAndParams($action, $params)
        );
    }

    /**
     * Submit form to presenter action with given parameters.
     *
     * @param string                         $action   presenter action name
     * @param array<non-empty-string, mixed> $params   presenter parameters
     * @param array<non-empty-string, mixed> $data     form data including files upload
     * @param string                         $formName submitted form name
     */
    protected function submitForm(
        string $action,
        array $params,
        array $data,
        string $formName,
        FormSubmitMethod $method = FormSubmitMethod::Post
    ): Response {
        return $this->runPresenterForm(
            presenter: $this->presenter,
            args: $this->mergeActionAndParams($action, $params),
            data: $data,
            formName: $formName,
            method: $method
        );
    }

    /**
     * Submit form to presenter action with success check.
     *
     * If submitted form is not valid a failure is raised.
     *
     * @param string                         $action   presenter action name
     * @param array<non-empty-string, mixed> $params   presenter parameters
     * @param array<non-empty-string, mixed> $data     form data including files upload
     * @param string                         $formName submitted form name
     *
     * @throws AssertionFailedError
     */
    protected function submitFormSuccess(
        string $action,
        array $params,
        array $data,
        string $formName,
        FormSubmitMethod $method = FormSubmitMethod::Post
    ): Response {
        return $this->runPresenterFormSuccess(
            presenter: $this->presenter,
            args: $this->mergeActionAndParams($action, $params),
            data: $data,
            formName: $formName,
            method: $method
        );
    }

    /**
     * Submit form to presenter action with failure check.
     *
     * If submitted form is valid a failure is raised.
     *
     * @param string                         $action   presenter action name
     * @param array<non-empty-string, mixed> $params   presenter parameters
     * @param array<non-empty-string, mixed> $data     form data including files upload
     * @param string                         $formName submitted form name
     *
     * @return array<string> a list of form errors
     */
    protected function submitFormError(
        string $action,
        array $params,
        array $data,
        string $formName,
        FormSubmitMethod $method = FormSubmitMethod::Post
    ): array {
        return $this->runPresenterFormError(
            presenter: $this->presenter,
            args: $this->mergeActionAndParams($action, $params),
            data: $data,
            formName: $formName,
            method: $method
        );
    }

    /**
     * Run forward response on presenter.
     */
    protected function runForwardResponse(ForwardResponse $response): Response
    {
        return $this->runForwardResponseOn($response, $this->presenter);
    }

    /**
     * Add action to presenter parameters.
     *
     * @param array<non-empty-string, mixed> $params
     *
     * @return array<non-empty-string, mixed>
     */
    private function mergeActionAndParams(string $action, array $params): array
    {
        return array_merge(['action' => $action], $params);
    }
}
