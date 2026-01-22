/**
 * Print Service
 * Handles PDF generation and printing for student documents
 */

const API_BASE_URL = 'http://localhost:8000/api';

interface PrintOptions {
  filename?: string;
  autoPrint?: boolean;
}

/**
 * Get authentication token from localStorage
 */
const getAuthToken = (): string | null => {
  return localStorage.getItem('token');
};

/**
 * Download a file from a URL
 */
const downloadFile = async (url: string, filename: string, autoPrint: boolean = false): Promise<void> => {
  const token = getAuthToken();
  
  try {
    const response = await fetch(url, {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/pdf',
      },
    });

    if (!response.ok) {
      throw new Error('Failed to download document');
    }

    const blob = await response.blob();
    const blobUrl = window.URL.createObjectURL(blob);

    if (autoPrint) {
      // Open in new window and trigger print
      const printWindow = window.open(blobUrl, '_blank');
      if (printWindow) {
        printWindow.addEventListener('load', () => {
          printWindow.print();
        });
      }
    } else {
      // Download the file
      const link = document.createElement('a');
      link.href = blobUrl;
      link.download = filename;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    }

    // Clean up
    setTimeout(() => window.URL.revokeObjectURL(blobUrl), 100);
  } catch (error) {
    console.error('Download error:', error);
    throw error;
  }
};

/**
 * Generate and download admission letter
 */
export const printAdmissionLetter = async (options: PrintOptions = {}): Promise<void> => {
  const { filename = 'admission-letter.pdf', autoPrint = false } = options;
  const url = `${API_BASE_URL}/student/documents/admission-letter`;
  
  await downloadFile(url, filename, autoPrint);
};

/**
 * Download transcript (PDF)
 */
export const downloadTranscript = async (options: PrintOptions = {}): Promise<void> => {
  const { filename = 'transcript.pdf', autoPrint = false } = options;
  const url = `${API_BASE_URL}/student/academics/transcript/download`;
  
  await downloadFile(url, filename, autoPrint);
};

/**
 * Generate payment receipt
 */
export const generatePaymentReceipt = async (
  paymentId: number,
  options: PrintOptions = {}
): Promise<void> => {
  const { filename = `payment-receipt-${paymentId}.pdf`, autoPrint = false } = options;
  const url = `${API_BASE_URL}/student/payments/${paymentId}/receipt`;
  
  await downloadFile(url, filename, autoPrint);
};

/**
 * Generate invoice receipt
 */
export const generateInvoiceReceipt = async (
  invoiceId: number,
  options: PrintOptions = {}
): Promise<void> => {
  const { filename = `invoice-${invoiceId}.pdf`, autoPrint = false } = options;
  const url = `${API_BASE_URL}/student/registration/invoices/${invoiceId}/download`;
  
  await downloadFile(url, filename, autoPrint);
};

/**
 * Print course registration form
 */
export const printCourseRegistration = async (
  semesterCode?: string,
  options: PrintOptions = {}
): Promise<void> => {
  const { filename = 'course-registration.pdf', autoPrint = false } = options;
  const url = semesterCode 
    ? `${API_BASE_URL}/student/enrollment/registration-form?semester=${semesterCode}`
    : `${API_BASE_URL}/student/enrollment/registration-form`;
  
  await downloadFile(url, filename, autoPrint);
};

/**
 * Download timetable PDF
 */
export const downloadTimetable = async (options: PrintOptions = {}): Promise<void> => {
  const { filename = 'timetable.pdf', autoPrint = false } = options;
  const url = `${API_BASE_URL}/student/timetable/download`;
  
  await downloadFile(url, filename, autoPrint);
};

/**
 * Print student ID card
 */
export const printIDCard = async (options: PrintOptions = {}): Promise<void> => {
  const { filename = 'student-id-card.pdf', autoPrint = false } = options;
  const url = `${API_BASE_URL}/student/documents/id-card`;
  
  await downloadFile(url, filename, autoPrint);
};

/**
 * Download accommodation allocation letter
 */
export const downloadAllocationLetter = async (options: PrintOptions = {}): Promise<void> => {
  const { filename = 'accommodation-letter.pdf', autoPrint = false } = options;
  const url = `${API_BASE_URL}/student/accommodation/allocation-letter/download`;
  
  await downloadFile(url, filename, autoPrint);
};

/**
 * Download exam timetable
 */
export const downloadExamTimetable = async (options: PrintOptions = {}): Promise<void> => {
  const { filename = 'exam-timetable.pdf', autoPrint = false } = options;
  const url = `${API_BASE_URL}/student/exams/timetable/download`;
  
  await downloadFile(url, filename, autoPrint);
};

/**
 * Download course outline/syllabus
 */
export const downloadCourseOutline = async (
  courseId: number,
  options: PrintOptions = {}
): Promise<void> => {
  const { filename = `course-outline-${courseId}.pdf`, autoPrint = false } = options;
  const url = `${API_BASE_URL}/courses/${courseId}/outline/download`;
  
  await downloadFile(url, filename, autoPrint);
};

/**
 * Print multiple documents at once
 */
export const printBulkDocuments = async (documentUrls: string[]): Promise<void> => {
  const token = getAuthToken();
  
  for (const url of documentUrls) {
    try {
      const response = await fetch(url, {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/pdf',
        },
      });

      if (!response.ok) continue;

      const blob = await response.blob();
      const blobUrl = window.URL.createObjectURL(blob);
      
      const printWindow = window.open(blobUrl, '_blank');
      if (printWindow) {
        printWindow.addEventListener('load', () => {
          printWindow.print();
        });
      }

      // Clean up
      setTimeout(() => window.URL.revokeObjectURL(blobUrl), 100);
      
      // Wait a bit before opening next document
      await new Promise(resolve => setTimeout(resolve, 500));
    } catch (error) {
      console.error('Error printing document:', error);
    }
  }
};

export default {
  printAdmissionLetter,
  downloadTranscript,
  generatePaymentReceipt,
  generateInvoiceReceipt,
  printCourseRegistration,
  downloadTimetable,
  printIDCard,
  downloadAllocationLetter,
  downloadExamTimetable,
  downloadCourseOutline,
  printBulkDocuments,
};
