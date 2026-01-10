import { useState, useEffect } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Calendar as CalendarIcon, Clock, Plus, Filter, AlertCircle } from 'lucide-react';
import { academicCalendarApi, type AcademicEvent } from '@/lib/academicApi';
import { useToast } from '@/hooks/use-toast';
import { format, parseISO, isToday, isFuture, isPast } from 'date-fns';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import {
  Tabs,
  TabsContent,
  TabsList,
  TabsTrigger,
} from '@/components/ui/tabs';

const EVENT_TYPE_COLORS: Record<string, string> = {
  exam: 'bg-red-100 text-red-800 border-red-300',
  holiday: 'bg-green-100 text-green-800 border-green-300',
  registration: 'bg-blue-100 text-blue-800 border-blue-300',
  orientation: 'bg-purple-100 text-purple-800 border-purple-300',
  break: 'bg-yellow-100 text-yellow-800 border-yellow-300',
  deadline: 'bg-orange-100 text-orange-800 border-orange-300',
  other: 'bg-gray-100 text-gray-800 border-gray-300',
};

const EVENT_TYPE_LABELS: Record<string, string> = {
  exam: 'Exam',
  holiday: 'Holiday',
  registration: 'Registration',
  orientation: 'Orientation',
  break: 'Break',
  deadline: 'Deadline',
  other: 'Other',
};

interface AcademicCalendarProps {
  viewMode: 'student' | 'teacher' | 'admin';
}

