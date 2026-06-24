<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'role_id',
        'name',
        'email',
        'password',
        'phone',
        'is_active',
        'created_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class);
    }

    // Koç için öğrenciler
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'coach_students', 'coach_id', 'student_id')
            ->withTimestamps();
    }

    // Öğrenci için koçlar
    public function coaches(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'coach_students', 'student_id', 'coach_id')
            ->withTimestamps();
    }

    public function courseTemplates(): HasMany
    {
        return $this->hasMany(CourseTemplate::class, 'coach_id');
    }

    public function questionLogs(): HasMany
    {
        return $this->hasMany(QuestionLog::class, 'student_id');
    }

    public function studyLogs(): HasMany
    {
        return $this->hasMany(StudyLog::class, 'student_id');
    }

    public function examResults(): HasMany
    {
        return $this->hasMany(ExamResult::class, 'student_id');
    }

    public function resources(): HasMany
    {
        return $this->hasMany(Resource::class, 'created_by_user_id');
    }

    public function assignedResources(): HasMany
    {
        return $this->hasMany(StudentResource::class, 'student_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Helper Methods
    public function isAdmin(): bool
    {
        // Hem 'admin' hem 'superadmin' rollerini kapsar
        return in_array($this->role?->name, ['admin', 'superadmin']);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role?->name === 'superadmin';
    }

    public function isCoach(): bool
    {
        return $this->role?->name === 'coach';
    }

    public function isStudent(): bool
    {
        return $this->role?->name === 'student';
    }
}
