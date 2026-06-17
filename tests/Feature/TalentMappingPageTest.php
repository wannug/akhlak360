<?php

namespace Tests\Feature;

use App\Models\AssessmentPeriod;
use App\Models\AssessmentResult;
use App\Models\Department;
use App\Models\Employee;
use App\Models\IdpRecommendation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TalentMappingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_talent_mapping_page_filters_results_and_shows_distribution(): void
    {
        $user = User::factory()->create(['role' => 'admin_hr']);
        $fixture = $this->fixture();

        $highPotential = $this->employee($fixture['operations'], 'EMP-TM-001', 'High Potential Person');
        $solid = $this->employee($fixture['operations'], 'EMP-TM-002', 'Solid Person');
        $core = $this->employee($fixture['finance'], 'EMP-TM-003', 'Core Person');
        $needDevelopment = $this->employee($fixture['finance'], 'EMP-TM-004', 'Need Development Person');

        $this->assessmentResult($fixture['period'], $highPotential, 4.80, 0.10, 'High Potential');
        $this->assessmentResult($fixture['period'], $solid, 4.00, 0.90, 'Solid Contributor');
        $this->assessmentResult($fixture['period'], $core, 3.30, 0.20, 'Core Contributor');
        $needResult = $this->assessmentResult($fixture['period'], $needDevelopment, 2.80, -0.70, 'Need Development');
        IdpRecommendation::create([
            'assessment_period_id' => $fixture['period']->id,
            'employee_id' => $needDevelopment->id,
            'weakest_core_value' => 'Adaptif',
            'recommendation' => 'Change management workshop, innovation challenge, and problem-solving training.',
            'status' => 'in_progress',
        ]);

        $this->actingAs($user)
            ->get("/idp-talent/talent-mapping?period_id={$fixture['period']->id}&department_id={$fixture['finance']->id}")
            ->assertOk()
            ->assertSee('Talent Mapping')
            ->assertSee('Development/IDP uses 60%')
            ->assertSee('Talent Mapping uses 40%')
            ->assertSee('Need Development Person')
            ->assertSee('Need Development')
            ->assertSee('In progress')
            ->assertSee('Core Person')
            ->assertDontSee('High Potential Person')
            ->assertSee('talentCategoryChart');

        $this->assertSame('Need Development', $needResult->talent_mapping_category);
    }

    public function test_talent_mapping_csv_export_uses_filters(): void
    {
        $user = User::factory()->create(['role' => 'management']);
        $fixture = $this->fixture();
        $included = $this->employee($fixture['operations'], 'EMP-TM-005', 'Export Included');
        $excluded = $this->employee($fixture['finance'], 'EMP-TM-006', 'Export Excluded');

        $this->assessmentResult($fixture['period'], $included, 4.60, 0.00, 'High Potential');
        $this->assessmentResult($fixture['period'], $excluded, 3.20, 0.00, 'Core Contributor');

        $response = $this->actingAs($user)
            ->get("/idp-talent/talent-mapping/export?period_id={$fixture['period']->id}&department_id={$fixture['operations']->id}")
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8')
            ->streamedContent();

        $this->assertStringContainsString('employee_name,department,period,final_score,gap_score,talent_mapping_category,idp_status', $response);
        $this->assertStringContainsString('Export Included', $response);
        $this->assertStringContainsString('High Potential', $response);
        $this->assertStringNotContainsString('Export Excluded', $response);
    }

    private function fixture(): array
    {
        $operations = Department::create(['name' => 'Operations', 'code' => 'OPS']);
        $finance = Department::create(['name' => 'Finance', 'code' => 'FIN']);
        $period = AssessmentPeriod::create([
            'name' => 'Semester 1 2026',
            'semester' => 'Semester 1',
            'year' => 2026,
            'start_date' => '2026-06-17',
            'end_date' => '2026-06-30',
            'status' => 'active',
            'threshold_score' => 3.00,
        ]);

        return compact('operations', 'finance', 'period');
    }

    private function employee(Department $department, string $number, string $name): Employee
    {
        return Employee::create([
            'department_id' => $department->id,
            'employee_number' => $number,
            'name' => $name,
            'email' => strtolower(str_replace(' ', '.', $name)).'@example.com',
            'employment_status' => 'active',
        ]);
    }

    private function assessmentResult(AssessmentPeriod $period, Employee $employee, float $finalScore, float $gapScore, string $category): AssessmentResult
    {
        return AssessmentResult::create([
            'assessment_period_id' => $period->id,
            'employee_id' => $employee->id,
            'amanah_score' => $finalScore,
            'kompeten_score' => $finalScore,
            'harmonis_score' => $finalScore,
            'loyal_score' => $finalScore,
            'adaptif_score' => $finalScore,
            'kolaboratif_score' => $finalScore,
            'self_score' => $finalScore + $gapScore,
            'others_score' => $finalScore,
            'gap_score' => $gapScore,
            'final_score' => $finalScore,
            'category' => $finalScore >= 3.75 ? 'Baik' : 'Cukup',
            'talent_mapping_category' => $category,
        ]);
    }
}
