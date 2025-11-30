import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../components/ui/card';
import { Button } from '../components/ui/button';
import { Input } from '../components/ui/input';
import { Label } from '../components/ui/label';
import { Textarea } from '../components/ui/textarea';
import { Badge } from '../components/ui/badge';
import { Separator } from '../components/ui/separator';
import { Avatar, AvatarFallback, AvatarImage } from '../components/ui/avatar';
import { 
  User, 
  Mail, 
  Phone, 
  MapPin, 
  Calendar, 
  BookOpen, 
  GraduationCap,
  Award,
  TrendingUp,
  Edit,
  Save,
  X,
  ExternalLink
} from 'lucide-react';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../components/ui/select';

interface StudentProfile {
  id: number;
  user: {
    id: number;
    name: string;
    email: string;
    phone?: string;
    address?: string;
    date_of_birth?: string;
    gender?: string;
    profile_image?: string;
    program?: string;
    year_level?: string;
    student_status: string;
    enrollment_date?: string;
    current_cgpa?: number;
    bio?: string;
    social_links?: {
      facebook?: string;
      twitter?: string;
      linkedin?: string;
      instagram?: string;
    };
  };
  student_id: string;
  department?: {
    id: number;
    name: string;
    code: string;
  };
  semester?: number;
  section?: string;
  batch?: string;
  current_gpa?: number;
  total_credits?: number;
  statistics?: {
    total_enrollments: number;
    completed_courses: number;
    attendance_percentage: number;
    average_grade: number;
    current_gpa: number;
    total_credits: number;
  };
}

