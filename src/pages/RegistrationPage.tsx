import { useEffect, useState } from 'react';
import { FileText, Upload, Download, CheckCircle, AlertCircle, Clock, DollarSign } from 'lucide-react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { registrationApi, Registration, Insurance, Invoice, Payment } from '@/lib/api/registrationApi';
import { useToast } from '@/hooks/use-toast';

export default function RegistrationPage() {
  const [loading, setLoading] = useState(true);
  const [currentRegistration, setCurrentRegistration] = useState<Registration | null>(null);
  const [registrationHistory, setRegistrationHistory] = useState<Registration[]>([]);
  const [insurance, setInsurance] = useState<Insurance | null>(null);
  const [invoices, setInvoices] = useState<Invoice[]>([]);
  const [payments, setPayments] = useState<Payment[]>([]);
  const [totalPaid, setTotalPaid] = useState('0.00');
  const [uploadingInsurance, setUploadingInsurance] = useState(false);
  const { toast } = useToast();

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    setLoading(true);
    try {
      // Fetch all data in parallel
      const [historyData, insuranceData, invoicesData, paymentsData] = await Promise.all([
        registrationApi.getRegistrationHistory(),
        registrationApi.getInsuranceStatus().catch(() => null),
        registrationApi.getInvoices(),
        registrationApi.getPaymentHistory(),
      ]);

      setRegistrationHistory(historyData.registrations);
      setCurrentRegistration(historyData.registrations[0] || null);
      setInsurance(insuranceData);
      setInvoices(invoicesData);
      setPayments(paymentsData.payments);
      setTotalPaid(paymentsData.total_paid);
    } catch (error) {
      console.error('Error fetching registration data:', error);
      toast({
        title: 'Error',
        description: 'Failed to load registration data. Please try again.',
        variant: 'destructive',
      });
    } finally {
      setLoading(false);
    }
  };

  const handleInsuranceUpload = async (event: React.ChangeEvent<HTMLInputElement>) => {
    const file = event.target.files?.[0];
    if (!file || !currentRegistration) return;

    setUploadingInsurance(true);
    try {
      await registrationApi.uploadInsurance(file, currentRegistration.semester_code);
      toast({
        title: 'Success',
        description: 'Insurance document uploaded successfully',
      });
      fetchData();
    } catch (error) {
      toast({
        title: 'Error',
        description: 'Failed to upload insurance document',
        variant: 'destructive',
      });
    } finally {
      setUploadingInsurance(false);
    }
  };

  const handleDownloadInvoice = async (invoiceId: number) => {
    try {
      const blob = await registrationApi.downloadInvoice(invoiceId);
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `invoice-${invoiceId}.pdf`;
      document.body.appendChild(a);
      a.click();
      window.URL.revokeObjectURL(url);
      document.body.removeChild(a);
    } catch (error) {
      toast({
        title: 'Error',
        description: 'Failed to download invoice',
        variant: 'destructive',
      });
    }
  };

  const getStatusBadge = (status: string) => {
    const variants: Record<string, 'default' | 'secondary' | 'destructive'> = {
      verified: 'default',
      pending: 'secondary',
      rejected: 'destructive',
    };
    return <Badge variant={variants[status] || 'secondary'}>{status}</Badge>;
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto"></div>
          <p className="mt-4 text-muted-foreground">Loading registration data...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="container mx-auto p-6 max-w-7xl">
      <div className="mb-8">
        <h1 className="text-3xl font-bold mb-2">Registration & Fees</h1>
        <p className="text-muted-foreground">
          Manage your semester registration, insurance, and fee payments
        </p>
      </div>

      {/* Current Registration Summary */}
      {currentRegistration && (
        <div className="grid gap-6 mb-6 md:grid-cols-3">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Registration Status</CardTitle>
              <FileText className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold mb-2">
                {getStatusBadge(currentRegistration.status)}
              </div>
              <p className="text-xs text-muted-foreground">
                {currentRegistration.semester_code} • {currentRegistration.academic_year}
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Payment Progress</CardTitle>
              <DollarSign className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold mb-2">
                {currentRegistration.payment_percentage}%
              </div>
              <Progress value={currentRegistration.payment_percentage} className="mb-2" />
              <p className="text-xs text-muted-foreground">
                ₦{currentRegistration.amount_paid} of ₦{currentRegistration.total_fees}
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Balance Due</CardTitle>
              <AlertCircle className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold mb-2">₦{currentRegistration.balance}</div>
              <p className="text-xs text-muted-foreground">
                {parseFloat(currentRegistration.balance) > 0 ? 'Payment required' : 'Fully paid'}
              </p>
            </CardContent>
          </Card>
        </div>
      )}

      {/* Tabs */}
      <Tabs defaultValue="overview" className="space-y-4">
        <TabsList>
          <TabsTrigger value="overview">Overview</TabsTrigger>
          <TabsTrigger value="insurance">Insurance</TabsTrigger>
          <TabsTrigger value="invoices">Invoices</TabsTrigger>
          <TabsTrigger value="payments">Payments</TabsTrigger>
          <TabsTrigger value="history">History</TabsTrigger>
        </TabsList>

        {/* Overview Tab */}
        <TabsContent value="overview" className="space-y-4">
          {currentRegistration && (
            <Card>
              <CardHeader>
                <CardTitle>Current Semester Registration</CardTitle>
                <CardDescription>
                  {currentRegistration.semester_code} - {currentRegistration.academic_year}
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="grid md:grid-cols-2 gap-4">
                  <div>
                    <Label>Registration Date</Label>
                    <p className="text-sm">
                      {new Date(currentRegistration.registration_date).toLocaleDateString()}
                    </p>
                  </div>
                  <div>
                    <Label>Status</Label>
                    <div>{getStatusBadge(currentRegistration.status)}</div>
                  </div>
                  <div>
                    <Label>Fees Verified</Label>
                    <div className="flex items-center gap-2">
                      {currentRegistration.fees_verified ? (
                        <CheckCircle className="h-4 w-4 text-green-500" />
                      ) : (
                        <Clock className="h-4 w-4 text-yellow-500" />
                      )}
                      <span className="text-sm">
                        {currentRegistration.fees_verified ? 'Verified' : 'Pending'}
                      </span>
                    </div>
                  </div>
                  <div>
                    <Label>Insurance Verified</Label>
                    <div className="flex items-center gap-2">
                      {currentRegistration.insurance_verified ? (
                        <CheckCircle className="h-4 w-4 text-green-500" />
                      ) : (
                        <Clock className="h-4 w-4 text-yellow-500" />
                      )}
                      <span className="text-sm">
                        {currentRegistration.insurance_verified ? 'Verified' : 'Pending'}
                      </span>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>
          )}
        </TabsContent>

        {/* Insurance Tab */}
        <TabsContent value="insurance" className="space-y-4">
          <Card>
            <CardHeader>
              <CardTitle>Student Insurance</CardTitle>
              <CardDescription>Upload and manage your insurance documents</CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              {insurance ? (
                <div className="space-y-4">
                  <Alert>
                    <CheckCircle className="h-4 w-4" />
                    <AlertDescription>
                      Your insurance is {insurance.status} and{' '}
                      {insurance.is_expired ? 'has expired' : `expires in ${insurance.days_until_expiry} days`}
                    </AlertDescription>
                  </Alert>

                  <div className="grid md:grid-cols-2 gap-4">
                    <div>
                      <Label>Provider</Label>
                      <p className="text-sm">{insurance.provider}</p>
                    </div>
                    <div>
                      <Label>Policy Number</Label>
                      <p className="text-sm">{insurance.policy_number}</p>
                    </div>
                    <div>
                      <Label>Expiry Date</Label>
                      <p className="text-sm">
                        {new Date(insurance.expiry_date).toLocaleDateString()}
                      </p>
                    </div>
                    <div>
                      <Label>Status</Label>
                      <div>{getStatusBadge(insurance.status)}</div>
                    </div>
                  </div>
                </div>
              ) : (
                <Alert>
                  <AlertCircle className="h-4 w-4" />
                  <AlertDescription>
                    No insurance document found. Please upload your insurance document.
                  </AlertDescription>
                </Alert>
              )}

              <div className="border-2 border-dashed rounded-lg p-6">
                <div className="flex flex-col items-center text-center">
                  <Upload className="h-10 w-10 text-muted-foreground mb-4" />
                  <h3 className="font-semibold mb-2">Upload Insurance Document</h3>
                  <p className="text-sm text-muted-foreground mb-4">
                    Supported formats: PDF, JPG, PNG (Max 5MB)
                  </p>
                  <Input
                    type="file"
                    accept=".pdf,.jpg,.jpeg,.png"
                    onChange={handleInsuranceUpload}
                    disabled={uploadingInsurance}
                    className="max-w-xs"
                  />
                </div>
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        {/* Invoices Tab */}
        <TabsContent value="invoices" className="space-y-4">
          <Card>
            <CardHeader>
              <CardTitle>Invoices</CardTitle>
              <CardDescription>View and download your fee invoices</CardDescription>
            </CardHeader>
            <CardContent>
              {invoices.length > 0 ? (
                <div className="space-y-2">
                  {invoices.map((invoice) => (
                    <div
                      key={invoice.id}
                      className="flex items-center justify-between p-4 border rounded-lg"
                    >
                      <div>
                        <p className="font-medium">{invoice.invoice_number}</p>
                        <p className="text-sm text-muted-foreground">
                          {invoice.semester} • ₦{invoice.amount}
                        </p>
                        <p className="text-xs text-muted-foreground">
                          Due: {new Date(invoice.due_date).toLocaleDateString()}
                        </p>
                      </div>
                      <div className="flex items-center gap-2">
                        {getStatusBadge(invoice.status)}
                        <Button
                          variant="outline"
                          size="sm"
                          onClick={() => handleDownloadInvoice(invoice.id)}
                        >
                          <Download className="h-4 w-4 mr-2" />
                          Download
                        </Button>
                      </div>
                    </div>
                  ))}
                </div>
              ) : (
                <p className="text-center text-muted-foreground py-8">No invoices found</p>
              )}
            </CardContent>
          </Card>
        </TabsContent>

        {/* Payments Tab */}
        <TabsContent value="payments" className="space-y-4">
          <Card>
            <CardHeader>
              <CardTitle>Payment History</CardTitle>
              <CardDescription>
                Total Paid: <span className="font-bold">₦{totalPaid}</span>
              </CardDescription>
            </CardHeader>
            <CardContent>
              {payments.length > 0 ? (
                <div className="space-y-2">
                  {payments.map((payment) => (
                    <div key={payment.id} className="flex items-center justify-between p-4 border rounded-lg">
                      <div>
                        <p className="font-medium">₦{payment.amount}</p>
                        <p className="text-sm text-muted-foreground">
                          {payment.payment_method.replace('_', ' ').toUpperCase()}
                        </p>
                        <p className="text-xs text-muted-foreground">
                          Ref: {payment.reference_number}
                        </p>
                      </div>
                      <div className="text-right">
                        {getStatusBadge(payment.status)}
                        <p className="text-xs text-muted-foreground mt-1">
                          {new Date(payment.payment_date).toLocaleDateString()}
                        </p>
                      </div>
                    </div>
                  ))}
                </div>
              ) : (
                <p className="text-center text-muted-foreground py-8">No payments found</p>
              )}
            </CardContent>
          </Card>
        </TabsContent>

        {/* History Tab */}
        <TabsContent value="history" className="space-y-4">
          <Card>
            <CardHeader>
              <CardTitle>Registration History</CardTitle>
              <CardDescription>View all your past registrations</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-2">
                {registrationHistory.map((reg) => (
                  <div key={reg.id} className="p-4 border rounded-lg">
                    <div className="flex items-center justify-between mb-2">
                      <p className="font-medium">{reg.semester_code}</p>
                      {getStatusBadge(reg.status)}
                    </div>
                    <div className="grid grid-cols-2 md:grid-cols-4 gap-2 text-sm">
                      <div>
                        <span className="text-muted-foreground">Total Fees:</span> ₦{reg.total_fees}
                      </div>
                      <div>
                        <span className="text-muted-foreground">Paid:</span> ₦{reg.amount_paid}
                      </div>
                      <div>
                        <span className="text-muted-foreground">Balance:</span> ₦{reg.balance}
                      </div>
                      <div>
                        <span className="text-muted-foreground">Progress:</span> {reg.payment_percentage}%
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  );
}
