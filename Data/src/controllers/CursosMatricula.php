<?php

declare(strict_types=1);

namespace App\controllers;

use App\Models\CursosMatricula as Curso;
use Illuminate\Database\QueryException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CursosMatricula
{
    public function __construct(private ContainerInterface $container){
    }

    public function read(Request $request, Response $response, array $args): Response
    {
        $this->container->get('eloquent');

        if (isset($args['id'])) {
            $curso = Curso::query()
                ->select(['id','codigo_curso','nombre','creditos','cupo_maximo','estado','created_at'])
                ->find($args['id']);

            if ($curso === null) {
                return $this->json($response, ['error' => 'Curso no encontrado.'], 404);
            }
            return $this->json($response, ['data' => $curso], 200);
        }

        $cursos = Curso::query()
            ->select(['id','codigo_curso','nombre','creditos','cupo_maximo','estado','created_at'])
            ->get()
            ->toArray();
        return $this->json($response, ['data' => $cursos], 200);
    }

    public function create(Request $request, Response $response, array $args): Response{
        $this->container->get('eloquent');

        $data = $request->getParsedBody();
        
        $data = is_array($data) ? $data : [];

        $errors = [];
        $codigoCurso = trim((string) ($data['codigo_curso'] ?? ''));
        $nombre = trim((string) ($data['nombre'] ?? ''));
        $creditos = filter_var($data['creditos'] ?? null, FILTER_VALIDATE_INT);
        $cupoMaximo = filter_var($data['cupo_maximo'] ?? null, FILTER_VALIDATE_INT);
        $estado = isset($data['estado'])
            ? trim((string) $data['estado'])
            : '1';

        if ($codigoCurso === '' || mb_strlen($codigoCurso) > 20) {
            $errors['codigo_curso'] = 'Es obligatorio y debe tener máximo 20 caracteres.';
        }
        if ($nombre === '' || mb_strlen($nombre) > 100) {
            $errors['nombre'] = 'Es obligatorio y debe tener máximo 100 caracteres.';
        }
        if ($creditos === false || $creditos < 1) {
            $errors['creditos'] = 'Debe ser un entero positivo.';
        }
        if ($cupoMaximo === false || $cupoMaximo < 1) {
            $errors['cupo_maximo'] = 'Debe ser un entero positivo.';
        }
        if ($estado !== '0' && $estado !== '1') {
            $errors['estado'] = 'Debe ser 0 o 1.';
        }

        if (!empty($errors)) {
            return $this->json($response, ['errors' => $errors], 422);
        }

        try {
            $curso = Curso::create([
                'codigo_curso' => $codigoCurso,
                'nombre' => $nombre,
                'creditos' => $creditos,
                'cupo_maximo' => $cupoMaximo,
                'estado' => $estado,
            ]);

            return $this->json($response, ['data' => $curso->toArray()], 201);

        } catch (QueryException $exception) {
            if ((int) ($exception->errorInfo[1] ?? 0) === 1062) {
                return $this->json($response, ['error' => 'El código del curso ya está registrado.'], 409);
            }
            throw $exception;
        }
    }

    public function update(Request $request, Response $response, array $args): Response {
        $eloquent = $this->container->get('eloquent');

        if (isset($args['id'])) {
            $curso = Curso::find($args['id']);

            if ($curso === null) {
                return $this->json($response, ['error' => 'Curso no encontrado.'], 404);
            }

            $data = $request->getParsedBody();
            $data = is_array($data) ? $data : [];

            $errors = [];

            if (isset($data['codigo_curso'])) {
                $codigoCurso = trim((string) $data['codigo_curso']);
                if ($codigoCurso === '' || mb_strlen($codigoCurso) > 20) {
                    $errors['codigo_curso'] = 'Es obligatorio y debe tener máximo 20 caracteres.';
                } else {
                    $curso->codigo_curso = $codigoCurso;
                }
            }

            if (isset($data['nombre'])) {
                $nombre = trim((string) $data['nombre']);
                if ($nombre === '' || mb_strlen($nombre) > 100) {
                    $errors['nombre'] = 'Es obligatorio y debe tener máximo 100 caracteres.';
                } else {
                    $curso->nombre = $nombre;
                }
            }

            if (isset($data['creditos'])) {
                $creditos = filter_var($data['creditos'], FILTER_VALIDATE_INT);
                if ($creditos === false || $creditos < 1) {
                    $errors['creditos'] = 'Debe ser un entero positivo.';
                } else {
                    $curso->creditos = $creditos;
                }
            }

            if (isset($data['cupo_maximo'])) {
                $cupoMaximo = filter_var($data['cupo_maximo'], FILTER_VALIDATE_INT);
                if ($cupoMaximo === false || $cupoMaximo < 1) {
                    $errors['cupo_maximo'] = 'Debe ser un entero positivo.';
                } else {
                    $curso->cupo_maximo = $cupoMaximo;
                }
            }

            if (isset($data['estado'])) {
                $estado = trim((string) $data['estado']);

                if ($estado !== '0' && $estado !== '1') {
                    $errors['estado'] = 'Debe ser 0 o 1.';
                } else {
                    $curso->estado = $estado;
                }
            }

            if (!empty($errors)) {
                    return $this->json($response, ['errors' => $errors], 422);
            }

            $curso->save();

            return $this->json($response, ['data' => $curso->toArray()], 200);
        }

        return $this->json($response, ['error' => 'Debe indicar el id del curso.'], 422);
    }

    public function delete(Request $request, Response $response, array $args): Response{
        $eloquent = $this->container->get('eloquent');
        if (isset($args['id'])) {
            $curso = Curso::find($args['id']);

            if ($curso === null) {
                return $this->json($response, ['error' => 'Curso no encontrado.'], 404);
            }

            $curso->delete();
            return $this->json($response, ['message' => 'Curso eliminado exitosamente'], 200);
        }

        return $this->json($response, ['error' => 'Debe indicar el id del curso.'], 422);
    }

    private function json(Response $response, array $payload, int $status): Response{
        $response->getBody()->write(json_encode($payload, 
        JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

        return $response
        ->withHeader('Content-Type', 'application/json; charset=utf-8')
        ->withStatus($status);
    }
}
