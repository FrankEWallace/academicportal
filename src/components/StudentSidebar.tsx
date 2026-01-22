import { 
  LayoutDashboard, 
  BookOpen, 
  GraduationCap,
  Calendar,
  FileText,
  Bell,
  Settings,
  LogOut,
  Clock,
  TrendingUp,
  Printer,
  FilePlus,
  CalendarDays
} from "lucide-react";
import { NavLink } from "@/components/NavLink";
import {
  Sidebar,
  SidebarContent,
  SidebarGroup,
  SidebarGroupContent,
  SidebarGroupLabel,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  SidebarHeader,
  SidebarFooter,
  useSidebar,
} from "@/components/ui/sidebar";
import { Button } from "@/components/ui/button";
import { useAuth } from "@/contexts/AuthContext";
import { GraduationCap as Logo } from "lucide-react";

interface MenuItem {
  title: string;
  url: string;
  icon: React.ComponentType<{ className?: string }>;
}

const studentMenuItems: MenuItem[] = [
  { title: "Dashboard", url: "/student", icon: LayoutDashboard },
  { title: "Timetable", url: "/student/timetable", icon: Calendar },
  { title: "Academic Calendar", url: "/student/calendar", icon: CalendarDays },
  { title: "Degree Progress", url: "/student/progress", icon: TrendingUp },
  { title: "Waitlist", url: "/student/waitlist", icon: Clock },
  { title: "Print Forms", url: "/student/print-forms", icon: Printer },
  { title: "Document Requests", url: "/student/document-requests", icon: FilePlus },
];

export function StudentSidebar() {
  const { state } = useSidebar();
  const { logout, user } = useAuth();
  const isCollapsed = state === "collapsed";

  return (
    <Sidebar>
      <SidebarHeader className="p-4 border-b">
        <div className="flex items-center gap-3">
          <div className="w-8 h-8 bg-primary rounded-lg flex items-center justify-center">
            <Logo className="w-5 h-5 text-primary-foreground" />
          </div>
          {!isCollapsed && (
            <div>
              <h2 className="text-lg font-semibold text-sidebar-foreground">Academic Portal</h2>
              <p className="text-xs text-sidebar-foreground/60">Student Dashboard</p>
            </div>
          )}
        </div>
      </SidebarHeader>

      <SidebarContent className="px-4 py-6">
        <SidebarGroup>
          <SidebarGroupLabel>Navigation</SidebarGroupLabel>
          <SidebarGroupContent>
            <SidebarMenu>
              {studentMenuItems.map((item) => (
                <SidebarMenuItem key={item.title}>
                  <SidebarMenuButton asChild>
                    <NavLink
                      to={item.url}
                      className="flex items-center gap-3 px-3 py-2 hover:bg-sidebar-accent rounded-lg transition-colors"
                      activeClassName="bg-sidebar-accent text-sidebar-accent-foreground font-medium"
                    >
                      <item.icon className="w-5 h-5 flex-shrink-0" />
                      {!isCollapsed && <span>{item.title}</span>}
                    </NavLink>
                  </SidebarMenuButton>
                </SidebarMenuItem>
              ))}
            </SidebarMenu>
          </SidebarGroupContent>
        </SidebarGroup>
      </SidebarContent>

      <SidebarFooter className="p-4">
        {!isCollapsed && user && (
          <div className="mb-4 p-3 bg-sidebar-accent/50 rounded-lg">
            <p className="text-sm font-medium text-sidebar-foreground">{user.name}</p>
            <p className="text-xs text-sidebar-foreground/60">{user.email}</p>
            <p className="text-xs text-sidebar-foreground/60">Student</p>
          </div>
        )}
        <SidebarMenu>
          <SidebarMenuItem>
            <SidebarMenuButton asChild>
              <NavLink
                to="/student/settings"
                className="flex items-center gap-3 px-3 py-2 hover:bg-sidebar-accent rounded-lg transition-colors"
                activeClassName="bg-sidebar-accent text-sidebar-accent-foreground font-medium"
              >
                <Settings className="w-5 h-5 flex-shrink-0" />
                {!isCollapsed && <span>Settings</span>}
              </NavLink>
            </SidebarMenuButton>
          </SidebarMenuItem>
          <SidebarMenuItem>
            <SidebarMenuButton asChild>
              <Button
                variant="ghost"
                className="w-full justify-start gap-3 px-3 py-2 hover:bg-sidebar-accent text-sidebar-foreground"
                onClick={logout}
              >
                <LogOut className="w-5 h-5 flex-shrink-0" />
                {!isCollapsed && <span>Logout</span>}
              </Button>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarFooter>
    </Sidebar>
  );
}
