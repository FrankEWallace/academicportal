import { useState, useEffect } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Calendar, Clock, MapPin, User, Plus, Filter } from 'lucide-react';
import { timetableApi, type Timetable } from '@/lib/academicApi';
import { useToast } from '@/hooks/use-toast';
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
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
const TIME_SLOTS = [
  '08:00', '09:00', '10:00', '11:00', '12:00', 
  '13:00', '14:00', '15:00', '16:00', '17:00'
];

interface TimetableViewProps {
  studentId?: number;
  teacherId?: number;
  viewMode: 'student' | 'teacher' | 'admin';
}

export default function TimetableView({ studentId, teacherId, viewMode }: TimetableViewProps) {
  const [timetables, setTimetables] = useState<Timetable[]>([]);
  const [loading, setLoading] = useState(true);
  const [selectedDay, setSelectedDay] = useState<string>('all');
  const [selectedSemester, setSelectedSemester] = useState<number>(1);
  const { toast } = useToast();

  useEffect(() => {
    fetchTimetables();
  }, [studentId, teacherId, selectedDay, selectedSemester]);

  const fetchTimetables = async () => {
    try {
      setLoading(true);
      let response;

      if (viewMode === 'student' && studentId) {
        response = await timetableApi.getStudentTimetable(studentId);
      } else if (viewMode === 'teacher' && teacherId) {
        response = await timetableApi.getTeacherTimetable(teacherId);
      } else {
        const filters: any = { semester: selectedSemester };
        if (selectedDay !== 'all') filters.day = selectedDay;
        response = await timetableApi.getAll(filters);
      }

      if (response.success) {
        setTimetables(response.data);
      }
    } catch (error) {
      toast({
        title: 'Error',
        description: 'Failed to fetch timetable',
        variant: 'destructive'
      });
    } finally {
      setLoading(false);
    }
  };

  const groupByDay = () => {
    const grouped: Record<string, Timetable[]> = {};
    DAYS.forEach(day => {
      grouped[day] = timetables.filter(t => t.day_of_week === day);
    });
    return grouped;
  };

  const getTimeSlotClass = (startTime: string, endTime: string, day: string) => {
    const classes = timetables.filter(
      t => t.day_of_week === day && 
      ((t.start_time >= startTime && t.start_time < endTime) ||
       (t.end_time > startTime && t.end_time <= endTime))
    );
    return classes[0];
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>
    );
  }

  const groupedTimetables = groupByDay();

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex justify-between items-center">
        <div>
          <h2 className="text-3xl font-bold tracking-tight">Class Schedule</h2>
          <p className="text-muted-foreground">
            {viewMode === 'student' ? 'Your weekly timetable' : 
             viewMode === 'teacher' ? 'Your teaching schedule' : 
             'All class schedules'}
          </p>
        </div>
        <div className="flex gap-2">
          <Select value={selectedSemester.toString()} onValueChange={(v) => setSelectedSemester(parseInt(v))}>
            <SelectTrigger className="w-32">
              <SelectValue placeholder="Semester" />
            </SelectTrigger>
            <SelectContent>
              {[1, 2, 3, 4, 5, 6, 7, 8].map(sem => (
                <SelectItem key={sem} value={sem.toString()}>Semester {sem}</SelectItem>
              ))}
            </SelectContent>
          </Select>
        </div>
      </div>

      {/* Timetable Grid */}
      <Card>
        <CardContent className="p-6">
          <div className="overflow-x-auto">
            <table className="w-full border-collapse">
              <thead>
                <tr>
                  <th className="border p-2 bg-muted text-left font-semibold min-w-[100px]">Time</th>
                  {DAYS.map(day => (
                    <th key={day} className="border p-2 bg-muted text-center font-semibold min-w-[150px]">
                      {day}
                    </th>
                  ))}
                </tr>
              </thead>
              <tbody>
                {TIME_SLOTS.map((time, idx) => {
                  const nextTime = TIME_SLOTS[idx + 1] || '18:00';
                  return (
                    <tr key={time}>
                      <td className="border p-2 font-medium text-sm bg-muted/50">
                        {time}
                      </td>
                      {DAYS.map(day => {
                        const classInSlot = getTimeSlotClass(time, nextTime, day);
                        return (
                          <td key={`${day}-${time}`} className="border p-2 h-20 align-top">
                            {classInSlot && (
                              <div className="bg-primary/10 border-l-4 border-primary p-2 rounded h-full hover:bg-primary/20 transition-colors cursor-pointer">
                                <div className="font-semibold text-sm line-clamp-1">
                                  {classInSlot.course?.name || 'Course'}
                                </div>
                                <div className="text-xs text-muted-foreground mt-1">
                                  <div className="flex items-center gap-1">
                                    <Clock className="h-3 w-3" />
                                    {classInSlot.start_time} - {classInSlot.end_time}
                                  </div>
                                  <div className="flex items-center gap-1 mt-1">
                                    <MapPin className="h-3 w-3" />
                                    {classInSlot.room}
                                  </div>
                                  {classInSlot.teacher && (
                                    <div className="flex items-center gap-1 mt-1">
                                      <User className="h-3 w-3" />
                                      {classInSlot.teacher.user?.name}
                                    </div>
                                  )}
                                </div>
                              </div>
                            )}
                          </td>
                        );
                      })}
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>

      {/* List View */}
      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        {Object.entries(groupedTimetables).map(([day, classes]) => (
          classes.length > 0 && (
            <Card key={day}>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Calendar className="h-5 w-5" />
                  {day}
                </CardTitle>
                <CardDescription>{classes.length} class{classes.length !== 1 ? 'es' : ''}</CardDescription>
              </CardHeader>
              <CardContent className="space-y-3">
                {classes.map(cls => (
                  <div key={cls.id} className="border-l-4 border-primary pl-3 py-2">
                    <div className="font-semibold">{cls.course?.name}</div>
                    <div className="text-sm text-muted-foreground space-y-1 mt-1">
                      <div className="flex items-center gap-2">
                        <Clock className="h-3 w-3" />
                        {cls.start_time} - {cls.end_time}
                      </div>
                      <div className="flex items-center gap-2">
                        <MapPin className="h-3 w-3" />
                        {cls.room}
                      </div>
                      {cls.teacher && (
                        <div className="flex items-center gap-2">
                          <User className="h-3 w-3" />
                          {cls.teacher.user?.name}
                        </div>
                      )}
                    </div>
                    <Badge variant={cls.status === 'scheduled' ? 'default' : 'secondary'} className="mt-2">
                      {cls.status}
                    </Badge>
                  </div>
                ))}
              </CardContent>
            </Card>
          )
        ))}
      </div>
    </div>
  );
}