export default function AcademicCalendar({ viewMode }: AcademicCalendarProps) {
  const [events, setEvents] = useState<AcademicEvent[]>([]);
  const [upcomingEvents, setUpcomingEvents] = useState<AcademicEvent[]>([]);
  const [currentEvents, setCurrentEvents] = useState<AcademicEvent[]>([]);
  const [loading, setLoading] = useState(true);
  const [selectedType, setSelectedType] = useState<string>('all');
  const [selectedSemester, setSelectedSemester] = useState<number | 'all'>('all');
  const { toast } = useToast();

  useEffect(() => {
    fetchEvents();
  }, [selectedType, selectedSemester]);

  useEffect(() => {
    fetchUpcomingEvents();
    fetchCurrentEvents();
  }, []);

  const fetchEvents = async () => {
    try {
      setLoading(true);
      const filters: any = {};
      if (selectedType !== 'all') filters.event_type = selectedType;
      if (selectedSemester !== 'all') filters.semester = selectedSemester;

      const response = await academicCalendarApi.getAll(filters);
      if (response.success) {
        setEvents(response.data);
      }
    } catch (error) {
      toast({
        title: 'Error',
        description: 'Failed to fetch academic calendar',
        variant: 'destructive'
      });
    } finally {
      setLoading(false);
    }
  };

  const fetchUpcomingEvents = async () => {
    try {
      const response = await academicCalendarApi.getUpcoming();
      if (response.success) {
        setUpcomingEvents(response.data);
      }
    } catch (error) {
      console.error('Failed to fetch upcoming events:', error);
    }
  };

  const fetchCurrentEvents = async () => {
    try {
      const response = await academicCalendarApi.getCurrent();
      if (response.success) {
        setCurrentEvents(response.data);
      }
    } catch (error) {
      console.error('Failed to fetch current events:', error);
    }
  };

  const getEventStatus = (event: AcademicEvent) => {
    const start = parseISO(event.start_date);
    const end = parseISO(event.end_date);
    const now = new Date();

    if (isPast(end)) return 'completed';
    if (isFuture(start)) return 'upcoming';
    return 'ongoing';
  };

  const groupEventsByMonth = () => {
    const grouped: Record<string, AcademicEvent[]> = {};
    events.forEach(event => {
      const month = format(parseISO(event.start_date), 'MMMM yyyy');
      if (!grouped[month]) grouped[month] = [];
      grouped[month].push(event);
    });
    return grouped;
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>
    );
  }

  const groupedEvents = groupEventsByMonth();

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex justify-between items-center">
        <div>
          <h2 className="text-3xl font-bold tracking-tight">Academic Calendar</h2>
          <p className="text-muted-foreground">
            Important dates and events for the academic year
          </p>
        </div>
        {viewMode === 'admin' && (
          <Button>
            <Plus className="h-4 w-4 mr-2" />
            Add Event
          </Button>
        )}
      </div>

      {/* Filters */}
      <div className="flex gap-2">
        <Select value={selectedType} onValueChange={setSelectedType}>
          <SelectTrigger className="w-40">
            <SelectValue placeholder="Event Type" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">All Types</SelectItem>
            {Object.entries(EVENT_TYPE_LABELS).map(([key, label]) => (
              <SelectItem key={key} value={key}>{label}</SelectItem>
            ))}
          </SelectContent>
        </Select>

        <Select value={selectedSemester.toString()} onValueChange={(v) => setSelectedSemester(v === 'all' ? 'all' : parseInt(v))}>
          <SelectTrigger className="w-40">
            <SelectValue placeholder="Semester" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">All Semesters</SelectItem>
            {[1, 2, 3, 4, 5, 6, 7, 8].map(sem => (
              <SelectItem key={sem} value={sem.toString()}>Semester {sem}</SelectItem>
            ))}
          </SelectContent>
        </Select>
      </div>

      {/* Current & Upcoming Events */}
      <div className="grid gap-6 md:grid-cols-2">
        {/* Current Events */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <AlertCircle className="h-5 w-5 text-orange-500" />
              Current Events
            </CardTitle>
            <CardDescription>Events happening now</CardDescription>
          </CardHeader>
          <CardContent className="space-y-3">
            {currentEvents.length === 0 ? (
              <p className="text-sm text-muted-foreground">No current events</p>
            ) : (
              currentEvents.map(event => (
                <div key={event.id} className={`border-l-4 pl-3 py-2 ${EVENT_TYPE_COLORS[event.event_type]?.replace('bg-', 'border-')}`}>
                  <div className="font-semibold">{event.title}</div>
                  <div className="text-sm text-muted-foreground mt-1">
                    {format(parseISO(event.start_date), 'MMM d')} - {format(parseISO(event.end_date), 'MMM d, yyyy')}
                  </div>
                  <Badge className={`mt-2 ${EVENT_TYPE_COLORS[event.event_type]}`}>
                    {EVENT_TYPE_LABELS[event.event_type]}
                  </Badge>
                </div>
              ))
            )}
          </CardContent>
        </Card>

        {/* Upcoming Events */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Clock className="h-5 w-5 text-blue-500" />
              Upcoming Events
            </CardTitle>
            <CardDescription>Next 10 events</CardDescription>
          </CardHeader>
          <CardContent className="space-y-3">
            {upcomingEvents.length === 0 ? (
              <p className="text-sm text-muted-foreground">No upcoming events</p>
            ) : (
              upcomingEvents.slice(0, 10).map(event => (
                <div key={event.id} className={`border-l-4 pl-3 py-2 ${EVENT_TYPE_COLORS[event.event_type]?.replace('bg-', 'border-')}`}>
                  <div className="font-semibold">{event.title}</div>
                  <div className="text-sm text-muted-foreground mt-1">
                    {format(parseISO(event.start_date), 'MMM d')} - {format(parseISO(event.end_date), 'MMM d, yyyy')}
                  </div>
                  <Badge className={`mt-2 ${EVENT_TYPE_COLORS[event.event_type]}`}>
                    {EVENT_TYPE_LABELS[event.event_type]}
                  </Badge>
                </div>
              ))
            )}
          </CardContent>
        </Card>
      </div>

      {/* All Events by Month */}
      <Card>
        <CardHeader>
          <CardTitle>All Events</CardTitle>
          <CardDescription>Chronological list of all academic events</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="space-y-6">
            {Object.entries(groupedEvents).map(([month, monthEvents]) => (
              <div key={month}>
                <h3 className="font-semibold text-lg mb-3 flex items-center gap-2">
                  <CalendarIcon className="h-5 w-5" />
                  {month}
                </h3>
                <div className="space-y-2">
                  {monthEvents.map(event => {
                    const status = getEventStatus(event);
                    return (
                      <div key={event.id} className="border rounded-lg p-4 hover:shadow-md transition-shadow">
                        <div className="flex justify-between items-start">
                          <div className="flex-1">
                            <h4 className="font-semibold">{event.title}</h4>
                            {event.description && (
                              <p className="text-sm text-muted-foreground mt-1">{event.description}</p>
                            )}
                            <div className="flex items-center gap-4 mt-2 text-sm text-muted-foreground">
                              <span className="flex items-center gap-1">
                                <CalendarIcon className="h-4 w-4" />
                                {format(parseISO(event.start_date), 'MMM d')} - {format(parseISO(event.end_date), 'MMM d, yyyy')}
                              </span>
                              {event.semester && (
                                <span>Semester {event.semester}</span>
                              )}
                            </div>
                          </div>
                          <div className="flex gap-2">
                            <Badge className={EVENT_TYPE_COLORS[event.event_type]}>
                              {EVENT_TYPE_LABELS[event.event_type]}
                            </Badge>
                            <Badge variant={
                              status === 'ongoing' ? 'default' : 
                              status === 'upcoming' ? 'secondary' : 
                              'outline'
                            }>
                              {status}
                            </Badge>
                          </div>
                        </div>
                      </div>
                    );
                  })}
                </div>
              </div>
            ))}
            {events.length === 0 && (
              <p className="text-center text-muted-foreground py-8">
                No events found for the selected filters
              </p>
            )}
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
