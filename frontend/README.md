# Leave Application System - Vue.js 3

A complete, multi-view Frontend for an internal Leave Application System built with Vue.js 3, Vite, Pinia, and Tailwind CSS.

## Features

- **Authentication System**: Role-based login (Admin, Manager, Employee)
- **Employee Dashboard**: View leave balance, recent requests, and create new leave applications
- **Leave Request Form**: Create leave requests with validation and automatic day calculation
- **Manager Approval System**: Review and approve/reject employee leave requests
- **Mock API**: Simulated API calls with realistic delays
- **Responsive Design**: Mobile-friendly interface using Tailwind CSS
- **State Management**: Centralized state with Pinia stores

## Tech Stack

- **Framework**: Vue.js 3 (Composition API)
- **Build Tool**: Vite
- **Styling**: Tailwind CSS v3
- **State Management**: Pinia
- **Routing**: Vue Router
- **Icons**: Lucide Vue Next
- **HTTP Client**: Axios (mocked)

## Project Structure

```
src/
├── components/          # Reusable components
│   ├── Navbar.vue
│   └── StatusBadge.vue
├── views/              # Page components
│   ├── LoginView.vue
│   ├── DashboardView.vue
│   ├── LeaveRequestForm.vue
│   └── ManagerApprovalView.vue
├── stores/             # Pinia stores
│   ├── auth.js
│   └── leaves.js
├── router/
│   └── index.js
├── App.vue
├── main.js
└── style.css
```

## Installation & Setup

1. **Install dependencies**:
   ```bash
   npm install
   ```

2. **Start development server**:
   ```bash
   npm run dev
   ```
   The application will open at `http://localhost:5173`

3. **Build for production**:
   ```bash
   npm run build
   ```

4. **Preview production build**:
   ```bash
   npm run preview
   ```

## Test Credentials

The system includes three test users:

| Role     | Email                | Password |
|----------|----------------------|----------|
| Admin    | admin@example.com       | password123 |
| Manager  | manager1@example.com     | password123 |
| Employee | employee1@example.com    | password123 |

### Quick Start:
Click the "Fill Employee Credentials" button on the login page to auto-fill test credentials.

## Usage

### Employee View
1. **Login** with employee credentials
2. **Dashboard**: View your leave balance and recent applications
3. **Create Request**: Click "Create Request" to submit a new leave application
4. **Track Status**: Monitor your request status in the recent activity table

### Manager View
1. **Login** with manager credentials
2. **Approval Management**: Access the "Approvals" menu
3. **Review Requests**: View all pending leave applications
4. **Approve/Reject**: Click the green checkmark to approve or red X to reject requests

## Data Schema

### User Object
```json
{
  "id": "USER001",
  "name": "Nguyen Van A",
  "email": "employee@company.com",
  "type": 2  // 0: Admin, 1: Manager, 2: Employee
}
```

### Leave Application Object
```json
{
  "id": "LEAVE001",
  "user_id": "USER001",
  "user_name": "Nguyen Van A",
  "start_date": "2024-02-01",
  "end_date": "2024-02-03",
  "total_days": 3,
  "type": "annual",  // Options: 'annual', 'sick', 'unpaid'
  "status": "pending",  // Options: 'new', 'pending', 'approved', 'rejected', 'cancelled'
  "reason": "Personal matters"
}
```

## Features Details

### Login System
- Email and password validation
- Session persistence using localStorage
- Automatic redirect to dashboard for authenticated users
- Test credential helper button

### Employee Dashboard
- **Statistics Cards**:
  - Total Allowance: 12 days (static)
  - Used Days: Calculated from approved leaves
  - Remaining Days: 12 - Used Days
  - Pending Requests: Count of new/pending applications

- **Recent Activity Table**:
  - View all leave applications sorted by date
  - Color-coded status badges
  - Quick action button to create new requests

### Leave Request Form
- **Fields**:
  - Leave Type: Select dropdown (Annual, Sick, Unpaid)
  - Start Date: Date picker
  - End Date: Date picker
  - Reason: Text area with validation
  - Total Days: Auto-calculated read-only field

- **Validation**:
  - All fields required
  - End date must be after start date
  - Reason must be at least 5 characters
  - Real-time error messages

### Manager Approval System
- Filter applications by status (Pending/All)
- View employee information and request details
- Approve requests (turns green)
- Reject requests (turns red)
- Action buttons disabled for non-pending requests

## Status Badge Colors

| Status    | Color  | Badge |
|-----------|--------|-------|
| New       | Yellow | New   |
| Pending   | Yellow | Pending |
| Approved  | Green  | Approved |
| Rejected  | Red    | Rejected |
| Cancelled | Gray   | Cancelled |

## State Management (Pinia)

### Auth Store (`stores/auth.js`)
- `user`: Current user object
- `token`: Authentication token
- `isLoading`: Loading state
- `error`: Error messages
- Methods: `login()`, `logout()`, `initializeAuth()`
- Helpers: `isAuthenticated()`, `isAdmin()`, `isManager()`, `isEmployee()`

### Leaves Store (`stores/leaves.js`)
- `applications`: Array of all leave applications
- `isLoading`: Loading state
- Methods:
  - `createApplication(formData)`: Create new request
  - `approveApplication(id)`: Approve request
  - `rejectApplication(id)`: Reject request
  - `cancelApplication(id)`: Cancel request
  - `getApplicationsByUser(userId)`: Filter by user
  - `getTotalApprovedDays(userId)`: Calculate used days

## Routing

| Route                 | Component             | Auth Required | Role Restricted |
|-----------------------|-----------------------|---------------|-----------------|
| `/login`              | LoginView             | No            | -               |
| `/dashboard`          | DashboardView         | Yes           | -               |
| `/leave/create`       | LeaveRequestForm      | Yes           | -               |
| `/manager/approvals`  | ManagerApprovalView   | Yes           | Manager/Admin   |

## Mock API Delays

All API calls are simulated with delays:
- **Login**: 500ms
- **Create Application**: 800ms
- **Approve/Reject**: 500ms

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## Notes

- This is a frontend-only implementation with mocked API calls
- Data is persisted in Pinia stores during the session
- Authentication token is stored in localStorage
- No backend server required for development/testing
- For production use, integrate with a real backend API

## Future Enhancements

- Real backend API integration
- Email notifications
- Calendar view for leave planning
- Admin user management
- Leave type customization
- Advanced filtering and sorting
- Export to CSV/PDF
- Dashboard charts and analytics

## License

MIT
