<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use App\Models\Attendance;
use App\Models\ContactMessage;
use App\Models\Course;
use App\Models\Discount;
use App\Models\Enrollment;
use App\Models\Evaluation;
use App\Models\Fee;
use App\Models\Formation;
use App\Models\Grade;
use App\Models\Module;
use App\Models\ParentProfile;
use App\Models\Payment;
use App\Models\Room;
use App\Models\Site;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Reset permissions cache ──────────────────────────────
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->call(SiteSeeder::class);
        $cotonouSiteId = Site::query()->where('nom', 'Cotonou')->value('id');

        // ── 1. Rôles IFAM ─────────────────────────────────────────
        $this->call(RoleSeeder::class);

        $password = Hash::make('Admin@1234!');

        // ── 2. Utilisateurs de test ───────────────────────────────

        // Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@ifam.bj'],
            [
                'first_name' => 'Admin',
                'last_name' => 'IFAM',
                'matricule' => 'ADM-001',
                'password' => $password,
                'is_active' => true,
                'must_change_password' => false,
            ]
        );
        $admin->syncRoles(['administrateur']);

        // Direction
        $direction = User::firstOrCreate(
            ['email' => 'direction@ifam.bj'],
            [
                'first_name' => 'Directrice',
                'last_name' => 'AKPOVO',
                'matricule' => 'DIR-001',
                'password' => null,
                'activation_token' => Str::random(60),
                'activation_expires_at' => now()->addHours(48),
                'is_active' => true,
                'must_change_password' => true,
            ]
        );
        $direction->syncRoles(['direction']);

        // Enseignant
        $teacherUser = User::firstOrCreate(
            ['email' => 'prof.dupont@ifam.bj'],
            [
                'first_name' => 'Jean',
                'last_name' => 'DUPONT',
                'matricule' => 'ENS-001',
                'password' => null,
                'activation_token' => Str::random(60),
                'activation_expires_at' => now()->addHours(48),
                'is_active' => true,
                'must_change_password' => true,
            ]
        );
        $teacherUser->syncRoles(['enseignant']);
        $teacherProfile = TeacherProfile::firstOrCreate(
            ['user_id' => $teacherUser->id],
            [
                'teacher_type' => 'permanent',
                'qualification' => 'Docteur / Enseignant-Chercheur',
            ]
        );

        // Étudiant 1 (Marc KODJO)
        $studentUser1 = User::firstOrCreate(
            ['email' => 'etudiant@ifam.bj'],
            [
                'first_name' => 'Marc',
                'last_name' => 'KODJO',
                'matricule' => 'IFAM-2026-0001',
                'birth_date' => '2004-05-15',
                'gender' => 'M',
                'phone' => '+229 97 00 11 22',
                'password' => null,
                'activation_token' => Str::random(60),
                'activation_expires_at' => now()->addHours(48),
                'is_active' => true,
                'must_change_password' => true,
            ]
        );
        $studentUser1->syncRoles(['etudiant']);
        $studentProfile1 = StudentProfile::firstOrCreate(
            ['user_id' => $studentUser1->id],
            [
                'nationality' => 'Béninoise',
                'birth_place' => 'Cotonou',
                'city' => 'Cotonou',
                'address' => 'Haie Vive, Carré 124',
                'entry_year' => 2025,
            ]
        );

        // Étudiant 2 (Amina SOULE)
        $studentUser2 = User::firstOrCreate(
            ['email' => 'etudiante@ifam.bj'],
            [
                'first_name' => 'Amina',
                'last_name' => 'SOULE',
                'matricule' => 'IFAM-2026-0002',
                'birth_date' => '2005-02-20',
                'gender' => 'F',
                'phone' => '+229 95 33 44 55',
                'password' => null,
                'activation_token' => Str::random(60),
                'activation_expires_at' => now()->addHours(48),
                'is_active' => true,
                'must_change_password' => true,
            ]
        );
        $studentUser2->syncRoles(['etudiant']);
        $studentProfile2 = StudentProfile::firstOrCreate(
            ['user_id' => $studentUser2->id],
            [
                'nationality' => 'Béninoise',
                'birth_place' => 'Porto-Novo',
                'city' => 'Cotonou',
                'entry_year' => 2025,
            ]
        );

        // Parent (Paul KODJO)
        $parentUser = User::firstOrCreate(
            ['email' => 'parent@ifam.bj'],
            [
                'first_name' => 'Paul',
                'last_name' => 'KODJO',
                'phone' => '+229 97 88 99 00',
                'password' => null,
                'activation_token' => Str::random(60),
                'activation_expires_at' => now()->addHours(48),
                'is_active' => true,
                'must_change_password' => true,
            ]
        );
        $parentUser->syncRoles(['parent']);
        ParentProfile::firstOrCreate(
            ['user_id' => $parentUser->id],
            [
                'profession' => 'Ingénieur Télécom',
            ]
        );

        // Liaison parent <-> étudiant 1
        if (! $parentUser->children()->where('student_id', $studentUser1->id)->exists()) {
            $parentUser->children()->attach($studentUser1->id, ['relationship' => 'Père']);
        }

        // ── 3. Formations ─────────────────────────────────────────
        $formationLIR = Formation::firstOrCreate(
            ['name' => 'Licence Informatique & Réseaux (LIR)'],
            [
                'is_active' => true,
            ]
        );

        $formationLGE = Formation::firstOrCreate(
            ['name' => 'Licence Gestion des Entreprises (LGE)'],
            [
                'is_active' => true,
            ]
        );

        // ── 4. Sessions académiques ───────────────────────────────
        $sessionCurrent = AcademicSession::firstOrCreate(
            ['name' => 'Année Académique 2025-2026'],
            [
                'start_date' => '2025-10-01',
                'end_date' => '2026-07-31',
                'is_current' => true,
                'site_id' => $cotonouSiteId,
            ]
        );

        // ── 5. Inscriptions (Enrollments) ─────────────────────────
        Enrollment::firstOrCreate(
            [
                'student_profile_id' => $studentProfile1->id,
                'formation_id' => $formationLIR->id,
                'academic_session_id' => $sessionCurrent->id,
            ],
            [
                'status' => 'active',
                'enrollment_date' => '2025-10-05',
                'notes' => 'Inscription validée après étude du dossier.',
                'site_id' => $cotonouSiteId,
            ]
        );

        Enrollment::firstOrCreate(
            [
                'student_profile_id' => $studentProfile2->id,
                'formation_id' => $formationLIR->id,
                'academic_session_id' => $sessionCurrent->id,
            ],
            [
                'status' => 'active',
                'enrollment_date' => '2025-10-06',
                'notes' => 'Inscription standard.',
                'site_id' => $cotonouSiteId,
            ]
        );

        // ── 6. Modules ────────────────────────────────────────────
        $mod1 = Module::firstOrCreate(
            ['code' => 'INF101'],
            [
                'name' => 'Algorithmique & Structures de Données',
                'coefficient' => 3,
                'description' => 'Fondements de la logique algorithmique, complexité et programmation.',
            ]
        );

        $mod2 = Module::firstOrCreate(
            ['code' => 'INF102'],
            [
                'name' => 'Systèmes de Gestion de Bases de Données (SQL)',
                'coefficient' => 4,
                'description' => 'Conception relationnelle, normalisation, requêtes SQL et transactions.',
            ]
        );

        $mod3 = Module::firstOrCreate(
            ['code' => 'INF103'],
            [
                'name' => 'Développement Web Moderne (Laravel & Livewire)',
                'coefficient' => 5,
                'description' => 'Architecture MVC, composants Livewire, API REST et interfaces réactives.',
            ]
        );

        $teacherProfile->modules()->sync([$mod1->id, $mod2->id, $mod3->id]);

        // ── 7. Salles ─────────────────────────────────────────────
        Room::firstOrCreate(['name' => 'Salle Info A1'], ['description' => 'Laboratoire (35 places)', 'is_active' => true, 'site_id' => $cotonouSiteId]);
        Room::firstOrCreate(['name' => 'Amphithéâtre Houdégbé'], ['description' => 'Amphi (120 places)', 'is_active' => true, 'site_id' => $cotonouSiteId]);

        // ── 8. Cours ──────────────────────────────────────────────
        $course1 = Course::firstOrCreate(
            [
                'formation_id' => $formationLIR->id,
                'academic_session_id' => $sessionCurrent->id,
                'module_id' => $mod1->id,
            ],
            [
                'enseignant_id' => $teacherProfile->id,
                'name' => 'Algorithmique & Programmation LIR',
                'color' => '#e40066',
            ]
        );

        $course2 = Course::firstOrCreate(
            [
                'formation_id' => $formationLIR->id,
                'academic_session_id' => $sessionCurrent->id,
                'module_id' => $mod2->id,
            ],
            [
                'enseignant_id' => $teacherProfile->id,
                'name' => 'Bases de Données Relationnelles LIR',
                'color' => '#2563eb',
            ]
        );

        $course3 = Course::firstOrCreate(
            [
                'formation_id' => $formationLIR->id,
                'academic_session_id' => $sessionCurrent->id,
                'module_id' => $mod3->id,
            ],
            [
                'enseignant_id' => $teacherProfile->id,
                'name' => 'Développement d\'Applications Web LIR',
                'color' => '#059669',
            ]
        );

        // ── 9. Évaluations & Notes ────────────────────────────────
        // Éval 1 : Interrogation Algo
        $eval1 = Evaluation::firstOrCreate(
            [
                'cours_id' => $course1->id,
                'titre' => 'Interrogation Écrite #1 - Complexité & Tris',
            ],
            [
                'type' => 'controle_continu',
                'date' => '2025-11-15',
                'statut' => 'cloturee',
            ]
        );
        Grade::firstOrCreate(
            ['evaluation_id' => $eval1->id, 'student_profile_id' => $studentProfile1->id],
            ['score' => 15.5, 'is_absent' => false, 'comments' => 'Bonne maîtrise des concepts de base.']
        );
        Grade::firstOrCreate(
            ['evaluation_id' => $eval1->id, 'student_profile_id' => $studentProfile2->id],
            ['score' => 13.0, 'is_absent' => false]
        );

        // Éval 2 : Devoir Surveillé BD
        $eval2 = Evaluation::firstOrCreate(
            [
                'cours_id' => $course2->id,
                'titre' => 'Devoir Surveillé - Modélisation & SQL Avancé',
            ],
            [
                'type' => 'devoir',
                'date' => '2025-12-10',
                'statut' => 'cloturee',
            ]
        );
        Grade::firstOrCreate(
            ['evaluation_id' => $eval2->id, 'student_profile_id' => $studentProfile1->id],
            ['score' => 14.0, 'is_absent' => false, 'comments' => 'Schéma relationnel très propre.']
        );
        Grade::firstOrCreate(
            ['evaluation_id' => $eval2->id, 'student_profile_id' => $studentProfile2->id],
            ['score' => 16.5, 'is_absent' => false]
        );

        // Éval 3 : Projet Partiel Web Laravel
        $eval3 = Evaluation::firstOrCreate(
            [
                'cours_id' => $course3->id,
                'titre' => 'Examen Pratique - Réalisation d\'une App Laravel',
            ],
            [
                'type' => 'devoir',
                'date' => '2026-01-20',
                'statut' => 'cloturee',
            ]
        );
        Grade::firstOrCreate(
            ['evaluation_id' => $eval3->id, 'student_profile_id' => $studentProfile1->id],
            ['score' => 16.0, 'is_absent' => false, 'comments' => 'Excellent travail sur l\'UI et les composants.']
        );
        Grade::firstOrCreate(
            ['evaluation_id' => $eval3->id, 'student_profile_id' => $studentProfile2->id],
            ['score' => 14.5, 'is_absent' => false]
        );

        // ── 10. Présences ─────────────────────────────────────────
        Attendance::firstOrCreate(
            [
                'course_id' => $course1->id,
                'student_profile_id' => $studentProfile1->id,
                'date' => '2026-02-02',
            ],
            ['status' => 'present']
        );
        Attendance::firstOrCreate(
            [
                'course_id' => $course2->id,
                'student_profile_id' => $studentProfile1->id,
                'date' => '2026-02-03',
            ],
            ['status' => 'present']
        );
        Attendance::firstOrCreate(
            [
                'course_id' => $course3->id,
                'student_profile_id' => $studentProfile1->id,
                'date' => '2026-02-04',
            ],
            ['status' => 'present']
        );

        // ── 11. Frais, Bourses et Paiements ───────────────────────
        $fee = Fee::firstOrCreate(
            [
                'student_profile_id' => $studentProfile1->id,
                'title' => 'Frais Annuels de Scolarité 2025-2026',
            ],
            [
                'academic_session_id' => $sessionCurrent->id,
                'amount' => 450000,
                'due_date' => '2025-11-30',
                'status' => 'partial',
                'description' => 'Scolarité annuelle pour Licence Informatique & Réseaux.',
            ]
        );

        // Réduction / Bourse
        Discount::firstOrCreate(
            [
                'student_profile_id' => $studentProfile1->id,
                'label' => 'Bourse au mérite académique',
            ],
            [
                'fee_id' => $fee->id,
                'type' => 'fixed',
                'value' => 50000,
                'reason' => 'Mention Très Bien au Baccalauréat.',
            ]
        );

        // Paiement 1
        Payment::firstOrCreate(
            [
                'student_profile_id' => $studentProfile1->id,
                'reference' => 'REC-2025-001',
            ],
            [
                'fee_id' => $fee->id,
                'amount_paid' => 200000,
                'payment_date' => '2025-10-15',
                'method' => 'bank_transfer',
                'notes' => 'Acompte 1ère tranche rentrée.',
            ]
        );

        // ── 12. Message de contact ────────────────────────────────
        ContactMessage::firstOrCreate(
            [
                'user_id' => $studentUser1->id,
                'subject' => 'Demande d\'attestation de scolarité',
            ],
            [
                'message' => 'Bonjour Monsieur l\'administrateur, je souhaiterais obtenir mon attestation d\'inscription pour mon dossier de passeport. Merci d\'avance.',
                'is_read' => false,
                'site_id' => $cotonouSiteId,
            ]
        );
    }
}
