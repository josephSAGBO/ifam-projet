<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Evaluation;
use App\Models\Formation;
use App\Models\Grade;
use App\Models\Module;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EvaluationAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_assign_a_devoir_to_a_teacher_associated_with_its_module(): void
    {
        $context = $this->createContext();

        $response = $this->actingAs($context['admin'])->post(route('admin.evaluations.store'), [
            'formation_id' => $context['formation']->id,
            'session_id' => $context['session']->id,
            'module_id' => $context['module']->id,
            'assigned_teacher_profile_id' => $context['assignedTeacher']->id,
            'titre' => 'Devoir de coupe',
            'date' => '2026-10-20',
            'statut' => 'ouverte',
        ]);

        $response->assertRedirect(route('admin.evaluations.index'));
        $evaluation = Evaluation::query()->sole();
        $this->assertSame($context['formation']->id, $evaluation->formation_id);
        $this->assertSame($context['assignedTeacher']->id, $evaluation->assigned_teacher_profile_id);
        $this->assertSame($context['session']->id, $evaluation->session_id);
        $this->assertSame($context['module']->id, $evaluation->module_id);
        $this->assertSame('devoir', $evaluation->type);
    }

    public function test_admin_cannot_assign_a_devoir_to_a_teacher_not_associated_with_its_module(): void
    {
        $context = $this->createContext();

        $response = $this->actingAs($context['admin'])->post(route('admin.evaluations.store'), [
            'formation_id' => $context['formation']->id,
            'session_id' => $context['session']->id,
            'module_id' => $context['module']->id,
            'assigned_teacher_profile_id' => $context['otherTeacher']->id,
            'titre' => 'Devoir de coupe',
            'date' => '2026-10-20',
            'statut' => 'ouverte',
        ]);

        $response->assertSessionHasErrors('assigned_teacher_profile_id');
        $this->assertDatabaseCount('evaluations', 0);
    }

    public function test_admin_notes_navigation_filters_evaluations_by_session_formation_and_module(): void
    {
        $context = $this->createContext();
        $selectedEvaluation = Evaluation::create([
            'type' => 'devoir',
            'created_by' => $context['admin']->id,
            'assigned_teacher_profile_id' => $context['assignedTeacher']->id,
            'formation_id' => $context['formation']->id,
            'module_id' => $context['module']->id,
            'session_id' => $context['session']->id,
            'titre' => 'Devoir à afficher',
            'statut' => 'ouverte',
        ]);
        $otherSession = AcademicSession::create([
            'name' => '2027-2028',
            'start_date' => '2027-09-01',
            'end_date' => '2028-06-30',
        ]);
        $otherFormation = Formation::create(['name' => 'Autre formation']);
        $otherModule = Module::create(['name' => 'Autre module', 'code' => 'MOD-AUTRE']);
        $otherFormation->modules()->attach($otherModule->id);
        $otherModule->teachers()->attach($context['assignedTeacher']->id);
        Evaluation::create([
            'type' => 'devoir',
            'created_by' => $context['admin']->id,
            'assigned_teacher_profile_id' => $context['assignedTeacher']->id,
            'formation_id' => $otherFormation->id,
            'module_id' => $otherModule->id,
            'session_id' => $otherSession->id,
            'titre' => 'Devoir à masquer',
            'statut' => 'ouverte',
        ]);

        $response = $this->actingAs($context['admin'])->get(route('admin.grades.index', [
            'session_id' => $context['session']->id,
            'formation_id' => $context['formation']->id,
            'module_id' => $context['module']->id,
        ]));

        $response->assertOk();
        $response->assertSee('Devoir à afficher');
        $response->assertDontSee('Devoir à masquer');
        $response->assertSee(route('admin.grades.show', $selectedEvaluation));
    }

    public function test_teacher_course_grades_are_visible_in_admin_notes(): void
    {
        $context = $this->createContext();
        $course = Course::create([
            'formation_id' => $context['formation']->id,
            'academic_session_id' => $context['session']->id,
            'module_id' => $context['module']->id,
            'enseignant_id' => $context['assignedTeacher']->id,
            'teacher_profile_id' => $context['assignedTeacher']->id,
            'name' => 'Cours de coupe',
        ]);
        $studentUser = User::create([
            'first_name' => 'Paul',
            'last_name' => 'Etudiant',
            'email' => 'paul-course-grade@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $student = StudentProfile::create(['user_id' => $studentUser->id]);
        Enrollment::create([
            'student_profile_id' => $student->id,
            'formation_id' => $context['formation']->id,
            'academic_session_id' => $context['session']->id,
            'status' => 'active',
        ]);

        $createResponse = $this->actingAs($context['assignedTeacher']->user)
            ->post(route('teacher.grades.createEvaluation'), [
                'course_id' => $course->id,
                'titre' => 'Contrôle continu créé par enseignant',
                'date' => '2026-10-20',
                'type' => 'controle_continu',
            ]);
        $createResponse->assertRedirect(route('teacher.grades.index', ['course_id' => $course->id]));

        $evaluation = Evaluation::query()->sole();
        $this->assertSame($context['formation']->id, $evaluation->formation_id);

        $saveResponse = $this->actingAs($context['assignedTeacher']->user)
            ->post(route('teacher.grades.store'), [
                'course_id' => $course->id,
                'evaluation_id' => $evaluation->id,
                'grades' => [
                    $student->id => ['score' => '16', 'comments' => 'Bon travail'],
                ],
            ]);
        $saveResponse->assertRedirect(route('teacher.grades.index', ['course_id' => $course->id]));
        $this->assertDatabaseHas('grades', [
            'evaluation_id' => $evaluation->id,
            'student_profile_id' => $student->id,
            'score' => 16,
            'comments' => 'Bon travail',
        ]);

        $adminResponse = $this->actingAs($context['admin'])->get(route('admin.grades.index', [
            'session_id' => $context['session']->id,
            'formation_id' => $context['formation']->id,
            'module_id' => $context['module']->id,
        ]));
        $adminResponse->assertSee('Contrôle continu créé par enseignant');

        $adminEvaluationsResponse = $this->actingAs($context['admin'])
            ->get(route('admin.evaluations.index'));
        $adminEvaluationsResponse->assertSeeInOrder([
            'Enseignant assigné',
            'Créé par',
            'Professeur Affecte',
            'Professeur Affecte',
        ]);
        $adminEvaluationsResponse->assertDontSee('Non assigné');

        $adminGradeView = $this->actingAs($context['admin'])->get(route('admin.grades.show', $evaluation));
        $adminGradeView->assertSee('Etudiant Paul');
        $adminGradeView->assertSee('16');
    }

    public function test_admin_notes_include_course_evaluations_missing_formation_id(): void
    {
        $context = $this->createContext();
        $course = Course::create([
            'formation_id' => $context['formation']->id,
            'academic_session_id' => $context['session']->id,
            'module_id' => $context['module']->id,
            'enseignant_id' => $context['assignedTeacher']->id,
            'teacher_profile_id' => $context['assignedTeacher']->id,
            'name' => 'Cours historique',
        ]);
        $evaluation = Evaluation::create([
            'type' => 'controle_continu',
            'created_by' => $context['assignedTeacher']->user->id,
            'cours_id' => $course->id,
            'module_id' => $course->module_id,
            'session_id' => $course->academic_session_id,
            'titre' => 'Ancien contrôle continu',
            'statut' => 'ouverte',
        ]);
        $studentUser = User::create([
            'first_name' => 'Paul',
            'last_name' => 'Etudiant',
            'email' => 'paul-legacy-grade@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $student = StudentProfile::create(['user_id' => $studentUser->id]);
        Enrollment::create([
            'student_profile_id' => $student->id,
            'formation_id' => $context['formation']->id,
            'academic_session_id' => $context['session']->id,
            'status' => 'active',
        ]);
        Grade::create([
            'evaluation_id' => $evaluation->id,
            'student_profile_id' => $student->id,
            'score' => 14,
        ]);

        $response = $this->actingAs($context['admin'])->get(route('admin.grades.index', [
            'session_id' => $context['session']->id,
            'formation_id' => $context['formation']->id,
            'module_id' => $context['module']->id,
        ]));

        $response->assertSee('Ancien contrôle continu');
        $response->assertSee(route('admin.grades.show', $evaluation));

        $adminGradeView = $this->actingAs($context['admin'])->get(route('admin.grades.show', $evaluation));
        $adminGradeView->assertSee('Etudiant Paul');
        $adminGradeView->assertSee('14');
    }

    public function test_assigned_teacher_can_enter_devoir_grades_for_active_enrolled_students(): void
    {
        $context = $this->createContext();
        $studentUser = User::create([
            'first_name' => 'Paul',
            'last_name' => 'Etudiant',
            'email' => 'paul@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $student = StudentProfile::create(['user_id' => $studentUser->id]);
        Enrollment::create([
            'student_profile_id' => $student->id,
            'formation_id' => $context['formation']->id,
            'academic_session_id' => $context['session']->id,
            'status' => 'active',
        ]);
        $evaluation = Evaluation::create([
            'type' => 'devoir',
            'created_by' => $context['admin']->id,
            'assigned_teacher_profile_id' => $context['assignedTeacher']->id,
            'formation_id' => $context['formation']->id,
            'module_id' => $context['module']->id,
            'session_id' => $context['session']->id,
            'titre' => 'Devoir de coupe',
            'statut' => 'ouverte',
        ]);

        $listing = $this->actingAs($context['assignedTeacher']->user)->get(route('teacher.grades.index'));
        $listing->assertSee('Devoir de coupe');

        $gradeForm = $this->actingAs($context['assignedTeacher']->user)->get(route('teacher.grades.assigned.show', $evaluation));
        $gradeForm->assertOk();
        $gradeForm->assertSee('Paul Etudiant');

        $saveResponse = $this->actingAs($context['assignedTeacher']->user)->post(route('teacher.grades.assigned.store', $evaluation), [
            'grades' => [
                $student->id => ['score' => '15', 'comments' => 'Bon travail'],
            ],
        ]);

        $saveResponse->assertRedirect(route('teacher.grades.assigned.show', $evaluation));
        $this->assertDatabaseHas('grades', [
            'evaluation_id' => $evaluation->id,
            'student_profile_id' => $student->id,
            'score' => 15,
            'comments' => 'Bon travail',
        ]);

        $adminView = $this->actingAs($context['admin'])->get(route('admin.grades.show', $evaluation));
        $adminView->assertOk();
        $adminView->assertSee('Etudiant Paul');
        $adminView->assertSee('Devoir de coupe');
    }

    public function test_teacher_can_delete_a_grade_for_their_continuous_assessment(): void
    {
        $context = $this->createContext();
        $course = Course::create([
            'formation_id' => $context['formation']->id,
            'academic_session_id' => $context['session']->id,
            'module_id' => $context['module']->id,
            'enseignant_id' => $context['assignedTeacher']->id,
            'teacher_profile_id' => $context['assignedTeacher']->id,
            'name' => 'Cours de coupe',
        ]);
        $evaluation = Evaluation::create([
            'type' => 'controle_continu',
            'created_by' => $context['assignedTeacher']->user->id,
            'cours_id' => $course->id,
            'formation_id' => $course->formation_id,
            'module_id' => $course->module_id,
            'session_id' => $course->academic_session_id,
            'titre' => 'Contrôle continu',
            'statut' => 'ouverte',
        ]);
        $studentUser = User::create([
            'first_name' => 'Paul',
            'last_name' => 'Etudiant',
            'email' => 'paul-delete-grade@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $student = StudentProfile::create(['user_id' => $studentUser->id]);
        Enrollment::create([
            'student_profile_id' => $student->id,
            'formation_id' => $context['formation']->id,
            'academic_session_id' => $context['session']->id,
            'status' => 'active',
        ]);
        $grade = Grade::create([
            'evaluation_id' => $evaluation->id,
            'student_profile_id' => $student->id,
            'score' => 16,
        ]);

        $page = $this->actingAs($context['assignedTeacher']->user)
            ->get(route('teacher.grades.index', ['course_id' => $course->id]));
        $page->assertSee(route('teacher.grades.destroy', $grade));
        $page->assertSee('Supprimer');

        $response = $this->actingAs($context['assignedTeacher']->user)
            ->delete(route('teacher.grades.destroy', $grade));

        $response->assertRedirect(route('teacher.grades.index', ['course_id' => $course->id]));
        $this->assertDatabaseMissing('grades', ['id' => $grade->id]);
    }

    public function test_teacher_cannot_delete_a_grade_for_a_devoir(): void
    {
        $context = $this->createContext();
        $course = Course::create([
            'formation_id' => $context['formation']->id,
            'academic_session_id' => $context['session']->id,
            'module_id' => $context['module']->id,
            'enseignant_id' => $context['assignedTeacher']->id,
            'teacher_profile_id' => $context['assignedTeacher']->id,
            'name' => 'Cours de coupe',
        ]);
        $evaluation = Evaluation::create([
            'type' => 'devoir',
            'created_by' => $context['admin']->id,
            'assigned_teacher_profile_id' => $context['assignedTeacher']->id,
            'cours_id' => $course->id,
            'formation_id' => $course->formation_id,
            'module_id' => $course->module_id,
            'session_id' => $course->academic_session_id,
            'titre' => 'Devoir',
            'statut' => 'ouverte',
        ]);
        $studentUser = User::create([
            'first_name' => 'Paul',
            'last_name' => 'Etudiant',
            'email' => 'paul-delete-homework-grade@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $student = StudentProfile::create(['user_id' => $studentUser->id]);
        $grade = Grade::create([
            'evaluation_id' => $evaluation->id,
            'student_profile_id' => $student->id,
            'score' => 16,
        ]);

        $response = $this->actingAs($context['assignedTeacher']->user)
            ->delete(route('teacher.grades.destroy', $grade));

        $response->assertForbidden();
        $this->assertDatabaseHas('grades', ['id' => $grade->id, 'score' => 16]);
    }

    public function test_teacher_cannot_delete_a_grade_for_another_teachers_continuous_assessment(): void
    {
        $context = $this->createContext();
        $course = Course::create([
            'formation_id' => $context['formation']->id,
            'academic_session_id' => $context['session']->id,
            'module_id' => $context['module']->id,
            'enseignant_id' => $context['assignedTeacher']->id,
            'teacher_profile_id' => $context['assignedTeacher']->id,
            'name' => 'Cours de coupe',
        ]);
        $evaluation = Evaluation::create([
            'type' => 'controle_continu',
            'created_by' => $context['assignedTeacher']->user->id,
            'cours_id' => $course->id,
            'formation_id' => $course->formation_id,
            'module_id' => $course->module_id,
            'session_id' => $course->academic_session_id,
            'titre' => 'Contrôle continu',
            'statut' => 'ouverte',
        ]);
        $studentUser = User::create([
            'first_name' => 'Paul',
            'last_name' => 'Etudiant',
            'email' => 'paul-delete-other-grade@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $student = StudentProfile::create(['user_id' => $studentUser->id]);
        $grade = Grade::create([
            'evaluation_id' => $evaluation->id,
            'student_profile_id' => $student->id,
            'score' => 16,
        ]);

        $response = $this->actingAs($context['otherTeacher']->user)
            ->delete(route('teacher.grades.destroy', $grade));

        $response->assertForbidden();
        $this->assertDatabaseHas('grades', ['id' => $grade->id, 'score' => 16]);
    }

    public function test_unassigned_teacher_cannot_open_or_submit_grades_for_another_teachers_devoir(): void
    {
        $context = $this->createContext();
        $studentUser = User::create([
            'first_name' => 'Paul',
            'last_name' => 'Etudiant',
            'email' => 'paul@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $student = StudentProfile::create(['user_id' => $studentUser->id]);
        Enrollment::create([
            'student_profile_id' => $student->id,
            'formation_id' => $context['formation']->id,
            'academic_session_id' => $context['session']->id,
            'status' => 'active',
        ]);
        $evaluation = Evaluation::create([
            'type' => 'devoir',
            'created_by' => $context['admin']->id,
            'assigned_teacher_profile_id' => $context['assignedTeacher']->id,
            'formation_id' => $context['formation']->id,
            'module_id' => $context['module']->id,
            'session_id' => $context['session']->id,
            'titre' => 'Devoir réservé',
            'statut' => 'ouverte',
        ]);
        $otherCourse = Course::create([
            'formation_id' => $context['formation']->id,
            'academic_session_id' => $context['session']->id,
            'module_id' => $context['module']->id,
            'enseignant_id' => $context['otherTeacher']->id,
            'teacher_profile_id' => $context['otherTeacher']->id,
            'name' => 'Cours du collègue',
        ]);

        $showResponse = $this->actingAs($context['otherTeacher']->user)
            ->get(route('teacher.grades.assigned.show', $evaluation));
        $showResponse->assertNotFound();

        $submitResponse = $this->actingAs($context['otherTeacher']->user)->post(route('teacher.grades.store'), [
            'course_id' => $otherCourse->id,
            'evaluation_id' => $evaluation->id,
            'grades' => [$student->id => ['score' => 19]],
        ]);

        $submitResponse->assertForbidden();
        $this->assertDatabaseCount('grades', 0);
    }

    public function test_closing_an_evaluation_blocks_teacher_grade_changes_but_keeps_saved_grades(): void
    {
        $context = $this->createContext();
        $course = Course::create([
            'formation_id' => $context['formation']->id,
            'academic_session_id' => $context['session']->id,
            'module_id' => $context['module']->id,
            'enseignant_id' => $context['assignedTeacher']->id,
            'teacher_profile_id' => $context['assignedTeacher']->id,
            'name' => 'Cours verrouillé',
        ]);
        $evaluation = Evaluation::create([
            'type' => 'controle_continu',
            'created_by' => $context['assignedTeacher']->user->id,
            'cours_id' => $course->id,
            'formation_id' => $course->formation_id,
            'module_id' => $course->module_id,
            'session_id' => $course->academic_session_id,
            'titre' => 'Contrôle clôturé',
            'statut' => 'ouverte',
        ]);
        $studentUser = User::create([
            'first_name' => 'Paul',
            'last_name' => 'Etudiant',
            'email' => 'paul-closed-evaluation@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $student = StudentProfile::create(['user_id' => $studentUser->id]);
        $grade = Grade::create([
            'evaluation_id' => $evaluation->id,
            'student_profile_id' => $student->id,
            'score' => 12,
        ]);

        $closeResponse = $this->actingAs($context['admin'])->patch(
            route('admin.evaluations.status', $evaluation),
            ['statut' => 'cloturee'],
        );
        $closeResponse->assertRedirect(route('admin.evaluations.index'));
        $this->assertDatabaseHas('evaluations', ['id' => $evaluation->id, 'statut' => 'cloturee']);

        $historyResponse = $this->actingAs($context['assignedTeacher']->user)->get(route('teacher.grades.index', [
            'history' => 1,
            'course_id' => $course->id,
        ]));
        $historyResponse->assertSee('Contrôle clôturé');
        $historyResponse->assertSee('Lecture seule — historique');
        $historyResponse->assertDontSee('Enregistrer les notes');

        $saveResponse = $this->actingAs($context['assignedTeacher']->user)->post(route('teacher.grades.store'), [
            'course_id' => $course->id,
            'evaluation_id' => $evaluation->id,
            'grades' => [$student->id => ['score' => 18]],
        ]);

        $saveResponse->assertForbidden();
        $this->assertDatabaseHas('grades', ['id' => $grade->id, 'score' => 12]);

        $updateResponse = $this->actingAs($context['assignedTeacher']->user)->put(
            route('teacher.grades.update', $grade),
            ['score' => 18],
        );
        $updateResponse->assertForbidden();

        $deleteResponse = $this->actingAs($context['assignedTeacher']->user)
            ->delete(route('teacher.grades.destroy', $grade));
        $deleteResponse->assertForbidden();
        $this->assertDatabaseHas('grades', ['id' => $grade->id, 'score' => 12]);
    }

    public function test_closing_an_assigned_devoir_blocks_teacher_grade_writes(): void
    {
        $context = $this->createContext();
        $studentUser = User::create([
            'first_name' => 'Paul',
            'last_name' => 'Etudiant',
            'email' => 'paul-closed-devoir@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $student = StudentProfile::create(['user_id' => $studentUser->id]);
        Enrollment::create([
            'student_profile_id' => $student->id,
            'formation_id' => $context['formation']->id,
            'academic_session_id' => $context['session']->id,
            'status' => 'active',
        ]);
        $evaluation = Evaluation::create([
            'type' => 'devoir',
            'created_by' => $context['admin']->id,
            'assigned_teacher_profile_id' => $context['assignedTeacher']->id,
            'formation_id' => $context['formation']->id,
            'module_id' => $context['module']->id,
            'session_id' => $context['session']->id,
            'titre' => 'Devoir clôturé',
            'statut' => 'ouverte',
        ]);
        $grade = Grade::create([
            'evaluation_id' => $evaluation->id,
            'student_profile_id' => $student->id,
            'score' => 14,
        ]);

        $closeResponse = $this->actingAs($context['admin'])->patch(
            route('admin.evaluations.status', $evaluation),
            ['statut' => 'cloturee'],
        );
        $closeResponse->assertRedirect(route('admin.evaluations.index'));

        $historyResponse = $this->actingAs($context['assignedTeacher']->user)
            ->get(route('teacher.grades.assigned.show', $evaluation));
        $historyResponse->assertSee('Devoir clôturé');
        $historyResponse->assertSee('consultables en lecture seule');
        $historyResponse->assertDontSee('Enregistrer les notes');

        $saveResponse = $this->actingAs($context['assignedTeacher']->user)
            ->post(route('teacher.grades.assigned.store', $evaluation), [
                'grades' => [$student->id => ['score' => 19]],
            ]);

        $saveResponse->assertForbidden();
        $this->assertDatabaseHas('grades', ['id' => $grade->id, 'score' => 14]);
    }

    public function test_completing_a_course_blocks_teacher_evaluation_and_grade_writes(): void
    {
        $context = $this->createContext();
        $course = Course::create([
            'formation_id' => $context['formation']->id,
            'academic_session_id' => $context['session']->id,
            'module_id' => $context['module']->id,
            'enseignant_id' => $context['assignedTeacher']->id,
            'teacher_profile_id' => $context['assignedTeacher']->id,
            'name' => 'Cours terminé',
        ]);

        $completeResponse = $this->actingAs($context['admin'])->patch(
            route('admin.courses.status', $course),
            ['statut' => 'termine'],
        );
        $completeResponse->assertRedirect();
        $this->assertDatabaseHas('courses', ['id' => $course->id, 'statut' => 'termine']);

        $createResponse = $this->actingAs($context['assignedTeacher']->user)
            ->post(route('teacher.grades.createEvaluation'), [
                'course_id' => $course->id,
                'titre' => 'Contrôle interdit',
                'date' => '2026-10-20',
                'type' => 'controle_continu',
            ]);
        $createResponse->assertForbidden();
        $this->assertDatabaseCount('evaluations', 0);

        $evaluation = Evaluation::create([
            'type' => 'controle_continu',
            'created_by' => $context['assignedTeacher']->user->id,
            'cours_id' => $course->id,
            'formation_id' => $course->formation_id,
            'module_id' => $course->module_id,
            'session_id' => $course->academic_session_id,
            'titre' => 'Évaluation existante',
            'statut' => 'ouverte',
        ]);

        $saveResponse = $this->actingAs($context['assignedTeacher']->user)->post(route('teacher.grades.store'), [
            'course_id' => $course->id,
            'evaluation_id' => $evaluation->id,
            'grades' => ['1' => ['score' => 18]],
        ]);
        $saveResponse->assertForbidden();
        $this->assertDatabaseCount('grades', 0);

        $studentUser = User::create([
            'first_name' => 'Paul',
            'last_name' => 'Etudiant',
            'email' => 'paul-completed-course@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $student = StudentProfile::create(['user_id' => $studentUser->id]);
        $grade = Grade::create([
            'evaluation_id' => $evaluation->id,
            'student_profile_id' => $student->id,
            'score' => 13,
        ]);

        $updateResponse = $this->actingAs($context['assignedTeacher']->user)->put(
            route('teacher.grades.update', $grade),
            ['score' => 19],
        );
        $updateResponse->assertForbidden();

        $deleteResponse = $this->actingAs($context['assignedTeacher']->user)
            ->delete(route('teacher.grades.destroy', $grade));
        $deleteResponse->assertForbidden();
        $this->assertDatabaseHas('grades', ['id' => $grade->id, 'score' => 13]);
    }

    /**
     * @return array{admin: User, assignedTeacher: TeacherProfile, otherTeacher: TeacherProfile, formation: Formation, session: AcademicSession, module: Module}
     */
    private function createContext(): array
    {
        Role::create(['name' => 'administrateur', 'guard_name' => 'web']);
        Role::create(['name' => 'enseignant', 'guard_name' => 'web']);
        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'IFAM',
            'email' => 'admin@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $admin->assignRole('administrateur');

        $assignedTeacher = $this->createTeacherProfile('professeur@ifam.test', 'Professeur', 'Affecte');
        $otherTeacher = $this->createTeacherProfile('autre@ifam.test', 'Professeur', 'Autre');
        $formation = Formation::create(['name' => 'Stylisme année 1']);
        $session = AcademicSession::create([
            'name' => '2026-2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
        ]);
        $module = Module::create(['name' => 'Coupe', 'code' => 'MOD-COUPE']);
        $formation->modules()->attach($module->id);
        $module->teachers()->attach($assignedTeacher->id);

        return compact('admin', 'assignedTeacher', 'otherTeacher', 'formation', 'session', 'module');
    }

    private function createTeacherProfile(string $email, string $firstName, string $lastName): TeacherProfile
    {
        $user = User::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $user->assignRole('enseignant');

        return TeacherProfile::create(['user_id' => $user->id]);
    }
}
