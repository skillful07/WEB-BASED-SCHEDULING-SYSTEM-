# Test Cases — Web-Based Scheduling System with Constraint Algorithm

| # | Module | Test Case | Input | Expected Result |
|---|--------|-----------|-------|------------------|
| 1 | Login | Correct credentials | Valid username + valid password | Redirect to dashboard.php, session created |
| 2 | Login | Wrong password | Valid username + incorrect password | "Invalid username or password" error shown |
| 3 | Login | Non-existent username | Random username | "Invalid username or password" error shown |
| 4 | Login | Empty fields | Blank username and password | "Please fill in both fields" error shown |
| 5 | Login | Inactive account | Valid credentials, account status = inactive | "Account has been deactivated" error shown |
| 6 | Login | SQL injection attempt | `' OR '1'='1` in username field | Login fails safely (prepared statements block injection) |
| 7 | Session | Logout | Click Logout | Session destroyed, redirected to login.php |
| 8 | Session | Access without login | Visit dashboard.php directly, no session | Redirected to login.php |
| 9 | Session | Idle timeout | No activity for 30+ minutes | Session expires, redirected to login with timeout message |
| 10 | Access Control | Non-admin accesses user management | Lecturer logs in, visits users/manage_users.php | Redirected to dashboard with "access denied" |
| 11 | User Mgmt | Add valid user | Unique username, valid password (6+ chars), role | User created, appears in list |
| 12 | User Mgmt | Add duplicate username | Existing username | "Username already taken" error shown |
| 13 | User Mgmt | Delete own account | Admin tries to delete the account they are logged in as | Deletion blocked with warning message |
| 14 | Resource Mgmt | Add resource | Valid name, type, capacity ≥ 0 | Resource created and listed |
| 15 | Resource Mgmt | Mark resource unavailable | Edit resource, set status = unavailable | Resource excluded from "New Schedule" resource dropdown |
| 16 | Timeslot Mgmt | Add non-overlapping slot | Day/start/end that doesn't overlap existing slots | Slot created |
| 17 | Timeslot Mgmt | Add overlapping slot | Day/time range overlapping an existing slot | "Overlaps an existing time slot" error shown |
| 18 | Timeslot Mgmt | Invalid time order | Start time later than end time | "End time must be after start time" error shown |
| 19 | Constraint Engine | Schedule with no conflicts | Free resource + free slot + free lecturer | Schedule status = approved and saved |
| 20 | Constraint Engine | Room clash | Same resource + same slot as an existing approved schedule (different lecturer) | Schedule rejected — "Room clash" message shown |
| 21 | Constraint Engine | Lecturer clash | Same lecturer + same slot as an existing schedule (different resource) | Schedule rejected — "Lecturer clash" message shown |
| 22 | Constraint Engine | Unavailable resource | Resource with status = unavailable selected | Schedule rejected — "resource currently marked unavailable" |
| 23 | Constraint Engine | Update triggers re-validation | Edit an existing schedule to a slot/resource that now clashes | Update rejected, original schedule unchanged |
| 24 | Constraint Engine | Update with no new conflict | Edit schedule's course title only, same resource/slot/user | Update succeeds (excludes itself from clash check) |
| 25 | Schedule Mgmt | View schedules search | Search term matching a course title | Only matching rows returned |
| 26 | Schedule Mgmt | Filter by status | Select status = rejected | Only rejected schedules shown |
| 27 | Schedule Mgmt | Pagination | More than 10 schedules exist | Results split across multiple pages, page links work |
| 28 | Schedule Mgmt | Delete schedule | Click delete + confirm | Schedule removed from list and database |
| 29 | Reports | Export CSV | Click "Export Schedule (Excel/CSV)" | Browser downloads a .csv file openable in Excel |
| 30 | Reports | Print report | Click Print button | Browser print dialog opens with sidebar/navbar hidden |
| 31 | Security | Direct DB error handling | Wrong DB credentials in config/db.php | Friendly error message shown, no raw stack trace |
| 32 | Security | XSS in form fields | `<script>alert(1)</script>` in course title | Rendered as harmless text (htmlspecialchars escaping) |

**Total: 32 test cases** (exceeds the minimum of 20 required).

## How to execute these tests
1. Import `database/database.sql`, then run `reset_admin_password.php` once (see Installation Guide).
2. Log in as `admin` / `admin123`.
3. Walk through each row above in order — most build on data created by earlier steps (e.g. Test 20 requires the schedule created in Test 19 to already exist).
4. Record actual vs expected result for your project defense documentation/appendix.
