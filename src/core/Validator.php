<?php

namespace MotoristaCheck\Core;

class Validator
{
    private array $errors = [];
    private array $data   = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function required(string $field, string $label = ''): static
    {
        $label = $label ?: $field;
        if (empty(trim($this->data[$field] ?? ''))) {
            $this->errors[$field][] = "O campo {$label} é obrigatório.";
        }
        return $this;
    }

    public function email(string $field): static
    {
        $val = $this->data[$field] ?? '';
        if ($val !== '' && !filter_var($val, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = "E-mail inválido.";
        }
        return $this;
    }

    public function cpf(string $field): static
    {
        $cpf = preg_replace('/\D/', '', $this->data[$field] ?? '');
        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
            $this->errors[$field][] = "CPF inválido.";
            return $this;
        }
        for ($t = 9; $t < 11; $t++) {
            $d = 0;
            for ($c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                $this->errors[$field][] = "CPF inválido.";
                break;
            }
        }
        return $this;
    }

    public function minLength(string $field, int $min): static
    {
        $val = $this->data[$field] ?? '';
        if (strlen($val) < $min) {
            $this->errors[$field][] = "Mínimo de {$min} caracteres.";
        }
        return $this;
    }

    public function maxLength(string $field, int $max): static
    {
        $val = $this->data[$field] ?? '';
        if (strlen($val) > $max) {
            $this->errors[$field][] = "Máximo de {$max} caracteres.";
        }
        return $this;
    }

    public function numeric(string $field): static
    {
        $val = $this->data[$field] ?? '';
        if ($val !== '' && !is_numeric($val)) {
            $this->errors[$field][] = "Deve ser um valor numérico.";
        }
        return $this;
    }

    public function date(string $field): static
    {
        $val = $this->data[$field] ?? '';
        if ($val !== '' && !strtotime($val)) {
            $this->errors[$field][] = "Data inválida.";
        }
        return $this;
    }

    public function inArray(string $field, array $options): static
    {
        $val = $this->data[$field] ?? '';
        if ($val !== '' && !in_array($val, $options, true)) {
            $this->errors[$field][] = "Valor inválido para este campo.";
        }
        return $this;
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): string
    {
        foreach ($this->errors as $field => $msgs) {
            return $msgs[0];
        }
        return '';
    }

    public function get(string $field, mixed $default = null): mixed
    {
        return $this->data[$field] ?? $default;
    }

    public function all(): array
    {
        return $this->data;
    }
}
