import { DashboardLayout } from "@/components/DashboardLayout";
import TimetableView from "@/pages/TimetableView";
import { useCurrentUser } from "@/hooks/useApi";

const TeacherTimetablePage = () => {
  const { data: currentUser } = useCurrentUser();
  // @ts-ignore - We'll handle type definitions later
  const teacherId = currentUser?.data?.teacher?.id || currentUser?.data?.user?.id;

  return (
    <DashboardLayout>
      <div className="space-y-6">
        <div>
          <h1 className="text-3xl font-bold tracking-tight">My Timetable</h1>
          <p className="text-muted-foreground">
            View your teaching schedule
          </p>
        </div>
        
        <TimetableView viewMode="teacher" teacherId={teacherId} />
      </div>
    </DashboardLayout>
  );
};

export default TeacherTimetablePage;
