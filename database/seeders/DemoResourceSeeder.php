<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Resource;
use App\Models\Role;
use App\Models\StudentResource;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoResourceSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $coachRole = Role::where('name', 'coach')->first();
        $studentRole = Role::where('name', 'student')->first();

        if (!$adminRole || !$coachRole || !$studentRole) {
            return;
        }

        $admin = User::where('role_id', $adminRole->id)->first();
        $coaches = User::where('role_id', $coachRole->id)->take(2)->get();
        $students = User::where('role_id', $studentRole->id)->take(5)->get();

        if (!$admin || $coaches->isEmpty() || $students->isEmpty()) {
            return;
        }

        // Admin tarafından eklenen kaynaklar
        $adminResources = [
            [
                'name' => 'TYT Matematik Soru Bankası',
                'description' => 'TYT matematik konularını kapsayan kapsamlı soru bankası',
            ],
            [
                'name' => 'AYT Fizik 1000 Soru',
                'description' => 'AYT fizik müfredatını kapsayan detaylı soru bankası',
            ],
            [
                'name' => 'TYT Türkçe Deneme Seti',
                'description' => '40 adet TYT türkçe denemesi',
            ],
            [
                'name' => 'AYT Matematik Konu Anlatımlı',
                'description' => 'AYT matematik konuları detaylı anlatım ve örneklerle',
            ],
            [
                'name' => 'DGS Sayısal Mantık',
                'description' => 'DGS sayısal mantık ve matematik soruları',
            ],
            [
                'name' => 'KPSS Genel Yetenek',
                'description' => 'KPSS genel yetenek testleri',
            ],
            [
                'name' => 'TYT Geometri Fasikülü',
                'description' => 'TYT geometri konuları fasikül şeklinde',
            ],
            [
                'name' => 'AYT Kimya Soru Bankası',
                'description' => 'AYT kimya dersi için kapsamlı soru bankası',
            ],
            [
                'name' => 'TYT Tarih Konu Anlatım',
                'description' => 'TYT tarih konularının özet anlatımı',
            ],
            [
                'name' => 'AYT Edebiyat Denemeler',
                'description' => '30 adet AYT edebiyat denemesi',
            ],
            [
                'name' => 'TYT Biyoloji Soru Bankası',
                'description' => 'TYT biyoloji konuları kapsamlı soru bankası',
            ],
            [
                'name' => 'KPSS Genel Kültür',
                'description' => 'KPSS genel kültür soru bankası',
            ],
        ];

        foreach ($adminResources as $resourceData) {
            Resource::create([
                'name' => $resourceData['name'],
                'description' => $resourceData['description'],
                'created_by_user_id' => $admin->id,
                'is_admin_resource' => true,
            ]);
        }

        // Koçlar tarafından eklenen kaynaklar
        foreach ($coaches as $coach) {
            $coachResources = [
                [
                    'name' => 'Özel Ders Notu - ' . $coach->name,
                    'description' => 'Kendi hazırladığım ders notları',
                ],
                [
                    'name' => 'Test Kitabı - ' . substr($coach->name, 0, 10),
                    'description' => 'Öğrencilerim için hazırladığım testler',
                ],
            ];

            foreach ($coachResources as $resourceData) {
                Resource::create([
                    'name' => $resourceData['name'],
                    'description' => $resourceData['description'],
                    'created_by_user_id' => $coach->id,
                    'is_admin_resource' => false,
                ]);
            }
        }

        // Kaynakları öğrencilere atama
        $allResources = Resource::all();
        $courses = Course::where('is_active', true)->get();

        foreach ($coaches as $coach) {
            // Her koçun öğrencilerini bul
            $coachStudents = $coach->students;

            foreach ($coachStudents as $student) {
                // Her öğrenciye 3-5 kaynak ata
                $resourcesToAssign = $allResources->random(rand(3, 5));

                foreach ($resourcesToAssign as $resource) {
                    // Bazı kaynaklara ders de ata
                    $course = rand(0, 1) ? $courses->random() : null;

                    StudentResource::create([
                        'student_id' => $student->id,
                        'coach_id' => $coach->id,
                        'resource_id' => $resource->id,
                        'course_id' => $course?->id,
                        'assigned_at' => now()->subDays(rand(1, 30)),
                    ]);
                }
            }
        }
    }
}

