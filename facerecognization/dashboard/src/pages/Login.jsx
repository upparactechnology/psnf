import React, { useState } from 'react';
import { Box, Button, TextField, Typography, Card, CardContent, InputAdornment, Alert, CircularProgress } from '@mui/material';
import { AccountCircle, Lock } from '@mui/icons-material';
import { useNavigate } from 'react-router-dom';
import api from '../services/api';

const Login = () => {
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const navigate = useNavigate();

  const handleLogin = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError(null);
    try {
      const res = await api.post('/api/v1/auth/login', { username, password });
      const { access_token, refresh_token } = res.data;
      localStorage.setItem('access_token', access_token);
      localStorage.setItem('refresh_token', refresh_token);
      navigate('/');
    } catch (err) {
      setError(err.response?.data?.detail || 'Authentication failed. Please verify credentials.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <Box
      sx={{
        minHeight: '100vh',
        display: 'flex',
        justifyContent: 'center',
        alignItems: 'center',
        background: 'linear-gradient(135deg, #0A192F 0%, #172A45 100%)',
        p: 2,
      }}
    >
      <Card
        sx={{
          width: '100%',
          maxWidth: 420,
          borderRadius: 4,
          boxShadow: '0 20px 40px rgba(0,0,0,0.4)',
          background: 'rgba(23, 42, 69, 0.85)',
          backdropFilter: 'blur(10px)',
          border: '1px solid rgba(100, 255, 218, 0.1)',
          color: '#E2E8F0',
        }}
      >
        <CardContent sx={{ p: 4 }}>
          <Box display="flex" flexDirection="column" alignItems="center" mb={4}>
            <Box
              sx={{
                width: 60,
                height: 60,
                borderRadius: '50%',
                background: 'linear-gradient(135deg, #64FFDA 0%, #005FAF 100%)',
                display: 'flex',
                justifyContent: 'center',
                alignItems: 'center',
                mb: 2,
                boxShadow: '0 0 20px rgba(100, 255, 218, 0.4)',
              }}
            >
              <Lock sx={{ color: '#0A192F', fontSize: 30 }} />
            </Box>
            <Typography variant="h5" sx={{ fontWeight: 'bold', color: '#64FFDA', letterSpacing: 1 }}>
              Portal Login
            </Typography>
            <Typography variant="body2" sx={{ color: '#8892B0', mt: 1 }}>
              AI Face Recognition Attendance Admin
            </Typography>
          </Box>

          {error && <Alert severity="error" sx={{ mb: 3, borderRadius: 2 }}>{error}</Alert>}

          <form onSubmit={handleLogin}>
            <TextField
              fullWidth
              label="Username"
              variant="outlined"
              value={username}
              onChange={(e) => setUsername(e.target.value)}
              sx={{
                mb: 3,
                '& .MuiOutlinedInput-root': {
                  color: '#E2E8F0',
                  '& fieldset': { borderColor: 'rgba(136, 146, 176, 0.3)' },
                  '&:hover fieldset': { borderColor: '#64FFDA' },
                  '&.Mui-focused fieldset': { borderColor: '#64FFDA' },
                },
                '& .MuiInputLabel-root': { color: '#8892B0' },
                '& .MuiInputLabel-root.Mui-focused': { color: '#64FFDA' },
              }}
              InputProps={{
                startAdornment: (
                  <InputAdornment position="start">
                    <AccountCircle sx={{ color: '#8892B0' }} />
                  </InputAdornment>
                ),
              }}
            />

            <TextField
              fullWidth
              label="Password"
              type="password"
              variant="outlined"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              sx={{
                mb: 4,
                '& .MuiOutlinedInput-root': {
                  color: '#E2E8F0',
                  '& fieldset': { borderColor: 'rgba(136, 146, 176, 0.3)' },
                  '&:hover fieldset': { borderColor: '#64FFDA' },
                  '&.Mui-focused fieldset': { borderColor: '#64FFDA' },
                },
                '& .MuiInputLabel-root': { color: '#8892B0' },
                '& .MuiInputLabel-root.Mui-focused': { color: '#64FFDA' },
              }}
              InputProps={{
                startAdornment: (
                  <InputAdornment position="start">
                    <Lock sx={{ color: '#8892B0' }} />
                  </InputAdornment>
                ),
              }}
            />

            <Button
              type="submit"
              fullWidth
              variant="contained"
              disabled={loading}
              sx={{
                py: 1.5,
                borderRadius: 2.5,
                background: 'linear-gradient(135deg, #64FFDA 0%, #005FAF 100%)',
                color: '#0A192F',
                fontWeight: 'bold',
                fontSize: '1rem',
                boxShadow: '0 10px 20px rgba(100, 255, 218, 0.2)',
                '&:hover': {
                  background: 'linear-gradient(135deg, #4ce6c3 0%, #004c8c 100%)',
                  boxShadow: '0 12px 24px rgba(100, 255, 218, 0.3)',
                },
              }}
            >
              {loading ? <CircularProgress size={24} sx={{ color: '#0A192F' }} /> : 'Sign In'}
            </Button>
          </form>
        </CardContent>
      </Card>
    </Box>
  );
};

export default Login;
