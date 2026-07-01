<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Institution;
use App\Models\InventoryCategory;
use App\Models\InventoryUnit;
use App\Models\EmployeeDepartment;
use App\Models\EmployeeDesignation;
use App\Models\AcademicGroup;
use App\Models\AcademicSession;
use App\Models\AcademicSection;
use App\Models\AcademicSubject;
use App\Models\AcademicClass;
use App\Models\ExamTerm;
use App\Models\ExamType;
use App\Models\ExamMark;
use App\Models\ExamHall;
use App\Models\ExamGrade;
use App\Models\FeeType;
use App\Models\LeaveCategory;
use App\Models\OfficeHead;
use App\Models\OfficeAccount;
use App\Models\EventType;

class InstitutionDefaultsService
{
    public static function create(Institution $institution): void
    {
        self::createInventoryCategories($institution);
        self::createInventoryUnits($institution);
        self::createDepartments($institution);
        self::createDesignations($institution);
        self::createAcademicSession($institution);
        self::createAcademicGroups($institution);
        self::createAcademicSections($institution);
        self::createAcademicSubjects($institution);
        self::createAcademicClasses($institution);
        self::createExamTerms($institution);
        self::createExamTypes($institution);
        self::createExamMarks($institution);
        self::createExamHalls($institution);
        self::createExamGrades($institution);
        // self::createFeeTypes($institution);
        self::createLeaveCategories($institution);
        self::createOfficeHeads($institution);
        self::createOfficeAccounts($institution);
        self::createEventTypes($institution);
    }

