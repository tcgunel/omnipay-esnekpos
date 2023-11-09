<?php

namespace Omnipay\Esnekpos\Models;

use ReflectionProperty;

class BaseModel
{
    public function __construct(?array $abstract)
    {
        foreach ($abstract as $key => $arg) {

            if (property_exists($this, $key)) {

                $property_type = (new ReflectionProperty($this, $key))->getType()->getName();

                if (class_exists($property_type)) {

                    $this->$key = $arg === null ? null : new $property_type($arg);

                } else if (is_array($arg) && class_exists(__NAMESPACE__ . '\\' . $this->singular($key) . 'Model')) {

                    $model_class_name = __NAMESPACE__ . '\\' . $this->singular($key) . 'Model';

                    foreach ($arg as $item){

                        $this->$key[] = new $model_class_name($item);

                    }

                } else if (in_array($property_type, ['string', 'int', 'float', 'bool'], true)) {

                    $this->$key = $arg;

                }

            }

        }

        if (!empty($abstract))
            $this->original_response = json_encode($abstract);
    }

    public string $original_response;

    private function singular(string $plural)
    {
        $singular = $plural;

        $irregularPlurals = [
            's'  => '',
            'es' => '',
            'S'  => '',
            'ES' => '',
        ];

        if (array_key_exists($plural, $irregularPlurals)) {
            $singular = $irregularPlurals[$plural];
        } else {
            $lastTwoLetters = substr($plural, -2);

            if (preg_match('/es$/i', $lastTwoLetters)) {
                $singular = substr($plural, 0, -2);
            } elseif (preg_match('/s$/i', $lastTwoLetters)) {
                $singular = substr($plural, 0, -1);
            }
        }

        return $singular;
    }
}
