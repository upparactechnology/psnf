# AI Prompt: React Admin Dashboard Development

Use this prompt to instruct an AI assistant to implement the React web dashboard:

```markdown
## Objective
Develop the Admin Web Dashboard using React 18, Vite, Material UI (MUI v5), and Chart.js.

## Requirements
1. **Protected Routing**:
   - Configure React Router to protect admin dashboard paths.
   - Redirect unauthenticated users to `/login`.
2. **Key Screens**:
   - **Home Dashboard**: Displays key daily metrics (e.g., Present, Absent, Late counts) and charts showing attendance distributions.
   - **Employee Directory**: Shows employee listings, registration statuses, and buttons to add/edit/delete profiles.
   - **Shift Configuration**: Interface for designing shift templates and scheduling employees.
   - **Reports Panel**: Date range and filter settings to search records and export data to CSV/Excel/PDF.
3. **API Integration**:
   - Configure an Axios instance with interceptors to automatically inject JWT tokens into request headers.
   - Implement auth token refresh logic to automatically refresh expired sessions.

## Target Folder Structure
```
dashboard/
├── src/
│   ├── components/
│   │   ├── Layout.jsx                  # Main shell layout with sidebar navigation
│   │   └── ProtectedRoute.jsx          # Protected route validation wrapper
│   ├── pages/
│   │   ├── Login.jsx                   # Admin login screen
│   │   ├── Dashboard.jsx               # Home dashboard with metric cards
│   │   ├── Employees.jsx               # Staff directory data table
│   │   ├── Shifts.jsx                  # Shift roster configurations
│   │   └── Reports.jsx                 # Attendance reports search and export UI
│   ├── services/
│   │   └── api.js                      # Axios instance and API routes
│   └── App.jsx                         # React routing setup
```

## Coding Standards
- Implement functional React components using hooks.
- Handle API errors gracefully, showing feedback alerts on-screen.

## Expected Deliverables
1. React code files for all pages and components.
2. Build configurations (`vite.config.js` and `package.json`).

## Acceptance Criteria
- Running `npm run dev` starts the local development server.
- Logging in as an admin redirects to the home page, successfully loading stats and displaying interactive charts.
```
