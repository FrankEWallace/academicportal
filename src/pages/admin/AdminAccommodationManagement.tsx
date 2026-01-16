import { useState, useEffect } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { CheckCircle, XCircle, Building2, Users, Bed, AlertTriangle } from 'lucide-react';
import { adminAccommodationApi } from '@/lib/api/adminApi';
import { useToast } from '@/hooks/use-toast';

interface Hostel {
  id: number;
  name: string;
  code: string;
  gender: 'male' | 'female';
  total_rooms: number;
  capacity: number;
  current_occupancy: number;
  available_spaces: number;
}

interface Room {
  id: number;
  hostel_id: number;
  room_number: string;
  floor: number;
  capacity: number;
  current_occupancy: number;
  status: 'available' | 'occupied' | 'full' | 'maintenance';
}

interface AccommodationRequest {
  id: number;
  student_id: number;
  student_name: string;
  matric_number: string;
  gender: 'male' | 'female';
  level: number;
  status: 'pending' | 'allocated' | 'rejected';
  requested_at: string;
  allocated_hostel?: string;
  allocated_room?: string;
}

interface AccommodationStatistics {
  total_requests: number;
  pending_allocation: number;
  allocated: number;
  total_capacity: number;
  current_occupancy: number;
}

export default function AdminAccommodationManagement() {
  const [hostels, setHostels] = useState<Hostel[]>([]);
  const [requests, setRequests] = useState<AccommodationRequest[]>([]);
  const [statistics, setStatistics] = useState<AccommodationStatistics | null>(null);
  const [selectedHostel, setSelectedHostel] = useState<Hostel | null>(null);
  const [availableRooms, setAvailableRooms] = useState<Room[]>([]);
  const [loading, setLoading] = useState(false);
  const [message, setMessage] = useState('');
  const { toast } = useToast();

  useEffect(() => {
    fetchHostels();
    fetchRequests();
    fetchStatistics();
  }, []);

  const fetchHostels = async () => {
    setLoading(true);
    try {
      const response = await adminAccommodationApi.getHostels();
      
      if (response.success) {
        setHostels(response.data);
      }
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.message || 'Failed to fetch hostels',
        variant: 'destructive',
      });
    } finally {
      setLoading(false);
    }
  };

  const fetchRequests = async () => {
    try {
      const response = await adminAccommodationApi.getPendingRequests();
      if (response.success) {
        setRequests(response.data);
      }
    } catch (error: any) {
      console.error('Failed to fetch requests:', error);
    }
  };

  const fetchStatistics = async () => {
    try {
      const response = await adminAccommodationApi.getStatistics();
      if (response.success) {
        setStatistics(response.data);
      }
    } catch (error: any) {
      console.error('Failed to fetch statistics:', error);
    }
  };

  const fetchAvailableRooms = async (hostelId: number) => {
    try {
      const response = await adminAccommodationApi.getAvailableRooms(hostelId);
      if (response.success) {
        setAvailableRooms(response.data);
      }
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.message || 'Failed to fetch available rooms',
        variant: 'destructive',
      });
    }
  };

  const handleAllocate = async (requestId: number, hostelId: number, roomId: number) => {
    try {
      const response = await adminAccommodationApi.allocateRoom(requestId, hostelId, roomId);
      if (response.success) {
        toast({
          title: 'Success',
          description: 'Room allocated successfully',
        });
        fetchRequests();
        fetchHostels();
        fetchStatistics();
      }
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.message || 'Failed to allocate room',
        variant: 'destructive',
      });
    }
  };

  const handleVacate = async (requestId: number) => {
    try {
      const response = await adminAccommodationApi.vacateRoom(requestId);
      if (response.success) {
        toast({
          title: 'Success',
          description: 'Room vacated successfully',
        });
        fetchRequests();
        fetchHostels();
        fetchStatistics();
      }
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.message || 'Failed to vacate room',
        variant: 'destructive',
      });
    }
  };

  const getStatusBadge = (status: string) => {
    switch (status) {
      case 'allocated':
        return <Badge variant="default"><CheckCircle className="mr-1 h-3 w-3" />Allocated</Badge>;
      case 'rejected':
        return <Badge variant="destructive"><XCircle className="mr-1 h-3 w-3" />Rejected</Badge>;
      default:
        return <Badge variant="secondary"><AlertTriangle className="mr-1 h-3 w-3" />Pending</Badge>;
    }
  };

  const getOccupancyColor = (occupancy: number, capacity: number) => {
    const percentage = (occupancy / capacity) * 100;
    if (percentage >= 90) return 'text-red-600';
    if (percentage >= 70) return 'text-yellow-600';
    return 'text-green-600';
  };

  return (
    <div className="space-y-6 p-6">
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-3xl font-bold">Accommodation Management</h1>
          <p className="text-muted-foreground">Manage hostel allocations and room assignments</p>
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
        <div className="grid gap-4 md:grid-cols-5">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Requests</CardTitle>
              <Users className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{statistics.total_requests}</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Pending</CardTitle>
              <AlertTriangle className="h-4 w-4 text-yellow-600" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{statistics.pending_allocation}</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Allocated</CardTitle>
              <CheckCircle className="h-4 w-4 text-green-600" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{statistics.allocated}</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Capacity</CardTitle>
              <Building2 className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{statistics.total_capacity}</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Occupancy</CardTitle>
              <Bed className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{statistics.current_occupancy}</div>
              <p className="text-xs text-muted-foreground">
                {((statistics.current_occupancy / statistics.total_capacity) * 100).toFixed(1)}% occupied
              </p>
            </CardContent>
          </Card>
        </div>
      )}

      {/* Hostels Overview */}
      <Card>
        <CardHeader>
          <CardTitle>Hostel Overview</CardTitle>
          <CardDescription>Current capacity and availability across all hostels</CardDescription>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Hostel</TableHead>
                <TableHead>Code</TableHead>
                <TableHead>Gender</TableHead>
                <TableHead>Total Rooms</TableHead>
                <TableHead>Capacity</TableHead>
                <TableHead>Occupancy</TableHead>
                <TableHead>Available</TableHead>
                <TableHead>Status</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {hostels.map((hostel) => (
                <TableRow key={hostel.id}>
                  <TableCell className="font-medium">{hostel.name}</TableCell>
                  <TableCell>{hostel.code}</TableCell>
                  <TableCell>
                    <Badge variant="outline">{hostel.gender}</Badge>
                  </TableCell>
                  <TableCell>{hostel.total_rooms}</TableCell>
                  <TableCell>{hostel.capacity}</TableCell>
                  <TableCell>
                    <span className={getOccupancyColor(hostel.current_occupancy, hostel.capacity)}>
                      {hostel.current_occupancy}
                    </span>
                  </TableCell>
                  <TableCell>{hostel.available_spaces}</TableCell>
                  <TableCell>
                    {hostel.available_spaces > 0 ? (
                      <Badge variant="default">Available</Badge>
                    ) : (
                      <Badge variant="destructive">Full</Badge>
                    )}
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </CardContent>
      </Card>

      {/* Allocation Requests */}
      <Card>
        <CardHeader>
          <CardTitle>Allocation Requests</CardTitle>
          <CardDescription>Process student accommodation requests</CardDescription>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Matric Number</TableHead>
                <TableHead>Student Name</TableHead>
                <TableHead>Gender</TableHead>
                <TableHead>Level</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Allocated To</TableHead>
                <TableHead>Requested</TableHead>
                <TableHead>Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {requests.map((request) => (
                <TableRow key={request.id}>
                  <TableCell className="font-medium">{request.matric_number}</TableCell>
                  <TableCell>{request.student_name}</TableCell>
                  <TableCell>
                    <Badge variant="outline">{request.gender}</Badge>
                  </TableCell>
                  <TableCell>{request.level}</TableCell>
                  <TableCell>{getStatusBadge(request.status)}</TableCell>
                  <TableCell>
                    {request.allocated_hostel ? (
                      <span className="text-sm">
                        {request.allocated_hostel} - {request.allocated_room}
                      </span>
                    ) : (
                      '-'
                    )}
                  </TableCell>
                  <TableCell className="text-sm text-muted-foreground">
                    {new Date(request.requested_at).toLocaleDateString()}
                  </TableCell>
                  <TableCell>
                    {request.status === 'pending' ? (
                      <Dialog>
                        <DialogTrigger asChild>
                          <Button
                            size="sm"
                            onClick={() => {
                              const matchingHostel = hostels.find(h => h.gender === request.gender);
                              if (matchingHostel) {
                                setSelectedHostel(matchingHostel);
                                fetchAvailableRooms(matchingHostel.id);
                              }
                            }}
                          >
                            Allocate Room
                          </Button>
                        </DialogTrigger>
                        <DialogContent>
                          <DialogHeader>
                            <DialogTitle>Allocate Room</DialogTitle>
                            <DialogDescription>
                              Assign a room to {request.student_name}
                            </DialogDescription>
                          </DialogHeader>
                          <div className="space-y-4">
                            <div>
                              <label className="text-sm font-medium">Select Hostel</label>
                              <Select
                                value={selectedHostel?.id.toString()}
                                onValueChange={(value) => {
                                  const hostel = hostels.find(h => h.id === parseInt(value));
                                  if (hostel) {
                                    setSelectedHostel(hostel);
                                    fetchAvailableRooms(hostel.id);
                                  }
                                }}
                              >
                                <SelectTrigger>
                                  <SelectValue placeholder="Select hostel" />
                                </SelectTrigger>
                                <SelectContent>
                                  {hostels
                                    .filter(h => h.gender === request.gender && h.available_spaces > 0)
                                    .map(hostel => (
                                      <SelectItem key={hostel.id} value={hostel.id.toString()}>
                                        {hostel.name} ({hostel.available_spaces} spaces)
                                      </SelectItem>
                                    ))}
                                </SelectContent>
                              </Select>
                            </div>
                            <div>
                              <label className="text-sm font-medium">Available Rooms</label>
                              <div className="grid grid-cols-2 gap-2 mt-2">
                                {availableRooms.map(room => (
                                  <Button
                                    key={room.id}
                                    variant="outline"
                                    onClick={() => {
                                      if (selectedHostel) {
                                        handleAllocate(request.id, selectedHostel.id, room.id);
                                      }
                                    }}
                                  >
                                    {room.room_number}
                                    <span className="ml-2 text-xs text-muted-foreground">
                                      ({room.current_occupancy}/{room.capacity})
                                    </span>
                                  </Button>
                                ))}
                              </div>
                            </div>
                          </div>
                        </DialogContent>
                      </Dialog>
                    ) : request.status === 'allocated' ? (
                      <Button
                        size="sm"
                        variant="destructive"
                        onClick={() => handleVacate(request.id)}
                      >
                        Vacate
                      </Button>
                    ) : null}
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
