<?php

declare(strict_types=1);

namespace App\Form\FormHandling\NestedInputBag;

use Symfony\Component\HttpFoundation\ParameterBag;

class NestedInputBag extends ParameterBag
{
    public function getArray(string $key, array $default = []): array
    {
        $val = $this->getNestedValue($this->parameters, $key);
        return is_array($val) ? $val : $default;
    }

    /**
     * accepts keys in the format "key1.key2..."
     *
     * @param array $data
     * @param string $key
     * @return mixed
     */
    private function getNestedValue(array $data, string $key): mixed
    {
        $keys = explode('.', $key);
        foreach ($keys as $k) {
            if (!is_array($data) || !array_key_exists($k, $data)) {
                return null;
            }
            $data = $data[$k];
        }
        return $data;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->getNestedValue($this->parameters, $key) ?? $default;
    }
}