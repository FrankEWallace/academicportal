import { DashboardLayout } from "@/components/DashboardLayout";
import TimetableView from "@/pages/TimetableView";

const AdminTimetablePage = () => {
  return (
    <DashboardLayout>
      <div className="space-y-6">
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Timetable Management</h1>
          <p className="text-muted-foreground">
            Manage class schedules and timetables
          </p>
        </div>
        
        <TimetableView viewMode="admin" />
      </div>
    </DashboardLayout>
  );
};

export default AdminTimetablePage;
