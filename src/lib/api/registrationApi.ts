import axios from 'axios';

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

// Types
export interface Registration {
  id: number;
  semester_code: string;
  academic_year: string;
  status: string;
  total_fees: string;
  amount_paid: string;
  balance: string;
  fees_verified: boolean;
  insurance_verified: boolean;
  registration_date: string;
  payment_percentage: number;
}

export interface Insurance {
  id: number;
  semester_code: string;
  academic_year: string;
  provider: string;
  policy_number: string;
  expiry_date: string;
  document_path: string;
  status: string;
  submission_date: string;
  is_expired: boolean;
  days_until_expiry: number;
}

export interface Invoice {
  id: number;
  invoice_number: string;
  semester: string;
  amount: string;
  due_date: string;
  status: string;
  created_at: string;
}

export interface Payment {
  id: number;
  invoice_id: number;
  amount: string;
  payment_method: string;
  payment_date: string;
  reference_number: string;
  status: string;
}

// API Client
const getAuthHeader = () => {
  const token = localStorage.getItem('token');
  return {
    headers: {
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json',
    },
  };
};

export const registrationApi = {
  // Get current semester registration
  getCurrentRegistration: async (): Promise<Registration> => {
    const response = await axios.get(
      `${API_BASE_URL}/student/registration/current`,
      getAuthHeader()
    );
    return response.data;
  },

  // Get registration history
  getRegistrationHistory: async (): Promise<{ registrations: Registration[]; total_count: number }> => {
    const response = await axios.get(
      `${API_BASE_URL}/student/registration/history`,
      getAuthHeader()
    );
    return response.data;
  },

  // Upload insurance document
  uploadInsurance: async (file: File, semesterCode: string): Promise<any> => {
    const formData = new FormData();
    formData.append('insurance_document', file);
    formData.append('semester_code', semesterCode);

    const token = localStorage.getItem('token');
    const response = await axios.post(
      `${API_BASE_URL}/student/insurance/upload`,
      formData,
      {
        headers: {
          Authorization: `Bearer ${token}`,
          'Content-Type': 'multipart/form-data',
        },
      }
    );
    return response.data;
  },

  // Get insurance status
  getInsuranceStatus: async (): Promise<Insurance> => {
    const response = await axios.get(
      `${API_BASE_URL}/student/insurance/status`,
      getAuthHeader()
    );
    return response.data;
  },

  // Get invoices
  getInvoices: async (): Promise<Invoice[]> => {
    const response = await axios.get(
      `${API_BASE_URL}/student/invoices`,
      getAuthHeader()
    );
    return response.data.invoices;
  },

  // Download invoice
  downloadInvoice: async (invoiceId: number): Promise<Blob> => {
    const token = localStorage.getItem('token');
    const response = await axios.get(
      `${API_BASE_URL}/student/invoices/${invoiceId}/download`,
      {
        headers: {
          Authorization: `Bearer ${token}`,
        },
        responseType: 'blob',
      }
    );
    return response.data;
  },

  // Get payment history
  getPaymentHistory: async (): Promise<{ payments: Payment[]; total_paid: string }> => {
    const response = await axios.get(
      `${API_BASE_URL}/student/payment-history`,
      getAuthHeader()
    );
    return response.data;
  },

  // Verify payment
  verifyPayment: async (referenceNumber: string): Promise<any> => {
    const response = await axios.post(
      `${API_BASE_URL}/student/payment/verify`,
      { reference_number: referenceNumber },
      getAuthHeader()
    );
    return response.data;
  },
};
