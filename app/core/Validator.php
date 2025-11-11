<?php

class Validator
{
    protected array $data = [];
    protected array $rules = [];
    protected ?string $currentField = null;
    protected array $errors = [];
    protected $conn;

    public function __construct(array $data = [], $conn = null)
    {
        $this->data = $data;
        $this->conn = $conn;
    }

    public function setData(array $data): self 
    { 
        $this->data = $data; 
        return $this; 
    }

    public function required(string $field = null): self
    {
        if ($field !== null) {
            $this->currentField = $field;
            if (!isset($this->rules[$field])) {
                $this->rules[$field] = [];
            }
        }
        
        $this->rules[$this->currentField][] = [
            'type' => 'required',
            'msg' => 'Field ' . $this->currentField . ' wajib diisi.'
        ];
        
        return $this;
    }

    public function numeric(string $field = null): self
    {
        if ($field !== null) {
            $this->currentField = $field;
            if (!isset($this->rules[$field])) {
                $this->rules[$field] = [];
            }
        }
        
        $this->rules[$this->currentField][] = [
            'type' => 'numeric',
            'msg' => 'Field ' . $this->currentField . ' harus berupa angka.'
        ];
        
        return $this;
    }

    public function between(string $field, int $min, int $max): self
    {
        $this->currentField = $field;
        if (!isset($this->rules[$field])) {
            $this->rules[$field] = [];
        }
        
        $this->rules[$field][] = [
            'type' => 'between',
            'min' => $min,
            'max' => $max,
            'msg' => "Field {$field} harus antara {$min} dan {$max} karakter."
        ];
        
        return $this;
    }

    public function dateFormat(string $field, string $format): self
    {
        $this->currentField = $field;
        if (!isset($this->rules[$field])) {
            $this->rules[$field] = [];
        }
        
        $this->rules[$field][] = [
            'type' => 'dateFormat',
            'format' => $format,
            'msg' => "Field {$field} harus sesuai format {$format}."
        ];
        
        return $this;
    }

    public function passes(): bool
    {
        return $this->validate();
    }

    public function validate(): bool
    {
        $this->errors = [];
        
        foreach ($this->rules as $field => $rules) {
            $value = $this->data[$field] ?? null;
            
            foreach ($rules as $rule) {
                $type = $rule['type'] ?? 'callback';
                
                if ($type === 'required') {
                    if ($value === null || (is_string($value) && trim($value) === '')) {
                        $this->addError($field, $rule['msg']);
                    }
                    continue;
                }
                
                if ($type === 'numeric') {
                    if ($value !== null && $value !== '' && !is_numeric($value)) {
                        $this->addError($field, $rule['msg']);
                    }
                    continue;
                }
                
                if ($type === 'between') {
                    if ($value === null || $value === '') continue;
                    
                    $min = $rule['min'];
                    $max = $rule['max'];
                    $len = mb_strlen((string)$value);
                    
                    if ($len < $min || $len > $max) {
                        $this->addError($field, $rule['msg']);
                    }
                    continue;
                }
                
                if ($type === 'dateFormat') {
                    if ($value === null || $value === '') continue;
                    
                    $format = $rule['format'];
                    $d = DateTime::createFromFormat($format, $value);
                    $valid = $d && $d->format($format) === $value;
                    
                    if (!$valid) {
                        $this->addError($field, $rule['msg']);
                    }
                    continue;
                }
            }
        }

        return empty($this->errors);
    }

    protected function addError(string $field, string $msg): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $msg;
    }

    public function errors(): array 
    { 
        $flat = [];
        foreach ($this->errors as $field => $messages) {
            foreach ($messages as $msg) {
                $flat[] = $msg;
            }
        }
        return $flat;
    }

    public function first(string $field): ?string 
    { 
        return $this->errors[$field][0] ?? null; 
    }
}