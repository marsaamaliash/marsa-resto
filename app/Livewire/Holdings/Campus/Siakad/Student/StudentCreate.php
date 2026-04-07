<?php

namespace App\Livewire\Holdings\Campus\Siakad\Student;

use App\Models\Holdings\Campus\Siakads\Faculty;
use App\Models\Holdings\Campus\Siakads\StudyProgram;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class StudentCreate extends Component
{
    // ─────────────────────────────────────────────
    // STATE
    // ─────────────────────────────────────────────
    public $showCancelConfirm = false;

    // TAB 1 — PERSONAL
    public $nim;

    public $first_name;

    public $middle_name;

    public $last_name;

    public $gender = 'Male';

    public $religion;

    public $birth_place;

    public $birth_date;

    // TAB 2 — ACADEMIC
    public $academic_year;

    public $faculty_id;

    public $program_id;

    public $class_group;

    public $student_status = 'Active';

    // TAB 3 — CONTACT
    public $email;

    public $phone;

    public $address;

    public $city;

    // TAB 4 — PARENTS
    public $father_name;

    public $father_phone;

    public $mother_name;

    public $mother_phone;

    public $guardian_name;

    // ─────────────────────────────────────────────
    // VALIDATION RULES
    // ─────────────────────────────────────────────
    protected $rules = [
        // PERSONAL
        'nim' => 'nullable|string|max:50|unique:students,student_number',
        'first_name' => 'required|string|max:100',
        'last_name' => 'required|string|max:100',
        'gender' => 'required|string',
        'religion' => 'required|string',
        'birth_place' => 'required|string|max:255',
        'birth_date' => 'required|date',

        // ACADEMIC
        'academic_year' => 'required|string|max:20',
        'faculty_id' => 'required|integer',
        'program_id' => 'required|integer',
        'class_group' => 'nullable|string|max:50',
        'student_status' => 'required|string',

        // CONTACT
        'email' => 'nullable|email|max:255',
        'phone' => 'nullable|string|max:20',
        'address' => 'nullable|string',
        'city' => 'nullable|string|max:100',

        // PARENTS
        'father_name' => 'nullable|string|max:255',
        'father_phone' => 'nullable|string|max:20',
        'mother_name' => 'nullable|string|max:255',
        'mother_phone' => 'nullable|string|max:20',
        'guardian_name' => 'nullable|string|max:255',
    ];

    // ─────────────────────────────────────────────
    // CANCEL BUTTON
    // ─────────────────────────────────────────────
    public function confirmCancel()
    {
        $this->showCancelConfirm = true;
    }

    public function cancel()
    {
        return redirect()->route('campus.siakad.student.index');
    }

    // ─────────────────────────────────────────────
    // SAVE
    // ─────────────────────────────────────────────
    public function save()
    {
        $this->validate();

        DB::beginTransaction();

        try {
            // Auto full name
            $full_name = trim(
                $this->first_name.' '.
                ($this->middle_name ? $this->middle_name.' ' : '').
                $this->last_name
            );

            Student::create([
                // PERSONAL
                'student_number' => $this->nim,
                'first_name' => $this->first_name,
                'middle_name' => $this->middle_name,
                'last_name' => $this->last_name,
                'full_name' => $full_name,
                'gender' => $this->gender,
                'religion' => $this->religion,
                'birth_place' => $this->birth_place,
                'birth_date' => $this->birth_date,

                // ACADEMIC
                'academic_year' => $this->academic_year,
                'faculty_id' => $this->faculty_id,
                'program_id' => $this->program_id,
                'class_group' => $this->class_group,
                'student_status' => $this->student_status,

                // CONTACT
                'email' => $this->email,
                'phone' => $this->phone,
                'address' => $this->address,
                'city' => $this->city,

                // PARENTS
                'father_name' => $this->father_name,
                'father_phone' => $this->father_phone,
                'mother_name' => $this->mother_name,
                'mother_phone' => $this->mother_phone,
                'guardian_name' => $this->guardian_name,
            ]);

            DB::commit();
            session()->flash('success', 'Mahasiswa baru berhasil ditambahkan.');

            return redirect()->route('campus.siakad.student.index');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error: '.$e->getMessage());
        }
    }

    // ─────────────────────────────────────────────
    // RENDER
    // ─────────────────────────────────────────────
    public function render()
    {
        return view('livewire.holdings.campus.siakad.student.students-create', [
            'faculties' => Faculty::orderBy('faculty_name')->get(),
            'programs' => StudyProgram::orderBy('program_name')->get(),
        ]);
    }
}
