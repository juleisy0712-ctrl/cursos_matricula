<?php

declare(strict_types=1);

namespace App\Validators;

use Respect\Validation\ValidatorBuilder as v;
use Respect\Validation\ValidationException;

final class Validators
{
    private function reglas(): array
    {
        return [
            'codigo_curso' => [
                'regla' => v::stringType()->notBlank()->length(v::between(1,20)),
                'mensaje' => 'El código del curso es obligatorio y debe tener entre 1 y 20 caracteres.',
            ],
            'nombre' => [
                'regla' => v::stringType()->notBlank()->length(v::between(1,100)),
                'mensaje' => 'El nombre es obligatorio y debe tener entre 1 y 100 caracteres.',
            ],
            'creditos' => [
                'regla' => v::intType()->positive(),
                'mensaje' => 'Los créditos deben ser un entero positivo.',
            ],
            'cupo_maximo' => [
                'regla' => v::intType()->positive(),
                'mensaje' => 'El cupo máximo debe ser un entero positivo.',
            ],
        ];
    }

    public function validate(array $data): array {
        $errores = [];

        foreach($this->reglas() as $campo => $config){
            $valor = $data[$campo] ?? null;
            $regla = $config['regla'];
            $mensaje = $config['mensaje'];
            if($regla->validate($valor)->hasFailed()){
                $errores[$campo] = $mensaje;
            }
        }
        return $errores;
    }
}
