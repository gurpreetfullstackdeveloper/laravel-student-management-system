<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\StoreAdmissionEnquiryRequest;
use App\Models\AdmissionEnquiry;
use App\Models\Event;
use App\Models\Gallery;
use App\Models\News;
use App\Models\Notice;
use App\Models\SchoolClass;
use App\Models\SchoolSetting;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class PublicSiteController extends Controller
{
    public function home(): View
    {
        return view('public.home', $this->sharedData([
            'news' => News::where('is_published', true)->latest('published_at')->limit(3)->get(),
            'events' => Event::where('is_published', true)->latest('published_at')->limit(3)->get(),
            'notices' => Notice::where('is_published', true)->latest('published_at')->limit(3)->get(),
        ]));
    }

    public function about(): View { return view('public.about', $this->sharedData()); }

    public function academics(): View
    {
        return view('public.academics', $this->sharedData([
            'classes' => SchoolClass::with('sections')->orderBy('order')->get(),
            'subjects' => Subject::orderBy('name')->get(),
        ]));
    }

    public function admissions(): View { return view('public.admissions', $this->sharedData()); }

    public function submitAdmission(StoreAdmissionEnquiryRequest $request): RedirectResponse
    {
        AdmissionEnquiry::create($request->validated());

        return to_route('public.admissions')->with('status', 'Your admission enquiry has been received.');
    }

    public function facilities(): View { return view('public.facilities', $this->sharedData()); }
    public function contact(): View { return view('public.contact', $this->sharedData()); }
    public function faq(): View { return view('public.faq', $this->sharedData()); }

    public function faculty(): View
    {
        return view('public.faculty', $this->sharedData([
            'teachers' => Teacher::with('user')->whereHas('user', fn ($query) => $query->where('is_active', true))->orderBy('employee_code')->paginate(12),
        ]));
    }

    public function events(): View
    {
        return view('public.events.index', $this->sharedData([
            'events' => Event::where('is_published', true)->latest('published_at')->paginate(12),
        ]));
    }

    public function event(string $slug): View
    {
        return view('public.events.show', $this->sharedData([
            'event' => Event::where('slug', $slug)->where('is_published', true)->firstOrFail(),
        ]));
    }

    public function news(): View
    {
        return view('public.news.index', $this->sharedData([
            'news' => News::where('is_published', true)->latest('published_at')->paginate(12),
        ]));
    }

    public function article(string $slug): View
    {
        return view('public.news.show', $this->sharedData([
            'article' => News::where('slug', $slug)->where('is_published', true)->firstOrFail(),
        ]));
    }

    public function gallery(): View
    {
        return view('public.gallery.index', $this->sharedData([
            'galleries' => Gallery::with('images')->where('is_published', true)->latest('published_at')->paginate(12),
        ]));
    }

    public function notices(): View
    {
        return view('public.notices.index', $this->sharedData([
            'notices' => Notice::where('is_published', true)->latest('published_at')->paginate(12),
        ]));
    }

    private function sharedData(array $data = []): array
    {
        return array_merge([
            'settings' => Cache::remember('public.school_settings', now()->addMinutes(10), fn () => SchoolSetting::query()->pluck('value', 'key')),
        ], $data);
    }
}