# Fee Structure API Documentation

## Overview
The Fee Structure API provides endpoints for managing fee structures for different academic programs and semesters. Administrators have full CRUD access, while students and teachers have read-only access.

## Base URL
```
http://127.0.0.1:8000/api
```

## Authentication
All endpoints require authentication via Sanctum Bearer token.
```
Authorization: Bearer {your_token_here}
```

---

## Admin Endpoints (Full CRUD Access)

### 1. Get All Fee Structures
**GET** `/admin/fee-structures`

**Permission Required:** `fees.read`

**Query Parameters:**
- `program` (string, optional): Filter by program name
- `semester` (integer, optional): Filter by semester number
- `status` (string, optional): Filter by status (active/inactive)
- `fee_type` (string, optional): Filter by fee type
- `per_page` (integer, optional): Items per page (default: 15)

**Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "program": "Computer Science",
        "semester": 1,
        "amount": "2500.00",
        "due_date": "2025-01-15T00:00:00.000000Z",
        "fee_type": "tuition",
        "description": "Semester 1 tuition fees for Computer Science program",
        "status": "active",
        "created_at": "2025-12-16T21:00:18.000000Z",
        "updated_at": "2025-12-16T21:00:18.000000Z"
      }
    ],
    "total": 15,
    "per_page": 15
  },
  "message": "Fee structures retrieved successfully"
}
```

### 2. Create Fee Structure
**POST** `/admin/fee-structures`

**Permission Required:** `fees.create`

**Request Body:**
```json
{
  "program": "Computer Science",
  "semester": 1,
  "amount": 2500.00,
  "due_date": "2025-01-15",
  "fee_type": "tuition",
  "description": "Semester 1 tuition fees",
  "status": "active"
}
```

**Validation Rules:**
- `program`: required|string|max:255
- `semester`: required|integer|min:1|max:8
- `amount`: required|numeric|min:0
- `due_date`: required|date
- `fee_type`: required|string|max:255
- `description`: nullable|string
- `status`: in:active,inactive

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 16,
    "program": "Computer Science",
    "semester": 1,
    "amount": "2500.00",
    "due_date": "2025-01-15T00:00:00.000000Z",
    "fee_type": "tuition",
    "description": "Semester 1 tuition fees",
    "status": "active",
    "created_at": "2025-12-16T21:00:18.000000Z",
    "updated_at": "2025-12-16T21:00:18.000000Z"
  },
  "message": "Fee structure created successfully"
}
```

### 3. Get Single Fee Structure
**GET** `/admin/fee-structures/{id}`

**Permission Required:** `fees.read`

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "program": "Computer Science",
    "semester": 1,
    "amount": "2500.00",
    "due_date": "2025-01-15T00:00:00.000000Z",
    "fee_type": "tuition",
    "description": "Semester 1 tuition fees for Computer Science program",
    "status": "active",
    "created_at": "2025-12-16T21:00:18.000000Z",
    "updated_at": "2025-12-16T21:00:18.000000Z"
  },
  "message": "Fee structure retrieved successfully"
}
```

### 4. Update Fee Structure
**PUT** `/admin/fee-structures/{id}`

**Permission Required:** `fees.update`

**Request Body:**
```json
{
  "program": "Computer Science",
  "semester": 2,
  "amount": 2600.00,
  "due_date": "2025-06-15",
  "fee_type": "tuition",
  "description": "Updated semester 2 tuition fees",
  "status": "active"
}
```

**Validation Rules:**
- `program`: sometimes|required|string|max:255
- `semester`: sometimes|required|integer|min:1|max:8
- `amount`: sometimes|required|numeric|min:0
- `due_date`: sometimes|required|date
- `fee_type`: sometimes|required|string|max:255
- `description`: nullable|string
- `status`: sometimes|in:active,inactive

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "program": "Computer Science",
    "semester": 2,
    "amount": "2600.00",
    "due_date": "2025-06-15T00:00:00.000000Z",
    "fee_type": "tuition",
    "description": "Updated semester 2 tuition fees",
    "status": "active",
    "created_at": "2025-12-16T21:00:18.000000Z",
    "updated_at": "2025-12-17T08:00:00.000000Z"
  },
  "message": "Fee structure updated successfully"
}
```

