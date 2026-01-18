import { useState, useEffect } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Plus, Pencil, Trash2, CheckCircle, XCircle } from 'lucide-react';
import { feeManagementApi, FeeStructure } from '@/lib/api/feeManagementApi';
import { useToast } from '@/hooks/use-toast';

interface FeeStructuresTabProps {
  onUpdate?: () => void;
}

export default function FeeStructuresTab({ onUpdate }: FeeStructuresTabProps) {
  const [feeStructures, setFeeStructures] = useState<FeeStructure[]>([]);
  const [loading, setLoading] = useState(false);
  const [showCreateDialog, setShowCreateDialog] = useState(false);
  const [showEditDialog, setShowEditDialog] = useState(false);
  const [showDeleteDialog, setShowDeleteDialog] = useState(false);
  const [selectedStructure, setSelectedStructure] = useState<FeeStructure | null>(null);

  const [formData, setFormData] = useState({
    program: '',
    semester: '',
    amount: '',
    due_date: '',
    fee_type: 'tuition',
    description: '',
    status: 'active'
  });

  const { toast } = useToast();

  useEffect(() => {
    fetchFeeStructures();
  }, []);

  const fetchFeeStructures = async () => {
    setLoading(true);
    try {
      const response = await feeManagementApi.getFeeStructures();
      if (response.success) {
        const data = Array.isArray(response.data) ? response.data : (response.data?.data || []);
        setFeeStructures(data);
      }
    } catch (error: any) {
      console.error('Failed to fetch fee structures:', error);
      toast({
        title: 'Error',
        description: error.message || 'Failed to fetch fee structures',
        variant: 'destructive',
      });
      setFeeStructures([]);
    } finally {
      setLoading(false);
    }
  };

  const handleCreate = async () => {
    try {
      const data = {
        program: formData.program,
        semester: parseInt(formData.semester),
        amount: parseFloat(formData.amount),
        due_date: formData.due_date,
        fee_type: formData.fee_type,
        description: formData.description || undefined,
        status: formData.status as 'active' | 'inactive'
      };

      const response = await feeManagementApi.createFeeStructure(data);
      if (response.success) {
        toast({
          title: 'Success',
          description: 'Fee structure created successfully',
        });
        setShowCreateDialog(false);
        resetForm();
        fetchFeeStructures();
        onUpdate?.();
      }
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.message || 'Failed to create fee structure',
        variant: 'destructive',
      });
    }
  };

  const handleUpdate = async () => {
    if (!selectedStructure) return;

    try {
      const data = {
        program: formData.program,
        semester: parseInt(formData.semester),
        amount: parseFloat(formData.amount),
        due_date: formData.due_date,
        fee_type: formData.fee_type,
        description: formData.description || undefined,
        status: formData.status as 'active' | 'inactive'
      };

      const response = await feeManagementApi.updateFeeStructure(selectedStructure.id, data);
      if (response.success) {
        toast({
          title: 'Success',
          description: 'Fee structure updated successfully',
        });
        setShowEditDialog(false);
        setSelectedStructure(null);
        resetForm();
        fetchFeeStructures();
        onUpdate?.();
      }
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.message || 'Failed to update fee structure',
        variant: 'destructive',
      });
    }
  };

  const handleDelete = async () => {
    if (!selectedStructure) return;

    try {
      const response = await feeManagementApi.deleteFeeStructure(selectedStructure.id);
      if (response.success) {
        toast({
          title: 'Success',
          description: 'Fee structure deleted successfully',
        });
        setShowDeleteDialog(false);
        setSelectedStructure(null);
        fetchFeeStructures();
        onUpdate?.();
      }
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.message || 'Failed to delete fee structure',
        variant: 'destructive',
      });
    }
  };

  const openEditDialog = (structure: FeeStructure) => {
    setSelectedStructure(structure);
    setFormData({
      program: structure.program,
      semester: structure.semester.toString(),
      amount: structure.amount.toString(),
      due_date: structure.due_date,
      fee_type: structure.fee_type,
      description: structure.description || '',
      status: structure.status
    });
    setShowEditDialog(true);
  };

  const resetForm = () => {
    setFormData({
      program: '',
      semester: '',
      amount: '',
      due_date: '',
      fee_type: 'tuition',
      description: '',
      status: 'active'
    });
  };

  const getStatusBadge = (status: string) => {
    return status === 'active' ? (
      <Badge variant="default"><CheckCircle className="mr-1 h-3 w-3" />Active</Badge>
    ) : (
      <Badge variant="secondary"><XCircle className="mr-1 h-3 w-3" />Inactive</Badge>
    );
  };

  return (
    <Card>
      <CardHeader>
        <div className="flex justify-between items-center">
          <div>
            <CardTitle>Fee Structures</CardTitle>
            <CardDescription>Manage fee structures for different programs and semesters</CardDescription>
          </div>
          <Dialog open={showCreateDialog} onOpenChange={setShowCreateDialog}>
            <DialogTrigger asChild>
              <Button onClick={() => { resetForm(); setShowCreateDialog(true); }}>
                <Plus className="mr-2 h-4 w-4" />
                Add Fee Structure
              </Button>
            </DialogTrigger>
            <DialogContent className="max-w-2xl">
              <DialogHeader>
                <DialogTitle>Create Fee Structure</DialogTitle>
                <DialogDescription>Add a new fee structure</DialogDescription>
              </DialogHeader>
              <FeeStructureForm
                formData={formData}
                setFormData={setFormData}
                onSubmit={handleCreate}
                onCancel={() => setShowCreateDialog(false)}
              />
            </DialogContent>
          </Dialog>
        </div>
      </CardHeader>
      <CardContent>
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Program</TableHead>
              <TableHead>Semester</TableHead>
              <TableHead>Fee Type</TableHead>
              <TableHead>Amount</TableHead>
              <TableHead>Due Date</TableHead>
              <TableHead>Status</TableHead>
              <TableHead>Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {loading ? (
              <TableRow>
                <TableCell colSpan={7} className="text-center py-8">
                  Loading fee structures...
                </TableCell>
              </TableRow>
            ) : feeStructures.length === 0 ? (
              <TableRow>
                <TableCell colSpan={7} className="text-center text-muted-foreground">
                  No fee structures found
                </TableCell>
              </TableRow>
            ) : (
              feeStructures.map((structure) => (
                <TableRow key={structure.id}>
                  <TableCell className="font-medium">{structure.program}</TableCell>
                  <TableCell>{structure.semester}</TableCell>
                  <TableCell className="capitalize">{structure.fee_type}</TableCell>
                  <TableCell className="font-semibold">${structure.amount.toLocaleString()}</TableCell>
                  <TableCell>{new Date(structure.due_date).toLocaleDateString()}</TableCell>
                  <TableCell>{getStatusBadge(structure.status)}</TableCell>
                  <TableCell>
                    <div className="flex gap-2">
                      <Button
                        size="sm"
                        variant="outline"
                        onClick={() => openEditDialog(structure)}
                      >
                        <Pencil className="h-3 w-3" />
                      </Button>
                      <Button
                        size="sm"
                        variant="destructive"
                        onClick={() => {
                          setSelectedStructure(structure);
                          setShowDeleteDialog(true);
                        }}
                      >
                        <Trash2 className="h-3 w-3" />
                      </Button>
                    </div>
                  </TableCell>
                </TableRow>
              ))
            )}
          </TableBody>
        </Table>
      </CardContent>

      {/* Edit Dialog */}
      <Dialog open={showEditDialog} onOpenChange={setShowEditDialog}>
        <DialogContent className="max-w-2xl">
          <DialogHeader>
            <DialogTitle>Edit Fee Structure</DialogTitle>
            <DialogDescription>Update fee structure information</DialogDescription>
          </DialogHeader>
          <FeeStructureForm
            formData={formData}
            setFormData={setFormData}
            onSubmit={handleUpdate}
            onCancel={() => setShowEditDialog(false)}
          />
        </DialogContent>
      </Dialog>

      {/* Delete Confirmation Dialog */}
      <Dialog open={showDeleteDialog} onOpenChange={setShowDeleteDialog}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Delete Fee Structure</DialogTitle>
            <DialogDescription>
              Are you sure you want to delete this fee structure? This action cannot be undone.
            </DialogDescription>
          </DialogHeader>
          <div className="flex justify-end gap-2 mt-4">
            <Button variant="outline" onClick={() => setShowDeleteDialog(false)}>
              Cancel
            </Button>
            <Button variant="destructive" onClick={handleDelete}>
              Delete
            </Button>
          </div>
        </DialogContent>
      </Dialog>
    </Card>
  );
}

