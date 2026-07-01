import React, { useEffect, useState } from 'react';
import { Container, Grid, Card, CardContent, Typography, Box, CircularProgress, Alert } from '@mui/material';
import api from '../services/api';

const Dashboard = () => {
  const [metrics, setMetrics] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchMetrics = async () => {
      try {
        const today = new Date().toISOString().split('T')[0];
        const res = await api.get(`/api/v1/reports/daily?date=${today}`);
        
        // Compute metrics from reports data
        const records = res.data?.data || [];
        const present = records.filter(r => r.status === 'PRESENT' || r.status === 'LATE').length;
        const late = records.filter(r => r.status === 'LATE').length;
        const absent = records.filter(r => r.status === 'ABSENT').length;
        
        setMetrics({
          total: records.length,
          present,
          late,
          absent
        });
      } catch (err) {
        setError('Failed to fetch dashboard metrics. Please verify backend connection.');
      } finally {
        setLoading(false);
      }
    };
    
    fetchMetrics();
  }, []);

  if (loading) {
    return (
      <Box display="flex" justifyContent="center" alignItems="center" minHeight="80vh">
        <CircularProgress color="primary" />
      </Box>
    );
  }

  return (
    <Container maxWidth="lg" sx={{ mt: 4, mb: 4 }}>
      <Typography variant="h4" gutterBottom sx={{ mb: 4, fontWeight: 'bold', color: '#005FAF' }}>
        Today's Attendance Monitor
      </Typography>
      
      {error && <Alert severity="error" sx={{ mb: 4 }}>{error}</Alert>}
      
      <Grid container spacing={3}>
        {/* Total Roster Card */}
        <Grid item xs={12} sm={6} md={3}>
          <Card sx={{ bgcolor: '#F0F4F8', minHeight: 140 }}>
            <CardContent>
              <Typography color="textSecondary" gutterBottom variant="subtitle2">
                TOTAL EMPLOYEES
              </Typography>
              <Typography variant="h3" component="div" sx={{ fontWeight: 'bold' }}>
                {metrics?.total || 0}
              </Typography>
            </CardContent>
          </Card>
        </Grid>
        
        {/* Present Card */}
        <Grid item xs={12} sm={6} md={3}>
          <Card sx={{ bgcolor: '#E8F5E9', minHeight: 140 }}>
            <CardContent>
              <Typography color="textSecondary" gutterBottom variant="subtitle2">
                PRESENT TODAY
              </Typography>
              <Typography variant="h3" component="div" sx={{ color: '#2E7D32', fontWeight: 'bold' }}>
                {metrics?.present || 0}
              </Typography>
            </CardContent>
          </Card>
        </Grid>
        
        {/* Late Card */}
        <Grid item xs={12} sm={6} md={3}>
          <Card sx={{ bgcolor: '#FFF8E1', minHeight: 140 }}>
            <CardContent>
              <Typography color="textSecondary" gutterBottom variant="subtitle2">
                LATE ARRIVALS
              </Typography>
              <Typography variant="h3" component="div" sx={{ color: '#F57F17', fontWeight: 'bold' }}>
                {metrics?.late || 0}
              </Typography>
            </CardContent>
          </Card>
        </Grid>
        
        {/* Absent Card */}
        <Grid item xs={12} sm={6} md={3}>
          <Card sx={{ bgcolor: '#FFEBEE', minHeight: 140 }}>
            <CardContent>
              <Typography color="textSecondary" gutterBottom variant="subtitle2">
                ABSENT TODAY
              </Typography>
              <Typography variant="h3" component="div" sx={{ color: '#C62828', fontWeight: 'bold' }}>
                {metrics?.absent || 0}
              </Typography>
            </CardContent>
          </Card>
        </Grid>
      </Grid>
    </Container>
  );
};

export default Dashboard;