### 5. Delete Fee Structure
**DELETE** `/admin/fee-structures/{id}`

**Permission Required:** `fees.delete`

**Response:**
```json
{
  "success": true,
  "message": "Fee structure deleted successfully"
}
```

### 6. Get Fee Structures by Program and Semester
**GET** `/admin/fee-structures/program-semester`

**Permission Required:** `fees.read`

**Query Parameters:**
- `program` (string, required): Program name
- `semester` (integer, required): Semester number

**Example Request:**
```
GET /admin/fee-structures/program-semester?program=Computer%20Science&semester=1
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "program": "Computer Science",
      "semester": 1,
      "amount": "2500.00",
      "due_date": "2025-01-15T00:00:00.000000Z",
      "fee_type": "tuition",
      "description": "Semester 1 tuition fees for Computer Science program",
      "status": "active",
      "created_at": "2025-12-16T21:00:18.000000Z",
      "updated_at": "2025-12-16T21:00:18.000000Z"
    }
  ],
  "message": "Fee structures retrieved successfully"
}
```

### 7. Get Overdue Fee Structures
**GET** `/admin/fee-structures/overdue`

**Permission Required:** `fees.read`

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 14,
      "program": "Computer Science",
      "semester": 1,
      "amount": "150.00",
      "due_date": "2024-12-01T00:00:00.000000Z",
      "fee_type": "late_fee",
      "description": "Late payment penalty fee",
      "status": "active",
      "created_at": "2025-12-16T21:00:18.000000Z",
      "updated_at": "2025-12-16T21:00:18.000000Z"
    }
  ],
  "message": "Overdue fee structures retrieved successfully"
}
```

---

## Public Endpoints (Read-Only Access)

### 1. Get All Fee Structures (Public)
**GET** `/fee-structures`

**Permission Required:** `fees.read`

Same as admin endpoint but read-only access for students and teachers.

### 2. Get Single Fee Structure (Public)
**GET** `/fee-structures/{id}`

**Permission Required:** `fees.read`

Same as admin endpoint but read-only access.

### 3. Get Fee Structures by Program and Semester (Public)
**GET** `/fee-structures/program-semester`

**Permission Required:** `fees.read`

Same as admin endpoint but read-only access.

---

## Error Responses

### Validation Error (422)
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "program": ["The program field is required."],
    "amount": ["The amount must be a number."]
  }
}
```

### Not Found (404)
```json
{
  "success": false,
  "message": "Fee structure not found",
  "error": "No query results for model [App\\Models\\FeeStructure] 999"
}
```

### Unauthorized (401)
```json
{
  "message": "Unauthenticated."
}
```

### Forbidden (403)
```json
{
  "message": "This action is unauthorized."
}
```

### Server Error (500)
```json
{
  "success": false,
  "message": "Error creating fee structure",
  "error": "Database connection failed"
}
```

---

## Fee Types
Common fee types include:
- `tuition` - Regular tuition fees
- `library` - Library usage fees
- `lab` - Laboratory fees
- `workshop` - Workshop fees
- `activity` - Student activity fees
- `exam` - Examination fees
- `late_fee` - Late payment penalties
- `registration` - Registration fees

## Programs
Available programs:
- Computer Science
- Business Administration
- Engineering
- Mathematics
- (Add more as needed)

## Model Attributes

### FeeStructure Model
- `id` (integer): Primary key
- `program` (string): Academic program name
- `semester` (integer): Semester number (1-8)
- `amount` (decimal): Fee amount
- `due_date` (date): Payment due date
- `fee_type` (string): Type of fee
- `description` (text): Fee description
- `status` (enum): active|inactive
- `created_at` (timestamp): Creation timestamp
- `updated_at` (timestamp): Last update timestamp

### Model Scopes
- `active()` - Filter only active fee structures
- `forProgram($program)` - Filter by program
- `forSemester($semester)` - Filter by semester
- `dueBefore($date)` - Filter fees due before date
- `dueAfter($date)` - Filter fees due after date

### Model Accessors
- `formatted_amount` - Returns formatted amount (e.g., "$2,500.00")
- `is_overdue` - Returns boolean if fee is overdue
