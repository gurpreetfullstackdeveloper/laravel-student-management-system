<?php

namespace Tests\Feature;

use App\Models\AdmissionEnquiry;
use App\Models\Event;
use App\Models\Gallery;
use App\Models\News;
use App\Models\Notice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicWebsiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_are_available_without_authentication(): void
    {
        foreach (['/', '/about', '/academics', '/admissions', '/facilities', '/faculty', '/events', '/news', '/gallery', '/notices', '/contact', '/faq'] as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function test_public_lists_show_published_content_only(): void
    {
        $admin = User::factory()->superAdmin()->create();
        Event::create(['title' => 'Open Day', 'slug' => 'open-day', 'body' => 'Visit us.', 'published_at' => now(), 'is_published' => true, 'created_by' => $admin->id]);
        Event::create(['title' => 'Internal Draft', 'slug' => 'internal-draft', 'body' => 'Not public.', 'is_published' => false, 'created_by' => $admin->id]);
        News::create(['title' => 'School News', 'slug' => 'school-news', 'body' => 'News.', 'published_at' => now(), 'is_published' => true, 'created_by' => $admin->id]);
        Notice::create(['title' => 'Public Notice', 'slug' => 'public-notice', 'body' => 'Notice.', 'published_at' => now(), 'is_published' => true, 'created_by' => $admin->id]);

        $this->get('/events')->assertSee('Open Day')->assertDontSee('Internal Draft');
        $this->get('/news')->assertSee('School News');
        $this->get('/notices')->assertSee('Public Notice');
        $this->get('/events/open-day')->assertOk()->assertSee('Visit us.');
        $this->get('/events/internal-draft')->assertNotFound();
    }

    public function test_admission_enquiry_is_validated_and_stored(): void
    {
        $this->from('/admissions')->post('/admissions', [
            'student_name' => 'A Student',
            'parent_name' => 'A Parent',
            'phone' => '555-0110',
            'email' => 'parent@example.test',
            'class_applied_for' => 'Class 7',
            'message' => 'Please contact us.',
        ])->assertRedirect('/admissions');

        $this->assertDatabaseHas('admission_enquiries', [
            'student_name' => 'A Student',
            'status' => 'new',
        ]);
    }

    public function test_invalid_admission_enquiry_is_rejected(): void
    {
        $this->from('/admissions')->post('/admissions', [
            'student_name' => '',
            'parent_name' => '',
            'phone' => '',
            'email' => 'invalid',
        ])->assertRedirect('/admissions')->assertSessionHasErrors(['student_name', 'parent_name', 'phone', 'email', 'class_applied_for']);

        $this->assertDatabaseCount('admission_enquiries', 0);
    }

    public function test_public_navigation_links_to_each_application_surface(): void
    {
        $response = $this->get('/');
        $response->assertSee(route('student.login'), false)
            ->assertSee(route('teacher.login'), false)
            ->assertSee('/admin', false)
            ->assertSee(route('public.admissions'), false)
            ->assertSee(route('public.news'), false);
    }
}
