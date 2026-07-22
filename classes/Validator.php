<?php
/**
 * Validator.php
 * Small reusable form-validation helper.
 *
 * OOP CONCEPT DEMONSTRATED: Encapsulation
 * - $errors is private; the outside world can only read it through getErrors()/hasErrors().
 */
class Validator
{
    private array $errors = [];

    public function required($value, string $field): self
    {
        if ($value === null || trim((string)$value) === '') {
            $this->errors[$field] = ucfirst($field) . ' is required.';
        }
        return $this;
    }

    public function email($value, string $field): self
    {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = 'Please enter a valid email address.';
        }
        return $this;
    }

    public function minLength($value, int $length, string $field): self
    {
        if (!empty($value) && strlen((string)$value) < $length) {
            $this->errors[$field] = ucfirst($field) . " must be at least {$length} characters.";
        }
        return $this;
    }

    public function numeric($value, string $field): self
    {
        if (!empty($value) && !is_numeric($value)) {
            $this->errors[$field] = ucfirst($field) . ' must be numeric.';
        }
        return $this;
    }

    public function date($value, string $field): self
    {
        if (!empty($value)) {
            $d = DateTime::createFromFormat('Y-m-d', $value);
            if (!$d || $d->format('Y-m-d') !== $value) {
                $this->errors[$field] = ucfirst($field) . ' must be a valid date (YYYY-MM-DD).';
            }
        }
        return $this;
    }

    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
