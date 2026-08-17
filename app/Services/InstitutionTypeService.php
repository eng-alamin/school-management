<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * InstitutionTypeService
 *
 * Central place to read institution type metadata (labels + allowed
 * Academic Class numeric range) from config/institution_types.php.
 *
 * Usage examples:
 *   InstitutionTypeService::options();                 // for dropdowns
 *   InstitutionTypeService::classRange('school');       // ['min' => 1, 'max' => 10]
 *   InstitutionTypeService::isClassAllowed('school', 6); // true
 *   InstitutionTypeService::label('madrasa');            // "Madrasa"
 */
final class InstitutionTypeService
{
    /**
     * Raw config array, cached for the request lifecycle.
     *
     * @var array<string, array{label: string, min_numeric: int, max_numeric: int, sort_order: int}>|null
     */
    private static ?array $types = null;

    /**
     * Load and cache the institution_types config.
     *
     * @return array<string, array{label: string, min_numeric: int, max_numeric: int, sort_order: int}>
     */
    private static function all(): array
    {
        if (self::$types === null) {
            self::$types = config('institution_types', []);
        }

        return self::$types;
    }

    /**
     * Get all institution types sorted for dropdown display.
     *
     * @return Collection<string, array{label: string, min_numeric: int, max_numeric: int, sort_order: int}>
     */
    public static function options(): Collection
    {
        return collect(self::all())
            ->sortBy(fn (array $type) => $type['sort_order'] ?? 0)
            ->map(function (array $type, string $slug) {
                $type['slug'] = $slug;

                return $type;
            });
    }

    /**
     * Get a single institution type's config by slug.
     *
     * @return array{label: string, min_numeric: int, max_numeric: int, sort_order: int}|null
     */
    public static function find(?string $slug): ?array
    {
        if ($slug === null) {
            return null;
        }

        return self::all()[$slug] ?? null;
    }

    /**
     * Check whether a given institution type slug exists in config.
     */
    public static function exists(?string $slug): bool
    {
        return self::find($slug) !== null;
    }

    /**
     * Get the display label for a given slug.
     */
    public static function label(string $slug): string
    {
        $type = self::find($slug);

        if ($type === null) {
            return $slug;
        }

        return $type['label'];
    }

    /**
     * Get the allowed Academic Class numeric range for an institution type.
     *
     * @return array{min: int, max: int}
     *
     * @throws InvalidArgumentException if the slug is unknown.
     */
    public static function classRange(string $slug): array
    {
        $type = self::find($slug);

        if ($type === null) {
            throw new InvalidArgumentException("Unknown institution type: [{$slug}]");
        }

        return [
            'min' => $type['min_numeric'],
            'max' => $type['max_numeric'],
        ];
    }

    /**
     * Check whether a given Academic Class numeric value is allowed
     * for the given institution type.
     *
     * Returns true (permissive) when the institution type is unknown
     * or the numeric value is null, so this never blocks legacy data
     * that predates this config. Callers that need strict enforcement
     * should combine this with an explicit exists() check.
     */
    public static function isClassAllowed(?string $slug, ?int $numeric): bool
    {
        if ($slug === null || $numeric === null) {
            return true;
        }

        $type = self::find($slug);

        if ($type === null) {
            return true;
        }

        return $numeric >= $type['min_numeric'] && $numeric <= $type['max_numeric'];
    }
}