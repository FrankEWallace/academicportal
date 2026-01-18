import { useState, useEffect } from 'react';
import { DashboardLayout } from '@/components/DashboardLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { 
  MessageSquare, 
  AlertCircle, 
  Clock, 
  CheckCircle2, 
  XCircle, 
  User,
  TrendingUp,
  Paperclip
} from 'lucide-react';
import { useToast } from '@/hooks/use-toast';

interface FeedbackTicket {
  id: number;
  ticket_number: string;
  student_id: number;
  student_name: string;
  matric_number: string;
  category: string;
  priority: 'low' | 'medium' | 'high' | 'urgent';
  subject: string;
  status: 'open' | 'in_progress' | 'resolved' | 'closed';
  assigned_to: string | null;
  assigned_to_id: number | null;
  submitted_at: string;
  updated_at: string;
  attachments_count: number;
}

interface FeedbackStatistics {
  total_tickets: number;
  open_tickets: number;
  in_progress: number;
  resolved: number;
  unassigned: number;
  by_category: Record<string, number>;
  by_priority: Record<string, number>;
}

export default function AdminFeedbackManagement() {
  const [tickets, setTickets] = useState<FeedbackTicket[]>([]);
  const [statistics, setStatistics] = useState<FeedbackStatistics | null>(null);
  const [loading, setLoading] = useState(true);
  const [selectedTicket, setSelectedTicket] = useState<FeedbackTicket | null>(null);
  const [showDetailDialog, setShowDetailDialog] = useState(false);
  const [showAssignDialog, setShowAssignDialog] = useState(false);
  const [showPriorityDialog, setShowPriorityDialog] = useState(false);
  const [response, setResponse] = useState('');
  const [assignTo, setAssignTo] = useState('');
  const [newPriority, setNewPriority] = useState<'low' | 'medium' | 'high' | 'urgent'>('medium');
  const { toast } = useToast();

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    setLoading(true);
    try {
      // Simulated data - replace with actual API calls
      const mockTickets: FeedbackTicket[] = [
        {
          id: 1,
          ticket_number: 'TKT-2026-001',
          student_id: 1,
          student_name: 'John Doe',
          matric_number: 'STU/2024/001',
          category: 'academic',
          priority: 'high',
          subject: 'Course Registration Issue',
          status: 'open',
          assigned_to: null,
          assigned_to_id: null,
          submitted_at: '2026-01-15T10:00:00Z',
          updated_at: '2026-01-15T10:00:00Z',
          attachments_count: 2,
        },
        {
          id: 2,
          ticket_number: 'TKT-2026-002',
          student_id: 2,
          student_name: 'Jane Smith',
          matric_number: 'STU/2024/002',
          category: 'accommodation',
          priority: 'medium',
          subject: 'Hostel Room Assignment',
          status: 'in_progress',
          assigned_to: 'Admin User',
          assigned_to_id: 1,
          submitted_at: '2026-01-14T14:30:00Z',
          updated_at: '2026-01-15T09:00:00Z',
          attachments_count: 0,
        },
      ];

      const mockStats: FeedbackStatistics = {
        total_tickets: 15,
        open_tickets: 5,
        in_progress: 3,
        resolved: 6,
        unassigned: 4,
        by_category: {
          academic: 5,
          accommodation: 3,
          fees: 2,
          portal: 3,
          general: 2,
        },
        by_priority: {
          low: 3,
          medium: 7,
          high: 4,
          urgent: 1,
        },
      };

      setTickets(mockTickets);
      setStatistics(mockStats);
    } catch (error: any) {
      toast({
        title: 'Error',
        description: 'Failed to load feedback data',
        variant: 'destructive',
      });
    } finally {
      setLoading(false);
    }
  };

  const handleAssign = async () => {
    if (!selectedTicket || !assignTo.trim()) {
      toast({
        title: 'Error',
        description: 'Please enter an admin name to assign',
        variant: 'destructive',
      });
      return;
    }

    // Simulated API call
    toast({
      title: 'Success',
      description: `Ticket assigned to ${assignTo}`,
    });
    setShowAssignDialog(false);
    setAssignTo('');
    fetchData();
  };

  const handleChangePriority = async () => {
    if (!selectedTicket) return;

    // Simulated API call
    toast({
      title: 'Success',
      description: `Priority changed to ${newPriority}`,
    });
    setShowPriorityDialog(false);
    fetchData();
  };

  const handleUpdateStatus = async (ticketId: number, status: string) => {
    // Simulated API call
    toast({
      title: 'Success',
      description: `Status updated to ${status}`,
    });
    fetchData();
  };

  const getPriorityBadge = (priority: string) => {
    const colors = {
      low: 'bg-gray-100 text-gray-800',
      medium: 'bg-blue-100 text-blue-800',
      high: 'bg-orange-100 text-orange-800',
      urgent: 'bg-red-100 text-red-800',
    };
    return (
      <Badge className={colors[priority as keyof typeof colors] || colors.medium}>
        {priority.toUpperCase()}
      </Badge>
    );
  };

  const getStatusBadge = (status: string) => {
    switch (status) {
      case 'resolved':
        return <Badge variant="default"><CheckCircle2 className="mr-1 h-3 w-3" />Resolved</Badge>;
      case 'closed':
        return <Badge variant="secondary"><XCircle className="mr-1 h-3 w-3" />Closed</Badge>;
      case 'in_progress':
        return <Badge variant="default" className="bg-blue-500"><Clock className="mr-1 h-3 w-3" />In Progress</Badge>;
      default:
        return <Badge variant="secondary"><AlertCircle className="mr-1 h-3 w-3" />Open</Badge>;
    }
  };

  if (loading) {
    return (
      <DashboardLayout title="Feedback Management">
        <div className="flex items-center justify-center p-8">
          <div className="text-center">
            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto"></div>
            <p className="mt-4 text-muted-foreground">Loading feedback tickets...</p>
          </div>
        </div>
      </DashboardLayout>
    );
  }

  return (
    <DashboardLayout title="Feedback Management">
      <div className="space-y-6">
        {/* Statistics Cards */}
      {statistics && (
        <div className="grid gap-4 md:grid-cols-5">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Tickets</CardTitle>
              <MessageSquare className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{statistics.total_tickets}</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Open</CardTitle>
              <AlertCircle className="h-4 w-4 text-yellow-500" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{statistics.open_tickets}</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">In Progress</CardTitle>
              <Clock className="h-4 w-4 text-blue-500" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{statistics.in_progress}</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Resolved</CardTitle>
              <CheckCircle2 className="h-4 w-4 text-green-500" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{statistics.resolved}</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Unassigned</CardTitle>
              <User className="h-4 w-4 text-orange-500" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{statistics.unassigned}</div>
            </CardContent>
          </Card>
        </div>
      )}

      {/* Tickets Table */}
      <Tabs defaultValue="all">
        <TabsList>
          <TabsTrigger value="all">All Tickets</TabsTrigger>
          <TabsTrigger value="open">Open</TabsTrigger>
          <TabsTrigger value="in_progress">In Progress</TabsTrigger>
          <TabsTrigger value="resolved">Resolved</TabsTrigger>
          <TabsTrigger value="unassigned">Unassigned</TabsTrigger>
        </TabsList>

        <TabsContent value="all" className="space-y-4">
          <Card>
            <CardHeader>
              <CardTitle>All Feedback Tickets</CardTitle>
              <CardDescription>View and manage all student feedback submissions</CardDescription>
            </CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Ticket #</TableHead>
                    <TableHead>Student</TableHead>
                    <TableHead>Category</TableHead>
                    <TableHead>Subject</TableHead>
                    <TableHead>Priority</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Assigned To</TableHead>
                    <TableHead>Submitted</TableHead>
                    <TableHead>Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {tickets.length === 0 ? (
                    <TableRow>
                      <TableCell colSpan={9} className="text-center text-muted-foreground">
                        No tickets found
                      </TableCell>
                    </TableRow>
                  ) : (
                    tickets.map((ticket) => (
                      <TableRow key={ticket.id}>
                        <TableCell className="font-medium">{ticket.ticket_number}</TableCell>
                        <TableCell>
                          <div>
                            <div className="font-medium">{ticket.student_name}</div>
                            <div className="text-xs text-muted-foreground">{ticket.matric_number}</div>
                          </div>
                        </TableCell>
                        <TableCell>
                          <Badge variant="outline" className="capitalize">
                            {ticket.category}
                          </Badge>
                        </TableCell>
                        <TableCell>
                          <div className="max-w-xs truncate">{ticket.subject}</div>
                          {ticket.attachments_count > 0 && (
                            <div className="text-xs text-muted-foreground flex items-center gap-1 mt-1">
                              <Paperclip className="h-3 w-3" />
                              {ticket.attachments_count} attachment(s)
                            </div>
                          )}
                        </TableCell>
                        <TableCell>{getPriorityBadge(ticket.priority)}</TableCell>
                        <TableCell>{getStatusBadge(ticket.status)}</TableCell>
                        <TableCell>
                          {ticket.assigned_to || (
                            <span className="text-xs text-muted-foreground">Unassigned</span>
                          )}
                        </TableCell>
                        <TableCell>
                          {new Date(ticket.submitted_at).toLocaleDateString()}
                        </TableCell>
                        <TableCell>
                          <div className="flex gap-2">
                            <Button
                              size="sm"
                              variant="outline"
                              onClick={() => {
                                setSelectedTicket(ticket);
                                setShowDetailDialog(true);
                              }}
                            >
                              View
                            </Button>
                            {!ticket.assigned_to && (
                              <Button
                                size="sm"
                                variant="default"
                                onClick={() => {
                                  setSelectedTicket(ticket);
                                  setShowAssignDialog(true);
                                }}
                              >
                                Assign
                              </Button>
                            )}
                            <Button
                              size="sm"
                              variant="outline"
                              onClick={() => {
                                setSelectedTicket(ticket);
                                setNewPriority(ticket.priority);
                                setShowPriorityDialog(true);
                              }}
                            >
                              Priority
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
        </TabsContent>

        <TabsContent value="open">
          <Card>
            <CardHeader>
              <CardTitle>Open Tickets</CardTitle>
            </CardHeader>
            <CardContent>
              <p className="text-muted-foreground">Showing open tickets only...</p>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="in_progress">
          <Card>
            <CardHeader>
              <CardTitle>In Progress Tickets</CardTitle>
            </CardHeader>
            <CardContent>
              <p className="text-muted-foreground">Showing tickets in progress...</p>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="resolved">
          <Card>
            <CardHeader>
              <CardTitle>Resolved Tickets</CardTitle>
            </CardHeader>
            <CardContent>
              <p className="text-muted-foreground">Showing resolved tickets...</p>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="unassigned">
          <Card>
            <CardHeader>
              <CardTitle>Unassigned Tickets</CardTitle>
            </CardHeader>
            <CardContent>
              <p className="text-muted-foreground">Showing unassigned tickets...</p>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>

      {/* Detail Dialog */}
      <Dialog open={showDetailDialog} onOpenChange={setShowDetailDialog}>
        <DialogContent className="max-w-2xl">
          <DialogHeader>
            <DialogTitle>Ticket Details</DialogTitle>
            <DialogDescription>
              {selectedTicket?.ticket_number} - {selectedTicket?.subject}
            </DialogDescription>
          </DialogHeader>
          {selectedTicket && (
            <div className="space-y-4">
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label>Student</Label>
                  <p className="text-sm font-medium">{selectedTicket.student_name}</p>
                  <p className="text-xs text-muted-foreground">{selectedTicket.matric_number}</p>
                </div>
                <div>
                  <Label>Category</Label>
                  <p className="text-sm capitalize">{selectedTicket.category}</p>
                </div>
                <div>
                  <Label>Priority</Label>
                  <div className="mt-1">{getPriorityBadge(selectedTicket.priority)}</div>
                </div>
                <div>
                  <Label>Status</Label>
                  <div className="mt-1">{getStatusBadge(selectedTicket.status)}</div>
                </div>
              </div>

              <div>
                <Label>Message</Label>
                <p className="text-sm mt-1 p-3 bg-muted rounded-md">
                  This is the detailed message content from the student...
                </p>
              </div>

              <div>
                <Label>Admin Response</Label>
                <Textarea
                  value={response}
                  onChange={(e) => setResponse(e.target.value)}
                  placeholder="Type your response here..."
                  rows={4}
                />
              </div>

              <div className="flex justify-between">
                <div className="flex gap-2">
                  <Button
                    variant="outline"
                    onClick={() => handleUpdateStatus(selectedTicket.id, 'in_progress')}
                  >
                    Mark In Progress
                  </Button>
                  <Button
                    variant="default"
                    onClick={() => handleUpdateStatus(selectedTicket.id, 'resolved')}
                  >
                    Mark Resolved
                  </Button>
                </div>
                <Button onClick={() => setShowDetailDialog(false)}>Close</Button>
              </div>
            </div>
          )}
        </DialogContent>
      </Dialog>

      {/* Assign Dialog */}
      <Dialog open={showAssignDialog} onOpenChange={setShowAssignDialog}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Assign Ticket</DialogTitle>
            <DialogDescription>
              Assign this ticket to an administrator
            </DialogDescription>
          </DialogHeader>
          <div className="space-y-4">
            <div>
              <Label>Assign To *</Label>
              <Input
                value={assignTo}
                onChange={(e) => setAssignTo(e.target.value)}
                placeholder="Enter admin name or email"
              />
            </div>
            <div className="flex justify-end gap-2">
              <Button variant="outline" onClick={() => setShowAssignDialog(false)}>
                Cancel
              </Button>
              <Button onClick={handleAssign}>Assign Ticket</Button>
            </div>
          </div>
        </DialogContent>
      </Dialog>

      {/* Change Priority Dialog */}
      <Dialog open={showPriorityDialog} onOpenChange={setShowPriorityDialog}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Change Priority</DialogTitle>
            <DialogDescription>
              Update the priority level for this ticket
            </DialogDescription>
          </DialogHeader>
          <div className="space-y-4">
            <div>
              <Label>Priority Level *</Label>
              <Select value={newPriority} onValueChange={(value: any) => setNewPriority(value)}>
                <SelectTrigger>
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="low">Low</SelectItem>
                  <SelectItem value="medium">Medium</SelectItem>
                  <SelectItem value="high">High</SelectItem>
                  <SelectItem value="urgent">Urgent</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div className="flex justify-end gap-2">
              <Button variant="outline" onClick={() => setShowPriorityDialog(false)}>
                Cancel
              </Button>
              <Button onClick={handleChangePriority}>Update Priority</Button>
            </div>
          </div>
        </DialogContent>
      </Dialog>
      </div>
    </DashboardLayout>
  );
}
