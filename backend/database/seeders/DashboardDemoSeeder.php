<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Document;
use App\Models\Folder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use RuntimeException;

class DashboardDemoSeeder extends Seeder
{
    private const MONTHLY_DOCUMENTS = 5;

    public function run(): void
    {
        $users = User::query()->orderBy('email')->get();
        $rootFolders = Folder::query()
            ->whereNull('parent_id')
            ->orderBy('reference_code')
            ->limit(3)
            ->get();

        if ($users->isEmpty()) {
            throw new RuntimeException('Create at least one user before seeding dashboard demo data.');
        }

        if ($rootFolders->count() < 3) {
            throw new RuntimeException('Create at least three root folders before seeding dashboard demo data.');
        }

        $demoFolders = collect(['Administration', 'Finance', 'Human Resources'])
            ->map(function (string $name, int $index) use ($rootFolders): Folder {
                $slug = Str::slug($name);
                $department = Department::firstOrCreate(
                    ['slug' => "demo-dashboard-{$slug}"],
                    [
                        'name' => "[DEMO] {$name}",
                        'description' => 'Fictitious department created for dashboard demonstration data.',
                    ]
                );

                return Folder::firstOrCreate(
                    ['reference_code' => sprintf('DEMO.DASH.%02d', $index + 1)],
                    [
                        'name' => "[DEMO] {$name}",
                        'parent_id' => $rootFolders[$index]->id,
                        'department_id' => $department->id,
                    ]
                );
            });

        $now = Carbon::now();
        $firstMonth = $now->copy()->startOfMonth()->subMonths(5);
        $fileTypes = [
            ['extension' => 'pdf', 'mime_type' => 'application/pdf'],
            ['extension' => 'docx', 'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            ['extension' => 'xlsx', 'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        ];

        for ($monthIndex = 0; $monthIndex < 6; $monthIndex++) {
            $month = $firstMonth->copy()->addMonths($monthIndex);

            for ($documentIndex = 0; $documentIndex < self::MONTHLY_DOCUMENTS; $documentIndex++) {
                $sequence = ($monthIndex * self::MONTHLY_DOCUMENTS) + $documentIndex + 1;
                $createdAt = $month->copy()
                    ->addDays(5 + ($documentIndex * 4))
                    ->setTime(9 + $documentIndex, 0);
                $folder = $demoFolders[($monthIndex + $documentIndex) % $demoFolders->count()];
                $user = $users[($monthIndex + $documentIndex) % $users->count()];
                $fileType = $fileTypes[$sequence % count($fileTypes)];
                $referenceCode = sprintf('DEMO.DASH.%s.%03d', $month->format('Ym'), $sequence);
                $name = sprintf(
                    '[DEMO] Relatorio %s %02d.%s',
                    $month->locale('pt')->monthName,
                    $sequence,
                    $fileType['extension']
                );

                $document = Document::firstOrNew(['reference_code' => $referenceCode]);
                if (! $document->exists) {
                    $document->fill([
                        'folder_id' => $folder->id,
                        'name' => $name,
                        'file_path' => "demo/dashboard/{$referenceCode}.{$fileType['extension']}",
                        'mime_type' => $fileType['mime_type'],
                        'size' => 48_000 + ($sequence * 7_500),
                        'year' => (int) $month->format('Y'),
                        'sequence_number' => $sequence,
                        'user_id' => $user->id,
                    ]);
                    $document->created_at = $createdAt;
                    $document->updated_at = $createdAt;
                    $document->save();
                }

                $this->recordActivity($document, $user, 'UPLOAD', $createdAt);

                if ($documentIndex % 2 === 0) {
                    $this->recordActivity($document, $user, 'DOWNLOAD', $createdAt->copy()->addHours(2));
                }
            }
        }
    }

    private function recordActivity(Document $document, User $user, string $action, Carbon $createdAt): void
    {
        $activity = AuditLog::firstOrNew([
            'user_id' => $user->id,
            'action' => $action,
            'resource_type' => Document::class,
            'resource_id' => $document->id,
        ]);

        if (! $activity->exists) {
            $activity->metadata = ['source' => 'dashboard-demo-seeder'];
            $activity->created_at = $createdAt;
            $activity->save();
        }
    }
}
