import { DashboardLayout } from "@/components/DashboardLayout";
import AcademicCalendar from "@/pages/AcademicCalendar";

const AdminCalendarPage = () => {
  return (
    <DashboardLayout>
      <div className="space-y-6">
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Academic Calendar</h1>
          <p className="text-muted-foreground">
            Manage academic events, holidays, and important dates
          </p>
        </div>
        
        <AcademicCalendar viewMode="admin" />
      </div>
    </DashboardLayout>
  );
};

export default AdminCalendarPage;
