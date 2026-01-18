import { useState, useEffect } from 'react';
import { DashboardLayout } from '@/components/DashboardLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { 
  DollarSign, 
  FileText, 
  CreditCard,
  AlertCircle,
  TrendingUp,
  Users,
  CheckCircle,
  Clock
} from 'lucide-react';
import { feeManagementApi, FeeStatistics } from '@/lib/api/feeManagementApi';
import { useToast } from '@/hooks/use-toast';
import FeeStructuresTab from './fees/FeeStructuresTab';
import InvoicesTab from './fees/InvoicesTab';
import PaymentsTab from './fees/PaymentsTab';

export default function FeesManagement() {
  const [statistics, setStatistics] = useState<FeeStatistics | null>(null);
  const [loading, setLoading] = useState(false);
  const { toast } = useToast();

  useEffect(() => {
    fetchStatistics();
  }, []);

  const fetchStatistics = async () => {
    setLoading(true);
    try {
      const response = await feeManagementApi.getStatistics();
      if (response.success) {
        setStatistics(response.data);
      }
    } catch (error: any) {
      console.error('Failed to fetch statistics:', error);
      toast({
        title: 'Error',
        description: error.message || 'Failed to fetch statistics',
        variant: 'destructive',
      });
    } finally {
      setLoading(false);
    }
  };

  return (
    <DashboardLayout title="Fees Management">
      <div className="space-y-6">
        {/* Statistics Cards */}
        {statistics && (
          <div className="grid gap-4 md:grid-cols-4">
            <Card>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">Total Revenue</CardTitle>
                <DollarSign className="h-4 w-4 text-green-600" />
              </CardHeader>
              <CardContent>
                <div className="text-2xl font-bold">${statistics.total_revenue?.toLocaleString() || 0}</div>
                <p className="text-xs text-muted-foreground">
                  {statistics.collection_rate?.toFixed(1)}% collection rate
                </p>
              </CardContent>
            </Card>

            <Card>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">Pending Payments</CardTitle>
                <Clock className="h-4 w-4 text-yellow-600" />
              </CardHeader>
              <CardContent>
                <div className="text-2xl font-bold">${statistics.pending_payments?.toLocaleString() || 0}</div>
                <p className="text-xs text-muted-foreground">
                  {statistics.partial_invoices || 0} partial payments
                </p>
              </CardContent>
            </Card>

            <Card>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">Overdue Amount</CardTitle>
                <AlertCircle className="h-4 w-4 text-red-600" />
              </CardHeader>
              <CardContent>
                <div className="text-2xl font-bold text-red-600">
                  ${statistics.overdue_amount?.toLocaleString() || 0}
                </div>
                <p className="text-xs text-muted-foreground">
                  {statistics.overdue_invoices || 0} overdue invoices
                </p>
              </CardContent>
            </Card>

            <Card>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">Paid Invoices</CardTitle>
                <CheckCircle className="h-4 w-4 text-green-600" />
              </CardHeader>
              <CardContent>
                <div className="text-2xl font-bold">{statistics.paid_invoices || 0}</div>
                <p className="text-xs text-muted-foreground">
                  {statistics.total_students || 0} total students
                </p>
              </CardContent>
            </Card>
          </div>
        )}

        {/* Tabs for Different Sections */}
        <Tabs defaultValue="structures" className="space-y-4">
          <TabsList>
            <TabsTrigger value="structures" className="gap-2">
              <FileText className="h-4 w-4" />
              Fee Structures
            </TabsTrigger>
            <TabsTrigger value="invoices" className="gap-2">
              <DollarSign className="h-4 w-4" />
              Invoices
            </TabsTrigger>
            <TabsTrigger value="payments" className="gap-2">
              <CreditCard className="h-4 w-4" />
              Payments
            </TabsTrigger>
          </TabsList>

          <TabsContent value="structures">
            <FeeStructuresTab onUpdate={fetchStatistics} />
          </TabsContent>

          <TabsContent value="invoices">
            <InvoicesTab onUpdate={fetchStatistics} />
          </TabsContent>

          <TabsContent value="payments">
            <PaymentsTab onUpdate={fetchStatistics} />
          </TabsContent>
        </Tabs>
      </div>
    </DashboardLayout>
  );
}
