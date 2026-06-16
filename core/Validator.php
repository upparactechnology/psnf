<?php

declare(strict_types=1);

namespace Core;

class Validator
{
    private array $data;
    private array $rules;
    private array $errors  = [];
    private array $passed  = [];

    public function __construct(array $data, array $rules)
    {
        $this->data  = $data;
        $this->rules = $rules;
        $this->validate();
    }

    private function validate(): void
    {
        foreach ($this->rules as $field => $ruleString) {
            $rules = explode('|', $ruleString);
            $value = $this->data[$field] ?? null;

            foreach ($rules as $rule) {
                $params = [];
                if (str_contains($rule, ':')) {
                    [$rule, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                }

                $error = $this->applyRule($field, $value, $rule, $params);
                if ($error) {
                    $this->errors[$field] = $error;
                    break; // Stop at first error per field
                }
            }

            if (!isset($this->errors[$field])) {
                $this->passed[$field] = $value;
            }
        }
    }

    private function applyRule(string $field, mixed $value, string $rule, array $params): ?string
    {
        $label = ucfirst(str_replace('_', ' ', $field));

        return match ($rule) {
            'required'  => (empty($value) && $value !== '0') ? "$label is required." : null,
            'email'     => ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) ? "Invalid email address." : null,
            'min'       => ($value && strlen($value) < (int) $params[0]) ? "$label must be at least {$params[0]} characters." : null,
            'max'       => ($value && strlen($value) > (int) $params[0]) ? "$label must not exceed {$params[0]} characters." : null,
            'numeric'   => ($value && !is_numeric($value)) ? "$label must be a number." : null,
            'integer'   => ($value && !filter_var($value, FILTER_VALIDATE_INT)) ? "$label must be an integer." : null,
            'alpha'     => ($value && !ctype_alpha($value)) ? "$label may only contain letters." : null,
            'alphanum'  => ($value && !ctype_alnum($value)) ? "$label may only contain letters and numbers." : null,
            'url'       => ($value && !filter_var($value, FILTER_VALIDATE_URL)) ? "Invalid URL format." : null,
            'in'        => ($value && !in_array($value, $params)) ? "$label must be one of: " . implode(', ', $params) . "." : null,
            'not_in'    => ($value && in_array($value, $params)) ? "$label contains an invalid value." : null,
            'confirmed' => ($value !== ($this->data[$field . '_confirmation'] ?? null)) ? "$label confirmation does not match." : null,
            'unique'    => $this->validateUnique($field, $value, $params),
            'exists'    => $this->validateExists($field, $value, $params),
            'date'      => ($value && !strtotime($value)) ? "$label must be a valid date." : null,
            'phone'     => ($value && !preg_match('/^[\+\d\s\-\(\)]{7,20}$/', $value)) ? "Invalid phone number." : null,
            'nullable'  => null,
            'sometimes' => null,
            default     => null,
        };
    }

    private function validateUnique(string $field, mixed $value, array $params): ?string
    {
        if (!$value) return null;
        [$table, $column] = [$params[0], $params[1] ?? $field];
        $excludeId = $params[2] ?? null;

        $db  = \Core\Application::$app->db;
        $sql = "SELECT COUNT(*) as cnt FROM `$table` WHERE `$column` = ? AND deleted_at IS NULL";
        $p   = [$value];
        if ($excludeId) { $sql .= " AND id != ?"; $p[] = $excludeId; }

        $result = $db->selectOne($sql, $p);
        return ($result['cnt'] ?? 0) > 0 ? ucfirst(str_replace('_', ' ', $field)) . ' already exists.' : null;
    }

    private function validateExists(string $field, mixed $value, array $params): ?string
    {
        if (!$value) return null;
        [$table, $column] = [$params[0], $params[1] ?? 'id'];
        $db     = \Core\Application::$app->db;
        $result = $db->selectOne("SELECT COUNT(*) as cnt FROM `$table` WHERE `$column` = ?", [$value]);
        return ($result['cnt'] ?? 0) === 0 ? ucfirst(str_replace('_', ' ', $field)) . ' does not exist.' : null;
    }

    public function passes(): bool  { return empty($this->errors); }
    public function fails(): bool   { return !empty($this->errors); }
    public function errors(): array { return $this->errors; }
    public function validated(): array { return $this->passed; }
}
