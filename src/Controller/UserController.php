<?php

declare(strict_types=1);

namespace Tito\CrudUsers\Controller;

use JsonException;
use Router\Request;
use Router\Response;
use Throwable;
use Tito\CrudUsers\Core\Application;
use Tito\CrudUsers\DTO\CreateUserRequestDTO;
use Tito\CrudUsers\DTO\UpdateUserRequestDTO;
use Tito\CrudUsers\Exception\NotFoundException;
use Tito\CrudUsers\Exception\ValidationException;

final class UserController
{
    public function index(Request $request, Response $response): void
    {
        try {
            $users = Application::userService()->findAll();
            $payload = array_map(fn($user): array => $user->toArray(), $users);

            $response->json($payload);
        } catch (Throwable $e) {
            $this->respondInternalError($response, $e->getMessage());
        }
    }

    public function show(Request $request, Response $response): void
    {
        try {
            $id = (string) $request->param('id', '');
            $user = Application::userService()->findById($id);

            $response->json($user->toArray());
        } catch (NotFoundException $e) {
            $response->jsonError($e->getMessage(), 404);
        } catch (Throwable $e) {
            $this->respondInternalError($response, $e->getMessage());
        }
    }

    public function store(Request $request, Response $response): void
    {
        try {
            $payload = $this->extractPayload($request);
            $dto = CreateUserRequestDTO::fromArray($payload);
            $user = Application::userService()->create($dto);

            $response->json($user->toArray(), 201);
        } catch (ValidationException $e) {
            $response->jsonError($e->getMessage(), 422, $e->errors());
        } catch (Throwable $e) {
            $this->respondInternalError($response, $e->getMessage());
        }
    }

    public function update(Request $request, Response $response): void
    {
        try {
            $id = (string) $request->param('id', '');
            $payload = $this->extractPayload($request);
            $dto = UpdateUserRequestDTO::fromArray($id, $payload);
            $user = Application::userService()->update($dto);

            $response->json($user->toArray());
        } catch (ValidationException $e) {
            $response->jsonError($e->getMessage(), 422, $e->errors());
        } catch (NotFoundException $e) {
            $response->jsonError($e->getMessage(), 404);
        } catch (Throwable $e) {
            $this->respondInternalError($response, $e->getMessage());
        }
    }

    public function destroy(Request $request, Response $response): void
    {
        try {
            $id = (string) $request->param('id', '');
            Application::userService()->deleteById($id);

            $response->json(['message' => 'Usuario eliminado correctamente.']);
        } catch (NotFoundException $e) {
            $response->jsonError($e->getMessage(), 404);
        } catch (Throwable $e) {
            $this->respondInternalError($response, $e->getMessage());
        }
    }

    /** @return array<string, mixed> */
    private function extractPayload(Request $request): array
    {
        if (!$request->isJson()) {
            return $request->allBody();
        }

        try {
            return $request->json();
        } catch (JsonException) {
            return [];
        }
    }

    private function respondInternalError(Response $response, string $message): void
    {
        $response->jsonError('Error interno del servidor.', 500, ['reason' => $message]);
    }
}
