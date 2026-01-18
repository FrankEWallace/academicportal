import { useState, useEffect } from 'react';
import { DashboardLayout } from '@/components/DashboardLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { 
  Building2, 
  Users, 
  BookOpen, 
  DollarSign,
  Plus,
  Pencil,
  Trash2,
  UserCog,
  CheckCircle,
  XCircle,
  Search,
  Phone,
  Mail,
  MapPin
} from 'lucide-react';
import { departmentApi, Department, DepartmentStatistics, Teacher } from '@/lib/api/departmentApi';
import { useToast } from '@/hooks/use-toast';

export default function DepartmentManagement() {
  const [departments, setDepartments] = useState<Department[]>([]);
  const [statistics, setStatistics] = useState<DepartmentStatistics | null>(null);
  const [teachers, setTeachers] = useState<Teacher[]>([]);
  const [loading, setLoading] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');
  const [filterStatus, setFilterStatus] = useState<'all' | 'active' | 'inactive'>('all');
  
  // Dialog states
  const [showCreateDialog, setShowCreateDialog] = useState(false);
  const [showEditDialog, setShowEditDialog] = useState(false);
  const [showDeleteDialog, setShowDeleteDialog] = useState(false);
  const [showAssignHeadDialog, setShowAssignHeadDialog] = useState(false);
  const [selectedDepartment, setSelectedDepartment] = useState<Department | null>(null);
  
  // Form state
  const [formData, setFormData] = useState({
    name: '',
    code: '',
    description: '',
    head_teacher_id: '',
    established_year: '',
    budget: '',
    location: '',
    phone: '',
    email: '',
    status: 'active'
  });

  const { toast } = useToast();

  useEffect(() => {
    fetchDepartments();
    fetchStatistics();
    fetchTeachers();
  }, [filterStatus, searchQuery]);

  const fetchDepartments = async () => {
    setLoading(true);
    try {
      const response = await departmentApi.getDepartments(
        filterStatus === 'all' ? undefined : filterStatus,
        searchQuery || undefined
      );
      
      if (response.success) {
        setDepartments(response.data || []);
      }
    } catch (error: any) {
      console.error('Failed to fetch departments:', error);
      toast({
        title: 'Error',
        description: error.message || 'Failed to fetch departments',
        variant: 'destructive',
      });
      setDepartments([]);
    } finally {
      setLoading(false);
    }
  };

  const fetchStatistics = async () => {
    try {
      const response = await departmentApi.getStatistics();
      if (response.success) {
        setStatistics(response.data);
      }
    } catch (error: any) {
      console.error('Failed to fetch statistics:', error);
    }
  };

  const fetchTeachers = async () => {
    try {
      const response = await departmentApi.getAvailableTeachers();
      if (response.success) {
        setTeachers(response.data || []);
      }
    } catch (error: any) {
      console.error('Failed to fetch teachers:', error);
    }
  };

  const handleCreate = async () => {
    try {
      const data: any = {
        name: formData.name,
        code: formData.code,
        description: formData.description || undefined,
        status: formData.status
      };

      if (formData.head_teacher_id) data.head_teacher_id = parseInt(formData.head_teacher_id);
      if (formData.established_year) data.established_year = parseInt(formData.established_year);
      if (formData.budget) data.budget = parseFloat(formData.budget);
      if (formData.location) data.location = formData.location;
      if (formData.phone) data.phone = formData.phone;
      if (formData.email) data.email = formData.email;

      const response = await departmentApi.createDepartment(data);
      
      if (response.success) {
        toast({
          title: 'Success',
          description: 'Department created successfully',
        });
        setShowCreateDialog(false);
        resetForm();
        fetchDepartments();
        fetchStatistics();
      }
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.message || 'Failed to create department',
        variant: 'destructive',
      });
    }
  };

  const handleUpdate = async () => {
    if (!selectedDepartment) return;

    try {
      const data: any = {
        name: formData.name,
        code: formData.code,
        description: formData.description || undefined,
        status: formData.status
      };

      if (formData.head_teacher_id) data.head_teacher_id = parseInt(formData.head_teacher_id);
      if (formData.established_year) data.established_year = parseInt(formData.established_year);
      if (formData.budget) data.budget = parseFloat(formData.budget);
      if (formData.location) data.location = formData.location;
      if (formData.phone) data.phone = formData.phone;
      if (formData.email) data.email = formData.email;

      const response = await departmentApi.updateDepartment(selectedDepartment.id, data);
      
      if (response.success) {
        toast({
          title: 'Success',
          description: 'Department updated successfully',
        });
        setShowEditDialog(false);
        setSelectedDepartment(null);
        resetForm();
        fetchDepartments();
        fetchStatistics();
      }
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.message || 'Failed to update department',
        variant: 'destructive',
      });
    }
  };

  const handleDelete = async () => {
    if (!selectedDepartment) return;

    try {
      const response = await departmentApi.deleteDepartment(selectedDepartment.id);
      
      if (response.success) {
        toast({
          title: 'Success',
          description: 'Department deleted successfully',
        });
        setShowDeleteDialog(false);
        setSelectedDepartment(null);
        fetchDepartments();
        fetchStatistics();
      }
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.message || 'Cannot delete department with associated records',
        variant: 'destructive',
      });
    }
  };

  const handleAssignHead = async (teacherId: number) => {
    if (!selectedDepartment) return;

    try {
      const response = await departmentApi.assignHead(selectedDepartment.id, teacherId);
      
      if (response.success) {
        toast({
          title: 'Success',
          description: 'Department head assigned successfully',
        });
        setShowAssignHeadDialog(false);
        setSelectedDepartment(null);
        fetchDepartments();
      }
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.message || 'Failed to assign department head',
        variant: 'destructive',
      });
    }
  };

  const handleRemoveHead = async (dept: Department) => {
    try {
      const response = await departmentApi.removeHead(dept.id);
      
      if (response.success) {
        toast({
          title: 'Success',
          description: 'Department head removed successfully',
        });
        fetchDepartments();
      }
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.message || 'Failed to remove department head',
        variant: 'destructive',
      });
    }
  };

  const openEditDialog = (dept: Department) => {
    setSelectedDepartment(dept);
    setFormData({
      name: dept.name,
      code: dept.code,
      description: dept.description || '',
      head_teacher_id: dept.head_teacher_id?.toString() || '',
      established_year: dept.established_year?.toString() || '',
      budget: dept.budget?.toString() || '',
      location: dept.location || '',
      phone: dept.phone || '',
      email: dept.email || '',
      status: dept.status
    });
    setShowEditDialog(true);
  };

  const resetForm = () => {
    setFormData({
      name: '',
      code: '',
      description: '',
      head_teacher_id: '',
      established_year: '',
      budget: '',
      location: '',
      phone: '',
      email: '',
      status: 'active'
    });
  };

  const getStatusBadge = (status: string) => {
    return status === 'active' ? (
      <Badge variant="default"><CheckCircle className="mr-1 h-3 w-3" />Active</Badge>
    ) : (
      <Badge variant="secondary"><XCircle className="mr-1 h-3 w-3" />Inactive</Badge>
    );
  };

  return (
    <DashboardLayout title="Department Management">
      <div className="space-y-6">
        {/* Statistics Cards */}
        {statistics && (
          <div className="grid gap-4 md:grid-cols-4">
            <Card>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">Total Departments</CardTitle>
                <Building2 className="h-4 w-4 text-muted-foreground" />
              </CardHeader>
              <CardContent>
                <div className="text-2xl font-bold">{statistics.total_departments}</div>
                <p className="text-xs text-muted-foreground">
                  {statistics.active_departments} active, {statistics.inactive_departments} inactive
                </p>
              </CardContent>
            </Card>
            <Card>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">With Department Head</CardTitle>
                <UserCog className="h-4 w-4 text-muted-foreground" />
              </CardHeader>
              <CardContent>
                <div className="text-2xl font-bold">{statistics.departments_with_head}</div>
                <p className="text-xs text-muted-foreground">
                  {Math.round((statistics.departments_with_head / statistics.total_departments) * 100)}% coverage
                </p>
              </CardContent>
            </Card>
            <Card>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">Total Budget</CardTitle>
                <DollarSign className="h-4 w-4 text-muted-foreground" />
              </CardHeader>
              <CardContent>
                <div className="text-2xl font-bold">${statistics.total_budget?.toLocaleString() || 0}</div>
                <p className="text-xs text-muted-foreground">Allocated funds</p>
              </CardContent>
            </Card>
            <Card>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">Largest Department</CardTitle>
                <Users className="h-4 w-4 text-muted-foreground" />
              </CardHeader>
              <CardContent>
                <div className="text-lg font-bold">{statistics.largest_department?.name || 'N/A'}</div>
                <p className="text-xs text-muted-foreground">
                  {statistics.largest_department?.student_count || 0} students
                </p>
              </CardContent>
            </Card>
          </div>
        )}

        {/* Actions and Filters */}
        <div className="flex justify-between items-center gap-4">
          <div className="flex gap-2 flex-1">
            <div className="relative flex-1 max-w-sm">
              <Search className="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
              <Input
                placeholder="Search departments..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="pl-8"
              />
            </div>
            <Select value={filterStatus} onValueChange={(value: any) => setFilterStatus(value)}>
              <SelectTrigger className="w-[150px]">
                <SelectValue placeholder="Filter by status" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All Status</SelectItem>
                <SelectItem value="active">Active</SelectItem>
                <SelectItem value="inactive">Inactive</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <Dialog open={showCreateDialog} onOpenChange={setShowCreateDialog}>
            <DialogTrigger asChild>
              <Button onClick={() => { resetForm(); setShowCreateDialog(true); }}>
                <Plus className="mr-2 h-4 w-4" />
                Add Department
              </Button>
            </DialogTrigger>
            <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
              <DialogHeader>
                <DialogTitle>Create New Department</DialogTitle>
                <DialogDescription>Add a new department to the system</DialogDescription>
              </DialogHeader>
              <DepartmentForm
                formData={formData}
                setFormData={setFormData}
                teachers={teachers}
                onSubmit={handleCreate}
                onCancel={() => setShowCreateDialog(false)}
              />
            </DialogContent>
          </Dialog>
        </div>

        {/* Departments Table */}
        <Card>
          <CardHeader>
            <CardTitle>Departments</CardTitle>
            <CardDescription>Manage all academic departments</CardDescription>
          </CardHeader>
          <CardContent>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Code</TableHead>
                  <TableHead>Name</TableHead>
                  <TableHead>Head</TableHead>
                  <TableHead>Teachers</TableHead>
                  <TableHead>Students</TableHead>
                  <TableHead>Courses</TableHead>
                  <TableHead>Budget</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead>Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {loading ? (
                  <TableRow>
                    <TableCell colSpan={9} className="text-center py-8">
                      Loading departments...
                    </TableCell>
                  </TableRow>
                ) : departments.length === 0 ? (
                  <TableRow>
                    <TableCell colSpan={9} className="text-center text-muted-foreground">
                      No departments found
                    </TableCell>
                  </TableRow>
                ) : (
                  departments.map((dept) => (
                    <TableRow key={dept.id}>
                      <TableCell className="font-medium">{dept.code}</TableCell>
                      <TableCell>
                        <div>
                          <div className="font-medium">{dept.name}</div>
                          {dept.location && (
                            <div className="text-xs text-muted-foreground flex items-center gap-1 mt-1">
                              <MapPin className="h-3 w-3" />
                              {dept.location}
                            </div>
                          )}
                        </div>
                      </TableCell>
                      <TableCell>
                        {dept.headTeacher ? (
                          <div>
                            <div className="text-sm">{dept.headTeacher.name}</div>
                            <div className="text-xs text-muted-foreground">{dept.headTeacher.email}</div>
                          </div>
                        ) : (
                          <Button
                            size="sm"
                            variant="outline"
                            onClick={() => {
                              setSelectedDepartment(dept);
                              setShowAssignHeadDialog(true);
                            }}
                          >
                            <UserCog className="h-3 w-3 mr-1" />
                            Assign
                          </Button>
                        )}
                      </TableCell>
                      <TableCell>{dept.teachers_count || 0}</TableCell>
                      <TableCell>{dept.students_count || 0}</TableCell>
                      <TableCell>{dept.courses_count || 0}</TableCell>
                      <TableCell>${dept.budget?.toLocaleString() || 0}</TableCell>
                      <TableCell>{getStatusBadge(dept.status)}</TableCell>
                      <TableCell>
                        <div className="flex gap-2">
                          <Button
                            size="sm"
                            variant="outline"
                            onClick={() => openEditDialog(dept)}
                          >
                            <Pencil className="h-3 w-3" />
                          </Button>
                          <Button
                            size="sm"
                            variant="destructive"
                            onClick={() => {
                              setSelectedDepartment(dept);
                              setShowDeleteDialog(true);
                            }}
                          >
                            <Trash2 className="h-3 w-3" />
                          </Button>
                        </div>
                      </TableCell>
                    </TableRow>
                  ))
                )}
              </TableBody>
            </Table>
          </CardContent>
        </Card>

        {/* Edit Dialog */}
        <Dialog open={showEditDialog} onOpenChange={setShowEditDialog}>
          <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
            <DialogHeader>
              <DialogTitle>Edit Department</DialogTitle>
              <DialogDescription>Update department information</DialogDescription>
            </DialogHeader>
            <DepartmentForm
              formData={formData}
              setFormData={setFormData}
              teachers={teachers}
              onSubmit={handleUpdate}
              onCancel={() => setShowEditDialog(false)}
            />
          </DialogContent>
        </Dialog>

        {/* Delete Confirmation Dialog */}
        <Dialog open={showDeleteDialog} onOpenChange={setShowDeleteDialog}>
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Delete Department</DialogTitle>
              <DialogDescription>
                Are you sure you want to delete "{selectedDepartment?.name}"? This action cannot be undone.
              </DialogDescription>
            </DialogHeader>
            <div className="flex justify-end gap-2 mt-4">
              <Button variant="outline" onClick={() => setShowDeleteDialog(false)}>
                Cancel
              </Button>
              <Button variant="destructive" onClick={handleDelete}>
                Delete
              </Button>
            </div>
          </DialogContent>
        </Dialog>

        {/* Assign Head Dialog */}
        <Dialog open={showAssignHeadDialog} onOpenChange={setShowAssignHeadDialog}>
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Assign Department Head</DialogTitle>
              <DialogDescription>
                Select a teacher to be the head of {selectedDepartment?.name}
              </DialogDescription>
            </DialogHeader>
            <div className="space-y-4">
              <Select onValueChange={(value) => handleAssignHead(parseInt(value))}>
                <SelectTrigger>
                  <SelectValue placeholder="Select teacher" />
                </SelectTrigger>
                <SelectContent>
                  {teachers
                    .filter(t => t.department_id === selectedDepartment?.id)
                    .map(teacher => (
                      <SelectItem key={teacher.id} value={teacher.id.toString()}>
                        {teacher.name} ({teacher.email})
                      </SelectItem>
                    ))}
                </SelectContent>
              </Select>
              <div className="flex justify-end gap-2">
                <Button variant="outline" onClick={() => setShowAssignHeadDialog(false)}>
                  Cancel
                </Button>
              </div>
            </div>
          </DialogContent>
        </Dialog>
      </div>
    </DashboardLayout>
  );
}