const StudentProfilePage: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const [student, setStudent] = useState<StudentProfile | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [isEditing, setIsEditing] = useState(false);
  const [formData, setFormData] = useState<Partial<StudentProfile['user']>>({});
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    fetchStudentProfile();
  }, [id]);

  const fetchStudentProfile = async () => {
    try {
      setLoading(true);
      const token = localStorage.getItem('token');
      
      // Determine the correct API endpoint
      const endpoint = id 
        ? `/api/students/${id}` 
        : '/api/student/profile';

      const response = await fetch(`http://localhost:8000${endpoint}`, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
        },
      });

      if (!response.ok) {
        throw new Error('Failed to fetch student profile');
      }

      const data = await response.json();
      setStudent(data.data.student || data.data);
      setError(null);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'An error occurred');
    } finally {
      setLoading(false);
    }
  };

  const handleEdit = () => {
    setIsEditing(true);
    setFormData({
      name: student?.user.name || '',
      email: student?.user.email || '',
      phone: student?.user.phone || '',
      address: student?.user.address || '',
      program: student?.user.program || '',
      year_level: student?.user.year_level || '',
      bio: student?.user.bio || '',
      social_links: student?.user.social_links || {},
    });
  };

  const handleSave = async () => {
    try {
      setSaving(true);
      const token = localStorage.getItem('token');
      
      const endpoint = id 
        ? `/api/students/${id}` 
        : '/api/student/profile';

      const response = await fetch(`http://localhost:8000${endpoint}`, {
        method: 'PUT',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData),
      });

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || 'Failed to update profile');
      }

      await fetchStudentProfile();
      setIsEditing(false);
      setError(null);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to update profile');
    } finally {
      setSaving(false);
    }
  };

  const handleCancel = () => {
    setIsEditing(false);
    setFormData({});
  };

  const formatDate = (dateString?: string) => {
    if (!dateString) return 'Not specified';
    return new Date(dateString).toLocaleDateString();
  };

  const getStatusColor = (status: string) => {
    switch (status.toLowerCase()) {
      case 'active': return 'bg-green-100 text-green-800';
      case 'inactive': return 'bg-gray-100 text-gray-800';
      case 'graduated': return 'bg-blue-100 text-blue-800';
      case 'suspended': return 'bg-red-100 text-red-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  if (loading) {
    return (
      <div className="flex justify-center items-center min-h-[400px]">
        <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600"></div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="flex flex-col items-center justify-center min-h-[400px] space-y-4">
        <div className="text-red-600 text-lg">{error}</div>
        <Button onClick={() => navigate(-1)} variant="outline">
          Go Back
        </Button>
      </div>
    );
  }

  if (!student) {
    return (
      <div className="flex flex-col items-center justify-center min-h-[400px] space-y-4">
        <div className="text-gray-600 text-lg">Student profile not found</div>
        <Button onClick={() => navigate(-1)} variant="outline">
          Go Back
        </Button>
      </div>
    );
  }

  return (
    <div className="container mx-auto px-4 py-8 max-w-6xl">
      <div className="flex justify-between items-start mb-8">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">Student Profile</h1>
          <p className="text-gray-600 mt-2">
            {isEditing ? 'Edit student information' : 'View student details and academic information'}
          </p>
        </div>
        <div className="flex space-x-2">
          {!isEditing ? (
            <Button onClick={handleEdit} className="flex items-center space-x-2">
              <Edit className="w-4 h-4" />
              <span>Edit Profile</span>
            </Button>
          ) : (
            <div className="flex space-x-2">
              <Button 
                onClick={handleSave} 
                disabled={saving}
                className="flex items-center space-x-2"
              >
                <Save className="w-4 h-4" />
                <span>{saving ? 'Saving...' : 'Save'}</span>
              </Button>
              <Button 
                onClick={handleCancel} 
                variant="outline"
                className="flex items-center space-x-2"
              >
                <X className="w-4 h-4" />
                <span>Cancel</span>
              </Button>
            </div>
          )}
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {/* Left Column - Profile Info */}
        <div className="lg:col-span-1 space-y-6">
          {/* Profile Card */}
          <Card>
            <CardHeader className="text-center">
              <Avatar className="w-24 h-24 mx-auto mb-4">
                <AvatarImage src={student.user.profile_image} />
                <AvatarFallback className="text-2xl">
                  {student.user.name.split(' ').map(n => n[0]).join('')}
                </AvatarFallback>
              </Avatar>
              {isEditing ? (
                <div className="space-y-4">
                  <div>
                    <Label htmlFor="name">Full Name</Label>
                    <Input
                      id="name"
                      value={formData.name || ''}
                      onChange={(e) => setFormData({...formData, name: e.target.value})}
                      placeholder="Enter full name"
                    />
                  </div>
                  <div>
                    <Label htmlFor="email">Email</Label>
                    <Input
                      id="email"
                      type="email"
                      value={formData.email || ''}
                      onChange={(e) => setFormData({...formData, email: e.target.value})}
                      placeholder="Enter email address"
                    />
                  </div>
                </div>
              ) : (
                <>
                  <CardTitle className="text-xl">{student.user.name}</CardTitle>
                  <CardDescription className="flex items-center justify-center space-x-2">
                    <Mail className="w-4 h-4" />
                    <span>{student.user.email}</span>
                  </CardDescription>
                </>
              )}
            </CardHeader>
            <CardContent>
              <div className="space-y-3">
                <div className="flex justify-between items-center">
                  <span className="text-sm font-medium text-gray-600">Student ID:</span>
                  <Badge variant="outline">{student.student_id}</Badge>
                </div>
                <div className="flex justify-between items-center">
                  <span className="text-sm font-medium text-gray-600">Status:</span>
                  <Badge className={getStatusColor(student.user.student_status)}>
                    {student.user.student_status}
                  </Badge>
                </div>
                {student.department && (
                  <div className="flex justify-between items-center">
                    <span className="text-sm font-medium text-gray-600">Department:</span>
                    <span className="text-sm">{student.department.name}</span>
                  </div>
                )}
              </div>
            </CardContent>
          </Card>

          {/* Contact Information */}
          <Card>
            <CardHeader>
              <CardTitle className="text-lg">Contact Information</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              {isEditing ? (
                <>
                  <div>
                    <Label htmlFor="phone">Phone</Label>
                    <Input
                      id="phone"
                      value={formData.phone || ''}
                      onChange={(e) => setFormData({...formData, phone: e.target.value})}
                      placeholder="Enter phone number"
                    />
                  </div>
                  <div>
                    <Label htmlFor="address">Address</Label>
                    <Textarea
                      id="address"
                      value={formData.address || ''}
                      onChange={(e) => setFormData({...formData, address: e.target.value})}
                      placeholder="Enter address"
                      rows={3}
                    />
                  </div>
                </>
              ) : (
                <>
                  {student.user.phone && (
                    <div className="flex items-center space-x-3">
                      <Phone className="w-4 h-4 text-gray-400" />
                      <span>{student.user.phone}</span>
                    </div>
                  )}
                  {student.user.address && (
                    <div className="flex items-start space-x-3">
                      <MapPin className="w-4 h-4 text-gray-400 mt-0.5" />
                      <span className="text-sm">{student.user.address}</span>
                    </div>
                  )}
                  {student.user.date_of_birth && (
                    <div className="flex items-center space-x-3">
                      <Calendar className="w-4 h-4 text-gray-400" />
                      <span>{formatDate(student.user.date_of_birth)}</span>
                    </div>
                  )}
                </>
              )}
            </CardContent>
          </Card>

          {/* Social Links */}
          <Card>
            <CardHeader>
              <CardTitle className="text-lg">Social Links</CardTitle>
            </CardHeader>
            <CardContent>
              {isEditing ? (
                <div className="space-y-3">
                  {['facebook', 'twitter', 'linkedin', 'instagram'].map((platform) => (
                    <div key={platform}>
                      <Label htmlFor={platform} className="capitalize">{platform}</Label>
                      <Input
                        id={platform}
                        value={formData.social_links?.[platform as keyof typeof formData.social_links] || ''}
                        onChange={(e) => setFormData({
                          ...formData, 
                          social_links: {
                            ...formData.social_links,
                            [platform]: e.target.value
                          }
                        })}
                        placeholder={`${platform} URL`}
                      />
                    </div>
                  ))}
                </div>
              ) : (
                <div className="space-y-2">
                  {student.user.social_links && Object.entries(student.user.social_links).map(([platform, url]) => {
                    if (!url) return null;
                    return (
                      <a
                        key={platform}
                        href={url}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="flex items-center justify-between p-2 rounded-md hover:bg-gray-50 transition-colors"
                      >
                        <span className="capitalize font-medium">{platform}</span>
                        <ExternalLink className="w-4 h-4 text-gray-400" />
                      </a>
                    );
                  })}
                  {!student.user.social_links || Object.keys(student.user.social_links).length === 0 && (
                    <p className="text-sm text-gray-500">No social links added</p>
                  )}
                </div>
              )}
            </CardContent>
          </Card>
        </div>

        {/* Right Column - Academic Info */}
        <div className="lg:col-span-2 space-y-6">
          {/* Academic Information */}
          <Card>
            <CardHeader>
              <CardTitle className="text-xl flex items-center space-x-2">
                <GraduationCap className="w-5 h-5" />
                <span>Academic Information</span>
              </CardTitle>
            </CardHeader>
            <CardContent>
              {isEditing ? (
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <Label htmlFor="program">Program</Label>
                    <Input
                      id="program"
                      value={formData.program || ''}
                      onChange={(e) => setFormData({...formData, program: e.target.value})}
                      placeholder="e.g., Bachelor of Computer Science"
                    />
                  </div>
                  <div>
                    <Label htmlFor="year_level">Year Level</Label>
                    <Select 
                      value={formData.year_level || ''} 
                      onValueChange={(value) => setFormData({...formData, year_level: value})}
                    >
                      <SelectTrigger>
                        <SelectValue placeholder="Select year level" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="1st year">1st Year</SelectItem>
                        <SelectItem value="2nd year">2nd Year</SelectItem>
                        <SelectItem value="3rd year">3rd Year</SelectItem>
                        <SelectItem value="4th year">4th Year</SelectItem>
                        <SelectItem value="Graduate">Graduate</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                  <div className="md:col-span-2">
                    <Label htmlFor="bio">Bio</Label>
                    <Textarea
                      id="bio"
                      value={formData.bio || ''}
                      onChange={(e) => setFormData({...formData, bio: e.target.value})}
                      placeholder="Tell us about yourself..."
                      rows={4}
                    />
                  </div>
                </div>
              ) : (
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div className="space-y-4">
                    {student.user.program && (
                      <div>
                        <h4 className="font-medium text-gray-700">Program</h4>
                        <p className="text-gray-600">{student.user.program}</p>
                      </div>
                    )}
                    {student.user.year_level && (
                      <div>
                        <h4 className="font-medium text-gray-700">Year Level</h4>
                        <p className="text-gray-600">{student.user.year_level}</p>
                      </div>
                    )}
                    {student.user.enrollment_date && (
                      <div>
                        <h4 className="font-medium text-gray-700">Enrollment Date</h4>
                        <p className="text-gray-600">{formatDate(student.user.enrollment_date)}</p>
                      </div>
                    )}
                  </div>
                  <div className="space-y-4">
                    {student.semester && (
                      <div>
                        <h4 className="font-medium text-gray-700">Current Semester</h4>
                        <p className="text-gray-600">{student.semester}</p>
                      </div>
                    )}
                    {student.section && (
                      <div>
                        <h4 className="font-medium text-gray-700">Section</h4>
                        <p className="text-gray-600">{student.section}</p>
                      </div>
                    )}
                    {student.batch && (
                      <div>
                        <h4 className="font-medium text-gray-700">Batch</h4>
                        <p className="text-gray-600">{student.batch}</p>
                      </div>
                    )}
                  </div>
                  {student.user.bio && (
                    <div className="md:col-span-2">
                      <h4 className="font-medium text-gray-700 mb-2">About</h4>
                      <p className="text-gray-600 leading-relaxed">{student.user.bio}</p>
                    </div>
                  )}
                </div>
              )}
            </CardContent>
          </Card>

          {/* Academic Performance */}
          {student.statistics && (
            <Card>
              <CardHeader>
                <CardTitle className="text-xl flex items-center space-x-2">
                  <TrendingUp className="w-5 h-5" />
                  <span>Academic Performance</span>
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div className="grid grid-cols-2 md:grid-cols-3 gap-4">
                  <div className="text-center">
                    <div className="text-2xl font-bold text-blue-600">
                      {student.statistics.current_gpa?.toFixed(2) || '0.00'}
                    </div>
                    <div className="text-sm text-gray-500">Current GPA</div>
                  </div>
                  <div className="text-center">
                    <div className="text-2xl font-bold text-green-600">
                      {student.statistics.total_credits || 0}
                    </div>
                    <div className="text-sm text-gray-500">Total Credits</div>
                  </div>
                  <div className="text-center">
                    <div className="text-2xl font-bold text-purple-600">
                      {student.statistics.total_enrollments || 0}
                    </div>
                    <div className="text-sm text-gray-500">Enrolled Courses</div>
                  </div>
                  <div className="text-center">
                    <div className="text-2xl font-bold text-orange-600">
                      {student.statistics.completed_courses || 0}
                    </div>
                    <div className="text-sm text-gray-500">Completed</div>
                  </div>
                  <div className="text-center">
                    <div className="text-2xl font-bold text-indigo-600">
                      {student.statistics.attendance_percentage?.toFixed(1) || '0.0'}%
                    </div>
                    <div className="text-sm text-gray-500">Attendance</div>
                  </div>
                  <div className="text-center">
                    <div className="text-2xl font-bold text-red-600">
                      {student.statistics.average_grade?.toFixed(2) || '0.00'}
                    </div>
                    <div className="text-sm text-gray-500">Avg Grade</div>
                  </div>
                </div>
              </CardContent>
            </Card>
          )}
        </div>
      </div>
    </div>
  );
};

export default StudentProfilePage;
