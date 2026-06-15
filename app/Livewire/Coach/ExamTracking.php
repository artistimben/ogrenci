<?php

namespace App\Livewire\Coach;

use Livewire\Component;
use Livewire\WithPagination;

class ExamTracking extends Component
{
    use WithPagination;

    public $selectedStudent;
    public $selectedField;
    public $selectedExamType;
    public $dateFrom;
    public $dateTo;
    public $sortBy = 'exam_date';
    public $sortDirection = 'desc';
    
    // Deneme karşılaştırma için
    public $selectedFirstExam;
    public $selectedSecondExam;
    
    public $fields = [];
    public $examTypes = ['TYT', 'AYT', 'Deneme', 'Deneme-1', 'Deneme-2'];

    public function mount()
    {
        $this->fields = \App\Models\Field::courseFields()
            ->where('is_active', true)
            ->orderBy('order')
            ->get();
    }

    public function applyFilters()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->selectedStudent = null;
        $this->selectedField = null;
        $this->selectedExamType = null;
        $this->dateFrom = null;
        $this->dateTo = null;
        $this->selectedFirstExam = null;
        $this->selectedSecondExam = null;
        $this->resetPage();
    }
    
    public function updatedSelectedStudent()
    {
        // Öğrenci değiştiğinde deneme seçimlerini sıfırla
        $this->selectedFirstExam = null;
        $this->selectedSecondExam = null;
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'desc';
        }
        $this->resetPage();
    }

    public function render()
    {
        $students = auth()->user()->students;

        $query = \App\Models\ExamResult::whereIn('student_id', $students->pluck('id'));

        // Apply filters
        if ($this->selectedStudent) {
            $query->where('student_id', $this->selectedStudent);
        }

        if ($this->selectedField) {
            $query->where('field_id', $this->selectedField);
        }

        if ($this->selectedExamType) {
            $query->where('exam_type', $this->selectedExamType);
        }

        if ($this->dateFrom) {
            $query->whereDate('exam_date', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('exam_date', '<=', $this->dateTo);
        }

        // Apply sorting
        $query->orderBy($this->sortBy, $this->sortDirection);

        $examResults = $query->with(['student', 'course', 'field'])
            ->paginate(15);

        // Calculate statistics
        $allResults = \App\Models\ExamResult::whereIn('student_id', $students->pluck('id'));
        if ($this->selectedStudent) {
            $allResults->where('student_id', $this->selectedStudent);
        }
        
        $allResultsData = $allResults->get();
        
        // Gelişim hesaplamaları
        $firstHalf = $allResultsData->sortBy('exam_date')->take(ceil($allResultsData->count() / 2));
        $secondHalf = $allResultsData->sortBy('exam_date')->skip(floor($allResultsData->count() / 2));
        
        $firstHalfAvg = $firstHalf->count() > 0 ? round($firstHalf->avg('net_score'), 2) : 0;
        $secondHalfAvg = $secondHalf->count() > 0 ? round($secondHalf->avg('net_score'), 2) : 0;
        $improvement = $secondHalfAvg - $firstHalfAvg;
        $improvementPercentage = $firstHalfAvg > 0 ? round(($improvement / $firstHalfAvg) * 100, 1) : 0;
        
        $stats = [
            'total_exams' => $allResultsData->count(),
            'avg_net' => round($allResultsData->avg('net_score'), 2),
            'best_net' => round($allResultsData->max('net_score'), 2),
            'worst_net' => round($allResultsData->min('net_score'), 2),
            'first_half_avg' => $firstHalfAvg,
            'second_half_avg' => $secondHalfAvg,
            'improvement' => round($improvement, 2),
            'improvement_percentage' => $improvementPercentage,
        ];

        // Chart data preparation
        $chartData = $this->prepareChartData($students);
        
        // Seçilen öğrencinin deneme türlerini al
        $studentExamTypes = [];
        if ($this->selectedStudent) {
            $studentExamTypes = \App\Models\ExamResult::where('student_id', $this->selectedStudent)
                ->distinct()
                ->pluck('exam_type')
                ->filter()
                ->values()
                ->toArray();
        }
        
        // Öğrencinin tüm denemelerini al (karşılaştırma için) - Benzersiz deneme adı+tarih kombinasyonları
        $availableExams = [];
        if ($this->selectedStudent) {
            // Benzersiz deneme adı+tarih kombinasyonlarını bul
            $uniqueExams = \App\Models\ExamResult::where('student_id', $this->selectedStudent)
                ->select('exam_name', 'exam_date', 'exam_type')
                ->distinct()
                ->orderBy('exam_date', 'asc')
                ->orderBy('exam_name', 'asc')
                ->get();
            
            $examCounter = [];
            foreach ($uniqueExams as $uniqueExam) {
                // Her deneme türü için sayaç
                $typeKey = $uniqueExam->exam_type ?: 'Genel';
                if (!isset($examCounter[$typeKey])) {
                    $examCounter[$typeKey] = 0;
                }
                $examCounter[$typeKey]++;
                
                // Bu deneme için toplam net skorunu hesapla (tüm derslerin toplamı)
                // exam_date'i Carbon instance olarak kullan
                $examDate = is_string($uniqueExam->exam_date) ? \Carbon\Carbon::parse($uniqueExam->exam_date) : $uniqueExam->exam_date;
                
                $totalNet = \App\Models\ExamResult::where('student_id', $this->selectedStudent)
                    ->where('exam_name', $uniqueExam->exam_name)
                    ->whereDate('exam_date', $examDate->format('Y-m-d'))
                    ->sum('net_score');
                
                // İlk exam_result'ı ID olarak kullan (dropdown için)
                $firstResult = \App\Models\ExamResult::where('student_id', $this->selectedStudent)
                    ->where('exam_name', $uniqueExam->exam_name)
                    ->whereDate('exam_date', $examDate->format('Y-m-d'))
                    ->first();
                
                if ($firstResult) {
                    $dateFormatted = $examDate instanceof \Carbon\Carbon ? $examDate->format('d.m.Y') : \Carbon\Carbon::parse($examDate)->format('d.m.Y');
                    $availableExams[] = [
                        'id' => $firstResult->id,
                        'label' => $uniqueExam->exam_name . 
                                  ($uniqueExam->exam_type ? ' (' . $uniqueExam->exam_type . ' #' . $examCounter[$typeKey] . ')' : '') . 
                                  ' - ' . $dateFormatted . 
                                  ' - Toplam: ' . number_format($totalNet, 2) . ' Net',
                        'exam_name' => $uniqueExam->exam_name,
                        'exam_date' => $examDate instanceof \Carbon\Carbon ? $examDate->format('Y-m-d') : \Carbon\Carbon::parse($examDate)->format('Y-m-d'),
                        'exam_type' => $uniqueExam->exam_type,
                        'total_net' => round($totalNet, 2),
                    ];
                }
            }
        }

        return view('livewire.coach.exam-tracking', [
            'examResults' => $examResults,
            'students' => $students,
            'stats' => $stats,
            'chartData' => $chartData,
            'studentExamTypes' => $studentExamTypes,
            'availableExams' => $availableExams,
        ]);
    }

    protected function prepareChartData($students)
    {
        $query = \App\Models\ExamResult::whereIn('student_id', $students->pluck('id'));
        
        if ($this->selectedStudent) {
            $query->where('student_id', $this->selectedStudent);
        }

        // Tüm denemeleri al (limit yok, gelişimi görmek için)
        $recentExams = $query->with(['course', 'field'])
            ->orderBy('exam_date', 'asc') // Chronological order
            ->get();

        // Net Progress Chart (Line Chart) - TYT ve AYT ayrı göster
        $netProgressData = [
            'labels' => [],
            'tytData' => [],
            'aytData' => [],
            'allData' => [],
        ];

        foreach ($recentExams as $exam) {
            $dateLabel = $exam->exam_date->format('d.m.Y');
            $netProgressData['labels'][] = $dateLabel;
            $netProgressData['allData'][] = round($exam->net_score, 2);
            
            if ($exam->exam_type === 'TYT') {
                $netProgressData['tytData'][] = round($exam->net_score, 2);
                $netProgressData['aytData'][] = null;
            } elseif ($exam->exam_type === 'AYT') {
                $netProgressData['tytData'][] = null;
                $netProgressData['aytData'][] = round($exam->net_score, 2);
            } else {
                $netProgressData['tytData'][] = null;
                $netProgressData['aytData'][] = null;
            }
        }
        
        // Eğer veri yoksa boş array döndür
        if (empty($netProgressData['labels'])) {
            $netProgressData = [
                'labels' => [],
                'tytData' => [],
                'aytData' => [],
                'allData' => [],
            ];
        }

        // Course Performance Chart (Bar Chart) - Ortalama
        $coursePerformance = [];
        foreach ($recentExams as $exam) {
            if ($exam->course) {
                $courseName = $exam->course->name;
                if (!isset($coursePerformance[$courseName])) {
                    $coursePerformance[$courseName] = [
                        'total' => 0,
                        'count' => 0,
                    ];
                }
                $coursePerformance[$courseName]['total'] += $exam->net_score;
                $coursePerformance[$courseName]['count']++;
            }
        }

        $coursePerformanceData = [
            'labels' => [],
            'data' => [],
        ];

        foreach ($coursePerformance as $courseName => $data) {
            $coursePerformanceData['labels'][] = $courseName;
            $coursePerformanceData['data'][] = round($data['total'] / $data['count'], 2);
        }
        
        // Course Development Chart (Seçilen Denemeler Karşılaştırması)
        $courseDevelopmentData = [
            'labels' => [],
            'firstExam' => [],
            'secondExam' => [],
            'improvement' => [],
            'firstExamLabel' => 'İlk Deneme',
            'secondExamLabel' => 'Son Deneme',
            'debug' => [
                'firstExamCourses' => [],
                'secondExamCourses' => [],
                'commonCourses' => [],
            ],
        ];
        
        // Seçilen denemeleri al
        $firstExam = null;
        $secondExam = null;
        
        if ($this->selectedFirstExam) {
            $firstExam = \App\Models\ExamResult::with('course')->find($this->selectedFirstExam);
        }
        if ($this->selectedSecondExam) {
            $secondExam = \App\Models\ExamResult::with('course')->find($this->selectedSecondExam);
        }
        
        // Eğer deneme seçilmemişse, ilk ve son denemeyi otomatik seç
        if (!$firstExam && !$secondExam && $recentExams->count() > 0) {
            $firstExam = $recentExams->first();
            $secondExam = $recentExams->last();
            $courseDevelopmentData['firstExamLabel'] = 'İlk Deneme (' . $firstExam->exam_date->format('d.m.Y') . ')';
            $courseDevelopmentData['secondExamLabel'] = 'Son Deneme (' . $secondExam->exam_date->format('d.m.Y') . ')';
        } elseif ($firstExam && $secondExam) {
            $courseDevelopmentData['firstExamLabel'] = $firstExam->exam_name . ' (' . $firstExam->exam_date->format('d.m.Y') . ')';
            $courseDevelopmentData['secondExamLabel'] = $secondExam->exam_name . ' (' . $secondExam->exam_date->format('d.m.Y') . ')';
        }
        
        // Her ders için seçilen denemelerin net skorlarını bul
        if ($firstExam && $secondExam) {
            $coursesData = [];
            
            // İlk deneme için: Bu denemede hangi dersler var?
            // Not: Her deneme kaydı tek bir ders için, bu yüzden aynı deneme adı+tarihine sahip tüm kayıtları al
            $firstExamDate = is_string($firstExam->exam_date) ? \Carbon\Carbon::parse($firstExam->exam_date) : $firstExam->exam_date;
            $firstExamResults = \App\Models\ExamResult::where('student_id', $firstExam->student_id)
                ->where('exam_name', $firstExam->exam_name)
                ->whereDate('exam_date', $firstExamDate->format('Y-m-d'))
                ->with('course')
                ->get();
            
            foreach ($firstExamResults as $exam) {
                if ($exam->course) {
                    $courseId = $exam->course_id;
                    $courseName = $exam->course->name;
                    if (!isset($coursesData[$courseId])) {
                        $coursesData[$courseId] = [
                            'name' => $courseName,
                            'firstExam' => null,
                            'secondExam' => null,
                        ];
                    }
                    $coursesData[$courseId]['firstExam'] = round($exam->net_score, 2);
                    $courseDevelopmentData['debug']['firstExamCourses'][] = $courseName;
                }
            }
            
            // İkinci deneme için: Bu denemede hangi dersler var?
            $secondExamDate = is_string($secondExam->exam_date) ? \Carbon\Carbon::parse($secondExam->exam_date) : $secondExam->exam_date;
            $secondExamResults = \App\Models\ExamResult::where('student_id', $secondExam->student_id)
                ->where('exam_name', $secondExam->exam_name)
                ->whereDate('exam_date', $secondExamDate->format('Y-m-d'))
                ->with('course')
                ->get();
            
            foreach ($secondExamResults as $exam) {
                if ($exam->course) {
                    $courseId = $exam->course_id;
                    $courseName = $exam->course->name;
                    if (!isset($coursesData[$courseId])) {
                        $coursesData[$courseId] = [
                            'name' => $courseName,
                            'firstExam' => null,
                            'secondExam' => null,
                        ];
                    }
                    $coursesData[$courseId]['secondExam'] = round($exam->net_score, 2);
                    $courseDevelopmentData['debug']['secondExamCourses'][] = $courseName;
                }
            }
            
            // Aynı ders için her iki denemede de sonuç varsa ekle
            foreach ($coursesData as $courseData) {
                if (!is_null($courseData['firstExam']) && !is_null($courseData['secondExam'])) {
                    $courseDevelopmentData['labels'][] = $courseData['name'];
                    $courseDevelopmentData['firstExam'][] = $courseData['firstExam'];
                    $courseDevelopmentData['secondExam'][] = $courseData['secondExam'];
                    $improvement = $courseData['secondExam'] - $courseData['firstExam'];
                    $courseDevelopmentData['improvement'][] = round($improvement, 2);
                    $courseDevelopmentData['debug']['commonCourses'][] = $courseData['name'];
                }
            }
        } else {
            // Eski yöntem: İlk ve son deneme (tüm denemelerden)
            $coursesData = [];
            foreach ($recentExams as $exam) {
                if ($exam->course) {
                    $courseId = $exam->course_id;
                    $courseName = $exam->course->name;
                    
                    if (!isset($coursesData[$courseId])) {
                        $coursesData[$courseId] = [
                            'name' => $courseName,
                            'firstExam' => null,
                            'lastExam' => null,
                            'firstDate' => null,
                            'lastDate' => null,
                        ];
                    }
                    
                    // İlk deneme
                    if (is_null($coursesData[$courseId]['firstExam']) || 
                        $exam->exam_date < $coursesData[$courseId]['firstDate']) {
                        $coursesData[$courseId]['firstExam'] = round($exam->net_score, 2);
                        $coursesData[$courseId]['firstDate'] = $exam->exam_date;
                    }
                    
                    // Son deneme
                    if (is_null($coursesData[$courseId]['lastExam']) || 
                        $exam->exam_date > $coursesData[$courseId]['lastDate']) {
                        $coursesData[$courseId]['lastExam'] = round($exam->net_score, 2);
                        $coursesData[$courseId]['lastDate'] = $exam->exam_date;
                    }
                }
            }
            
            // Sadece hem ilk hem son denemesi olan dersleri ekle
            foreach ($coursesData as $courseData) {
                if (!is_null($courseData['firstExam']) && !is_null($courseData['lastExam'])) {
                    $courseDevelopmentData['labels'][] = $courseData['name'];
                    $courseDevelopmentData['firstExam'][] = $courseData['firstExam'];
                    $courseDevelopmentData['secondExam'][] = $courseData['lastExam'];
                    $improvement = $courseData['lastExam'] - $courseData['firstExam'];
                    $courseDevelopmentData['improvement'][] = round($improvement, 2);
                }
            }
        }

        // Field Distribution Chart (Pie Chart)
        $fieldDistribution = [];
        foreach ($recentExams as $exam) {
            if ($exam->field) {
                $fieldName = $exam->field->name;
                if (!isset($fieldDistribution[$fieldName])) {
                    $fieldDistribution[$fieldName] = 0;
                }
                $fieldDistribution[$fieldName]++;
            }
        }

        $fieldDistributionData = [
            'labels' => array_keys($fieldDistribution),
            'data' => array_values($fieldDistribution),
        ];
        
        // Deneme Türü Dağılımı (Exam Type Distribution)
        $examTypeDistribution = [];
        foreach ($recentExams as $exam) {
            $examType = $exam->exam_type ?: 'Diğer';
            if (!isset($examTypeDistribution[$examType])) {
                $examTypeDistribution[$examType] = 0;
            }
            $examTypeDistribution[$examType]++;
        }
        
        $examTypeData = [
            'labels' => array_keys($examTypeDistribution),
            'data' => array_values($examTypeDistribution),
        ];
        
        // Aylık Ortalama Net Skorları (Monthly Average)
        $monthlyAverage = [];
        foreach ($recentExams as $exam) {
            $monthKey = $exam->exam_date->format('Y-m');
            $monthLabel = $exam->exam_date->format('M Y');
            if (!isset($monthlyAverage[$monthKey])) {
                $monthlyAverage[$monthKey] = [
                    'label' => $monthLabel,
                    'total' => 0,
                    'count' => 0,
                ];
            }
            $monthlyAverage[$monthKey]['total'] += $exam->net_score;
            $monthlyAverage[$monthKey]['count']++;
        }
        
        $monthlyAverageData = [
            'labels' => [],
            'data' => [],
        ];
        
        foreach ($monthlyAverage as $data) {
            $monthlyAverageData['labels'][] = $data['label'];
            $monthlyAverageData['data'][] = round($data['total'] / $data['count'], 2);
        }

        return [
            'netProgress' => $netProgressData,
            'coursePerformance' => $coursePerformanceData,
            'courseDevelopment' => $courseDevelopmentData,
            'fieldDistribution' => $fieldDistributionData,
            'examTypeDistribution' => $examTypeData,
            'monthlyAverage' => $monthlyAverageData,
        ];
    }
}
