import { ReactNode } from "react";
import { SidebarProvider, SidebarTrigger } from "@/components/ui/sidebar";
import { AppSidebar } from "@/components/AppSidebar";
import { StudentSidebar } from "@/components/StudentSidebar";
import { TeacherSidebar } from "@/components/TeacherSidebar";
import { useAuth } from "@/contexts/AuthContext";

interface DashboardLayoutProps {
  children: ReactNode;
  title?: string;
}

export function DashboardLayout({ children, title = "Dashboard" }: DashboardLayoutProps) {
  const { user } = useAuth();

  const getSidebarComponent = () => {
    switch (user?.role) {
      case 'admin':
        return <AppSidebar />;
      case 'student':
        return <StudentSidebar />;
      case 'teacher':
        return <TeacherSidebar />;
      default:
        return <AppSidebar />;
    }
  };

  const getRoleDisplayName = () => {
    switch (user?.role) {
      case 'admin':
        return 'Administrator';
      case 'student':
        return 'Student';
      case 'teacher':
        return 'Teacher';
      default:
        return 'User';
    }
  };

  return (
    <SidebarProvider>
      <div className="min-h-screen flex w-full bg-background">
        {getSidebarComponent()}
        <div className="flex-1 flex flex-col">
          <header className="h-16 border-b bg-card flex items-center px-6 sticky top-0 z-10">
            <SidebarTrigger />
            <div className="ml-4 flex-1">
              <h1 className="text-lg font-semibold text-foreground">{title}</h1>
              <p className="text-sm text-muted-foreground">{getRoleDisplayName()} Portal</p>
            </div>
            {user && (
              <div className="text-right">
                <p className="text-sm font-medium text-foreground">{user.name}</p>
                <p className="text-xs text-muted-foreground">{getRoleDisplayName()}</p>
              </div>
            )}
          </header>
          <main className="flex-1 p-6">
            {children}
          </main>
        </div>
      </div>
    </SidebarProvider>
  );
}
