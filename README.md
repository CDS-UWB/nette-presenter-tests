<div align="center">
<a href="https://cds.zcu.cz">
  <img src="https://cds.zcu.cz/images/logo.svg" width="200">
</a>
</div>

# Nette Presenter Tests
[![Tests](https://github.com/CDS-UWB/nette-presenter-tests/actions/workflows/tests.yml/badge.svg)](https://github.com/CDS-UWB/nette-presenter-tests/actions/workflows/tests.yml)

Library based on PHPUnit framework to test Nette presenters via simple API.

The main functionality of Nette presenters is in their actions invoked by requests. The library allows
to simply invoke presenter actions like as called from browser. Another functionality is handling
form submission that can be also tested.

```php
final class PresenterTest extends \PHPUnit\Framework\TestCase
{
    // Whole functionality is available via traits
    use \Cds\NettePresenterTests\Traits\Presenter;

    protected function setUp() : void
    {
        parent::setUp();

        // Initialize presenter for all following tests
        $this->presenter = $this->createPresenter(MyPresenter::class);
    }

    public function testAction(): void
    {
        // Invoke MyPresenter:default action with parameters
        $response = $this->request('default', [
            'param1' => 'value1',
        ]);

        // Check the presenter response is TextResponse with non-empty result
        self::assertNonEmptyTextResponse($response);
    }

    public function testActionRedirect(): void
    {
        // Invoke MyPresenter:redirect action
        $response = $this->request('redirect');

        // Check the presenter response is RedirectResponse with given target
        self::assertRedirectResponseTo('MyPresenter:default', $response);
    }

    public function testFormSubmit(): void
    {
        // Submit form 'testForm' via action default
        $response = $this->submitForm('default', [], [
            'key1' => 'value1',
            'key2' => [1, 2, 3],
        ], 'testForm');

        // Check the presenter response is RedirectResponse with given target
        self::assertRedirectResponseTo('MyPresenter:default', $response);
    }
}
```

## Available traits

- `AssertResponse` - adds assertions that allow to check presenter responses.
- `ExpectErrors` - adds expectations to check raising presenter error via `error` method.
- `Presenter` - union trait that includes all other traits.
- `PresenterCreate` - add method to create instance of presenter from class name via Nette DI container.
- `PresenterRequest` - simple API for invoking requests on property `presenter` (uses `PresenterRun` methods).
- `PresenterRun` - main request API for invoking requests on given presenter.

## Presenter mapping

The library require mapping from presenter class to presenter name which is used in request. In
normal application the name is built by router and then passed to presenter factory which create presenter instance.
The library works directly on presenter instance, so it needs to convert presenter class to presenter name which
is then passed to presenter request. Default implementation uses some predefined rules to convert class to name but
if something else is needed, own implementation of `buildNameFromPresenter` can be implemented to provide required
mapping.

```php
final class MyTest extends \PHPUnit\Framework\TestCase
{
    use \Cds\NettePresenterTests\Traits\Presenter;

    protected function buildNameFromPresenter(string $class) : string
    {
        // All request are from TestPresenter
        return 'Test';
    }
}
```

It's also possible to use Nette's presenter factory to provide class to name mapping but `unformatPresenterClass` is
marked as internal and deprecated. Alternatively `nepada/presenter-mapping` can be used which provides this method
(for now).

## More examples

### File upload testing

The library also allows test file uploading as part of form submission. The definition of file upload is specified
as part of form data only using `$this->uploadFile` method that specifies file name, and it's content. It's also
possible to specify upload result code (PHP's `UPLOAD_ERR_*` constants).

```php
final class PresenterTest extends \PHPUnit\Framework\TestCase
{
    // Whole functionality is available via traits
    use \Cds\NettePresenterTests\Traits\Presenter;

    protected function setUp() : void
    {
        parent::setUp();

        // Initialize presenter for all following tests
        $this->presenter = $this->createPresenter(MyPresenter::class);
    }

    public function testFileUpload(): void
    {
        $response = $this->submitForm('default', [], [
            'file1' => $this->uploadFile('file1.txt', 'Hello World!'),
            // Upload failure can be tested via error argument
            'file2' => $this->uploadFile(error: UPLOAD_ERR_CANT_WRITE),
        ], 'testForm');

        // Check the presenter response is RedirectResponse with given target
        self::assertRedirectResponseTo('MyPresenter:default', $response);
    }
}
```

### Testing without presenter property

It's possible test multiple different presenters without `presenter` property. Testing is done by
`PresenterRequest` trait that allow to pass any presenter as argument.


```php
final class PresenterTest extends \PHPUnit\Framework\TestCase
{
    use \Cds\NettePresenterTests\Traits\PresenterRequest;
    use \Cds\NettePresenterTests\Traits\PresenterCreate;

    public function testAction(): void
    {
        $presenter = $this->createPresenter(MyPresenter::class);

        // Invoke MyPresenter:default action with parameters
        $response = $this->requestPresenter($presenter, 'default', [
            'param1' => 'value1',
        ]);

        // Check the presenter response is TextResponse with non-empty result
        self::assertNonEmptyTextResponse($response);
    }

}
```
