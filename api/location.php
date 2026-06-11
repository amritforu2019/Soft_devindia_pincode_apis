<?php

declare(strict_types=1);

require_once __DIR__ . '/../helpers/logger.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/validator.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/redis.php';
require_once __DIR__ . '/../helpers/cache.php';
require_once __DIR__ . '/../helpers/location_repository.php';

$config = require __DIR__ . '/../config/env.php';

date_default_timezone_set($config['app']['timezone']);
mb_internal_encoding('UTF-8');

header('Access-Control-Allow-Origin: ' . $config['app']['cors_origins']);
header('Access-Control-Allow-Methods: ' . $config['app']['cors_methods']);
header('Access-Control-Allow-Headers: ' . $config['app']['cors_headers']);
header('Access-Control-Max-Age: 86400');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    ApiResponse::error('Method not allowed.', 405);
}

try {
    AuthHelper::enforce();

    $action = Validator::action($_GET['action'] ?? null);
    $repository = new LocationRepository(Database::getConnection());

    switch ($action) {
        case 'states':
            handleStates($repository);
            break;

        case 'cities':
            handleCities($repository);
            break;

        case 'areas':
            handleAreas($repository);
            break;

        case 'pincode':
            handlePincode($repository);
            break;

        case 'search':
            handleSearch($repository);
            break;

        default:
            ApiResponse::error('Invalid action parameter.', 400);
    }
} catch (InvalidArgumentException $e) {
    ApiResponse::error($e->getMessage(), 400);
} catch (Throwable $e) {
    Logger::error('Unhandled API exception', [
        'error' => $e->getMessage(),
        'file'  => $e->getFile(),
        'line'  => $e->getLine(),
    ]);

    $message = $config['app']['debug']
        ? 'Internal server error: ' . $e->getMessage()
        : 'Internal server error.';

    ApiResponse::error($message, 500);
}

function handleStates(LocationRepository $repository): never
{
    $cacheKey = CacheHelper::keyForStates();
    $data = CacheHelper::remember($cacheKey, static fn (): array => $repository->getAllStates());

    ApiResponse::success($data);
}

function handleCities(LocationRepository $repository): never
{
    $stateId = Validator::positiveInt($_GET['state_id'] ?? null, 'state_id');
    $cacheKey = CacheHelper::keyForCities($stateId);
    $data = CacheHelper::remember(
        $cacheKey,
        static fn (): array => $repository->getCitiesByState($stateId)
    );

    ApiResponse::success($data);
}

function handleAreas(LocationRepository $repository): never
{
    $cityId = Validator::positiveInt($_GET['city_id'] ?? null, 'city_id');
    $cacheKey = CacheHelper::keyForAreas($cityId);
    $data = CacheHelper::remember(
        $cacheKey,
        static fn (): array => $repository->getAreasByCity($cityId)
    );

    ApiResponse::success($data);
}

function handlePincode(LocationRepository $repository): never
{
    $pincode = Validator::pincode($_GET['pin'] ?? null);
    $cacheKey = CacheHelper::keyForPincode($pincode);
    $data = CacheHelper::remember(
        $cacheKey,
        static fn (): array => $repository->getLocationByPincode($pincode)
    );

    if ($data === []) {
        ApiResponse::error('No location found for the given pincode.', 404);
    }

    ApiResponse::success($data);
}

function handleSearch(LocationRepository $repository): never
{
    $search = Validator::searchId($_GET);
    $cacheKey = CacheHelper::keyForSearchById($search['type'], $search['id']);

    $data = CacheHelper::remember($cacheKey, static function () use ($repository, $search): array {
        $record = match ($search['type']) {
            'state_id' => $repository->getStateById($search['id']),
            'city_id'  => $repository->getCityById($search['id']),
            'area_id'  => $repository->getAreaById($search['id']),
            default    => null,
        };

        return $record !== null ? [$record] : [];
    });

    if ($data === []) {
        ApiResponse::error('No record found for the given ID.', 404);
    }

    ApiResponse::success($data);
}
