<?php

namespace Database\Seeders;

use App\Models\StudentCategory;
use App\Models\Template;
use App\Models\TemplateCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategoryAndTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        $userId = $user ? $user->id : null;

        // 1. Seed Student Categories (Hierarchical)
        // Level / Mode Categories
        $ug = StudentCategory::firstOrCreate(
            ['slug' => 'undergraduate-students'],
            [
                'user_id' => $userId,
                'name' => 'Undergraduate Students',
                'type' => 'level',
                'description' => 'All undergraduate students (Regular, Evening, Weekend)',
            ]
        );

        StudentCategory::firstOrCreate(
            ['slug' => 'regular-students'],
            [
                'user_id' => $userId,
                'parent_id' => $ug->id,
                'name' => 'Regular Students',
                'type' => 'study_mode',
                'description' => 'Full-time regular weekday students',
            ]
        );

        StudentCategory::firstOrCreate(
            ['slug' => 'evening-students'],
            [
                'user_id' => $userId,
                'parent_id' => $ug->id,
                'name' => 'Evening Students',
                'type' => 'study_mode',
                'description' => 'Evening stream students',
            ]
        );

        StudentCategory::firstOrCreate(
            ['slug' => 'weekend-students'],
            [
                'user_id' => $userId,
                'parent_id' => $ug->id,
                'name' => 'Weekend Students',
                'type' => 'study_mode',
                'description' => 'Weekend stream students',
            ]
        );

        $pg = StudentCategory::firstOrCreate(
            ['slug' => 'postgraduate-students'],
            [
                'user_id' => $userId,
                'name' => 'Postgraduate Students',
                'type' => 'level',
                'description' => 'Postgraduate students (Masters & PhD)',
            ]
        );

        StudentCategory::firstOrCreate(
            ['slug' => 'masters'],
            [
                'user_id' => $userId,
                'parent_id' => $pg->id,
                'name' => 'Masters Students',
                'type' => 'level',
                'description' => 'MSc, MBA, MA degree students',
            ]
        );

        StudentCategory::firstOrCreate(
            ['slug' => 'freshers'],
            [
                'user_id' => $userId,
                'name' => 'Freshers',
                'type' => 'level',
                'description' => 'Newly admitted first-year students',
            ]
        );

        // Faculty / School Categories
        StudentCategory::firstOrCreate(
            ['slug' => 'faculty-of-computing-and-information-systems'],
            [
                'user_id' => $userId,
                'name' => 'Faculty of Computing & Information Systems',
                'type' => 'faculty',
                'description' => 'Computer Science, IT, Software Engineering students',
            ]
        );

        StudentCategory::firstOrCreate(
            ['slug' => 'faculty-of-engineering'],
            [
                'user_id' => $userId,
                'name' => 'Faculty of Engineering',
                'type' => 'faculty',
                'description' => 'Electrical, Mechanical, Civil Engineering students',
            ]
        );

        StudentCategory::firstOrCreate(
            ['slug' => 'business-school'],
            [
                'user_id' => $userId,
                'name' => 'Business School',
                'type' => 'faculty',
                'description' => 'Accounting, Finance, Management, Marketing students',
            ]
        );

        // 2. Seed Template Categories
        $appCat = TemplateCategory::firstOrCreate(
            ['slug' => 'application-status'],
            [
                'user_id' => $userId,
                'name' => 'Application Status',
                'description' => 'Notifications regarding student application & admission',
            ]
        );

        $examCat = TemplateCategory::firstOrCreate(
            ['slug' => 'examination-results'],
            [
                'user_id' => $userId,
                'name' => 'Examination & Results',
                'description' => 'Exam timetables and academic result notifications',
            ]
        );

        $regCat = TemplateCategory::firstOrCreate(
            ['slug' => 'course-registration'],
            [
                'user_id' => $userId,
                'name' => 'Course Registration',
                'description' => 'Registration deadlines, links, and portals',
            ]
        );

        $generalCat = TemplateCategory::firstOrCreate(
            ['slug' => 'general-announcements'],
            [
                'user_id' => $userId,
                'name' => 'General Announcements',
                'description' => 'General school news and updates',
            ]
        );

        // 3. Seed Preset Templates
        Template::firstOrCreate(
            ['title' => 'Application Received Confirmation'],
            [
                'user_id' => $userId,
                'template_category_id' => $appCat->id,
                'body' => "Dear {{Name}}, your application for the {{Programme}} program has been successfully received. We will notify you once processing is complete.",
                'placeholders' => ['Name', 'Programme'],
            ]
        );

        Template::firstOrCreate(
            ['title' => 'Examination Results Available'],
            [
                'user_id' => $userId,
                'template_category_id' => $examCat->id,
                'body' => "Dear {{Name}} (Index No: {{Index Number}}), your semester examination results for {{Programme}} are now available on the student portal.",
                'placeholders' => ['Name', 'Index Number', 'Programme'],
            ]
        );

        Template::firstOrCreate(
            ['title' => 'Course Registration Now Open'],
            [
                'user_id' => $userId,
                'template_category_id' => $regCat->id,
                'body' => "Dear {{Name}}, course registration for the upcoming semester is now officially open. Please register your courses before the deadline.",
                'placeholders' => ['Name'],
            ]
        );

        Template::firstOrCreate(
            ['title' => 'Course Registration Direct Link'],
            [
                'user_id' => $userId,
                'template_category_id' => $regCat->id,
                'body' => "Dear {{Name}}, use this direct link to complete your course registration for {{Programme}}: https://portal.school.edu/register",
                'placeholders' => ['Name', 'Programme'],
            ]
        );

        Template::firstOrCreate(
            ['title' => 'School Reopening Announcement'],
            [
                'user_id' => $userId,
                'template_category_id' => $generalCat->id,
                'body' => "Dear {{Name}}, school reopens for all students on the 15th of October. Please ensure all fee payments and registrations are completed.",
                'placeholders' => ['Name'],
            ]
        );
    }
}
