import { DashboardLayout } from "@/components/DashboardLayout";
import AcademicCalendar from "@/pages/AcademicCalendar";

const TeacherCalendarPage = () => {
  return (
    <DashboardLayout>
      <div className="space-y-6">
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Academic Calendar</h1>
          <p className="text-muted-foreground">
            View important academic dates and events
          </p>
        </div>
        
        <AcademicCalendar viewMode="teacher" />
      </div>
    </DashboardLayout>
  );
};

export default TeacherCalendarPage;
