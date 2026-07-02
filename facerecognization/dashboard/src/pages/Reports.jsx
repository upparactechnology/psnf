import React, { useEffect, useState } from 'react';
import {
  Container, Typography, Box, TextField, Button, Table, TableBody, TableCell, TableContainer, TableHead, TableRow, Paper,
  Alert, CircularProgress, MenuItem, Select, FormControl, InputLabel
} from '@mui/material';
import { FileDownload, Assessment, Refresh } from '@mui/icons-material';
import api from '../services/api';

const Reports = () => {
  const [date, setDate] = useState(new Date().toISOString().split('T')[0]);
  const [records, setRecords] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [exporting, setExporting] = useState(false);

  const fetchReport = async () => {
    setLoading(true);
    setError(null);
    try {
      const res = await api.get(`/api/v1/reports/daily?date=${date}`);
      setRecords(res.data?.data || []);
    } catch (err) {
      setError('Failed to load reports. Make sure backend is running and date format is correct.');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchReport();
  }, [date]);

  const handleExport = async (format) => {
    setExporting(true);
    try {
      const res = await api.get(`/api/v1/reports/export?format=${format}&date=${date}`, {
        responseType: 'blob'
      });
      
      // Determine correct mime type
      let mimeType = 'text/csv';
      if (format === 'excel') mimeType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
      if (format === 'pdf') mimeType = 'application/pdf';

      const blob = new Blob([res.data], { type: mimeType });
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute('download', `attendance_report_${date}.${format === 'excel' ? 'xlsx' : format}`);
      document.body.appendChild(link);
      link.click();
      link.remove();
      window.URL.revokeObjectURL(url);
    } catch (err) {
      alert('Failed to export document. Verify connection.');
    } finally {
      setExporting(false);
    }
  };

  return (
    <Container maxWidth="lg">
      <Box display="flex" justifyContent="space-between" alignItems="center" mb={4}>
        <Typography variant="h5" sx={{ fontWeight: 'bold', color: '#1E293B' }}>
          Daily Attendance Records
        </Typography>
        <Box display="flex" gap={1.5}>
          <Button
            variant="outlined"
            startIcon={<FileDownload />}
            disabled={records.length === 0 || exporting}
            onClick={() => handleExport('csv')}
            sx={{ borderRadius: 2 }}
          >
            Export CSV
          </Button>
          <Button
            variant="outlined"
            startIcon={<FileDownload />}
            disabled={records.length === 0 || exporting}
            onClick={() => handleExport('excel')}
            sx={{ borderRadius: 2 }}
          >
            Export Excel
          </Button>
          <Button
            variant="contained"
            startIcon={<FileDownload />}
            disabled={records.length === 0 || exporting}
            onClick={() => handleExport('pdf')}
            sx={{
              background: 'linear-gradient(135deg, #005FAF 0%, #004c8c 100%)',
              borderRadius: 2,
            }}
          >
            Export PDF
          </Button>
        </Box>
      </Box>

      {error && <Alert severity="error" sx={{ mb: 3 }}>{error}</Alert>}

      {/* Date selector and filters */}
      <Box display="flex" gap={2} mb={4} alignItems="center">
        <TextField
          label="Target Date"
          type="date"
          value={date}
          size="small"
          onChange={(e) => setDate(e.target.value)}
          InputLabelProps={{ shrink: true }}
          sx={{ width: 220, bgcolor: '#FFFFFF', borderRadius: 1 }}
        />
        <IconButton onClick={fetchReport} sx={{ bgcolor: '#FFFFFF', border: '1px solid #E2E8F0', p: 1.2 }}>
          <Refresh />
        </IconButton>
      </Box>

      {/* Logs Table */}
      {loading ? (
        <Box display="flex" justifyContent="center" py={8}><CircularProgress /></Box>
      ) : (
        <TableContainer component={Paper} sx={{ borderRadius: 3, border: '1px solid #E2E8F0', boxShadow: 'none' }}>
          <Table>
            <TableHead sx={{ bgcolor: '#F8FAFC' }}>
              <TableRow>
                <TableCell sx={{ fontWeight: 'bold' }}>Employee ID</TableCell>
                <TableCell sx={{ fontWeight: 'bold' }}>Employee Name</TableCell>
                <TableCell sx={{ fontWeight: 'bold' }}>Check-In</TableCell>
                <TableCell sx={{ fontWeight: 'bold' }}>Check-Out</TableCell>
                <TableCell sx={{ fontWeight: 'bold' }}>Status</TableCell>
                <TableCell sx={{ fontWeight: 'bold' }}>Hours Worked</TableCell>
              </TableRow>
            </TableHead>
            <TableBody>
              {records.length === 0 ? (
                <TableRow>
                  <TableCell colSpan={6} align="center" sx={{ py: 6, color: 'text.secondary' }}>
                    No attendance logs registered for this date.
                  </TableCell>
                </TableRow>
              ) : (
                records.map((row, index) => (
                  <TableRow key={index} hover>
                    <TableCell sx={{ fontWeight: 'bold', color: '#475569' }}>{row.employee_id}</TableCell>
                    <TableCell>{row.employee_name}</TableCell>
                    <TableCell>{row.check_in}</TableCell>
                    <TableCell>{row.check_out}</TableCell>
                    <TableCell>
                      <Box
                        sx={{
                          display: 'inline-block',
                          px: 1.5,
                          py: 0.5,
                          borderRadius: 2,
                          fontSize: '0.75rem',
                          fontWeight: 'bold',
                          bgcolor:
                            row.status === 'PRESENT' || row.status === 'REGULAR'
                              ? '#E8F5E9'
                              : row.status === 'LATE'
                              ? '#FFF8E1'
                              : row.status === 'ABSENT'
                              ? '#FFEBEE'
                              : '#ECEFF1',
                          color:
                            row.status === 'PRESENT' || row.status === 'REGULAR'
                              ? '#2E7D32'
                              : row.status === 'LATE'
                              ? '#F57F17'
                              : row.status === 'ABSENT'
                              ? '#C62828'
                              : '#455A64',
                        }}
                      >
                        {row.status}
                      </Box>
                    </TableCell>
                    <TableCell sx={{ fontWeight: 'bold', color: '#1E293B' }}>
                      {row.total_hours} hrs
                    </TableCell>
                  </TableRow>
                ))
              )}
            </TableBody>
          </Table>
        </TableContainer>
      )}
    </Container>
  );
};

// Add standard IconButton import wrapper for MUI
import { IconButton } from '@mui/material';

export default Reports;
