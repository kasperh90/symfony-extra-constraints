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

Validates that the sum of a collection of numeric values meets a given constraint.

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

#### Options

- `exactly`: Require the sum to equal a specific value
- `min`: Require the sum to be greater than or equal to a value
- `max`: Require the sum to be less than or equal to a value

### Average

Validates that the average of a collection of numeric values meets a given constraint.

```php
class Ratings
{
    #[ExtraAssert\Average(min: 3.5)]
    public array $userRatings;

    #[ExtraAssert\Average(max: 5)]
    public array $scores;

    #[ExtraAssert\Average(exactly: 4)]
    public array $normalizedRatings;
}
```

#### Options

- `exactly`: Require the average to equal a specific value
- `min`: Require the average to be greater than or equal to a value
- `max`: Require the average to be less than or equal to a value

## License

This package is licensed under the MIT License. See the [LICENSE](LICENSE) file
for details.
