<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Institution;
use App\Models\Feature;
use App\Models\InventoryCategory;
use App\Models\InventoryUnit;
use App\Models\EmployeeDepartment;
use App\Models\EmployeeDesignation;
use App\Models\AcademicGroup;
use App\Models\AcademicSession;
use App\Models\AcademicSection;
use App\Models\AcademicSubject;
use App\Models\AcademicClass;
use App\Models\AcademicClassSection;
use App\Models\AcademicClassAssign;
use App\Models\AcademicClassAssignDetail;
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
        self::createFeatures($institution);

        self::createInventoryCategories($institution);
        self::createInventoryUnits($institution);

        self::createDepartments($institution);
        self::createDesignations($institution);

        self::createAcademicSession($institution);
        self::createAcademicGroups($institution);
        self::createAcademicSections($institution);
        self::createAcademicSubjects($institution);
        self::createAcademicClasses($institution);
        self::assignSectionsToClasses($institution);
        self::createAcademicAssigns($institution);

        self::createExamTerms($institution);
        self::createExamTypes($institution);
        self::createExamMarks($institution);
        self::createExamHalls($institution);
        self::createExamGrades($institution);

        self::createFeeTypes($institution);
        self::createLeaveCategories($institution);

        self::createOfficeHeads($institution);
        self::createOfficeAccounts($institution);

        self::createEventTypes($institution);
    }

    /**
     * Institution create howar shomoy default feature flag gulo
     * (module toggle) seed kore. Notun feature add korte hole
     * ei array te shudhu key add korle hobe.
     */
    private static function createFeatures(Institution $institution): void
    {
        $features = [
            'inventory_management_module',
            'card_management_module',
            'certificate_management_module',
            'branch_module',
        ];

        foreach ($features as $featureKey) {
            Feature::firstOrCreate(
                [
                    'institution_id' => $institution->id,
                    'feature_key'    => $featureKey,
                ],
                [
                    'is_active' => true,
                ]
            );
        }
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
            'General'          => true,
            'Science'          => false,
            'Business Studies' => false,
            'Humanities'       => false,
        ];
 
        foreach ($groups as $name => $isCurrent) {
            AcademicGroup::firstOrCreate(
                [
                    'institution_id' => $institution->id,
                    'name'           => $name,
                ],
                [
                    'is_current' => $isCurrent,
                ]
            );
        }
    }
    private static function createAcademicSections(Institution $institution): void
    {
        $sections = [
            'A',
            'B',
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
            'Economics',
            'Accounting',
            'Business Studies',
            'Finance and Banking',
            'Agriculture',
            'Islam and Moral Education',
            'Physical Education',
            'Arts and Crafts',
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
            ['name' => 'Play',     'numeric' => 0,  'has_section' => false],
            ['name' => 'Nursery',  'numeric' => 0,  'has_section' => false],
            ['name' => 'KG',       'numeric' => 0,  'has_section' => false],

            ['name' => 'Class 1',  'numeric' => 1,  'has_section' => false],
            ['name' => 'Class 2',  'numeric' => 2,  'has_section' => false],
            ['name' => 'Class 3',  'numeric' => 3,  'has_section' => false],
            ['name' => 'Class 4',  'numeric' => 4,  'has_section' => false],
            ['name' => 'Class 5',  'numeric' => 5,  'has_section' => false],

            ['name' => 'Class 6',  'numeric' => 6,  'has_section' => true],
            ['name' => 'Class 7',  'numeric' => 7,  'has_section' => true],
            ['name' => 'Class 8',  'numeric' => 8,  'has_section' => true],
            ['name' => 'Class 9',  'numeric' => 9,  'has_section' => true],
            ['name' => 'Class 10', 'numeric' => 10, 'has_section' => true],
            ['name' => 'Class 11', 'numeric' => 11, 'has_section' => true],
            ['name' => 'Class 12', 'numeric' => 12, 'has_section' => true],
        ];

        foreach ($classes as $class) {
            AcademicClass::updateOrCreate(
                [
                    'institution_id' => $institution->id,
                    'name' => $class['name'],
                ],
                [
                    'numeric' => $class['numeric'],
                    'has_section' => $class['has_section'],
                ]
            );
        }
    }
    private static function assignSectionsToClasses(Institution $institution): void
    {
        $sections = AcademicSection::where('institution_id', $institution->id)
            ->whereIn('name', ['A', 'B'])
            ->get();

        $classes = AcademicClass::where('institution_id', $institution->id)
            ->where('has_section', true)
            ->get();

        foreach ($classes as $class) {
            foreach ($sections as $section) {
                AcademicClassSection::firstOrCreate([
                    'institution_id' => $institution->id,
                    'class_id'       => $class->id,
                    'section_id'     => $section->id,
                ]);
            }
        }
    }
    private static function createAcademicAssigns(Institution $institution): void
    {
        $subjects = AcademicSubject::where('institution_id', $institution->id)->take(3)->get();
        $classes = AcademicClass::with('sections')->where('institution_id', $institution->id)->get();

        foreach ($classes as $class) {
            // যেসব class-এর section নেই
            if (! $class->has_section) {
                $classAssign = AcademicClassAssign::firstOrCreate([
                    'institution_id' => $institution->id,
                    'class_id'       => $class->id,
                    'section_id'     => null,
                ]);
                foreach ($subjects as $subject) {
                    AcademicClassAssignDetail::firstOrCreate([
                        'institution_id'            => $institution->id,
                        'academic_class_assign_id'  => $classAssign->id,
                        'subject_id'                => $subject->id,
                    ]);
                }
                continue;
            }

            // যেসব class-এর section আছে
            foreach ($class->sections as $section) {

                $classAssign = AcademicClassAssign::firstOrCreate([
                    'institution_id' => $institution->id,
                    'class_id'       => $class->id,
                    'section_id'     => $section->id,
                ]);

                foreach ($subjects as $subject) {
                    AcademicClassAssignDetail::firstOrCreate([
                        'institution_id'            => $institution->id,
                        'academic_class_assign_id'  => $classAssign->id,
                        'subject_id'                => $subject->id,
                    ]);
                }
            }
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

            ['name' => 'Monthly Fee',        'code' => 'monthly_fee'],
            ['name' => 'Admission Fee',      'code' => 'admission_fee'],
            ['name' => 'Registration Fee',   'code' => 'registration_fee'],
            ['name' => 'Exam Fee',           'code' => 'exam_fee'],
        ];

        foreach ($feeTypes as $feeType) {
            FeeType::firstOrCreate(
                [
                    'institution_id' => $institution->id,
                    'code'       => $feeType['code'],
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
            ['name' => 'Salary Payments',         'type' => 'expense'],
            ['name' => 'Electricity Bills',       'type' => 'expense'],
            ['name' => 'Office Rent',             'type' => 'expense'],
            ['name' => 'Student Fees Collection', 'type' => 'deposit'],
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