    private static function createInventoryCategories(Institution $institution): void
    {
        $categories = [
            'Sports',
            'Accessories',
            'Study Material',
            'Dress',
            'Books & Stationery',
            'Furniture and Equipment',
            'Computer',
        ];

        foreach ($categories as $category) {
            InventoryCategory::firstOrCreate([
                'institution_id' => $institution->id,
                'name'           => $category,
            ]);
        }
    }
    private static function createInventoryUnits(Institution $institution): void
    {
        $units = [
            'KG',
            'Piece',
            'Dozen',
            'Unit',
        ];

        foreach ($units as $unit) {
            InventoryUnit::firstOrCreate([
                'institution_id' => $institution->id,
                'name'           => $unit,
            ]);
        }
    }   
    private static function createDepartments(Institution $institution): void
    {
        $departments = [
            'Administration',
            'Academic',
            'Accounts',
            'Library',
            'Transport',
            'Security',
            'Maintenance',
            'IT',
            'Admissions',
            'Examination',
        ];

        foreach ($departments as $department) {
            EmployeeDepartment::firstOrCreate([
                'institution_id' => $institution->id,
                'name' => $department,
            ]);
        }
    }
    private static function createDesignations(Institution $institution): void
    {
        $designations = [
            'Principal',
            'Vice Principal',
            'Teacher',
            'Accountant',
            'Librarian',
            'Office Assistant',
            'Driver',
            'Security Guard',
            'Cleaner',
        ];

        foreach ($designations as $designation) {
            EmployeeDesignation::firstOrCreate([
                'institution_id' => $institution->id,
                'name' => $designation,
            ]);
        }
    }
    private static function createAcademicSession(Institution $institution): void
    {
        $year = Carbon::now()->year;

        AcademicSession::firstOrCreate([
            'institution_id' => $institution->id,
            'name' => $year,
        ], [
            'start_date' => Carbon::now()->startOfYear()->toDateString(),
            'end_date'   => Carbon::now()->endOfYear()->toDateString(),
            'is_current' => true,
        ]);
    }
    private static function createAcademicGroups(Institution $institution): void
    {
        $groups = [
            'Science',
            'Business Studies',
            'Humanities',
        ];

        foreach ($groups as $group) {
            AcademicGroup::firstOrCreate([
                'institution_id' => $institution->id,
                'name' => $group,
            ]);
        }
    }
    private static function createAcademicSections(Institution $institution): void
    {
        $sections = [
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'I',
            'J',
        ];

        foreach ($sections as $section) {
            AcademicSection::firstOrCreate([
                'institution_id' => $institution->id,
                'name' => $section,
            ]);
        }
    }
    private static function createAcademicSubjects(Institution $institution): void
    {
        $subjects = [
            'Bangla',
            'English',
            'Mathematics',
            'Science',
            'Physics',
            'Chemistry',
            'Biology',
            'Higher Mathematics',
            'Information and Communication Technology (ICT)',
            'Social Science',
            'History',
            'Geography',
            'Civics',
            'Economics',
            'Accounting',
            'Business Studies',
            'Finance and Banking',
            'Agriculture',
            'Islam and Moral Education',
            'Hindu Religion and Moral Education',
            'Buddhist Religion and Moral Education',
            'Christian Religion and Moral Education',
            'Physical Education',
            'Arts and Crafts',
            'Music',
            'Career Education',
        ];

        foreach ($subjects as $subject) {
            AcademicSubject::firstOrCreate([
                'institution_id' => $institution->id,
                'name' => $subject,
            ]);
        }
    }
    private static function createAcademicClasses(Institution $institution): void
    {
        $classes = [
            'Play',
            'Nursery',
            'KG',
            'Class 1',
            'Class 2',
            'Class 3',
            'Class 4',
            'Class 5',
            'Class 6',
            'Class 7',
            'Class 8',
            'Class 9',
            'Class 10',
            'Class 11',
            'Class 12',
        ];

        foreach ($classes as $class) {
            AcademicClass::firstOrCreate([
                'institution_id' => $institution->id,
                'name' => $class,
            ]);
        }
    }
    private static function createExamTerms(Institution $institution): void
    {
        $terms = [
            'First Term',
            'Second Term',
            'Mid Term',
            'Final Term',
            'Model Test',
            'Annual Exam',
        ];

        foreach ($terms as $term) {
            ExamTerm::firstOrCreate([
                'institution_id' => $institution->id,
                'name' => $term,
            ]);
        }
    }
    private static function createExamTypes(Institution $institution): void
    {
        $types = [
            'Mark',
            'Grade',
            'Mark And Grade',
        ];

        foreach ($types as $type) {
            ExamType::firstOrCreate([
                'institution_id' => $institution->id,
                'name' => $type,
            ]);
        }
    }
    private static function createExamMarks(Institution $institution): void
    {
        $marks = [
            'Written Exam',
            'MCQ Exam',
            'Practical Exam',
            'Viva Exam',
            'Attendance',
            'Class Test',
            'Assignment',
        ];

        foreach ($marks as $mark) {
            ExamMark::firstOrCreate([
                'institution_id' => $institution->id,
                'name' => $mark,
            ]);
        }
    }
    private static function createExamHalls(Institution $institution): void
    {
        $halls = [
            ['hall_no' => 'Hall A', 'no_of_seat' => 50],
            ['hall_no' => 'Hall B', 'no_of_seat' => 60],
            ['hall_no' => 'Hall C', 'no_of_seat' => 40],
        ];

        foreach ($halls as $hall) {
            ExamHall::firstOrCreate([
                'institution_id' => $institution->id,
                'hall_no' => $hall['hall_no'],
            ], [
                'no_of_seat' => $hall['no_of_seat'],
            ]);
        }
    }

    private static function createExamGrades(Institution $institution): void
    {
        $grades = [
            ['name' => 'A+', 'point' => 5.0, 'min' => 80, 'max' => 100, 'remark' => 'Excellent'],
            ['name' => 'A',  'point' => 4.0, 'min' => 70, 'max' => 79,  'remark' => 'Very Good'],
            ['name' => 'A-', 'point' => 3.5, 'min' => 60, 'max' => 69,  'remark' => 'Good'],
            ['name' => 'B',  'point' => 3.0, 'min' => 50, 'max' => 59,  'remark' => 'Average'],
            ['name' => 'C',  'point' => 2.5, 'min' => 40, 'max' => 49,  'remark' => 'Adequate'],
            ['name' => 'D',  'point' => 2.0, 'min' => 33, 'max' => 39,  'remark' => 'Poor'],
            ['name' => 'F',  'point' => 0.0, 'min' => 0,  'max' => 32,  'remark' => 'Fail'],
        ];

        foreach ($grades as $grade) {
            ExamGrade::firstOrCreate(
                [
                    'institution_id' => $institution->id,
                    'name' => $grade['name'],
                ],
                [
                    'grade_point' => $grade['point'],
                    'min_percentage' => $grade['min'],
                    'max_percentage' => $grade['max'],
                    'remarks' => $grade['remark'],
                ]
            );
        }
    }