// Fee Structure Form Component
function FeeStructureForm({
  formData,
  setFormData,
  onSubmit,
  onCancel
}: {
  formData: any;
  setFormData: (data: any) => void;
  onSubmit: () => void;
  onCancel: () => void;
}) {
  return (
    <div className="space-y-4">
      <div className="grid grid-cols-2 gap-4">
        <div className="space-y-2">
          <Label>Program *</Label>
          <Input
            value={formData.program}
            onChange={(e) => setFormData({ ...formData, program: e.target.value })}
            placeholder="Computer Science"
          />
        </div>
        <div className="space-y-2">
          <Label>Semester *</Label>
          <Select
            value={formData.semester}
            onValueChange={(value) => setFormData({ ...formData, semester: value })}
          >
            <SelectTrigger>
              <SelectValue placeholder="Select semester" />
            </SelectTrigger>
            <SelectContent>
              {[1, 2, 3, 4, 5, 6, 7, 8].map(sem => (
                <SelectItem key={sem} value={sem.toString()}>Semester {sem}</SelectItem>
              ))}
            </SelectContent>
          </Select>
        </div>
      </div>

      <div className="grid grid-cols-2 gap-4">
        <div className="space-y-2">
          <Label>Fee Type *</Label>
          <Select
            value={formData.fee_type}
            onValueChange={(value) => setFormData({ ...formData, fee_type: value })}
          >
            <SelectTrigger>
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="tuition">Tuition</SelectItem>
              <SelectItem value="library">Library</SelectItem>
              <SelectItem value="lab">Laboratory</SelectItem>
              <SelectItem value="sports">Sports</SelectItem>
              <SelectItem value="medical">Medical</SelectItem>
              <SelectItem value="technology">Technology</SelectItem>
              <SelectItem value="other">Other</SelectItem>
            </SelectContent>
          </Select>
        </div>
        <div className="space-y-2">
          <Label>Amount ($) *</Label>
          <Input
            type="number"
            value={formData.amount}
            onChange={(e) => setFormData({ ...formData, amount: e.target.value })}
            placeholder="1500.00"
          />
        </div>
      </div>

      <div className="grid grid-cols-2 gap-4">
        <div className="space-y-2">
          <Label>Due Date *</Label>
          <Input
            type="date"
            value={formData.due_date}
            onChange={(e) => setFormData({ ...formData, due_date: e.target.value })}
          />
        </div>
        <div className="space-y-2">
          <Label>Status</Label>
          <Select
            value={formData.status}
            onValueChange={(value) => setFormData({ ...formData, status: value })}
          >
            <SelectTrigger>
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="active">Active</SelectItem>
              <SelectItem value="inactive">Inactive</SelectItem>
            </SelectContent>
          </Select>
        </div>
      </div>

      <div className="space-y-2">
        <Label>Description</Label>
        <Textarea
          value={formData.description}
          onChange={(e) => setFormData({ ...formData, description: e.target.value })}
          placeholder="Fee structure description..."
          rows={3}
        />
      </div>

      <div className="flex justify-end gap-2 pt-4">
        <Button variant="outline" onClick={onCancel}>
          Cancel
        </Button>
        <Button onClick={onSubmit}>
          Save Fee Structure
        </Button>
      </div>
    </div>
  );
}
