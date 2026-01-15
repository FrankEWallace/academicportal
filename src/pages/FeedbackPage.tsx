import { useEffect, useState } from 'react';
import { MessageSquare, Send, Paperclip, CheckCircle2, Clock, AlertCircle } from 'lucide-react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog';
import { feedbackApi, FeedbackTicket, FeedbackDetails, FeedbackCategory, FeedbackPriority } from '@/lib/api/feedbackApi';
import { useToast } from '@/hooks/use-toast';

export default function FeedbackPage() {
  const [loading, setLoading] = useState(true);
  const [tickets, setTickets] = useState<FeedbackTicket[]>([]);
  const [statusCounts, setStatusCounts] = useState<any>({});
  const [categories, setCategories] = useState<FeedbackCategory[]>([]);
  const [priorities, setPriorities] = useState<FeedbackPriority[]>([]);
  const [selectedTicket, setSelectedTicket] = useState<FeedbackDetails | null>(null);
  const [showNewTicket, setShowNewTicket] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const { toast } = useToast();

  const [formData, setFormData] = useState({
    category: '',
    priority: 'medium',
    subject: '',
    message: '',
  });
  const [attachments, setAttachments] = useState<File[]>([]);

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    setLoading(true);
    try {
      const [historyData, categoriesData] = await Promise.all([
        feedbackApi.getFeedbackHistory(),
        feedbackApi.getFeedbackCategories(),
      ]);

      setTickets(historyData.tickets);
      setStatusCounts(historyData.status_counts);
      setCategories(categoriesData.categories);
      setPriorities(categoriesData.priorities);
    } catch (error) {
      console.error('Error fetching feedback data:', error);
      toast({
        title: 'Error',
        description: 'Failed to load feedback data',
        variant: 'destructive',
      });
    } finally {
      setLoading(false);
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setSubmitting(true);

    try {
      const data = new FormData();
      data.append('category', formData.category);
      data.append('priority', formData.priority);
      data.append('subject', formData.subject);
      data.append('message', formData.message);
      
      attachments.forEach((file, index) => {
        data.append(`attachments[${index}]`, file);
      });

      await feedbackApi.submitFeedback(data);
      
      toast({
        title: 'Success',
        description: 'Your feedback has been submitted successfully',
      });
      
      setShowNewTicket(false);
      setFormData({ category: '', priority: 'medium', subject: '', message: '' });
      setAttachments([]);
      fetchData();
    } catch (error) {
      toast({
        title: 'Error',
        description: 'Failed to submit feedback',
        variant: 'destructive',
      });
    } finally {
      setSubmitting(false);
    }
  };

  const handleViewTicket = async (ticketId: number) => {
    try {
      const details = await feedbackApi.getFeedbackDetails(ticketId);
      setSelectedTicket(details);
      await feedbackApi.markAsViewed(ticketId);
    } catch (error) {
      toast({
        title: 'Error',
        description: 'Failed to load ticket details',
        variant: 'destructive',
      });
    }
  };

  const getStatusBadge = (status: string) => {
    const config: Record<string, { variant: any; icon: any }> = {
      submitted: { variant: 'secondary', icon: Clock },
      in_review: { variant: 'default', icon: AlertCircle },
      in_progress: { variant: 'default', icon: Clock },
      resolved: { variant: 'default', icon: CheckCircle2 },
      closed: { variant: 'outline', icon: CheckCircle2 },
    };
    
    const { variant, icon: Icon } = config[status] || config.submitted;
    
    return (
      <Badge variant={variant} className="flex items-center gap-1">
        <Icon className="h-3 w-3" />
        {status.replace('_', ' ')}
      </Badge>
    );
  };

  const getPriorityBadge = (priority: string) => {
    const colors: Record<string, string> = {
      low: 'bg-gray-100 text-gray-800',
      medium: 'bg-blue-100 text-blue-800',
      high: 'bg-orange-100 text-orange-800',
      urgent: 'bg-red-100 text-red-800',
    };
    
    return (
      <span className={`px-2 py-1 rounded-full text-xs font-medium ${colors[priority]}`}>
        {priority}
      </span>
    );
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto"></div>
          <p className="mt-4 text-muted-foreground">Loading feedback...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="container mx-auto p-6 max-w-7xl">
      <div className="flex items-center justify-between mb-8">
        <div>
          <h1 className="text-3xl font-bold mb-2">Student Feedback & Support</h1>
          <p className="text-muted-foreground">Submit tickets and track your support requests</p>
        </div>
        <Dialog open={showNewTicket} onOpenChange={setShowNewTicket}>
          <DialogTrigger asChild>
            <Button>
              <MessageSquare className="h-4 w-4 mr-2" />
              New Ticket
            </Button>
          </DialogTrigger>
          <DialogContent className="max-w-2xl">
            <DialogHeader>
              <DialogTitle>Submit New Ticket</DialogTitle>
              <DialogDescription>
                Describe your issue or feedback, and we'll get back to you soon
              </DialogDescription>
            </DialogHeader>
            <form onSubmit={handleSubmit} className="space-y-4">
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label>Category *</Label>
                  <Select
                    value={formData.category}
                    onValueChange={(value) => setFormData({ ...formData, category: value })}
                    required
                  >
                    <SelectTrigger>
                      <SelectValue placeholder="Select category" />
                    </SelectTrigger>
                    <SelectContent>
                      {categories.map((cat) => (
                        <SelectItem key={cat.value} value={cat.value}>
                          {cat.label}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>
                <div>
                  <Label>Priority *</Label>
                  <Select
                    value={formData.priority}
                    onValueChange={(value) => setFormData({ ...formData, priority: value })}
                    required
                  >
                    <SelectTrigger>
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      {priorities.map((pri) => (
                        <SelectItem key={pri.value} value={pri.value}>
                          {pri.label}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>
              </div>

              <div>
                <Label>Subject *</Label>
                <Input
                  value={formData.subject}
                  onChange={(e) => setFormData({ ...formData, subject: e.target.value })}
                  placeholder="Brief description of your issue"
                  required
                />
              </div>

              <div>
                <Label>Message *</Label>
                <Textarea
                  value={formData.message}
                  onChange={(e) => setFormData({ ...formData, message: e.target.value })}
                  placeholder="Provide detailed information about your issue or feedback"
                  rows={6}
                  required
                />
              </div>

              <div>
                <Label>Attachments</Label>
                <Input
                  type="file"
                  multiple
                  accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                  onChange={(e) => setAttachments(Array.from(e.target.files || []))}
                />
                <p className="text-xs text-muted-foreground mt-1">
                  Max 10MB per file. Supported: PDF, Images, Word documents
                </p>
              </div>

              <div className="flex justify-end gap-2">
                <Button type="button" variant="outline" onClick={() => setShowNewTicket(false)}>
                  Cancel
                </Button>
                <Button type="submit" disabled={submitting}>
                  {submitting ? 'Submitting...' : 'Submit Ticket'}
                </Button>
              </div>
            </form>
          </DialogContent>
        </Dialog>
      </div>

      {/* Status Summary */}
      <div className="grid gap-4 mb-6 md:grid-cols-5">
        {Object.entries(statusCounts).map(([status, count]) => (
          <Card key={status}>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium capitalize">
                {status.replace('_', ' ')}
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{count as number}</div>
            </CardContent>
          </Card>
        ))}
      </div>

      {/* Tickets List */}
      <Card>
        <CardHeader>
          <CardTitle>Your Tickets</CardTitle>
          <CardDescription>View and manage your support tickets</CardDescription>
        </CardHeader>
        <CardContent>
          {tickets.length > 0 ? (
            <div className="space-y-2">
              {tickets.map((ticket) => (
                <div
                  key={ticket.id}
                  className="p-4 border rounded-lg hover:bg-muted/50 cursor-pointer transition"
                  onClick={() => handleViewTicket(ticket.id)}
                >
                  <div className="flex items-start justify-between mb-2">
                    <div className="flex-1">
                      <div className="flex items-center gap-2 mb-1">
                        <span className="font-mono text-sm text-muted-foreground">
                          {ticket.ticket_number}
                        </span>
                        {ticket.has_new_response && (
                          <Badge variant="destructive" className="text-xs">
                            New Response
                          </Badge>
                        )}
                      </div>
                      <h3 className="font-semibold">{ticket.subject}</h3>
                      <p className="text-sm text-muted-foreground line-clamp-2 mt-1">
                        {ticket.message}
                      </p>
                    </div>
                    <div className="flex flex-col items-end gap-2 ml-4">
                      {getStatusBadge(ticket.status)}
                      {getPriorityBadge(ticket.priority)}
                    </div>
                  </div>
                  <div className="flex items-center gap-4 text-xs text-muted-foreground">
                    <span className="capitalize">{ticket.category}</span>
                    <span>•</span>
                    <span>{new Date(ticket.submitted_at).toLocaleDateString()}</span>
                    {ticket.attachments_count > 0 && (
                      <>
                        <span>•</span>
                        <span className="flex items-center gap-1">
                          <Paperclip className="h-3 w-3" />
                          {ticket.attachments_count} attachment{ticket.attachments_count !== 1 ? 's' : ''}
                        </span>
                      </>
                    )}
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <div className="text-center py-12">
              <MessageSquare className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
              <h3 className="font-semibold mb-2">No tickets yet</h3>
              <p className="text-sm text-muted-foreground mb-4">
                Submit your first support ticket to get started
              </p>
              <Button onClick={() => setShowNewTicket(true)}>
                <MessageSquare className="h-4 w-4 mr-2" />
                Create Ticket
              </Button>
            </div>
          )}
        </CardContent>
      </Card>

      {/* Ticket Details Dialog */}
      {selectedTicket && (
        <Dialog open={!!selectedTicket} onOpenChange={() => setSelectedTicket(null)}>
          <DialogContent className="max-w-3xl max-h-[80vh] overflow-y-auto">
            <DialogHeader>
              <div className="flex items-center justify-between">
                <DialogTitle>{selectedTicket.ticket.ticket_number}</DialogTitle>
                <div className="flex gap-2">
                  {getStatusBadge(selectedTicket.ticket.status)}
                  {getPriorityBadge(selectedTicket.ticket.priority)}
                </div>
              </div>
              <DialogDescription>{selectedTicket.ticket.subject}</DialogDescription>
            </DialogHeader>

            <div className="space-y-4">
              {/* Original Message */}
              <div className="border-l-4 border-primary pl-4">
                <p className="text-sm font-medium mb-1">Your Message</p>
                <p className="text-sm text-muted-foreground whitespace-pre-wrap">
                  {selectedTicket.ticket.message}
                </p>
                <p className="text-xs text-muted-foreground mt-2">
                  {new Date(selectedTicket.ticket.submitted_at).toLocaleString()}
                </p>
              </div>

              {/* Responses */}
              {selectedTicket.responses.length > 0 && (
                <div className="space-y-3">
                  <h4 className="font-semibold">Responses</h4>
                  {selectedTicket.responses.map((response) => (
                    <div key={response.id} className="border-l-4 border-muted pl-4">
                      <p className="text-sm font-medium mb-1">
                        {response.responded_by_name}
                      </p>
                      <p className="text-sm whitespace-pre-wrap">{response.response_message}</p>
                      <p className="text-xs text-muted-foreground mt-2">
                        {new Date(response.responded_at).toLocaleString()}
                      </p>
                    </div>
                  ))}
                </div>
              )}

              {/* Attachments */}
              {selectedTicket.attachments.length > 0 && (
                <div>
                  <h4 className="font-semibold mb-2">Attachments</h4>
                  <div className="space-y-1">
                    {selectedTicket.attachments.map((att) => (
                      <div key={att.id} className="flex items-center gap-2 text-sm p-2 border rounded">
                        <Paperclip className="h-4 w-4" />
                        <span>{att.file_name}</span>
                        <span className="text-muted-foreground">
                          ({(att.file_size / 1024).toFixed(1)} KB)
                        </span>
                      </div>
                    ))}
                  </div>
                </div>
              )}
            </div>
          </DialogContent>
        </Dialog>
      )}
    </div>
  );
}
