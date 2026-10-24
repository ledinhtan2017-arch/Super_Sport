<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Validator — Input validation
 *
 * Cách dùng:
 *   $v = new Validator($request->all());
 *   $v->required('email')->email('email')->minLength('password', 8);
 *   if ($v->fails()) { return errors... }
 *   $data = $v->validated(); // chỉ trả về các field đã khai báo rule
 */
final class Validator
{
    private array $data;
    private array $errors  = [];
    private array $rules   = []; // field => đã khai báo rule
    private array $labels  = []; // field => display label

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    // ─── Rule Builders ────────────────────────────────────────────────────────

    public function required(string $field, ?string $label = null): static
    {
        $this->rules[$field] = true;
        $label ??= $field;
        $this->labels[$field] = $label;

        $value = $this->getValue($field);
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            $this->addError($field, "{$label} không được để trống.");
        }
        return $this;
    }

    public function optional(string $field): static
    {
        $this->rules[$field] = true;
        return $this;
    }

    public function email(string $field): static
    {
        $value = $this->getValue($field);
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $label = $this->labels[$field] ?? $field;
            $this->addError($field, "{$label} không phải địa chỉ email hợp lệ.");
        }
        return $this;
    }

    public function minLength(string $field, int $min): static
    {
        $value = $this->getValue($field);
        if ($value !== null && $value !== '' && mb_strlen((string)$value) < $min) {
            $label = $this->labels[$field] ?? $field;
            $this->addError($field, "{$label} phải có ít nhất {$min} ký tự.");
        }
        return $this;
    }

    public function maxLength(string $field, int $max): static
    {
        $value = $this->getValue($field);
        if ($value !== null && $value !== '' && mb_strlen((string)$value) > $max) {
            $label = $this->labels[$field] ?? $field;
            $this->addError($field, "{$label} không được vượt quá {$max} ký tự.");
        }
        return $this;
    }

    public function numeric(string $field): static
    {
        $value = $this->getValue($field);
        if ($value !== null && $value !== '' && !is_numeric($value)) {
            $label = $this->labels[$field] ?? $field;
            $this->addError($field, "{$label} phải là số.");
        }
        return $this;
    }

    public function integer(string $field): static
    {
        $value = $this->getValue($field);
        if ($value !== null && $value !== '' && filter_var($value, FILTER_VALIDATE_INT) === false) {
            $label = $this->labels[$field] ?? $field;
            $this->addError($field, "{$label} phải là số nguyên.");
        }
        return $this;
    }

    public function min(string $field, int|float $min): static
    {
        $value = $this->getValue($field);
        if ($value !== null && $value !== '' && is_numeric($value) && (float)$value < $min) {
            $label = $this->labels[$field] ?? $field;
            $this->addError($field, "{$label} phải lớn hơn hoặc bằng {$min}.");
        }
        return $this;
    }

    public function max(string $field, int|float $max): static
    {
        $value = $this->getValue($field);
        if ($value !== null && $value !== '' && is_numeric($value) && (float)$value > $max) {
            $label = $this->labels[$field] ?? $field;
            $this->addError($field, "{$label} phải nhỏ hơn hoặc bằng {$max}.");
        }
        return $this;
    }

    public function in(string $field, array $allowed): static
    {
        $value = $this->getValue($field);
        if ($value !== null && $value !== '' && !in_array($value, $allowed, true)) {
            $label = $this->labels[$field] ?? $field;
            $this->addError($field, "{$label} có giá trị không hợp lệ.");
        }
        return $this;
    }

    public function confirmed(string $field, string $confirmField = ''): static
    {
        $confirmField = $confirmField ?: $field . '_confirmation';
        $value        = $this->getValue($field);
        $confirm      = $this->getValue($confirmField);
        if ($value !== $confirm) {
            $label = $this->labels[$field] ?? $field;
            $this->addError($field, "{$label} xác nhận không khớp.");
        }
        return $this;
    }

    public function phone(string $field): static
    {
        $value = $this->getValue($field);
        if ($value !== null && $value !== '') {
            $digits = preg_replace('/\D/', '', $value);
            if (strlen($digits) < 9 || strlen($digits) > 15) {
                $label = $this->labels[$field] ?? $field;
                $this->addError($field, "{$label} không phải số điện thoại hợp lệ.");
            }
        }
        return $this;
    }

    /**
     * Rule tùy chỉnh với callback.
     * Callback nhận value, trả về null (pass) hoặc string lỗi.
     */
    public function custom(string $field, callable $callback): static
    {
        $this->rules[$field] = true;
        $value  = $this->getValue($field);
        $result = $callback($value, $this->data);
        if ($result !== null) {
            $this->addError($field, $result);
        }
        return $this;
    }

    // ─── Results ──────────────────────────────────────────────────────────────

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    /**
     * Trả về duy nhất các field đã khai báo rule (loại bỏ extra fields).
     */
    public function validated(): array
    {
        return array_intersect_key($this->data, $this->rules);
    }

    // ─── Private ─────────────────────────────────────────────────────────────

    private function getValue(string $field): mixed
    {
        // Hỗ trợ dot notation: 'address.city'
        $keys  = explode('.', $field);
        $value = $this->data;
        foreach ($keys as $key) {
            if (!is_array($value) || !array_key_exists($key, $value)) {
                return null;
            }
            $value = $value[$key];
        }
        return $value;
    }

    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }
}