    private static function createFeeTypes(Institution $institution): void
    {
        $feeTypes = [
            ['name' => 'January Month Fees',   'fee_code' => 'january-month-fees'],
            ['name' => 'February Month Fees',  'fee_code' => 'february-month-fees'],
            ['name' => 'March Month Fees',     'fee_code' => 'march-month-fees'],
            ['name' => 'April Month Fees',     'fee_code' => 'april-month-fees'],
            ['name' => 'May Month Fees',       'fee_code' => 'may-month-fees'],
            ['name' => 'June Month Fees',      'fee_code' => 'june-month-fees'],
            ['name' => 'July Month Fees',      'fee_code' => 'july-month-fees'],
            ['name' => 'August Month Fees',    'fee_code' => 'august-month-fees'],
            ['name' => 'September Month Fees', 'fee_code' => 'september-month-fees'],
            ['name' => 'October Month Fees',   'fee_code' => 'october-month-fees'],
            ['name' => 'November Month Fees',  'fee_code' => 'november-month-fees'],
            ['name' => 'December Month Fees',  'fee_code' => 'december-month-fees'],

            ['name' => 'Admission Fees', 'fee_code' => 'admission-fees'],
            ['name' => 'Exam Fees',      'fee_code' => 'exam-fees'],
            ['name' => 'Sports Fees',    'fee_code' => 'sports-fees'],
            ['name' => 'Transport Fees', 'fee_code' => 'transport-fees'],
        ];

        foreach ($feeTypes as $feeType) {
            FeeType::updateOrCreate(
                [
                    'institution_id' => $institution->id,
                    'fee_code'       => $feeType['fee_code'],
                ],
                [
                    'name'           => $feeType['name'],
                ]
            );
        }
    }
    private static function createLeaveCategories(Institution $institution): void
    {
        $leaveCategories = [
            ['name' => 'Illness',          'role' => 'admin',   'days' => 10],
            ['name' => 'Illness',          'role' => 'teacher', 'days' => 20],
            ['name' => 'Tour',             'role' => 'student', 'days' => 5],
            ['name' => 'Medical Leave',    'role' => 'admin',   'days' => 10],
            ['name' => 'Medical Leave',    'role' => 'teacher', 'days' => 20],
            ['name' => 'Medical Leave',    'role' => 'student', 'days' => 20],
            ['name' => 'Casual Leave',     'role' => 'teacher', 'days' => 10],
            ['name' => 'Maternity Leave',  'role' => 'teacher', 'days' => 60],
        ];

        foreach ($leaveCategories as $leave) {
            LeaveCategory::firstOrCreate(
                [
                    'institution_id' => $institution->id,
                    'name'           => $leave['name'],
                    'role'           => $leave['role'],
                ],
                [
                    'days'           => $leave['days'],
                    'is_paid'        => true,
                    'allow_half_day' => false,
                    'description'    => null,
                    'status'         => true,
                ]
            );
        }
    }
    private static function createOfficeHeads(Institution $institution): void
    {
        $officeHeads = [
            ['name' => 'Salary Payments',         'type' => 'Expense'],
            ['name' => 'Electricity Bills',       'type' => 'Expense'],
            ['name' => 'Office Rent',             'type' => 'Expense'],
            ['name' => 'Student Fees Collection', 'type' => 'Income'],
        ];

        foreach ($officeHeads as $head) {
            OfficeHead::firstOrCreate(
                [
                    'institution_id' => $institution->id,
                    'name'           => $head['name'],
                ],
                [
                    'type'           => $head['type'],
                ]
            );
        }
    }
    private static function createOfficeAccounts(Institution $institution): void
    {
        $officeAccounts = [
            ['name' => 'Main Account', 'number' => '5555441000144'],
            ['name' => 'Janata Bank',  'number' => '4144558884144'],
        ];

        foreach ($officeAccounts as $account) {
            OfficeAccount::updateOrCreate(
                [
                    'institution_id' => $institution->id,
                    'name'           => $account['name'],
                ],
                [
                    'number'         => $account['number'],
                ]
            );
        }
    }
    private static function createEventTypes(Institution $institution): void
    {
        $eventTypes = [
            'Special Festival',
            'Independent Day',
            'Summer Vacation',
            'Special Holiday',
            'Anniversary',
        ];

        foreach ($eventTypes as $eventType) {
            EventType::firstOrCreate([
                'institution_id' => $institution->id,
                'name'           => $eventType,
            ]);
        }
    }
}