// Department Form Component
function DepartmentForm({
  formData,
  setFormData,
  teachers,
  onSubmit,
  onCancel
}: {
  formData: any;
  setFormData: (data: any) => void;
  teachers: Teacher[];
  onSubmit: () => void;
  onCancel: () => void;
}) {
  return (
    <div className="space-y-4">
      <div className="grid grid-cols-2 gap-4">
        <div className="space-y-2">
          <Label>Department Name *</Label>
          <Input
            value={formData.name}
            onChange={(e) => setFormData({ ...formData, name: e.target.value })}
            placeholder="Computer Science"
          />
        </div>
        <div className="space-y-2">
          <Label>Department Code *</Label>
          <Input
            value={formData.code}
            onChange={(e) => setFormData({ ...formData, code: e.target.value })}
            placeholder="CSC"
          />
        </div>
      </div>

      <div className="space-y-2">
        <Label>Description</Label>
        <Textarea
          value={formData.description}
          onChange={(e) => setFormData({ ...formData, description: e.target.value })}
          placeholder="Department description..."
          rows={3}
        />
      </div>

      <div className="grid grid-cols-2 gap-4">
        <div className="space-y-2">
          <Label>Department Head</Label>
          <Select
            value={formData.head_teacher_id}
            onValueChange={(value) => setFormData({ ...formData, head_teacher_id: value })}
          >
            <SelectTrigger>
              <SelectValue placeholder="Select head" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="">None</SelectItem>
              {teachers.map(teacher => (
                <SelectItem key={teacher.id} value={teacher.id.toString()}>
                  {teacher.name}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        </div>
        <div className="space-y-2">
          <Label>Established Year</Label>
          <Input
            type="number"
            value={formData.established_year}
            onChange={(e) => setFormData({ ...formData, established_year: e.target.value })}
            placeholder="2020"
          />
        </div>
      </div>

      <div className="grid grid-cols-2 gap-4">
        <div className="space-y-2">
          <Label>Budget ($)</Label>
          <Input
            type="number"
            value={formData.budget}
            onChange={(e) => setFormData({ ...formData, budget: e.target.value })}
            placeholder="100000"
          />
        </div>
        <div className="space-y-2">
          <Label>Location</Label>
          <Input
            value={formData.location}
            onChange={(e) => setFormData({ ...formData, location: e.target.value })}
            placeholder="Building A, Floor 3"
          />
        </div>
      </div>

      <div className="grid grid-cols-2 gap-4">
        <div className="space-y-2">
          <Label>Phone</Label>
          <Input
            value={formData.phone}
            onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
            placeholder="+1234567890"
          />
        </div>
        <div className="space-y-2">
          <Label>Email</Label>
          <Input
            type="email"
            value={formData.email}
            onChange={(e) => setFormData({ ...formData, email: e.target.value })}
            placeholder="dept@university.edu"
          />
        </div>
      </div>

      <div className="space-y-2">
        <Label>Status</Label>
        <Select
          value={formData.status}
          onValueChange={(value) => setFormData({ ...formData, status: value })}
        >
          <SelectTrigger>
            <SelectValue />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="active">Active</SelectItem>
            <SelectItem value="inactive">Inactive</SelectItem>
          </SelectContent>
        </Select>
      </div>

      <div className="flex justify-end gap-2 pt-4">
        <Button variant="outline" onClick={onCancel}>
          Cancel
        </Button>
        <Button onClick={onSubmit}>
          Save Department
        </Button>
      </div>
    </div>
  );
}
