<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\Course;
use App\Models\Branch;

class Student extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'code',
        'name',
        // 'password',
        'email',
        'phone',
     'password',
        'status',
        'user_id',
        'course_id',
        'branch_id',
        // 'course_start_at',
        // 'course_end_at',
        // 'certificate_issued_at',
        // 'certificate_path',
    ];

    /**
     * The attributes that should be cast to dates.
     *
     * @var array
     */
    protected $dates = [
        'course_start_at',
        'course_end_at',
        'certificate_issued_at',
        'created_at',
        'updated_at',
    ];

    /**
     * Default attribute values.
     */
    protected $attributes = [
        'status' => 'active',
    ];

    // ---------------- RELATIONSHIPS ----------------

    /**
     * User who owns this student record
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Course associated with this student
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Branch associated with this student
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    // ---------------- CUSTOM HELPERS ----------------

    /**
     * Check if student has completed the course
     *
     * @return bool
     */
    public function hasCompletedCourse(): bool
    {
        return !is_null($this->course_end_at);
    }

    /**
     * Check if certificate is issued
     *
     * @return bool
     */
    public function hasCertificate(): bool
    {
        return !is_null($this->certificate_issued_at) && !empty($this->certificate_path);
    }

    /**
     * Mark course as completed
     *
     * @return $this
     */
    public function completeCourse(): self
    {
        $this->course_end_at = Carbon::now();
        $this->save();
        return $this;
    }

    /**
     * Issue certificate for student
     *
     * @param string $path
     * @return $this
     */
    public function issueCertificate(string $path): self
    {
        $this->certificate_issued_at = Carbon::now();
        $this->certificate_path = $path;
        $this->save();
        return $this;
    }
}
