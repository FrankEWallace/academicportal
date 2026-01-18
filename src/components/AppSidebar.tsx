import { 
  LayoutDashboard, 
  Users, 
  GraduationCap, 
  BookOpen, 
  Building2, 
  ClipboardCheck,
  FileText,
  CreditCard,
  Megaphone,
  Settings,
  LogOut,
  Calendar,
  CalendarDays,
  ListChecks,
  UserCog,
  Clock,
  Award,
  TrendingUp,
  ClipboardList,
  Shield,
  UserCheck,
  MessageSquare,
  BedDouble
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
import { Separator } from "@/components/ui/separator";
import { useAuth } from "@/contexts/AuthContext";
import { GraduationCap as Logo } from "lucide-react";

interface MenuItem {
  title: string;
  url: string;
  icon: React.ComponentType<{ className?: string }>;
}

const adminMenuItems: MenuItem[] = [
  { title: "Dashboard", url: "/admin", icon: LayoutDashboard },
  { title: "Registrations", url: "/admin/registrations", icon: ClipboardList },
  { title: "Insurance", url: "/admin/insurance", icon: Shield },
  { title: "Enrollments", url: "/admin/enrollments", icon: UserCheck },
  { title: "Results Moderation", url: "/admin/results", icon: Award },
  { title: "Accommodations", url: "/admin/accommodations", icon: BedDouble },
  { title: "Feedback", url: "/admin/feedback", icon: MessageSquare },
  { title: "Students", url: "/admin/students", icon: Users },
  { title: "Teachers", url: "/admin/teachers", icon: GraduationCap },
  { title: "Courses", url: "/admin/courses", icon: BookOpen },
  { title: "Departments", url: "/admin/departments", icon: Building2 },
  { title: "Timetable", url: "/admin/timetable", icon: Clock },
  { title: "Academic Calendar", url: "/admin/calendar", icon: CalendarDays },
  { title: "Attendance", url: "/admin/attendance", icon: ClipboardCheck },
  { title: "Exams & Grades", url: "/admin/exams", icon: FileText },
  { title: "Fees", url: "/admin/fees", icon: CreditCard },
  { title: "Announcements", url: "/admin/announcements", icon: Megaphone },
];

const studentMenuItems: MenuItem[] = [
  { title: "Dashboard", url: "/student", icon: LayoutDashboard },
  { title: "My Courses", url: "/student/courses", icon: BookOpen },
  { title: "Timetable", url: "/student/timetable", icon: Clock },
  { title: "Academic Calendar", url: "/student/calendar", icon: CalendarDays },
  { title: "Degree Progress", url: "/student/progress", icon: TrendingUp },
  { title: "Waitlist", url: "/student/waitlist", icon: ListChecks },
  { title: "Grades", url: "/student/grades", icon: Award },
  { title: "Attendance", url: "/student/attendance", icon: ClipboardCheck },
  { title: "Fees", url: "/student/fees", icon: CreditCard },
  { title: "Announcements", url: "/student/announcements", icon: Megaphone },
];

const teacherMenuItems: MenuItem[] = [
  { title: "Dashboard", url: "/teacher", icon: LayoutDashboard },
  { title: "My Courses", url: "/teacher/courses", icon: BookOpen },
  { title: "Timetable", url: "/teacher/timetable", icon: Clock },
  { title: "Academic Calendar", url: "/teacher/calendar", icon: CalendarDays },
  { title: "Students", url: "/teacher/students", icon: Users },
  { title: "Attendance", url: "/teacher/attendance", icon: ClipboardCheck },
  { title: "Grades", url: "/teacher/grades", icon: Award },
  { title: "Announcements", url: "/teacher/announcements", icon: Megaphone },
];

export function AppSidebar() {
  const { state } = useSidebar();
  const { logout, user } = useAuth();
  const isCollapsed = state === "collapsed";

  // Select menu items based on user role
  const menuItems = user?.role === 'student' ? studentMenuItems :
                    user?.role === 'teacher' ? teacherMenuItems :
                    adminMenuItems;

  // Determine base route for the role
  const baseRoute = user?.role === 'student' ? '/student' :
                    user?.role === 'teacher' ? '/teacher' :
                    '/admin';

  // Determine panel title
  const panelTitle = user?.role === 'student' ? 'Student Portal' :
                     user?.role === 'teacher' ? 'Teacher Portal' :
                     'Admin Panel';

  return (
    <Sidebar
      className={isCollapsed ? "w-14" : "w-64"}
      collapsible="icon"
    >
      <SidebarHeader className="p-4">
        {!isCollapsed && (
          <div className="flex items-center gap-2">
            <div className="w-10 h-10 bg-sidebar-primary rounded-lg flex items-center justify-center">
              <GraduationCap className="w-6 h-6 text-sidebar-primary-foreground" />
            </div>
            <div>
              <h2 className="font-bold text-sidebar-foreground">Academic Portal</h2>
              <p className="text-xs text-sidebar-foreground/70">{panelTitle}</p>
            </div>
          </div>
        )}
        {isCollapsed && (
          <div className="w-10 h-10 bg-sidebar-primary rounded-lg flex items-center justify-center mx-auto">
            <GraduationCap className="w-6 h-6 text-sidebar-primary-foreground" />
          </div>
        )}
      </SidebarHeader>

      <Separator />

      <SidebarContent>
        <SidebarGroup>
          <SidebarGroupLabel className={isCollapsed ? "sr-only" : ""}>
            Main Menu
          </SidebarGroupLabel>
          <SidebarGroupContent>
            <SidebarMenu>
              {menuItems.map((item) => (
                <SidebarMenuItem key={item.title}>
                  <SidebarMenuButton asChild>
                    <NavLink
                      to={item.url}
                      end={item.url === baseRoute}
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
            <p className="text-xs text-sidebar-foreground/60 capitalize">{user.role}</p>
          </div>
        )}
        <SidebarMenu>
          <SidebarMenuItem>
            <SidebarMenuButton asChild>
              <NavLink
                to={`${baseRoute}/settings`}
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
