## Assignment API Testing Guide

You can test the Assignment API using Postman or any HTTP client. Here are the endpoints:

### Prerequisites
1. Start the Laravel server: `php artisan serve`
2. Create an admin user and get authentication token via `/api/auth/login`

### API Endpoints

#### 1. **GET** `/api/admin/assignments` - List all assignments
**Headers:** 
```
Authorization: Bearer <your-token>
Content-Type: application/json
```

#### 2. **POST** `/api/admin/assignments` - Create new assignment
**Headers:** 
```
Authorization: Bearer <your-token>
Content-Type: application/json
```
**Body:**
```json
{
    "course_id": 1,
    "title": "Programming Assignment 1",
    "description": "Create a simple web application using HTML, CSS, and JavaScript",
    "due_date": "2025-12-15T23:59:59Z",
    "max_score": 100,
    "status": "published"
}
```

#### 3. **GET** `/api/admin/assignments/{id}` - Get single assignment
**Headers:** 
```
Authorization: Bearer <your-token>
Content-Type: application/json
```

#### 4. **PUT** `/api/admin/assignments/{id}` - Update assignment
**Headers:** 
```
Authorization: Bearer <your-token>
Content-Type: application/json
```
**Body:**
```json
{
    "title": "Updated Assignment Title",
    "max_score": 150
}
```

#### 5. **DELETE** `/api/admin/assignments/{id}` - Delete assignment
**Headers:** 
```
Authorization: Bearer <your-token>
Content-Type: application/json
```

#### 6. **GET** `/api/courses/{course_id}/assignments` - Get assignments by course
**Headers:** 
```
Authorization: Bearer <your-token>
Content-Type: application/json
```

#### 7. **GET** `/api/assignments/upcoming` - Get upcoming assignments
**Headers:** 
```
Authorization: Bearer <your-token>
Content-Type: application/json
```

### Sample Responses

**Success Response:**
```json
{
    "success": true,
    "message": "Assignment created successfully",
    "data": {
        "assignment": {
            "id": 1,
            "course_id": 1,
            "title": "Programming Assignment 1",
            "description": "Create a simple web application...",
            "due_date": "2025-12-15T23:59:59.000000Z",
            "max_score": 100,
            "status": "published",
            "is_active": true,
            "created_at": "2025-11-30T20:33:10.000000Z",
            "updated_at": "2025-11-30T20:33:10.000000Z",
            "course": {
                "id": 1,
                "name": "Web Development",
                "code": "CS101"
            }
        }
    }
}
```

**Error Response:**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "course_id": ["The course id field is required."],
        "title": ["The title field is required."],
        "due_date": ["The due date field is required."]
    }
}
```

### Testing Steps in Postman

1. **Login to get token:**
   - POST `/api/auth/login`
   - Body: `{"email": "admin@example.com", "password": "password"}`
   - Copy the `token` from response

2. **Create Assignment:**
   - POST `/api/admin/assignments`
   - Add Authorization header with Bearer token
   - Use the JSON body example above

3. **Test other endpoints:**
   - Use the assignment ID from step 2 in other endpoints
   - Test validation by sending incomplete data
   - Test permissions by using student/teacher tokens

### Filter Options for GET /api/admin/assignments

You can add query parameters to filter assignments:

- `course_id=1` - Filter by course
- `status=published` - Filter by status (draft, published, closed)
- `active=true` - Filter by active status
- `search=programming` - Search in title/description
- `sort_by=due_date` - Sort by field (due_date, created_at)
- `sort_direction=desc` - Sort direction (asc, desc)
- `per_page=10` - Items per page

Example: `/api/admin/assignments?course_id=1&status=published&sort_by=due_date&sort_direction=asc`
