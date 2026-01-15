import axios from 'axios';

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

// Types
export interface Accommodation {
  id: number;
  academic_year: string;
  hostel_name: string;
  room_number: string;
  bed_space: string;
  room_type: string;
  full_room: string;
  allocation_date: string;
  check_in_date: string;
  renewal_date: string;
  status: string;
  renewal_due_soon: boolean;
  is_active: boolean;
}

export interface Roommate {
  id: number;
  roommate_name: string;
  roommate_phone: string;
  roommate_email: string;
  bed_space: string;
}

export interface AccommodationFee {
  id: number;
  academic_year: string;
  fee_amount: string;
  amount_paid: string;
  balance: string;
  due_date: string;
  payment_status: string;
  payment_percentage: number;
  is_overdue: boolean;
}

export interface Amenity {
  id: number;
  amenity_name: string;
  description: string;
  is_available: boolean;
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

export const accommodationApi = {
  // Get current accommodation
  getCurrentAccommodation: async (): Promise<Accommodation> => {
    const response = await axios.get(
      `${API_BASE_URL}/student/accommodation/current`,
      getAuthHeader()
    );
    return response.data;
  },

  // Get roommates
  getRoommates: async (): Promise<Roommate[]> => {
    const response = await axios.get(
      `${API_BASE_URL}/student/accommodation/roommates`,
      getAuthHeader()
    );
    return response.data.roommates;
  },

  // Get accommodation fees
  getAccommodationFees: async (): Promise<AccommodationFee> => {
    const response = await axios.get(
      `${API_BASE_URL}/student/accommodation/fees`,
      getAuthHeader()
    );
    return response.data;
  },

  // Get hostel amenities
  getHostelAmenities: async (): Promise<Amenity[]> => {
    const response = await axios.get(
      `${API_BASE_URL}/student/accommodation/amenities`,
      getAuthHeader()
    );
    return response.data.amenities;
  },

  // Download allocation letter
  downloadAllocationLetter: async (): Promise<Blob> => {
    const token = localStorage.getItem('token');
    const response = await axios.get(
      `${API_BASE_URL}/student/accommodation/allocation-letter/download`,
      {
        headers: {
          Authorization: `Bearer ${token}`,
        },
        responseType: 'blob',
      }
    );
    return response.data;
  },
};
