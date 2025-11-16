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
  LogOut
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

interface MenuItem {
  title: string;
  url: string;
  icon: React.ComponentType<{ className?: string }>;
}

const menuItems: MenuItem[] = [
  { title: "Dashboard", url: "/admin", icon: LayoutDashboard },
  { title: "Students", url: "/admin/students", icon: Users },
  { title: "Teachers", url: "/admin/teachers", icon: GraduationCap },
  { title: "Courses", url: "/admin/courses", icon: BookOpen },
  { title: "Departments", url: "/admin/departments", icon: Building2 },
  { title: "Attendance", url: "/admin/attendance", icon: ClipboardCheck },
  { title: "Exams & Grades", url: "/admin/exams", icon: FileText },
  { title: "Fees", url: "/admin/fees", icon: CreditCard },
  { title: "Announcements", url: "/admin/announcements", icon: Megaphone },
];

export function AppSidebar() {
  const { state } = useSidebar();
  const isCollapsed = state === "collapsed";

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
              <p className="text-xs text-sidebar-foreground/70">Admin Panel</p>
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
                      end={item.url === "/admin"}
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
        <SidebarMenu>
          <SidebarMenuItem>
            <SidebarMenuButton asChild>
              <NavLink
                to="/admin/settings"
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
                onClick={() => window.location.href = "/"}
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
