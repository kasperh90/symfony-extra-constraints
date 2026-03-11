# Symfony Extra Constraints

A collection of additional validation constraints for the
[Symfony Validator](https://github.com/symfony/validator) component.

![PHP Version](https://img.shields.io/badge/php-%3E%3D8.2-blue)
![License](https://img.shields.io/badge/license-MIT-green)

## Installation

```bash
composer require kasperh90/symfony-extra-constraints
```

## Usage

Import the constraints:

```php
use Kasperh90\SymfonyExtraConstraints\Validator\Constraints as ExtraAssert;
```

### Sum

Validates the sum of an iterable of numeric values.

#### Options

- `exactly`: The exact expected sum of the collection. When set, the constraint
  ensures that the sum of all values equals this value.
- `min`: The minimum allowed sum of the collection.
- `max`: The maximum allowed sum of the collection.
- `exactMessage`: The message shown if the sum of the collection does not equal
  the expected value.
- `minMessage`: The message shown if the sum of the collection is lower than the
  configured minimum.
- `maxMessage`: The message shown if the sum of the collection exceeds the
  configured maximum.
- `notNumericMessage`: The message shown when a value in the collection is not
  numeric.

#### Default options

- `groups`: The validation groups this constraint belongs to
- `payload`: Domain-specific data attached to the constraint

#### Example

```php
use Kasperh90\SymfonyExtraConstraints\Validator\Constraints as ExtraAssert;

class Allocation
{
    #[ExtraAssert\Sum(exactly: 100)]
    public array $percentages;

    #[ExtraAssert\Sum(min: 10)]
    public array $scores;

    #[ExtraAssert\Sum(max: 500)]
    public array $expenses;
}
```

## License

This package is licensed under the MIT License. See the [LICENSE](LICENSE) file
for details.
