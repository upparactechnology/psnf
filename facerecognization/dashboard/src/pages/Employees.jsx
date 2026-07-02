import React, { useEffect, useState } from 'react';
import {
  Container, Typography, Box, Table, TableBody, TableCell, TableContainer, TableHead, TableRow, Paper, Button,
  Dialog, DialogTitle, DialogContent, DialogActions, TextField, Alert, IconButton, CircularProgress, Tooltip
} from '@mui/material';
import { Add, Edit, Delete, Face, Search, Refresh } from '@mui/icons-material';
import api from '../services/api';

const Employees = () => {
  const [employees, setEmployees] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [search, setSearch] = useState('');
  
  // Dialog controls
  const [openAdd, setOpenAdd] = useState(false);
  const [openEnroll, setOpenEnroll] = useState(false);
  const [selectedEmp, setSelectedEmp] = useState(null);
  
  // Forms
  const [empId, setEmpId] = useState('');
  const [firstName, setFirstName] = useState('');
  const [lastName, setLastName] = useState('');
  const [email, setEmail] = useState('');
  const [phone, setPhone] = useState('');
  const [actionLoading, setActionLoading] = useState(false);
  const [formError, setFormError] = useState(null);

  const fetchEmployees = async () => {
    setLoading(true);
    setError(null);
    try {
      const res = await api.get(`/api/v1/employees?search=${search}`);
      setEmployees(res.data.items || []);
    } catch (err) {
      setError('Failed to fetch employee directory. Please check database connection.');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchEmployees();
  }, [search]);

  const handleAddEmployee = async (e) => {
    e.preventDefault();
    setActionLoading(true);
    setFormError(null);
    try {
      await api.post('/api/v1/employees', {
        employee_id: empId,
        first_name: firstName,
        last_name: lastName,
        email: email,
        phone: phone || null,
      });
      setOpenAdd(false);
      // Reset form
      setEmpId('');
      setFirstName('');
      setLastName('');
      setEmail('');
      setPhone('');
      fetchEmployees();
    } catch (err) {
      setFormError(err.response?.data?.detail || 'Failed to save employee profile.');
    } finally {
      setActionLoading(false);
    }
  };

  const handleDeleteEmployee = async (id) => {
    if (!window.confirm('Are you sure you want to remove this employee profile? All attendance history and face logs will be deleted.')) {
      return;
    }
    try {
      await api.delete(`/api/v1/employees/${id}`);
      fetchEmployees();
    } catch (err) {
      alert('Failed to delete employee profile.');
    }
  };

  const handleEnrollFace = async () => {
    if (!selectedEmp) return;
    setActionLoading(true);
    setFormError(null);
    
    // Simulate generation of 512-dim face embeddings for multiple angles as specified in the docs
    const angles = ['STRAIGHT', 'TURN_LEFT', 'TURN_RIGHT', 'LOOK_UP', 'LOOK_DOWN'];
    const mockEmbeddings = angles.map(angle => ({
      angle,
      vector: Array(512).fill(0.0125)
    }));

    try {
      await api.post(`/api/v1/employees/${selectedEmp.id}/enroll`, {
        embeddings: mockEmbeddings
      });
      setOpenEnroll(false);
      fetchEmployees();
      alert(`Face templates successfully registered for ${selectedEmp.first_name}!`);
    } catch (err) {
      setFormError(err.response?.data?.detail || 'Failed to enroll face embeddings.');
    } finally {
      setActionLoading(false);
    }
  };

  return (
    <Container maxWidth="lg">
      <Box display="flex" justifyContent="space-between" alignItems="center" mb={4}>
        <Typography variant="h5" sx={{ fontWeight: 'bold', color: '#1E293B' }}>
          Staff Directory
        </Typography>
        <Button
          variant="contained"
          startIcon={<Add />}
          onClick={() => setOpenAdd(true)}
          sx={{
            background: 'linear-gradient(135deg, #005FAF 0%, #004c8c 100%)',
            borderRadius: 2,
            px: 3,
          }}
        >
          Add Employee
        </Button>
      </Box>

      {error && <Alert severity="error" sx={{ mb: 3 }}>{error}</Alert>}

      {/* Search and filter bar */}
      <Box display="flex" gap={2} mb={3}>
        <TextField
          placeholder="Search by name or Employee ID..."
          size="small"
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          sx={{ flexGrow: 1, bgcolor: '#FFFFFF', borderRadius: 1 }}
          InputProps={{
            startAdornment: <Search sx={{ color: 'text.secondary', mr: 1 }} />,
          }}
        />
        <IconButton onClick={fetchEmployees} sx={{ bgcolor: '#FFFFFF', border: '1px solid #E2E8F0' }}>
          <Refresh />
        </IconButton>
      </Box>

      {/* Employee List Table */}
      {loading ? (
        <Box display="flex" justifyContent="center" py={8}><CircularProgress /></Box>
      ) : (
        <TableContainer component={Paper} sx={{ borderRadius: 3, border: '1px solid #E2E8F0', boxShadow: 'none' }}>
          <Table>
            <TableHead sx={{ bgcolor: '#F8FAFC' }}>
              <TableRow>
                <TableCell sx={{ fontWeight: 'bold' }}>Employee ID</TableCell>
                <TableCell sx={{ fontWeight: 'bold' }}>Name</TableCell>
                <TableCell sx={{ fontWeight: 'bold' }}>Email</TableCell>
                <TableCell sx={{ fontWeight: 'bold' }}>Phone</TableCell>
                <TableCell sx={{ fontWeight: 'bold' }}>Status</TableCell>
                <TableCell sx={{ fontWeight: 'bold' }}>Actions</TableCell>
              </TableRow>
            </TableHead>
            <TableBody>
              {employees.length === 0 ? (
                <TableRow>
                  <TableCell colSpan={6} align="center" sx={{ py: 6, color: 'text.secondary' }}>
                    No employees found. Add staff to start.
                  </TableCell>
                </TableRow>
              ) : (
                employees.map((emp) => (
                  <TableRow key={emp.id} hover>
                    <TableCell sx={{ fontWeight: 'bold', color: '#475569' }}>{emp.employee_id}</TableCell>
                    <TableCell>{`${emp.first_name} ${emp.last_name}`}</TableCell>
                    <TableCell>{emp.email}</TableCell>
                    <TableCell>{emp.phone || 'N/A'}</TableCell>
                    <TableCell>
                      <Box
                        sx={{
                          display: 'inline-block',
                          px: 1.5,
                          py: 0.5,
                          borderRadius: 2,
                          fontSize: '0.75rem',
                          fontWeight: 'bold',
                          bgcolor: emp.status === 'ACTIVE' ? '#E8F5E9' : '#FFEBEE',
                          color: emp.status === 'ACTIVE' ? '#2E7D32' : '#C62828',
                        }}
                      >
                        {emp.status}
                      </Box>
                    </TableCell>
                    <TableCell>
                      <Box display="flex" gap={1}>
                        <Tooltip title="Enroll Face Template">
                          <IconButton
                            color="primary"
                            onClick={() => {
                              setSelectedEmp(emp);
                              setOpenEnroll(true);
                            }}
                          >
                            <Face />
                          </IconButton>
                        </Tooltip>
                        <Tooltip title="Delete Profile">
                          <IconButton color="error" onClick={() => handleDeleteEmployee(emp.id)}>
                            <Delete />
                          </IconButton>
                        </Tooltip>
                      </Box>
                    </TableCell>
                  </TableRow>
                ))
              )}
            </TableBody>
          </Table>
        </TableContainer>
      )}

      {/* Add Employee Dialog */}
      <Dialog open={openAdd} onClose={() => setOpenAdd(false)} fullWidth maxWidth="xs">
        <form onSubmit={handleAddEmployee}>
          <DialogTitle sx={{ fontWeight: 'bold' }}>Add New Employee</DialogTitle>
          <DialogContent>
            {formError && <Alert severity="error" sx={{ mb: 2 }}>{formError}</Alert>}
            <TextField
              margin="dense"
              label="Employee ID (e.g. EMP001)"
              fullWidth
              required
              value={empId}
              onChange={(e) => setEmpId(e.target.value)}
            />
            <TextField
              margin="dense"
              label="First Name"
              fullWidth
              required
              value={firstName}
              onChange={(e) => setFirstName(e.target.value)}
            />
            <TextField
              margin="dense"
              label="Last Name"
              fullWidth
              required
              value={lastName}
              onChange={(e) => setLastName(e.target.value)}
            />
            <TextField
              margin="dense"
              label="Email Address"
              type="email"
              fullWidth
              required
              value={email}
              onChange={(e) => setEmail(e.target.value)}
            />
            <TextField
              margin="dense"
              label="Phone Number"
              fullWidth
              value={phone}
              onChange={(e) => setPhone(e.target.value)}
            />
          </DialogContent>
          <DialogActions sx={{ p: 3 }}>
            <Button onClick={() => setOpenAdd(false)} color="inherit">Cancel</Button>
            <Button type="submit" variant="contained" disabled={actionLoading}>
              {actionLoading ? <CircularProgress size={24} /> : 'Save Profile'}
            </Button>
          </DialogActions>
        </form>
      </Dialog>

      {/* Enroll Face Dialog */}
      <Dialog open={openEnroll} onClose={() => setOpenEnroll(false)} fullWidth maxWidth="xs">
        <DialogTitle sx={{ fontWeight: 'bold' }}>Face Enrollment</DialogTitle>
        <DialogContent>
          {formError && <Alert severity="error" sx={{ mb: 2 }}>{formError}</Alert>}
          <Typography variant="body1" mb={2}>
            Registering face embeddings for <strong>{selectedEmp?.first_name} {selectedEmp?.last_name}</strong>.
          </Typography>
          <Typography variant="body2" color="text.secondary">
            This will generate 5 face vectors captured from multiple angles: Straight, Turn Left, Turn Right, Look Up, and Look Down.
          </Typography>
        </DialogContent>
        <DialogActions sx={{ p: 3 }}>
          <Button onClick={() => setOpenEnroll(false)} color="inherit">Cancel</Button>
          <Button onClick={handleEnrollFace} variant="contained" color="success" disabled={actionLoading}>
            {actionLoading ? <CircularProgress size={24} /> : 'Start Simulation'}
          </Button>
        </DialogActions>
      </Dialog>
    </Container>
  );
};

export default Employees;
