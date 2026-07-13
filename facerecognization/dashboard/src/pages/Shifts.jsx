import React, { useEffect, useState } from 'react';
import { Container, Typography, Box, Grid, Card, CardContent, Button, TextField, Dialog, DialogTitle, DialogContent, DialogActions, Table, TableBody, TableCell, TableContainer, TableHead, TableRow, Paper, Alert, CircularProgress } from '@mui/material';
import { Add, Schedule } from '@mui/icons-material';
import api from '../services/api';

const Shifts = () => {
  const [shifts, setShifts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  
  // Dialog state
  const [openAdd, setOpenAdd] = useState(false);
  const [name, setName] = useState('');
  const [startTime, setStartTime] = useState('09:00');
  const [endTime, setEndTime] = useState('18:00');
  const [gracePeriod, setGracePeriod] = useState(15);
  const [halfDay, setHalfDay] = useState(240);
  const [actionLoading, setActionLoading] = useState(false);
  const [formError, setFormError] = useState(null);

  const fetchShifts = async () => {
    setLoading(true);
    setError(null);
    try {
      const res = await api.get('/api/v1/shifts');
      setShifts(res.data || []);
    } catch (err) {
      setError(err.response?.data?.detail || 'Failed to fetch shifts.');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchShifts();
  }, []);

  const handleAddShift = async (e) => {
    e.preventDefault();
    setActionLoading(true);
    setFormError(null);
    
    try {
      await api.post('/api/v1/shifts', {
        name,
        start_time: startTime.length === 5 ? startTime + ':00' : startTime,
        end_time: endTime.length === 5 ? endTime + ':00' : endTime,
        grace_period_minutes: parseInt(gracePeriod),
        half_day_minutes: parseInt(halfDay)
      });
      setOpenAdd(false);
      setName('');
      setStartTime('09:00');
      setEndTime('18:00');
      setGracePeriod(15);
      setHalfDay(240);
      fetchShifts();
    } catch (err) {
      setFormError(err.response?.data?.detail || 'Failed to create shift.');
    } finally {
      setActionLoading(false);
    }
  };

  return (
    <Container maxWidth="lg">
      <Box display="flex" justifyContent="space-between" alignItems="center" mb={4}>
        <Typography variant="h5" sx={{ fontWeight: 'bold', color: '#1E293B' }}>
          Shift Templates
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
          Create Shift
        </Button>
      </Box>

      {error && <Alert severity="error" sx={{ mb: 3 }}>{error}</Alert>}

      {loading ? (
        <Box display="flex" justifyContent="center" py={8}><CircularProgress /></Box>
      ) : (
        <Grid container spacing={3}>
          {shifts.map((shift) => (
            <Grid item xs={12} md={4} key={shift.id}>
              <Card sx={{ borderRadius: 3, border: '1px solid #E2E8F0', boxShadow: 'none' }}>
                <CardContent>
                  <Box display="flex" alignItems="center" gap={1.5} mb={2}>
                    <Box
                      sx={{
                        width: 40,
                        height: 40,
                        borderRadius: 2,
                        bgcolor: 'rgba(0, 95, 175, 0.1)',
                        display: 'flex',
                        justifyContent: 'center',
                        alignItems: 'center',
                        color: '#005FAF',
                      }}
                    >
                      <Schedule />
                    </Box>
                    <Typography variant="h6" sx={{ fontWeight: 'bold' }}>
                      {shift.name}
                    </Typography>
                  </Box>
                  <Typography variant="body2" color="textSecondary" gutterBottom>
                    Timing: <strong>{shift.start_time.substring(0, 5)} - {shift.end_time.substring(0, 5)}</strong>
                  </Typography>
                  <Typography variant="body2" color="textSecondary" gutterBottom>
                    Grace Period: <strong>{shift.grace_period_minutes} mins</strong>
                  </Typography>
                  <Typography variant="body2" color="textSecondary">
                    Half-Day Threshold: <strong>{shift.half_day_minutes} mins</strong>
                  </Typography>
                </CardContent>
              </Card>
            </Grid>
          ))}
        </Grid>
      )}

      {/* Add Shift Dialog */}
      <Dialog open={openAdd} onClose={() => setOpenAdd(false)} fullWidth maxWidth="xs">
        <form onSubmit={handleAddShift}>
          <DialogTitle sx={{ fontWeight: 'bold' }}>Create Shift Template</DialogTitle>
          <DialogContent>
            {formError && <Alert severity="error" sx={{ mb: 2 }}>{formError}</Alert>}
            <TextField
              margin="dense"
              label="Shift Name (e.g. Night Shift)"
              fullWidth
              required
              value={name}
              onChange={(e) => setName(e.target.value)}
            />
            <TextField
              margin="dense"
              label="Start Time"
              type="time"
              fullWidth
              required
              value={startTime}
              onChange={(e) => setStartTime(e.target.value)}
              InputLabelProps={{ shrink: true }}
            />
            <TextField
              margin="dense"
              label="End Time"
              type="time"
              fullWidth
              required
              value={endTime}
              onChange={(e) => setEndTime(e.target.value)}
              InputLabelProps={{ shrink: true }}
            />
            <TextField
              margin="dense"
              label="Grace Period (Minutes)"
              type="number"
              fullWidth
              required
              value={gracePeriod}
              onChange={(e) => setGracePeriod(e.target.value)}
            />
            <TextField
              margin="dense"
              label="Half-Day Limit (Minutes)"
              type="number"
              fullWidth
              required
              value={halfDay}
              onChange={(e) => setHalfDay(e.target.value)}
            />
          </DialogContent>
          <DialogActions sx={{ p: 3 }}>
            <Button onClick={() => setOpenAdd(false)} color="inherit">Cancel</Button>
            <Button type="submit" variant="contained" disabled={actionLoading}>
              Save Shift
            </Button>
          </DialogActions>
        </form>
      </Dialog>
    </Container>
  );
};

export default Shifts;
