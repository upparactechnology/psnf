import React from 'react';
import { Box, Drawer, AppBar, Toolbar, List, Typography, Divider, ListItem, ListItemButton, ListItemIcon, ListItemText, Button } from '@mui/material';
import { Dashboard, People, Schedule, Assessment, ExitToApp, Devices, Settings as SettingsIcon } from '@mui/icons-material';
import { useNavigate, useLocation } from 'react-router-dom';

const drawerWidth = 260;

const Layout = ({ children }) => {
  const navigate = useNavigate();
  const location = useLocation();

  const menuItems = [
    { text: 'Dashboard', icon: <Dashboard />, path: '/' },
    { text: 'Employees', icon: <People />, path: '/employees' },
    { text: 'Shifts', icon: <Schedule />, path: '/shifts' },
    { text: 'Terminals', icon: <Devices />, path: '/terminals' },
    { text: 'Reports', icon: <Assessment />, path: '/reports' },
    { text: 'Settings', icon: <SettingsIcon />, path: '/settings' },
  ];

  const handleLogout = () => {
    localStorage.clear();
    navigate('/login');
  };

  return (
    <Box sx={{ display: 'flex', minHeight: '100vh', bgcolor: '#F8FAFC' }}>
      {/* AppBar / Top navigation header */}
      <AppBar
        position="fixed"
        sx={{
          width: { sm: `calc(100% - ${drawerWidth}px)` },
          ml: { sm: `${drawerWidth}px` },
          boxShadow: 'none',
          borderBottom: '1px solid #E2E8F0',
          bgcolor: '#FFFFFF',
          color: '#1E293B',
        }}
      >
        <Toolbar sx={{ justifyContent: 'space-between' }}>
          <Typography variant="h6" noWrap component="div" sx={{ fontWeight: 'bold', color: '#0F172A' }}>
            {menuItems.find(item => item.path === location.pathname)?.text || 'Management Console'}
          </Typography>
          <Button
            variant="outlined"
            color="error"
            startIcon={<ExitToApp />}
            onClick={handleLogout}
            sx={{ borderRadius: 2 }}
          >
            Logout
          </Button>
        </Toolbar>
      </AppBar>

      {/* Drawer / Sidebar navigation */}
      <Drawer
        variant="permanent"
        sx={{
          width: drawerWidth,
          flexShrink: 0,
          '& .MuiDrawer-paper': {
            width: drawerWidth,
            boxSizing: 'border-box',
            borderRight: '1px solid #E2E8F0',
            bgcolor: '#0F172A',
            color: '#94A3B8',
          },
        }}
      >
        <Toolbar sx={{ px: 3, py: 2 }}>
          <Box display="flex" alignItems="center" gap={1.5}>
            <Box
              sx={{
                width: 36,
                height: 36,
                borderRadius: '50%',
                background: 'linear-gradient(135deg, #64FFDA 0%, #005FAF 100%)',
              }}
            />
            <Typography variant="subtitle1" noWrap sx={{ fontWeight: 'bold', color: '#FFFFFF', letterSpacing: 0.5 }}>
              AI Attendance
            </Typography>
          </Box>
        </Toolbar>
        <Divider sx={{ borderColor: '#334155' }} />
        <List sx={{ px: 2, py: 3 }}>
          {menuItems.map((item) => {
            const isActive = location.pathname === item.path;
            return (
              <ListItem key={item.text} disablePadding sx={{ mb: 1 }}>
                <ListItemButton
                  onClick={() => navigate(item.path)}
                  sx={{
                    borderRadius: 2,
                    bgcolor: isActive ? 'rgba(100, 255, 218, 0.1)' : 'transparent',
                    color: isActive ? '#64FFDA' : '#94A3B8',
                    '&:hover': {
                      bgcolor: isActive ? 'rgba(100, 255, 218, 0.15)' : 'rgba(255, 255, 255, 0.05)',
                      color: isActive ? '#64FFDA' : '#F1F5F9',
                    },
                  }}
                >
                  <ListItemIcon sx={{ color: isActive ? '#64FFDA' : '#64748B', minWidth: 40 }}>
                    {item.icon}
                  </ListItemIcon>
                  <ListItemText
                    primary={item.text}
                    primaryTypographyProps={{ fontSize: '0.9rem', fontWeight: isActive ? 'bold' : 'medium' }}
                  />
                </ListItemButton>
              </ListItem>
            );
          })}
        </List>
      </Drawer>

      {/* Main Content Area */}
      <Box
        component="main"
        sx={{
          flexGrow: 1,
          p: 4,
          width: { sm: `calc(100% - ${drawerWidth}px)` },
          mt: '64px',
        }}
      >
        {children}
      </Box>
    </Box>
  );
};

export default Layout;
