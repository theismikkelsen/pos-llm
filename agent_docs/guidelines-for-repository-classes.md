# Guidelines For Repository Classes

## Approach

* **Explicit Mapping:** Manually map data in both directions (Database to Domain and Domain to Persistence). Do not use `Spatie\LaravelData\Data` for mapping within a repository.
* **No DocBlocks for Mapping:** The `mapToDomain` method should not include a DocBlock.
* **Return IDs on Create:** The `add` method must return the ID of the newly created record rather than the object itself.
* **Standardized Naming:** Use consistent verbs for method names: `get`, `find`, `add`, `update`, and `delete`.
* **Avoid Premature Casting:** Do not cast database columns to scalar types unless strictly necessary; doing so can mask underlying data integrity issues or errors.
* **PHPStan Property Access:** When mapping from a generic $dbRow object, use inline `// @phpstan-ignore property.notFound` comments for each property access to satisfy static analysis.

## Idiomatic, Generic Example Of A Repository Class

```PHP
<?php declare(strict_types=1);

namespace App\Repositories;

use App\Domain\Appointment;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AppointmentRepository	 
{
    public function get(int $id): Appointment
    {
        $dbRow = DB::table('appointments')
            ->where(['id' => $id])
            ->sole();

        return self::mapToDomain($dbRow);
    }

    /**
     * @return Collection<int, Appointment>
     */
    public function findAll(): Collection
    {
        return DB::table('appointments')
            ->get()
            ->map(fn($dbRow) => self::mapToDomain($dbRow));
    }

    /**
     * @return Collection<int, Appointment>
     */
    public function findByPatient(int $patientId): Collection
    {
        return DB::table('appointments')
            ->where('patient_id', $patientId)
            ->get()
            ->map(fn($dbRow) => self::mapToDomain($dbRow));
    }

    public function add(Appointment $appointment): int
    {
        return DB::table('appointments')->insertGetId(self::mapToPersistence($appointment));
    }

    public function update(Appointment $appointment): void
    {
        DB::table('appointments')
            ->where(['id' => $appointment->id])
            ->update(self::mapToPersistence($appointment));
    }

    private static function mapToDomain(object $dbRow): Appointment
    {
        return new Appointment(
            idAndTenant: new IdAndTenant(id: $dbRow->id, tenantId: $dbRow->tenant_id), // @phpstan-ignore property.notFound, property.notFound
            patientId: $dbRow->patient_id, // @phpstan-ignore property.notFound
            scheduledAt: CarbonImmutable::createFromFormat('Y-m-d H:i:s', $dbRow->scheduled_at), // @phpstan-ignore property.notFound
            reminderChannel: $dbRow->reminder_channel // @phpstan-ignore property.notFound
        );
    }

    /**
     * @return array<string, bool|int|string|CarbonImmutable>
     */
    private static function mapToPersistence(Appointment $appointment): array
    {
        return [
            'id' => $appointment->idAndTenant->idNullable,
            'tenant_id' => $appointment->idAndTenant->tenantId,
            'patient_id' => $appointment->patientId,
            'scheduled_at' => $appointment->scheduledAt->format('Y-m-d H:i:s'),
            'reminder_channel' => $appointment->reminderChannel,
        ];
    }
}
```
