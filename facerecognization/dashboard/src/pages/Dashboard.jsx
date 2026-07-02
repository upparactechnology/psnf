import React, { useEffect, useState, useRef } from 'react';
import { Container, Grid, Card, CardContent, Typography, Box, CircularProgress, Alert, Button } from '@mui/material';
import { CameraAlt, VideocamOff, CheckCircle, Cancel, Sync } from '@mui/icons-material';
import api from '../services/api';

const Dashboard = () => {
  const [metrics, setMetrics] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  // Webcam States
  const [cameraActive, setCameraActive] = useState(false);
  const [webcamError, setWebcamError] = useState(null);
  const [verifyStatus, setVerifyStatus] = useState(null);
  const [verifying, setVerifying] = useState(false);

  const videoRef = useRef(null);
  const streamRef = useRef(null);

  const fetchMetrics = async () => {
    try {
      const today = new Date().toISOString().split('T')[0];
      const res = await api.get(`/api/v1/reports/daily?date=${today}`);
      
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

  useEffect(() => {
    fetchMetrics();
  }, []);

  // Start Webcam Stream
  const startCamera = async () => {
    setWebcamError(null);
    setVerifyStatus(null);
    try {
      const stream = await navigator.mediaDevices.getUserMedia({
        video: { width: 640, height: 480, facingMode: 'user' }
      });
      streamRef.current = stream;
      if (videoRef.current) {
        videoRef.current.srcObject = stream;
      }
      setCameraActive(true);
    } catch (err) {
      setWebcamError('Webcam access denied or unavailable. Grant camera permission in your browser.');
    }
  };

  // Stop Webcam Stream
  const stopCamera = () => {
    if (streamRef.current) {
      streamRef.current.getTracks().forEach(track => track.stop());
      streamRef.current = null;
    }
    if (videoRef.current) {
      videoRef.current.srcObject = null;
    }
    setCameraActive(false);
  };

  // Capture frame and send to backend
  const captureAndVerify = () => {
    if (!videoRef.current || verifying) return;
    setVerifying(true);
    setVerifyStatus(null);

    try {
      const video = videoRef.current;
      const canvas = document.createElement('canvas');
      canvas.width = video.videoWidth || 640;
      canvas.height = video.videoHeight || 480;
      
      const ctx = canvas.getContext('2d');
      ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

      canvas.toBlob(async (blob) => {
        if (!blob) {
          setVerifying(false);
          setVerifyStatus({ success: false, message: 'Failed to capture frame.' });
          return;
        }

        const formData = new FormData();
        formData.append('file', blob, 'webcam_capture.jpg');

        try {
          const res = await api.post('/api/v1/recognition/verify-image', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
          });
          
          if (res.data?.matched) {
            const emp = res.data.employee;
            const log = res.data.attendance_log;
            setVerifyStatus({
              success: true,
              message: `Welcome ${emp.first_name} ${emp.last_name}! ${log.clock_type} registered successfully (${log.status}).`
            });
            // Refresh stats to include this new attendance log
            fetchMetrics();
          }
        } catch (err) {
          setVerifyStatus({
            success: false,
            message: err.response?.data?.detail || 'Face profile match score below threshold.'
          });
        } finally {
          setVerifying(false);
        }
      }, 'image/jpeg');
    } catch (err) {
      setVerifying(false);
      setVerifyStatus({ success: false, message: 'Webcam snapshot failed.' });
    }
  };

  useEffect(() => {
    return () => {
      // Cleanup tracks on unmount
      if (streamRef.current) {
        streamRef.current.getTracks().forEach(track => track.stop());
      }
    };
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
      <Typography variant="h4" gutterBottom sx={{ mb: 4, fontWeight: 'bold', color: '#0F172A' }}>
        Today's Attendance Monitor
      </Typography>
      
      {error && <Alert severity="error" sx={{ mb: 4 }}>{error}</Alert>}
      
      <Grid container spacing={4}>
        {/* Roster Cards */}
        <Grid item xs={12} sm={6} md={3}>
          <Card sx={{ bgcolor: '#F1F5F9', border: '1px solid #E2E8F0', boxShadow: 'none', borderRadius: 3 }}>
            <CardContent>
              <Typography color="textSecondary" gutterBottom variant="subtitle2">
                TOTAL EMPLOYEES
              </Typography>
              <Typography variant="h3" component="div" sx={{ fontWeight: 'bold', color: '#334155' }}>
                {metrics?.total || 0}
              </Typography>
            </CardContent>
          </Card>
        </Grid>
        
        <Grid item xs={12} sm={6} md={3}>
          <Card sx={{ bgcolor: '#ECFDF5', border: '1px solid #A7F3D0', boxShadow: 'none', borderRadius: 3 }}>
            <CardContent>
              <Typography color="textSecondary" gutterBottom variant="subtitle2">
                PRESENT TODAY
              </Typography>
              <Typography variant="h3" component="div" sx={{ color: '#047857', fontWeight: 'bold' }}>
                {metrics?.present || 0}
              </Typography>
            </CardContent>
          </Card>
        </Grid>
        
        <Grid item xs={12} sm={6} md={3}>
          <Card sx={{ bgcolor: '#FFFBEB', border: '1px solid #FDE68A', boxShadow: 'none', borderRadius: 3 }}>
            <CardContent>
              <Typography color="textSecondary" gutterBottom variant="subtitle2">
                LATE ARRIVALS
              </Typography>
              <Typography variant="h3" component="div" sx={{ color: '#B45309', fontWeight: 'bold' }}>
                {metrics?.late || 0}
              </Typography>
            </CardContent>
          </Card>
        </Grid>
        
        <Grid item xs={12} sm={6} md={3}>
          <Card sx={{ bgcolor: '#FEF2F2', border: '1px solid #FCA5A5', boxShadow: 'none', borderRadius: 3 }}>
            <CardContent>
              <Typography color="textSecondary" gutterBottom variant="subtitle2">
                ABSENT TODAY
              </Typography>
              <Typography variant="h3" component="div" sx={{ color: '#B91C1C', fontWeight: 'bold' }}>
                {metrics?.absent || 0}
              </Typography>
            </CardContent>
          </Card>
        </Grid>

        {/* Webcam simulator panel */}
        <Grid item xs={12}>
          <Card sx={{ borderRadius: 4, border: '1px solid #E2E8F0', boxShadow: 'none', overflow: 'hidden' }}>
            <Box sx={{ p: 3, bgcolor: '#F8FAFC', borderBottom: '1px solid #E2E8F0', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
              <Box>
                <Typography variant="h6" sx={{ fontWeight: 'bold', color: '#0F172A' }}>
                  Webcam Attendance Kiosk (Simulation Console)
                </Typography>
                <Typography variant="body2" color="textSecondary">
                  Open your camera to verify faces and record clocks directly from the admin panel
                </Typography>
              </Box>
              <Button
                variant="contained"
                onClick={cameraActive ? stopCamera : startCamera}
                color={cameraActive ? 'error' : 'primary'}
                startIcon={cameraActive ? <VideocamOff /> : <CameraAlt />}
                sx={{ borderRadius: 2 }}
              >
                {cameraActive ? 'Close Camera' : 'Start Camera'}
              </Button>
            </Box>

            <CardContent sx={{ p: 4 }}>
              {webcamError && <Alert severity="warning" sx={{ mb: 3 }}>{webcamError}</Alert>}

              <Grid container spacing={4} alignItems="center">
                {/* Camera preview window */}
                <Grid item xs={12} md={6} display="flex" justifyContent="center">
                  <Box
                    sx={{
                      width: '100%',
                      maxWidth: 440,
                      aspectRatio: '4/3',
                      bgcolor: '#0F172A',
                      borderRadius: 4,
                      overflow: 'hidden',
                      position: 'relative',
                      border: '4px solid #334155',
                      display: 'flex',
                      justifyContent: 'center',
                      alignItems: 'center',
                    }}
                  >
                    {cameraActive ? (
                      <>
                        <video
                          ref={videoRef}
                          autoPlay
                          playsInline
                          muted
                          style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                        />
                        {/* Oval HUD cut-out overlay */}
                        <Box
                          sx={{
                            position: 'absolute',
                            top: 0,
                            left: 0,
                            right: 0,
                            bottom: 0,
                            border: '40px solid rgba(0,0,0,0.5)',
                            pointerEvents: 'none',
                          }}
                        >
                          <Box
                            sx={{
                              width: '100%',
                              height: '100%',
                              border: '3px solid #64FFDA',
                              borderRadius: '50%',
                              boxShadow: '0 0 20px rgba(100, 255, 218, 0.4)',
                            }}
                          />
                        </Box>
                      </>
                    ) : (
                      <Box textAlign="center" color="#64748B">
                        <VideocamOff sx={{ fontSize: 60, mb: 1 }} />
                        <Typography variant="body2">Camera stream is inactive</Typography>
                      </Box>
                    )}
                  </Box>
                </Grid>

                {/* Verification result logs */}
                <Grid item xs={12} md={6}>
                  <Box display="flex" flexDirection="column" gap={3}>
                    <Typography variant="subtitle1" sx={{ fontWeight: 'bold', color: '#334155' }}>
                      Verification Status
                    </Typography>

                    {verifyStatus ? (
                      <Box
                        sx={{
                          p: 3,
                          borderRadius: 3,
                          border: `2px solid ${verifyStatus.success ? '#10B981' : '#EF4444'}`,
                          bgcolor: verifyStatus.success ? '#ECFDF5' : '#FEF2F2',
                          display: 'flex',
                          alignItems: 'flex-start',
                          gap: 2,
                        }}
                      >
                        {verifyStatus.success ? (
                          <CheckCircle sx={{ color: '#10B981', mt: 0.3 }} />
                        ) : (
                          <Cancel sx={{ color: '#EF4444', mt: 0.3 }} />
                        )}
                        <Box>
                          <Typography variant="subtitle2" sx={{ fontWeight: 'bold', color: verifyStatus.success ? '#065F46' : '#991B1B' }}>
                            {verifyStatus.success ? 'Match Found' : 'Verification Denied'}
                          </Typography>
                          <Typography variant="body2" sx={{ mt: 0.5, color: verifyStatus.success ? '#047857' : '#B91C1C' }}>
                            {verifyStatus.message}
                          </Typography>
                        </Box>
                      </Box>
                    ) : (
                      <Box
                        sx={{
                          p: 3,
                          borderRadius: 3,
                          border: '2px dashed #CBD5E1',
                          bgcolor: '#F8FAFC',
                          textAlign: 'center',
                          color: '#64748B',
                        }}
                      >
                        Start your camera, position your face in the oval, and click Check-In.
                      </Box>
                    )}

                    <Button
                      fullWidth
                      variant="contained"
                      disabled={!cameraActive || verifying}
                      onClick={captureAndVerify}
                      startIcon={verifying ? <CircularProgress size={20} /> : <Sync />}
                      sx={{
                        py: 1.5,
                        borderRadius: 2.5,
                        fontWeight: 'bold',
                        background: 'linear-gradient(135deg, #0F172A 0%, #1E293B 100%)',
                        boxShadow: '0 4px 12px rgba(15, 23, 42, 0.15)',
                        '&:hover': {
                          background: 'linear-gradient(135deg, #1E293B 0%, #334155 100%)',
                        },
                      }}
                    >
                      {verifying ? 'Analyzing Face Embedding...' : 'Verify Face & Check-In'}
                    </Button>
                  </Box>
                </Grid>
              </Grid>
            </CardContent>
          </Card>
        </Grid>
      </Grid>
    </Container>
  );
};

export default Dashboard;
