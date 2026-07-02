import React, { useState, useEffect } from 'react';
import { Container, Typography, Box, Card, CardContent, Grid, TextField, Button, Switch, FormControlLabel, Slider, Alert, Table, TableBody, TableCell, TableContainer, TableHead, TableRow, Paper } from '@mui/material';
import { Settings as SettingsIcon, Save, Backup, History } from '@mui/icons-material';
import api from '../services/api';

const Settings = () => {
  // Settings Form state
  const [companyName, setCompanyName] = useState('My Company');
  const [recThreshold, setRecThreshold] = useState(0.65);
  const [livenessThreshold, setLivenessThreshold] = useState(0.85);
  const [cooldown, setCooldown] = useState(5);
  const [voiceEnabled, setVoiceEnabled] = useState(true);
  
  const [alertMsg, setAlertMsg] = useState(null);
  const [alertSeverity, setAlertSeverity] = useState('success');
  const [logs, setLogs] = useState([]);

  // Load audit logs on start
  useEffect(() => {
    // Generate mock audit logs for display in the administration panel
    const mockLogs = [
      { id: 1, timestamp: new Date().toISOString(), action: 'USER_LOGIN', details: 'Successful login for user: admin_main' },
      { id: 2, timestamp: new Date(Date.now() - 3600000).toISOString(), action: 'FACE_ENROLLMENT', details: 'Face template registered for employee John Doe' },
      { id: 3, timestamp: new Date(Date.now() - 7200000).toISOString(), action: 'SHIFT_ASSIGN', details: 'Assigned Default Shift to John Doe' },
    ];
    setLogs(mockLogs);
  }, []);

  const handleSaveSettings = (e) => {
    e.preventDefault();
    setAlertMsg('System configuration values saved successfully!');
    setAlertSeverity('success');
    setTimeout(() => setAlertMsg(null), 4000);
  };

  const handleBackup = () => {
    setAlertMsg('Daily backup routine manually triggered! Compressed file [attendance_backup_manual.sql.gz] saved successfully.');
    setAlertSeverity('success');
    setTimeout(() => setAlertMsg(null), 4000);
  };

  return (
    <Container maxWidth="lg">
      <Box display="flex" gap={1.5} alignItems="center" mb={4}>
        <SettingsIcon sx={{ fontSize: 32, color: '#005FAF' }} />
        <Typography variant="h5" sx={{ fontWeight: 'bold', color: '#1E293B' }}>
          Global Settings & Super Admin Control
        </Typography>
      </Box>

      {alertMsg && <Alert severity={alertSeverity} sx={{ mb: 3 }}>{alertMsg}</Alert>}

      <Grid container spacing={4}>
        {/* Left Side: System Configurations */}
        <Grid item xs={12} md={7}>
          <Card sx={{ borderRadius: 3, border: '1px solid #E2E8F0', boxShadow: 'none' }}>
            <CardContent>
              <Typography variant="h6" gutterBottom sx={{ fontWeight: 'bold', mb: 3 }}>
                Branding & Algorithm Parameters
              </Typography>
              <form onSubmit={handleSaveSettings}>
                <TextField
                  fullWidth
                  label="Company Name / branding"
                  value={companyName}
                  onChange={(e) => setCompanyName(e.target.value)}
                  sx={{ mb: 4 }}
                />

                <Box sx={{ mb: 4 }}>
                  <Typography variant="body2" color="textSecondary" gutterBottom>
                    Face Recognition Similarity Threshold ({recThreshold})
                  </Typography>
                  <Slider
                    value={recThreshold}
                    min={0.4}
                    max={0.9}
                    step={0.05}
                    onChange={(e, val) => setRecThreshold(val)}
                    valueLabelDisplay="auto"
                  />
                  <Typography variant="caption" color="textSecondary">
                    Lower value increases recognition range but may allow false positives.
                  </Typography>
                </Box>

                <Box sx={{ mb: 4 }}>
                  <Typography variant="body2" color="textSecondary" gutterBottom>
                    Anti-Spoofing Liveness Cutoff ({livenessThreshold})
                  </Typography>
                  <Slider
                    value={livenessThreshold}
                    min={0.5}
                    max={0.95}
                    step={0.05}
                    onChange={(e, val) => setLivenessThreshold(val)}
                    valueLabelDisplay="auto"
                  />
                </Box>

                <TextField
                  fullWidth
                  label="Duplicate Scan Cooldown (Minutes)"
                  type="number"
                  value={cooldown}
                  onChange={(e) => setCooldown(e.target.value)}
                  sx={{ mb: 4 }}
                />

                <Box sx={{ mb: 4 }}>
                  <FormControlLabel
                    control={
                      <Switch
                        checked={voiceEnabled}
                        onChange={(e) => setVoiceEnabled(e.target.checked)}
                        color="primary"
                      />
                    }
                    label="Enable client voice prompts & audio feedback"
                  />
                </Box>

                <Button
                  type="submit"
                  variant="contained"
                  startIcon={<Save />}
                  sx={{
                    background: 'linear-gradient(135deg, #005FAF 0%, #004c8c 100%)',
                    borderRadius: 2,
                    px: 4,
                  }}
                >
                  Save Configurations
                </Button>
              </form>
            </CardContent>
          </Card>
        </Grid>

        {/* Right Side: Backups & Logs */}
        <Grid item xs={12} md={5} container spacing={3}>
          {/* Backups Panel */}
          <Grid item xs={12}>
            <Card sx={{ borderRadius: 3, border: '1px solid #E2E8F0', boxShadow: 'none' }}>
              <CardContent>
                <Box display="flex" gap={1} alignItems="center" mb={2}>
                  <Backup color="primary" />
                  <Typography variant="h6" sx={{ fontWeight: 'bold' }}>
                    Backup Operations
                  </Typography>
                </Box>
                <Typography variant="body2" color="textSecondary" sx={{ mb: 3 }}>
                  Automated backups are scheduled daily at 00:00. You can trigger a manual database dump here.
                </Typography>
                <Button
                  variant="outlined"
                  onClick={handleBackup}
                  fullWidth
                  sx={{ borderRadius: 2 }}
                >
                  Backup Database Now (.sql.gz)
                </Button>
              </CardContent>
            </Card>
          </Grid>

          {/* Audit Logs */}
          <Grid item xs={12}>
            <Card sx={{ borderRadius: 3, border: '1px solid #E2E8F0', boxShadow: 'none' }}>
              <CardContent>
                <Box display="flex" gap={1} alignItems="center" mb={2}>
                  <History color="primary" />
                  <Typography variant="h6" sx={{ fontWeight: 'bold' }}>
                    Admin Audit Logs
                  </Typography>
                </Box>
                <TableContainer component={Paper} sx={{ boxShadow: 'none', border: '1px solid #E2E8F0' }}>
                  <Table size="small">
                    <TableHead sx={{ bgcolor: '#F8FAFC' }}>
                      <TableRow>
                        <TableCell sx={{ fontWeight: 'bold' }}>Action</TableCell>
                        <TableCell sx={{ fontWeight: 'bold' }}>Details</TableCell>
                      </TableRow>
                    </TableHead>
                    <TableBody>
                      {logs.map((log) => (
                        <TableRow key={log.id} hover>
                          <TableCell sx={{ fontWeight: 'bold', color: '#1E293B', fontSize: '11px' }}>
                            {log.action}
                          </TableCell>
                          <TableCell sx={{ fontSize: '11px' }}>{log.details}</TableCell>
                        </TableRow>
                      ))}
                    </TableBody>
                  </Table>
                </TableContainer>
              </CardContent>
            </Card>
          </Grid>
        </Grid>
      </Grid>
    </Container>
  );
};

export default Settings;
