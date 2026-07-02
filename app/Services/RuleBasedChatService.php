<?php

namespace App\Services;

use App\Models\Student;
use App\Models\FeeInvoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RuleBasedChatService
{
    protected array $faqs;

    public function __construct()
    {
        $this->faqs = include base_path('resources/data/chatbot_faq.php');
    }

    /**
     * Main entry point. Takes the latest user message (plain text) and
     * returns a reply string. Chat history isn't needed for rule-based logic,
     * but kept in the signature for drop-in compatibility with the component.
     */
    public function reply(string $userMessage): string
    {
        $text = Str::lower(trim($userMessage));

        // 0) Admin-only report intents (checked first, but internally guarded by role)
        if ($this->isAdmin()) {
            if ($this->matchesAny($text, ['total student', 'ছাত্র সংখ্যা', 'মোট ছাত্র', 'student count'])) {
                return $this->handleTotalStudentIntent();
            }

            if ($this->matchesAny($text, ['total teacher', 'শিক্ষক সংখ্যা', 'মোট শিক্ষক', 'teacher count'])) {
                return $this->handleTotalTeacherIntent();
            }

            if ($this->matchesAny($text, ['fee collection', 'ফি আদায়', 'কালেকশন রিপোর্ট', 'income report', 'আয়ের রিপোর্ট'])) {
                return $this->handleFeeCollectionIntent();
            }

            if ($this->matchesAny($text, ['due fee', 'বকেয়া', 'ফি বাকি', 'due report'])) {
                return $this->handleDueFeeIntent();
            }

            if ($this->matchesAny($text, ['attendance report', 'উপস্থিতি রিপোর্ট', 'আজকের উপস্থিতি'])) {
                return $this->handleAttendanceReportIntent();
            }

            if ($this->matchesAny($text, ['exam summary', 'রেজাল্ট সামারি', 'পাশের হার', 'pass rate'])) {
                return $this->handleExamSummaryIntent();
            }

            if ($this->matchesAny($text, ['new admission', 'নতুন ভর্তি', 'admission report', 'ভর্তি রিপোর্ট'])) {
                return $this->handleAdmissionReportIntent();
            }
        }

        // 1) Dynamic intents that need DB lookups (student/parent facing)
        if ($this->matchesAny($text, ['result', 'রেজাল্ট', 'position', 'অবস্থান', 'গ্রেড'])) {
            return $this->handleResultIntent($text);
        }

        if ($this->matchesAny($text, ['notice', 'নোটিশ'])) {
            return $this->handleNoticeIntent();
        }

        if ($this->matchesAny($text, ['homework', 'হোমওয়ার্ক', 'বাড়ির কাজ'])) {
            return $this->handleHomeworkIntent();
        }

        // 2) Fall back to static FAQ matching
        foreach ($this->faqs as $faq) {
            if ($this->matchesAny($text, $faq['keywords'])) {
                return $faq['answer'];
            }
        }

        // 3) Nothing matched
        return 'দুঃখিত, আমি এই প্রশ্নটা বুঝতে পারিনি। রেজাল্ট, নোটিশ, হোমওয়ার্ক, ফি বা ভর্তি সংক্রান্ত প্রশ্ন করে দেখতে পারেন।';
    }

    protected function matchesAny(string $text, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (Str::contains($text, Str::lower($keyword))) {
                return true;
            }
        }
        return false;
    }

    /**
     * TODO: এখানে তোমার আসল role check বসাও।
     * উদাহরণ: return Auth::user()?->role === 'school_admin';
     * অথবা Spatie permission ব্যবহার করলে: Auth::user()?->hasRole('admin');
     */
    protected function isAdmin(): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        // Adjust this to match your actual users table / role system.
        return in_array($user->role ?? null, ['admin', 'school_admin', 'super_admin']);
    }

    protected function handleTotalStudentIntent(): string
    {
        $count = Student::count();

        return "মোট ছাত্র সংখ্যা: {$count} জন।";
    }

    /**
     * TODO: তোমার User model-এ role column অনুযায়ী শিক্ষক গণনা ঠিক করে নাও।
     */
    protected function handleTotalTeacherIntent(): string
    {
        $count = \App\Models\User::where('role', 'teacher')->count();

        return "মোট শিক্ষক সংখ্যা: {$count} জন।";
    }

    /**
     * TODO: FeeInvoice model-এর status column নাম/ভ্যালু তোমার schema অনুযায়ী মিলিয়ে নাও।
     */
    protected function handleFeeCollectionIntent(): string
    {
        if (!class_exists(FeeInvoice::class)) {
            return 'ফি কালেকশনের বিস্তারিত দেখতে "Billing" মডিউলে যান।';
        }

        $thisMonthTotal = FeeInvoice::where('status', 'paid')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('paid_amount');

        $formatted = number_format((float) $thisMonthTotal, 2);

        return "এই মাসে মোট ফি আদায় হয়েছে: {$formatted} টাকা। বিস্তারিত দেখতে 'Billing' মডিউল দেখুন।";
    }

    protected function handleDueFeeIntent(): string
    {
        if (!class_exists(FeeInvoice::class)) {
            return 'বকেয়া ফি দেখতে "Billing" মডিউলে যান।';
        }

        $dueTotal = FeeInvoice::where('status', 'due')
            ->sum('paid_amount');

        $dueCount = FeeInvoice::where('status', 'due')->count();

        $formatted = number_format((float) $dueTotal, 2);

        return "মোট বকেয়া ইনভয়েস: {$dueCount} টি, মোট বকেয়া টাকার পরিমাণ: {$formatted} টাকা।";
    }

    /**
     * TODO: তোমার Attendance model তৈরি হলে এখানে আসল query বসাও।
     */
    protected function handleAttendanceReportIntent(): string
    {
        if (!class_exists(\App\Models\Attendance::class)) {
            return 'উপস্থিতির বিস্তারিত রিপোর্ট দেখতে "Attendance" মডিউলে যান।';
        }

        $today = now()->toDateString();

        $present = \App\Models\Attendance::whereDate('date', $today)
            ->where('status', 'present')
            ->count();

        $total = \App\Models\Attendance::whereDate('date', $today)->count();

        if ($total === 0) {
            return 'আজকের উপস্থিতি এখনো এন্ট্রি করা হয়নি।';
        }

        $percentage = round(($present / $total) * 100, 1);

        return "আজকে উপস্থিত ছাত্র: {$present} জন / মোট {$total} জন ({$percentage}%)।";
    }

    /**
     * TODO: ExamPosition/Result model অনুযায়ী পাশের হার হিসাব ঠিক করে নাও।
     */
    protected function handleExamSummaryIntent(): string
    {
        if (!class_exists(\App\Models\ExamPosition::class)) {
            return 'পরীক্ষার সামারি দেখতে "Exam" মডিউলে যান।';
        }

        $total = \App\Models\ExamPosition::count();
        $passed = \App\Models\ExamPosition::where('result', 'pass')->count();

        if ($total === 0) {
            return 'এখনো কোনো পরীক্ষার রেজাল্ট এন্ট্রি করা হয়নি।';
        }

        $passRate = round(($passed / $total) * 100, 1);

        return "সর্বশেষ পরীক্ষায় মোট {$total} জনের মধ্যে {$passed} জন পাশ করেছে (পাশের হার {$passRate}%)।";
    }

    /**
     * TODO: Student model-এ admission_date বা created_at কোনটা ব্যবহার হচ্ছে যাচাই করো।
     */
    protected function handleAdmissionReportIntent(): string
    {
        $count = Student::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return "এই মাসে নতুন ভর্তি হয়েছে: {$count} জন ছাত্র।";
    }

    /**
     * Example dynamic handler: student result lookup.
     * NOTE: Adjust model/column names to match your actual exam_positions schema.
     */
    protected function handleResultIntent(string $text): string
    {
        $user = Auth::user();

        // TODO: replace with your real relation, e.g. $user->student
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return 'আপনার সাথে যুক্ত কোনো ছাত্রের তথ্য খুঁজে পাইনি। অনুগ্রহ করে "Exam Position" মডিউলে গিয়ে সরাসরি দেখুন।';
        }

        $position = \App\Models\ExamPosition::where('student_id', $student->id)
            ->latest()
            ->first();

        if (!$position) {
            return "{$student->name}-এর জন্য এখনো কোনো পরীক্ষার রেজাল্ট পাওয়া যায়নি।";
        }

        return "{$student->name}-এর সর্বশেষ পরীক্ষায় অবস্থান: {$position->position}। বিস্তারিত জানতে 'Exam Position' মডিউল দেখুন।";
    }

    protected function handleNoticeIntent(): string
    {
        // TODO: replace with your real Notice model
        if (!class_exists(\App\Models\Notice::class)) {
            return 'নোটিশ মডিউল থেকে সরাসরি সাম্প্রতিক নোটিশগুলো দেখতে পারবেন।';
        }

        $latest = \App\Models\Notice::latest()->take(3)->get(['title', 'created_at']);

        if ($latest->isEmpty()) {
            return 'এই মুহূর্তে কোনো নতুন নোটিশ নেই।';
        }

        $list = $latest->map(fn ($n) => "- {$n->title}")->implode("\n");

        return "সাম্প্রতিক নোটিশসমূহ:\n{$list}";
    }

    protected function handleHomeworkIntent(): string
    {
        return 'হোমওয়ার্ক দেখতে "Homework" মডিউলে যান, সেখানে ক্লাস অনুযায়ী সব অ্যাসাইনমেন্ট তালিকাভুক্ত আছে।';
    }
}