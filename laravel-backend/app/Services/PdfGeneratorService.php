<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;

class PdfGeneratorService
{
    /**
     * Generate admission letter PDF
     */
    public function generateAdmissionLetter(array $data): string
    {
        $pdf = Pdf::loadView('pdfs.admission-letter', $data);
        
        return $pdf->output();
    }

    /**
     * Generate student ID card PDF
     */
    public function generateIDCard(array $data): string
    {
        $pdf = Pdf::loadView('pdfs.id-card', $data);
        $pdf->setPaper([0, 0, 243, 153], 'landscape'); // ID card size (85.6mm x 53.98mm)
        
        return $pdf->output();
    }

    /**
     * Generate course registration form PDF
     */
    public function generateCourseRegistration(array $data): string
    {
        $pdf = Pdf::loadView('pdfs.course-registration', $data);
        
        return $pdf->output();
    }

    /**
     * Generate timetable PDF
     */
    public function generateTimetable(array $data): string
    {
        $pdf = Pdf::loadView('pdfs.timetable', $data);
        $pdf->setPaper('a4', 'landscape');
        
        return $pdf->output();
    }

    /**
     * Generate exam timetable PDF
     */
    public function generateExamTimetable(array $data): string
    {
        $pdf = Pdf::loadView('pdfs.exam-timetable', $data);
        $pdf->setPaper('a4', 'landscape');
        
        return $pdf->output();
    }

    /**
     * Generate payment receipt PDF
     */
    public function generatePaymentReceipt(array $data): string
    {
        $pdf = Pdf::loadView('pdfs.payment-receipt', $data);
        
        return $pdf->output();
    }

    /**
     * Generate course outline PDF
     */
    public function generateCourseOutline(array $data): string
    {
        $pdf = Pdf::loadView('pdfs.course-outline', $data);
        
        return $pdf->output();
    }
}
