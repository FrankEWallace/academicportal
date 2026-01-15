import { useState, useEffect } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Input } from '@/components/ui/input';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { CheckCircle, XCircle, Ban, AlertTriangle, FileText, DollarSign } from 'lucide-react';

interface Registration {
  id: number;
  student_id: number;
  student_name: string;
  matric_number: string;
  semester: number;
  academic_year: string;
  courses_count: number;
  total_units: number;
  status: 'pending' | 'verified' | 'blocked';
  fee_status: 'paid' | 'partial' | 'unpaid';
  submitted_at: string;
}

interface RegistrationStatistics {
  total_registrations: number;
  pending_verification: number;
  verified: number;
  blocked: number;
}

export default function AdminRegistrationControl() {
  const [registrations, setRegistrations] = useState<Registration[]>([]);
  const [statistics, setStatistics] = useState<RegistrationStatistics | null>(null);
  const [loading, setLoading] = useState(false);
  const [message, setMessage] = useState('');
  const [filter, setFilter] = useState<'all' | 'pending' | 'verified' | 'blocked'>('all');
  const [selectedRegistration, setSelectedRegistration] = useState<Registration | null>(null);

  useEffect(() => {
    fetchRegistrations();
    fetchStatistics();
  }, [filter]);

  const fetchRegistrations = async () => {
    setLoading(true);
    try {
      // TODO: Replace with actual API call
      const mockData: Registration[] = [
        {
          id: 1,
          student_id: 101,
          student_name: 'John Doe',
          matric_number: 'CS/2023/001',
          semester: 1,
          academic_year: '2025/2026',
          courses_count: 8,
          total_units: 24,
          status: 'pending',
          fee_status: 'paid',
          submitted_at: '2026-01-10 14:30:00',
        },
        {
          id: 2,
          student_id: 102,
          student_name: 'Jane Smith',
          matric_number: 'CS/2023/002',
          semester: 1,
          academic_year: '2025/2026',
          courses_count: 7,
          total_units: 21,
          status: 'verified',
          fee_status: 'paid',
          submitted_at: '2026-01-09 10:15:00',
        },
        {
          id: 3,
          student_id: 103,
          student_name: 'Mike Johnson',
          matric_number: 'CS/2023/003',
          semester: 1,
          academic_year: '2025/2026',
          courses_count: 9,
          total_units: 27,
          status: 'pending',
          fee_status: 'partial',
          submitted_at: '2026-01-11 09:00:00',
        },
      ];

      if (filter === 'all') {
        setRegistrations(mockData);
      } else {
        setRegistrations(mockData.filter((r) => r.status === filter));
      }
    } catch (error) {
      console.error('Failed to fetch registrations:', error);
    } finally {
      setLoading(false);
    }
  };

  const fetchStatistics = async () => {
    try {
      setStatistics({
        total_registrations: 156,
        pending_verification: 45,
        verified: 108,
        blocked: 3,
      });
    } catch (error) {
      console.error('Failed to fetch statistics:', error);
    }
  };

  const handleVerifyFees = async (registrationId: number) => {
    try {
      // TODO: Replace with actual API call
      setMessage('Fee verification successful');
      fetchRegistrations();
      setTimeout(() => setMessage(''), 3000);
    } catch (error) {
      console.error('Failed to verify fees:', error);
    }
  };

  const handleBlockRegistration = async (registrationId: number, reason: string) => {
    try {
      // TODO: Replace with actual API call
      setMessage('Registration blocked successfully');
      fetchRegistrations();
      fetchStatistics();
    } catch (error) {
      console.error('Failed to block registration:', error);
    }
  };

  const handleUnblockRegistration = async (registrationId: number) => {
    try {
      // TODO: Replace with actual API call
      setMessage('Registration unblocked successfully');
      fetchRegistrations();
      fetchStatistics();
    } catch (error) {
      console.error('Failed to unblock registration:', error);
    }
  };

  const getStatusBadge = (status: string) => {
    switch (status) {
      case 'verified':
        return <Badge variant="default"><CheckCircle className="mr-1 h-3 w-3" />Verified</Badge>;
      case 'blocked':
        return <Badge variant="destructive"><Ban className="mr-1 h-3 w-3" />Blocked</Badge>;
      default:
        return <Badge variant="secondary"><AlertTriangle className="mr-1 h-3 w-3" />Pending</Badge>;
    }
  };

  const getFeeStatusBadge = (status: string) => {
    switch (status) {
      case 'paid':
        return <Badge variant="default">Paid</Badge>;
      case 'partial':
        return <Badge variant="secondary">Partial</Badge>;
      default:
        return <Badge variant="destructive">Unpaid</Badge>;
    }
  };

  return (
    <div className="space-y-6 p-6">
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-3xl font-bold">Registration Control</h1>
          <p className="text-muted-foreground">Verify and manage student course registrations</p>
        </div>
      </div>

      {message && (
        <Alert>
          <CheckCircle className="h-4 w-4" />
          <AlertDescription>{message}</AlertDescription>
        </Alert>
      )}

      {/* Statistics Cards */}
      {statistics && (
        <div className="grid gap-4 md:grid-cols-4">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Registrations</CardTitle>
              <FileText className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{statistics.total_registrations}</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Pending Verification</CardTitle>
              <AlertTriangle className="h-4 w-4 text-yellow-600" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{statistics.pending_verification}</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Verified</CardTitle>
              <CheckCircle className="h-4 w-4 text-green-600" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{statistics.verified}</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Blocked</CardTitle>
              <Ban className="h-4 w-4 text-red-600" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{statistics.blocked}</div>
            </CardContent>
          </Card>
        </div>
      )}

      <Card>
        <CardHeader>
          <div className="flex justify-between items-center">
            <div>
              <CardTitle>Student Registrations</CardTitle>
              <CardDescription>Review and verify student course registrations</CardDescription>
            </div>
            <div className="flex gap-2">
              <Button
                variant={filter === 'all' ? 'default' : 'outline'}
                size="sm"
                onClick={() => setFilter('all')}
              >
                All
              </Button>
              <Button
                variant={filter === 'pending' ? 'default' : 'outline'}
                size="sm"
                onClick={() => setFilter('pending')}
              >
                Pending
              </Button>
              <Button
                variant={filter === 'verified' ? 'default' : 'outline'}
                size="sm"
                onClick={() => setFilter('verified')}
              >
                Verified
              </Button>
              <Button
                variant={filter === 'blocked' ? 'default' : 'outline'}
                size="sm"
                onClick={() => setFilter('blocked')}
              >
                Blocked
              </Button>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Matric Number</TableHead>
                <TableHead>Student Name</TableHead>
                <TableHead>Semester</TableHead>
                <TableHead>Courses</TableHead>
                <TableHead>Units</TableHead>
                <TableHead>Fee Status</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Submitted</TableHead>
                <TableHead>Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {registrations.map((registration) => (
                <TableRow key={registration.id}>
                  <TableCell className="font-medium">{registration.matric_number}</TableCell>
                  <TableCell>{registration.student_name}</TableCell>
                  <TableCell>
                    {registration.semester} ({registration.academic_year})
                  </TableCell>
                  <TableCell>{registration.courses_count}</TableCell>
                  <TableCell>{registration.total_units}</TableCell>
                  <TableCell>{getFeeStatusBadge(registration.fee_status)}</TableCell>
                  <TableCell>{getStatusBadge(registration.status)}</TableCell>
                  <TableCell className="text-sm text-muted-foreground">
                    {new Date(registration.submitted_at).toLocaleDateString()}
                  </TableCell>
                  <TableCell>
                    <div className="flex gap-2">
                      <Dialog>
                        <DialogTrigger asChild>
                          <Button size="sm" variant="outline">View Details</Button>
                        </DialogTrigger>
                        <DialogContent className="max-w-2xl">
                          <DialogHeader>
                            <DialogTitle>Registration Details</DialogTitle>
                            <DialogDescription>
                              {registration.matric_number} - {registration.student_name}
                            </DialogDescription>
                          </DialogHeader>
                          <div className="space-y-4">
                            <div className="grid grid-cols-2 gap-4">
                              <div>
                                <p className="text-sm font-medium">Academic Year</p>
                                <p className="text-sm text-muted-foreground">{registration.academic_year}</p>
                              </div>
                              <div>
                                <p className="text-sm font-medium">Semester</p>
                                <p className="text-sm text-muted-foreground">{registration.semester}</p>
                              </div>
                              <div>
                                <p className="text-sm font-medium">Total Courses</p>
                                <p className="text-sm text-muted-foreground">{registration.courses_count}</p>
                              </div>
                              <div>
                                <p className="text-sm font-medium">Total Units</p>
                                <p className="text-sm text-muted-foreground">{registration.total_units}</p>
                              </div>
                            </div>
                            <div className="flex gap-2 justify-end">
                              {registration.status === 'pending' && (
                                <>
                                  <Button onClick={() => handleVerifyFees(registration.id)}>
                                    <CheckCircle className="mr-2 h-4 w-4" />
                                    Verify
                                  </Button>
                                  <Button
                                    variant="destructive"
                                    onClick={() => handleBlockRegistration(registration.id, 'Incomplete fees')}
                                  >
                                    <Ban className="mr-2 h-4 w-4" />
                                    Block
                                  </Button>
                                </>
                              )}
                              {registration.status === 'blocked' && (
                                <Button onClick={() => handleUnblockRegistration(registration.id)}>
                                  <CheckCircle className="mr-2 h-4 w-4" />
                                  Unblock
                                </Button>
                              )}
                            </div>
                          </div>
                        </DialogContent>
                      </Dialog>
                    </div>
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>
  );
}
