import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { LucideIcon } from "lucide-react";

interface StatCardProps {
  title: string;
  value: string | number;
  icon: LucideIcon;
  description?: string;
  trend?: {
    value: string;
    positive: boolean;
  };
  colorClass?: string;
}

export function StatCard({ 
  title, 
  value, 
  icon: Icon, 
  description, 
  trend,
  colorClass = "bg-primary"
}: StatCardProps) {
  return (
    <Card className="shadow-card hover:shadow-md transition-shadow">
      <CardHeader className="flex flex-row items-center justify-between pb-2">
        <CardTitle className="text-sm font-medium text-muted-foreground">
          {title}
        </CardTitle>
        <div className={`${colorClass} p-2 rounded-lg`}>
          <Icon className="w-5 h-5 text-white" />
        </div>
      </CardHeader>
      <CardContent>
        <div className="text-3xl font-bold text-foreground">{value}</div>
        {description && (
          <p className="text-xs text-muted-foreground mt-1">{description}</p>
        )}
        {trend && (
          <div className={`text-xs mt-2 ${trend.positive ? 'text-success' : 'text-destructive'}`}>
            {trend.positive ? '↑' : '↓'} {trend.value}
          </div>
        )}
      </CardContent>
    </Card>
  );
}
