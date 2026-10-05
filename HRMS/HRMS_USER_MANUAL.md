# HRMS User Manual

**For HR / Administrators, Managers and Employees**

This manual explains how to use every menu, form, button and setting in your HRMS. It is based on the actual screens of the application. Where a feature you might expect does **not** exist in this HRMS, the manual says so (see [Appendix A – Things this HRMS does not have](#appendix-a--things-this-hrms-does-not-have)).

---

## Contents

1. [HRMS Overview](#1-hrms-overview)
2. [Your Menu – What You See and Why](#2-your-menu--what-you-see-and-why)
3. [Dashboard](#3-dashboard)
4. [Employees](#4-employees)
5. [Attendance](#5-attendance)
6. [Leave](#6-leave)
7. [Payroll](#7-payroll)
8. [Organization (Branches, Departments, Designations)](#8-organization)
9. [Documents](#9-documents)
10. [Administration (Users, Roles, Permissions)](#10-administration)
11. [Settings (Company Settings, Email, Audit Logs)](#11-settings)
12. [Reports – Complete List](#12-reports--complete-list)
13. [Notifications](#13-notifications)
14. [Your Profile, Password and Logout](#14-your-profile-password-and-logout)
15. [Master Reference – Every Checkbox, Toggle and Switch](#15-master-reference--every-checkbox-toggle-and-switch)
16. [Common Workflows](#16-common-workflows)
17. [Status Explanations](#17-status-explanations)
18. [Common Problems and Solutions](#18-common-problems-and-solutions)
19. [Role-Based Guide](#19-role-based-guide)
20. [A–Z Quick Reference](#20-az-quick-reference)
- [Appendix A – Things this HRMS does not have](#appendix-a--things-this-hrms-does-not-have)

---

# 1. HRMS Overview

## 1.1 What is this HRMS?

It is a web-based Human Resource Management System. One place to keep:

- **Employee records** (personal, job, bank, family, education, documents)
- **Attendance** (punch in/out from the browser, shifts, holidays, weekly offs, corrections)
- **Leave** (apply, approve, balances, policies, carry forward, encashment)
- **Payroll** (salary structures, monthly payroll runs, payslips, loans, advances, bonus, reimbursements)
- **Organisation set-up** (branches, departments, designations)
- **Access control** (user logins, roles, permissions, audit trail)

You open it in a web browser and sign in with your work e-mail and password.

## 1.2 Who does what?

The HRMS ships with four standard roles. Your company may also have extra roles created by the administrator, so what *you* see depends on the role you were given.

| Role | In plain words | Typical things they do |
|---|---|---|
| **Company Admin** | Full control | Everything, including settings, roles, locking/paying payroll, audit logs |
| **HR Manager** | Day-to-day HR operations | Add employees, manage shifts/leave/payroll set-up, generate and approve payroll, final (Level 2) leave approval |
| **Manager** | Team leader | Approve team leave (Level 1) and attendance corrections, view team attendance/leave, own self-service |
| **Employee** | Self-service | Punch in/out, request attendance corrections, apply/cancel leave, view own balance, download own payslips |

> **Important:** the menu is built from *permissions*. If a menu is missing for you, your role simply does not include it. Ask your administrator – you cannot unlock it yourself.

## 1.3 Signing in

1. Open the HRMS web address given by your company.
2. Enter your **Email** and **Password**.
3. Tick **Remember me** if you want to stay signed in on this device (up to 30 days). This only works if your account allows it (see 10.1 *Allow "Remember Me"*).
4. Click **Sign in**.

What can happen:

| Situation | What you see | What to do |
|---|---|---|
| Wrong e-mail or password | "Invalid email or password." | Try again. After **5** wrong attempts the account is locked for **15 minutes**. |
| Too many tries from your network | "Too many login attempts…" | Wait a minute and retry. |
| Account locked | "…temporarily locked due to repeated failed attempts." | Wait 15 minutes, or ask HR/Admin to unlock it. |
| You received a temporary password | After sign-in you are taken straight to **Change password** | Set a new password (rules in 14.2). |
| Company requires verified e-mail | "Please verify your email address before signing in…" | Open the verification e-mail and click the link, or ask the administrator to resend it. |
| Web login switched off for your account | "Web login is disabled for this account." | Contact your administrator. |

**Forgot password?** On the sign-in page click **Forgot password**, enter your e-mail and click **Send reset link**. If the address is registered, a link arrives that is valid for **30 minutes**. Open it, type a new password twice, click **Reset password**. If the link has expired, click **Request a new link**.

Your session also ends automatically after a period of inactivity (about 2 hours), so you may be asked to sign in again.

## 1.4 The screen layout

```
┌───────────────┬──────────────────────────────────────────────────────────┐
│ Company logo  │ Page title / breadcrumb      [Search] [+] [🌙] [🔔] [User ▾]│  ← Top bar
│ Search menu…  ├──────────────────────────────────────────────────────────┤
│ ★ Favorites   │                                                          │
│ Recent        │                 The page you are working on              │
│ Dashboard     │                                                          │
│ Employees     │                                                          │
│ Attendance ▸  │                                                          │
│ Leave ▸       │                                                          │
│ Payroll ▸     │                                                          │
│ …             │                                                          │
│ [You]  → Profile                                                         │
└───────────────┴──────────────────────────────────────────────────────────┘
```

### Sidebar (left)
- Shows your **company logo and name** at the top (a letter badge is shown if no logo has been uploaded).
- **Search menu…** box – type to filter the sidebar items.
- **Favorites** – hover over any menu item and click the **★** to pin it. Pinned items appear under *Favorites* at the top. Click ★ again to remove it.
- **Recent** – the last few pages you opened are listed automatically.
- Menu items are grouped under headings (Attendance, Leave, Payroll, Organization, Administration, Settings). Single items such as *Dashboard* and *Employees* sit on their own.
- Your **name and e-mail** at the bottom open your **Profile**.
- Use the **collapse** button (panel icon) at the top to shrink the sidebar; on a phone use the **☰ menu** button.

Favorites and Recent are remembered only in the browser you are using.

### Top bar (right side, left to right)
| Item | What it does |
|---|---|
| **Search box / Ctrl + K** | Opens the quick search. Type to find **pages**, **employees** and **users**. Use ↑ ↓ and Enter, Esc to close. With nothing typed it shows your recent searches. |
| **+ (Quick add)** | A shortcut menu. It only appears if you may create things, and offers **Employee**, **Leave application** and **User** (each only if allowed). |
| **Moon icon** | Switches between light and dark appearance for *you*. Remembered in this browser. |
| **Bell** | Notifications (see Section 13). A red number shows unread items. |
| **Your name** | Opens: **My profile**, **Change password**, **Log out other devices**, **Logout**. |

### Breadcrumb
Under the page title you see *Home › Group › Page*. Click *Home* to go to the Dashboard.

## 1.5 Common page controls (used everywhere)

| Control | Meaning |
|---|---|
| **Filter bar** (search box, drop-downs, date fields) | Narrows the list. Most lists refresh as you change a filter. |
| **Clear** | Removes all filters. |
| **Refresh** | Reloads the page data. |
| **Pencil icon** | Edit the row. |
| **Trash icon** | Delete the row (you are asked to confirm). |
| **Archive icon / Restore** | Moves a record out of the active list without destroying it; **Restore** brings it back. |
| **Eye icon** | View details. |
| **Export** (drop-down) | Downloads the list or report as **Excel (.xlsx)**, **CSV** or **PDF**. |
| **Side panel (drawer)** | Many small forms (e.g. Add branch, Add shift) slide in from the right. **Cancel** closes it without saving. |
| **Confirmation pop-up** | Appears before risky actions (delete, approve payroll, suspend…). Read it, then confirm or cancel. |
| **Green / red message** | Shown after you save: green = success, red = something to fix (read the text). |

---

# 2. Your Menu – What You See and Why

The sidebar below is the complete menu of this HRMS. Each line only appears if your role has the matching permission. The columns show who gets it **by default** with the four standard roles (A = Company Admin, H = HR Manager, M = Manager, E = Employee). Your company may have changed this.

| Menu | Sub-menu | A | H | M | E |
|---|---|:-:|:-:|:-:|:-:|
| **Dashboard** | – | ✔ | ✔ | ✔ | ✔ |
| **Employees** | – (list, profile, add/edit) | ✔ | ✔ | view only | – |
| **Attendance** | Dashboard | ✔ | ✔ | ✔ | – |
| | My Attendance | ✔ | ✔ | ✔ | ✔ |
| | Attendance (company list) | ✔ | ✔ | ✔ | – |
| | Shifts | ✔ | ✔ | – | – |
| | Shift Assignments | ✔ | ✔ | – | – |
| | Weekly Off | ✔ | ✔ | ✔ (view) | ✔ (view) |
| | Holidays | ✔ | ✔ | ✔ (view) | ✔ (view) |
| | Locations | ✔ | ✔ | ✔ (view) | – |
| | Devices | ✔ | ✔ | – | – |
| | Biometric Import | ✔ | ✔ | – | – |
| | Excel Import | ✔ | ✔ | – | – |
| | Regularizations (approval list) | ✔ | ✔ | ✔ | – |
| | Request Regularization | ✔ | ✔ | ✔ | ✔ |
| | Reports | ✔ | ✔ | ✔ | – |
| | Settings | ✔ | – | – | – |
| **Leave** | My Leave | ✔ | ✔ | ✔ | ✔ |
| | Leave Applications | ✔ | ✔ | ✔ | – |
| | Leave Calendar | ✔ | ✔ | ✔ | – |
| | Balances | ✔ | ✔ | ✔ | – |
| | Carry Forward | ✔ | ✔ | – | – |
| | Encashment | ✔ | ✔ | ✔ | – |
| | Leave Types | ✔ | ✔ | – | – |
| | Leave Policies | ✔ | ✔ | – | – |
| | Reports | ✔ | ✔ | ✔ | – |
| | Settings | ✔ | ✔ | – | – |
| **Payroll** | My Payroll | ✔ | ✔ | – | ✔ |
| | Dashboard | ✔ | ✔ | – | – |
| | Payroll Runs | ✔ | ✔ | – | – |
| | Salary Components | ✔ | ✔ | – | – |
| | Salary Structures | ✔ | ✔ | – | – |
| | Employee Salary | ✔ | ✔ | – | – |
| | Loans / Advances / Bonus / Incentives / Reimbursements | ✔ | ✔ | – | – |
| | Arrears | ✔ | ✔ | – | – |
| | Payslips | ✔ | ✔ | – | – |
| | Reports | ✔ | ✔ | – | – |
| | Settings | ✔ | – | – | – |
| **Organization** | Branches, Departments, Designations | ✔ | view | – | – |
| **Administration** | Users | ✔ | ✔ (no delete) | – | – |
| | Roles, Permissions | ✔ | – | – | – |
| **Settings** | Company Settings | ✔ | – | – | – |
| | Audit Logs | ✔ | – | – | – |

Notes that matter:

- A **Manager** can see company-wide lists for the screens marked ✔ (Attendance, Leave Applications, Balances …), but can only **act** (approve/reject) on requests from people who report to them. HR Manager and Company Admin act company-wide.
- Several powerful buttons are Company-Admin-only by default: **Lock / Unlock payroll**, **Mark payroll as Paid**, **Payroll Settings**, **Attendance Settings**, **Company Admin leave override**, **Delete user**, **Delete employee (archive)**.
- Two pages exist that have **no sidebar link**: **Attendance Overtime approval** and **Bulk mark attendance**. Open *Bulk mark* from the **Bulk mark** button on the Attendance list. Overtime approval is reached by typing `/attendance/overtime` after your HRMS address (see 5.20).
- Quick search (Ctrl + K) lists only pages that appear in *your* sidebar.

---

# 3. Dashboard

**Purpose:** a one-screen summary. The same page shows different boxes depending on your permissions.

**Who:** everyone.

**What you may see**

| Box | Shown to | Meaning |
|---|---|---|
| Company | Anyone who can see company numbers | Your company name |
| Users, Subscription | Admin level | Number of user logins; subscription status |
| Active employees | Employees with company-wide view | Count of employees whose status is *Active* |
| Present today | Same | Employees marked Present or Late today |
| Regularizations pending | Those who may approve corrections | Correction requests waiting for a decision |
| Leave requests pending | Those who may approve leave | Leave applications waiting |
| Payroll runs awaiting approval | Those who may view payroll | Payroll runs in *Draft/Generated* state |
| **My status today** | Anyone linked to an employee record | Your attendance (Punched in / Not punched in / today's status), your leave balance in days, and your pending regularizations |
| Attendance, last 7 days | Company view | Small chart of people present per day |
| Upcoming birthdays / holidays | Company view | The next 30 days ("None in the next 30 days" if empty) |

There are no buttons to configure; it is read-only.

---

# 4. Employees

**Menu:** Employees

## 4.1 Employee List

**Purpose:** the master list of everyone in the company.

**Who can use it:** Company Admin and HR Manager (full); Manager (view only – no Add/Edit buttons unless given extra rights). Employees do not have this menu.

**Filters (top of page)**

| Filter | What it does |
|---|---|
| Search box | Finds by employee ID, name, e-mail, mobile, PAN or Aadhaar number |
| Branch / Department / Designation | Shows only employees in that branch/department/designation |
| Status | Active, Probation, Notice Period, Suspended, Resigned, Terminated, Retired, Absconded, Relieved |
| Employment type | Full Time, Part Time, Contract, Intern, Consultant |
| Manager | Shows people who report to the chosen manager |
| **Clear** | Resets the filters |

**Columns:** Employee (photo/initials, name, code), Department, Designation, Branch, Mobile, Joining Date, Status.

**Buttons**

| Button | What happens |
|---|---|
| **Add employee** | Opens the new-employee form (4.2). |
| **Import** | Bulk-load employees from a spreadsheet (4.7). |
| **Export** ▾ | Downloads the *currently filtered* list as Excel, CSV or PDF. |
| **Archived** | Shows archived employees (4.8). |
| **Refresh** | Reloads the list. |
| 👁 View | Opens the employee profile (4.4). |
| ✏ Edit | Opens the edit form. |
| 🗄 Archive | After confirmation, moves the employee to the Archived list (nothing is permanently deleted). Only for people with delete rights. |

## 4.2 Add Employee / Edit Employee

The form has tabs: **Basic Information**, **Contact Information**, **Organization**, and **Login Account** (only if you may manage users). A tab turns red if it contains a field that needs fixing. Click **Create employee** (or **Save changes** when editing) at the bottom; **Cancel** leaves without saving.

### Employee Code (automatic)
You do not type it. When you click *Create employee* the system gives the next number in the company sequence, for example **EMP000001**, **EMP000002**… A code is never reused.

### Tab 1 – Basic Information

| Field | What it means | Required | What to enter / example | After saving |
|---|---|:-:|---|---|
| First name | Given name | **Yes** | `Anita` | Shown everywhere (lists, payslips, reports). |
| Middle name | Middle name | No | `Kumari` | Part of the full name. |
| Last name | Surname | **Yes** | `Sharma` | Shown everywhere. |
| Gender | Male / Female / Other | No | Male | Stored on the profile. |
| Date of birth | Birthday | No | `1994-05-12` | Used for the **Upcoming birthdays** box and birthday reminders to HR. |
| Blood group | A+, A-, B+, B-, AB+, AB-, O+, O- | No | O+ | Stored on the profile. |
| Marital status | Single / Married / Divorced / Widowed | No | Married | Stored on the profile. |
| Nationality | Country of citizenship | No | `Indian` | Stored. |
| Aadhaar number | 12-digit national ID | No | `123412341234` (exactly 12 digits) | Stored; searchable in the list. A wrong length is rejected. |
| PAN number | 10-character tax ID | No | `ABCDE1234F` (5 letters, 4 digits, 1 letter, capitals) | Stored; searchable. A wrong pattern is rejected. |
| Passport number | Passport | No | `K1234567` | Stored. |
| Driving license | License number | No | `DL-0420110012345` | Stored. |

### Tab 2 – Contact Information

| Field | Meaning | Required | Example | After saving |
|---|---|:-:|---|---|
| Mobile | Main phone | **Yes** | `9876543210` (exactly 10 digits) | Shown in the employee list. |
| Alternate mobile | Second phone | No | `9123456780` | Stored. |
| Personal email | Private e-mail | No | `anita@gmail.com` | Used to e-mail a payslip notice if there is no company e-mail. |
| Company email | Work e-mail | No | `anita@company.com` | Used for payslip notices; also pre-fills the *Login email* when you enable login. |

### Tab 3 – Organization

| Field | Meaning | Required | What to choose | After saving |
|---|---|:-:|---|---|
| Branch | Office/location the person belongs to | **Yes** | Pick from the list (set up under Organization → Branches) | Decides which **attendance locations (geofence)**, **holidays**, **weekly-off rules** and **Professional Tax state** apply to the employee. |
| Department | Team/function | **Yes** | Pick | Used for filters, reports, leave policy scope, shift assignment. |
| Designation | Job title | **Yes** | Pick | Same as above. |
| Reporting manager | The person who approves this employee's leave (Level 1) and attendance corrections | No | Search by name or employee code | **Very important:** leave goes to this person first. If left blank, Level 1 is skipped and the application goes straight to HR for approval. |
| Employment type | Full Time, Part Time, Contract, Intern, Consultant | No (defaults to Full Time) | Choose | Used for filters, leave-policy scope and shift assignment. |
| Employment category | Free-text grouping | No | `Permanent`, `Trainee` | Can be used when bulk-assigning shifts. |
| Current shift | The shift this employee works | No | Pick a shift (or "No shift assigned") | See Section 5 – attendance late/early/overtime calculations depend on the shift. |
| Shift effective from | Date the chosen shift starts | No (defaults to today) | `2026-10-01` | When you *change* the shift, the new shift takes effect on this date and the old assignment is kept as history. |
| Work location | Free-text site name | No | `Pune HQ – 3rd floor` | Information only. |
| Status | Employee lifecycle status | Yes | Active, Probation, Notice Period, Suspended, Resigned, Terminated, Retired, Absconded, Relieved (new employees start as **Probation**) | See Section 17 for what each status does. Changing it to some statuses switches the linked login off (4.5). |
| Date of joining | First working day | **Yes** | `2026-09-01` | Payroll only includes employees who joined on or before the period end. |
| Date of confirmation | Date made permanent | No | `2027-03-01` | Stored. |
| Probation period (months) | Length of probation | No (0–24) | `6` | Stored. |

### Tab 4 – Login Account (optional)
See 4.5 and Section 10.

## 4.3 Edit Employee
Same form as above, filled with the current data. Changing the **Reporting manager** affects *new* leave and correction requests from then on. Changing **Branch** changes which office location and holidays apply from then on.

## 4.4 Employee Profile ("360° view")

Click an employee's name or the 👁 icon.

**Header buttons:** *Edit*, *Print* (opens a printable PDF profile), and a **More** menu.

**More menu** (only options that make sense for the current status are shown):

| Option | What happens |
|---|---|
| **Activate** | Sets status to Active (and re-enables the linked login). |
| **Suspend** | After confirmation, status → Suspended. The linked login becomes inactive, so the person cannot sign in. |
| **Relieve** | After confirmation, status → Relieved. Login becomes inactive. |
| **Rejoin** | Brings a former employee back to Active. |
| **Archive** | After confirmation, moves the profile to *Archived* (only with delete rights). |

**Photo:** click the camera icon on the picture to upload a JPEG, PNG or WEBP photo.

**Tabs on the profile**

| Tab | Contains |
|---|---|
| **Overview** | Summary of Contact, Organization, Personal and Identity details. |
| **Personal** | Personal details plus **Address** – *Permanent* and *Current* (use the **Same as permanent** tick-box to copy). Address line 1/2, city, state, PIN etc. Click **Save address**. |
| **Organization** | Job details and the **Current Shift** card (or a note that there is no shift assigned and no company default shift). |
| **Bank Details** | Bank accounts (4.6). |
| **Documents** | Uploaded files (Section 9). |
| **Family** | Family members (4.6). |
| **Emergency** | Emergency contacts (4.6). |
| **Education** | Qualifications (4.6). |
| **Experience** | Previous jobs (4.6). |
| **Activity** | Status history (every status change, who did it and when) plus recent activity. Users with the status-change right also see the **Change status** form. |
| **Login Account** | Only if you may view users – see 4.5. |

### Change status form (Activity tab)
| Field | Meaning |
|---|---|
| Status (drop-down) | Probation, Notice Period, Resigned, Terminated, Retired or Absconded. (Use the **More** menu for Activate/Suspend/Relieve/Rejoin.) |
| Remarks | Optional reason, saved in the history. |
| **Apply** | Changes the status if the move is allowed. |

Allowed moves: Probation → Active, Notice Period, Resigned, Terminated, Absconded. Active → any of the other statuses. Notice Period → Active, Resigned, Terminated, Relieved. Suspended → Active, Terminated, Relieved. Resigned → Relieved or Active. Terminated / Retired / Relieved → Active (rejoin). Absconded → Active or Terminated. An impossible move shows an error such as *"Cannot move an employee from 'terminated' to 'resigned'."*

## 4.5 Employee Account (login) and Role/Access

An employee can exist **without** a login. A login is what lets the person sign in and use self-service.

**On the Add/Edit form (Login Account tab, new employee)**

| Control | Meaning |
|---|---|
| **Enable login for this employee** (switch) | **ON:** a login is created together with the employee. You must pick a Role and a Login email, and a temporary password is generated and shown **once** after saving. **OFF:** no login is created; you can create one later from the profile's *Login Account* tab. |
| Role | Decides what menus the person gets (Employee, Manager, HR Manager, Company Admin or a custom role). |
| Login email | The e-mail used to sign in (pre-filled from the company e-mail). |
| Username | Shown as a preview; created automatically from the name and cannot be edited at creation. |

> Copy the temporary password when it appears – it is displayed only once. The employee must change it at first sign-in.

**Profile → Login Account tab (existing login)** – for users with user-edit rights:

| Action / button | What happens |
|---|---|
| **Create Login Account** (if none) | Pop-up asks for Login email and Role; creates the login and shows a temporary password once. |
| **Disable Login / Enable Login** | Blocks or restores sign-in without deleting the account. |
| **Lock Account** | Asks for a **reason**; signs the person out everywhere and blocks sign-in until unlocked. (You cannot lock your own account.) |
| **Unlock Account** | Removes a lock (including the automatic 15-minute lock after failed attempts). |
| **Generate Temp Password** | Makes a new temporary password, shown once. |
| **Set Password Manually** | You type a new password twice; the person is signed out everywhere and must change it again at next sign-in. |
| **Send Reset Link** | E-mails the person a password-reset link. |
| **Expire Password** | Forces the person to choose a new password at next sign-in. |
| **Update Role** | Changes the role (and so the menus) of the person. |
| **Logout All Devices** / per-device **Logout** | Ends "Remember me" sign-ins and sessions. |
| **Save Settings** | Saves the four Account Settings switches (see Section 15, 15.14). |
| Self-Service Access badges | Read-only list showing which self-service areas this person's role currently allows. To change it, change the role. |
| Login history & Account audit log | Read-only lists of recent sign-ins and account actions. |

## 4.6 Sub-records on the profile (Bank, Family, Emergency, Education, Experience)

Each tab has an **Add** button that opens a side panel, and ✏ Edit / 🗑 Delete icons on each row. Bank details need extra permission (view / edit).

### Bank Details
| Field | Meaning | Required | Example |
|---|---|:-:|---|
| Account holder name | Name on the bank account | Yes | `Anita Sharma` |
| Bank name | Bank | Yes | `HDFC Bank` |
| Branch | Bank branch | No | `Koregaon Park` |
| Account number | Account no. | Yes | `50100123456789` |
| IFSC code | Bank branch code | Yes | `HDFC0000123` |
| UPI ID | UPI handle | No | `anita@okhdfc` |
| **Primary account** (tick) | See 15.12 | – | – |

The **primary** account is the one used for the payroll **bank transfer file**.

### Family
Name\*, Relationship\*, Date of birth, Occupation, and ticks **Dependent** and **Nominee** (15.13).

### Emergency contacts
Name\*, Relationship\*, Phone\*, Alternate phone, Address, **Priority** (1 = call first).

### Education
Qualification\*, Institution\*, Board/University, Percentage/CGPA, Year of passing (1950–2100).

### Experience
Company name\*, Designation, From date\*, To date (leave blank = still there), Years of experience, Reason for leaving.

(\* = required)

## 4.7 Import Employees

1. Click **Import** on the Employee list.
2. Click **Download sample template** and fill it in (use the same column headings).
3. Choose the file (.xlsx, .xls or .csv) or drag it into the box, then click **Preview import**.
4. The preview lists each row with a status. Fix errors in your file and start over if needed.
5. Optional tick **Skip duplicate employee codes** (15.11).
6. Click **Confirm import (N)** – N is the number of valid rows. **Start over** discards the preview.

## 4.8 Archived Employees
Lists archived employees (search by ID or name). Click **Restore** beside a person to bring them back to the active list.

## 4.9 Export
**Export ▾ → Excel / CSV / PDF** downloads exactly what the filters currently show.

---

# 5. Attendance

**Menu group:** Attendance

## 5.1 How attendance works – the big picture

```
Employee punches IN / OUT  ─┐
Biometric file imported    ─┼─►  Punch records  ─►  Daily attendance record  ─►  Leave / Payroll
HR corrects / bulk marks   ─┤                         (status, hours, late,
Correction request approved ┘                          early exit, overtime)
```

- Every day an employee works has one **daily attendance record** with a *status* (Present, Late, Half Day, Absent, …), first-in time, last-out time, worked hours, late minutes, early-exit minutes and overtime minutes.
- The record is **calculated automatically** after every punch, import or approved correction, using the employee's **shift** and the numbers in **Attendance Settings**.
- Approved leave writes the leave status into the attendance record for those dates (see Section 6).
- **Payroll reads these daily records** to find present days, paid leave, Loss of Pay (LOP) and approved overtime (Section 7).

> **Important – absent days:** this HRMS does **not** automatically create an "Absent" record for a day when nobody punched. If an employee did not work and no leave was approved, HR should mark that day **Absent** (Bulk mark or Excel import) before running payroll. Days with no record at all are neither "present" nor "LOP" in payroll.

## 5.2 Attendance Dashboard

**Purpose:** today's snapshot for HR and managers.
**Who:** Company Admin, HR Manager, Manager.

Tiles: **Present Today**, **Absent Today**, **Late Today**, **On Leave Today**, **Weekly Off Today**, **Holidays This Month**. Button: **Refresh**. Links lead to the full Attendance list and to Reports.

## 5.3 My Attendance (punch in / punch out)

**Purpose:** the employee's own punch screen.
**Who:** everyone with a login linked to an employee record. If your login is not linked to an employee record you see *"No employee profile is linked to your login account…"* – ask HR to link it.

### What is on the screen

| Item | Meaning |
|---|---|
| **Live clock and date** | Your device's current time. |
| **GPS chip** | Shows *Locating…*, then *GPS locked (±Nm)* (N = accuracy in metres; smaller is better), or a problem message (see below). |
| **Device chip** | *Device ready* – this browser has an identity code used for device tracking (5.12). |
| **Distance chip** | After a punch: how many metres you are from the office and the result, e.g. *"35m from office (inside)"*. |
| **Retry location** | Appears when location failed; click to ask the browser again. |
| **PUNCH IN / PUNCH OUT button** | One big button. It says PUNCH IN when you have no open punch-in and turns into PUNCH OUT (red) after you punch in. |
| **Today's hours** | Hours worked so far today (after punch-out). |
| **Today's status** | Present, Late, Half Day, Missed Punch… or *Not started*. |
| **My Shift** | Today's and tomorrow's shift (or *Weekly Off* / *No shift assigned*), grace minutes and working hours of the shift. Night shifts carry a *Night* badge. |
| **Map** | Your office location(s) as circles; a red dot shows you once GPS is found. |
| **Recent punches** | Your last 10 punches: Type (In/Out), Time, Location name, Geofence result. |
| **Request regularization** | Button to correct a missed/wrong punch (5.16). |

### Step by step – normal day
1. Open **Attendance → My Attendance**. Allow location if the browser asks.
2. Wait for **GPS locked**.
3. Click **PUNCH IN**. A green message *"Punched in."* appears.
4. Work.
5. At the end of the day click **PUNCH OUT** (*"Punched out."*).
6. Your day's record updates immediately.

### Rules the punch button follows
- You must **punch in before you can punch out**, and you cannot punch in twice in a row ("Already punched in — punch out first.").
- You must wait **about 1 minute** between two punches ("…please wait a minute before punching again.").
- More than 20 attempts in a minute is blocked ("Too many punch attempts. Please slow down.").
- Several in/out pairs in one day are added together to give the total worked time.
- A punch-out after midnight on a **night shift** is counted for the day the shift **started**.

### Location messages and what they mean

| Chip / message | Meaning | What to do |
|---|---|---|
| *Locating…* | Browser is finding you | Wait a few seconds. |
| *GPS locked (±12m)* | Good fix | Punch. |
| *Location permission denied — allow location access for this site, then retry* | You or your browser blocked location | Allow location for the HRMS site in browser settings, click **Retry location**. |
| *Location timed out — try again* / *Location unavailable* | GPS could not get a fix in 10 seconds | Move near a window/outdoors, turn on phone location, click **Retry location**. |
| *Location needs HTTPS — open this page as https://* | The page was opened without the secure "https" address | Open the HRMS using its https:// link. |
| *GPS unavailable on this browser* | Browser has no location support | Use another browser/phone. |

### The geofence result (Geofence column)

An **office location** is a point on the map with a radius (5.11). The HRMS compares your position with the nearest active location of **your branch**.

| Result | Meaning |
|---|---|
| **Inside** | You are within the radius and the GPS accuracy is good (100 m or better). |
| **Outside** | You are farther than the radius from the nearest office location. |
| **Low accuracy** | You appear to be inside the radius, but the GPS accuracy is worse than 100 m, so the position is unreliable. |
| **GPS disabled** | No coordinates arrived with the punch (location blocked/unavailable). |
| **Not applicable** | Your branch has no active office location, or the punch did not come from the GPS screen (manual, imported or corrected punches). |

### What "GPS required" changes (Attendance Settings, 5.18)

| Situation | GPS required **ON** | GPS required **OFF** |
|---|---|---|
| Inside | Punch accepted | Punch accepted |
| Low accuracy (inside) | Punch accepted, marked *Low accuracy* | Punch accepted, marked *Low accuracy* |
| Outside | **Rejected**: *"Attendance not allowed — you are outside the office location (240m away). Please punch from the office."* | Accepted; recorded as *Outside* for HR to review |
| No location received | **Rejected**: *"Location access is required to punch — please enable GPS and try again."* | Accepted; recorded as *GPS disabled* |
| Branch has no office location configured | Accepted (*Not applicable*) | Accepted (*Not applicable*) |

### How the day's record is worked out

- **Worked time** = the sum of every IN→OUT pair. If the last punch is an IN with no OUT, the day shows **Missed Punch**.
- **Late minutes** = first punch-in time − shift start − *Grace minutes* (never below zero).
- **Early exit minutes** = shift end − last punch-out (if you left before the shift ended).
- **Overtime minutes** = worked time − shift length (shift end − shift start), only if *Overtime tracking* is on and a shift exists. (Break times set on a shift are information only; they are not deducted.)
- **Status**, in this order:
  1. No punches → *Holiday* if it is a holiday, else *Weekly Off* if it is a weekly off, else *Absent*.
  2. Last punch is an IN → *Missed Punch*.
  3. Worked time **at least the Half Day minutes but less than the Full Day minutes** → *Half Day*.
  4. Otherwise, if late minutes are **at least the Late Mark minutes** (and above zero) → *Late*.
  5. Otherwise → *Present*.
- If a day has **no shift** (nothing assigned and no default shift), late, early-exit and overtime are not calculated.
- Working fewer minutes than the Half-Day figure does **not** turn the day into *Absent* automatically – it still shows Present/Late. HR should correct such a day with **Edit** (5.6) if needed.

## 5.4 Attendance (company list)

**Purpose:** see and correct everyone's daily attendance.
**Who:** Company Admin, HR Manager, Manager (view). Editing needs the *Edit attendance* right; archiving needs *Delete attendance*.

**Filters:** From date, To date, Employee (search by name/code), Branch, Department, Status (Present, Absent, Half Day, Holiday, Weekly Off, Leave, On Duty, Work From Home, Late, Missed Punch). **Clear** resets.

**Columns:** Date, Employee, Shift, In, Out, Hours, Late, Status.

| Button | What happens |
|---|---|
| **Bulk mark** | Opens 5.5. |
| **Refresh** | Reloads. |
| ✏ **Correct** | Opens 5.6 for that row. |
| 🗄 **Archive** | After confirmation, removes the record from the list. |

## 5.5 Bulk Mark Attendance

**Purpose:** set the same status for many employees on one date (for example mark everyone *Absent* after a strike day, or *Holiday* for an unplanned closure, or load absences before payroll).

| Field | Meaning | Required | Example | After saving |
|---|---|:-:|---|---|
| Date | The day to mark | Yes | `2026-10-02` | Records are created/overwritten for this date. |
| Status | Present, Absent, Half Day, Holiday, Work From Home, On Duty | Yes | Absent | Becomes the status of each selected employee for the date. For Present / Work From Home / On Duty the worked time is set to the company's *Full day minutes* (e.g. 8 h). |
| Apply to | All active employees / Whole branch / Whole department / Selected employees | Yes | Whole department | Decides who is marked. |
| Branch / Department | Appear for the matching choice | Yes if chosen | – | – |
| Employees | Search and add people for *Selected employees* | Yes if chosen | – | – |

Click **Mark attendance**. A message tells you how many employees were marked. **Cancel** returns to the list. Existing records for that date are *overwritten*.

## 5.6 Correct (Edit) a daily record

Pick a new **Status** from the list (Present, Absent, Half Day, Holiday, Weekly Off, Leave, On Duty, Work From Home, Late, Missed Punch) and click **Save correction**. Use it for one-off fixes. The change is recorded in the audit trail.

## 5.7 Shifts

**Purpose:** define working hours (e.g. *General 09:00–18:00*).
**Who:** Company Admin, HR Manager.

Filter: search by name or code. Buttons: **Add shift**, ✏ Edit, 🗑 Delete (confirmation).

| Field | Meaning | Required | Example |
|---|---|:-:|---|
| Shift name | Friendly name | Yes | `General Shift` |
| Shift code | Short unique code | Yes | `GEN` |
| Start time | When the shift begins | Yes | `09:00` |
| End time | When it ends. If earlier than start it is a night shift crossing midnight | Yes | `18:00` |
| Break start / Break end | Lunch break window | No | `13:00` / `14:00` – information only, not deducted from hours |
| Grace (min) | Shift's own grace minutes | No (10) | `10` |
| Late (min) | Shift's own late threshold | No (15) | `15` |
| Half day (min) | Shift's half-day minutes | No (240) | `240` |
| Full day (min) | Shift's full-day minutes | No (480) | `480` |
| **Night shift (crosses midnight)** (tick) | See 15.1 | – | – |
| Status | Active / Inactive | – | Active |

> **Good to know:** the daily late / half-day / full-day calculations use the **company-wide numbers in Attendance Settings (5.18)**. The Grace / Late / Half / Full values typed on a shift are stored and shown (for example on *My Attendance*), but do not change the status calculation in this version.

An **Inactive** or deleted shift can no longer be assigned to anyone; people already on it keep their history.

## 5.8 Shift Assignments

**Purpose:** put employees on a shift from a date.
**Who:** Company Admin, HR Manager.

| Field | Meaning | Required | After saving |
|---|---|:-:|---|
| Shift | The shift to assign | Yes | – |
| Effective from | First day the shift applies | Yes (today) | Earlier days keep the previous shift. |
| Assign by | Individual employees / Whole department / Whole branch / Whole designation / Employment type / Employment category | Yes | Shows the matching selector below. |
| Employees / Department / Branch / Designation / Employment type / Employment category | Who gets the shift | Yes for the chosen method | Only **active** employees are included. |
| **Replace existing assignments** (tick) | See 15.2 | – | – |

Click **Assign shift**. A summary reports how many were assigned and who was skipped. The shift can also be set from the employee form. A **history** page lists every shift an employee has had (Shift, Effective From, Effective To, Changed By).

**Which shift is used for a date?** (1) the employee's own assignment active on that date; otherwise (2) the **Default shift** from Attendance Settings; otherwise none.

## 5.9 Weekly Off

**Purpose:** rules for non-working weekdays (e.g. every Sunday, 2nd & 4th Saturday).
**Who:** everyone can **view**; adding/editing/deleting needs *Edit attendance* (Admin, HR).

| Field | Meaning | Required | Example |
|---|---|:-:|---|
| Rule name | Label | Yes | `2nd & 4th Saturday Off` |
| Day of week | Sunday … Saturday | Yes | Saturday |
| Pattern | Every week · 1st · 2nd · 3rd · 4th · 5th · Alternate (2nd & 4th) | Yes | Alternate (2nd & 4th) |
| Branch | Blank = all branches | No | Pune |
| Shift | Blank = any shift | No | Night |
| Status | Active / Inactive | – | Active |

"1st/2nd/…" means the first, second… occurrence of that weekday in the month. When someone has **no punches** on a weekly-off day their record shows **Weekly Off** (not Absent) and payroll does not count it as a working day.

## 5.10 Holidays

**Purpose:** the company holiday list.
**Who:** everyone can **view**; add/edit/delete/generate need *Edit attendance*; import needs *Import attendance*.

Screens: **List** (choose Year; columns Holiday, Date, Day, Type, Branch, Optional) and **Calendar view** (month grid, ◀ ▶ to move, **List view** to return).

Buttons: **Add holiday**, **Import**, **Generate {next year} holidays**, ✏ Edit, 🗑 Delete.

| Field | Meaning | Required | Example |
|---|---|:-:|---|
| Holiday name | Name | Yes | `Diwali` |
| Date | Date | Yes | `2026-11-08` |
| Type | Public / Restricted / Company | No (Public) | Public |
| Branch | Blank = all branches | No | Chennai |
| **Optional holiday** (tick) | See 15.3 | – | – |
| **Recurs every year (fixed date)** (tick) | See 15.4 | – | – |
| Description | Note | No | – |
| Status | Active / Inactive | – | Active |

**Import:** *Download sample template*, fill columns **Holiday Name, Date, Type (public/restricted/company), Optional (Y/N), Description**, upload (.xlsx/.xls/.csv), click **Import**.

**Generate next year:** shows a table of this year's holidays with a proposed date next year (tick-box per row, **date** editable). Fixed-date holidays are filled automatically; holidays marked *not recurring* need you to type the new date. Click **Generate Holidays** to create them.

On a holiday where nobody punched, the daily record shows **Holiday**. A branch-specific holiday beats an all-branches one for people in that branch. Leave settings decide whether holidays inside a leave period are counted as leave (6.9).

## 5.11 Locations (office geofence)

**Purpose:** tell the system where each office is, so punches can be checked against it.
**Who:** view – Admin, HR, Manager; add/edit/delete – Admin, HR.

| Field | Meaning | Required | Example |
|---|---|:-:|---|
| Location name | Office name | Yes | `Head Office – Pune` |
| Branch | Which branch this office belongs to | Yes | Pune |
| Radius (meters) | How far from the point still counts as "inside" (minimum 10, suggested 200) | Yes | `150` |
| Latitude / Longitude | The office's map coordinates | Yes | `18.5204` / `73.8567` |
| Address | Text address | No | – |
| Status | Active / Inactive | – | Active |

> **Replace the sample coordinates!** The form is pre-filled with a sample point (28.6139, 77.2090). Copy the real latitude/longitude from a map service for your office. A branch can have several locations; the system uses the **nearest active** one. Only *active* locations of the employee's branch are checked.

## 5.12 Devices

**Purpose:** a register of the browsers/phones employees use to punch.
**Who:** Admin, HR.

A "device" is one browser on one computer or phone (identified by a random code the browser keeps). Clearing browser data or using another browser creates a *new* device.

Filter: Status (Pending, Approved, Rejected, Blocked). Columns: Employee, Device (code), Browser/OS, IP, First seen, Last seen, Status.

| Button | What happens |
|---|---|
| ✔ **Approve** | Marks the device Approved. |
| ✖ **Reject** | Marks it Rejected. |
| ⛔ **Block** | Marks it Blocked. |

How a device gets its first status depends on *Device approval required* (15.6). **Important:** in this version, a Pending, Rejected or Blocked device is a *flag for HR to review* – the punch itself is still accepted.

## 5.13 Biometric Import

**Purpose:** load punches exported from a fingerprint/face machine.
**Who:** Admin, HR (needs *Import attendance*).

1. Prepare a .xlsx/.xls/.csv with columns **Device Serial, Employee Code, Punch Time, Punch Type**.
2. Choose the file and click **Stage file** – rows are listed with status *pending*.
3. Click **Sync pending records**. Each row becomes a real punch for that employee and the day is recalculated. Rows that cannot be matched (for example an unknown employee code) are marked *failed*; rows that worked are marked *synced*. A message gives the counts.

## 5.14 Excel Import (daily attendance)

**Purpose:** load finished daily attendance in bulk.
**Who:** Admin, HR.

1. Click **Download sample template** (it has an Instructions sheet).
2. Fill **Employee Code, Attendance Date (DD-MM-YYYY), In Time (HH:MM), Out Time (HH:MM), Status**.
3. Upload the file (or drag it in) and click **Validate & preview**.
4. The preview shows **Total / Valid / Invalid / Existing (will update)** rows and a Result per row (for example *Employee Code 'X' not found*, *Out Time must be after In Time*, *Duplicate Employee Code + Attendance Date within this file*).
5. Click **Import attendance (N)**. Only valid rows are imported; if something fails nothing is saved. **Start over** discards the file.

Allowed statuses in the file are the ones HR can set by hand: Present, Absent, Half Day, Holiday, Work From Home, On Duty. Existing records for the same employee and date are overwritten.

## 5.15 Regularizations (approval list)

**Purpose:** review employees' attendance-correction requests.
**Who:** Admin, HR (company-wide); Manager (only for their own reporting team).

Filter: Status – Pending (default), Approved, Rejected, All. Columns: Employee, Date, Reason, Requested In, Requested Out, Status.

| Button | What happens next |
|---|---|
| ✔ **Approve** | The requested in/out times are added as correction punches, the day is recalculated, the record is marked as *regularized*, request → **Approved**, and the employee receives the notification *"Attendance correction approved"*. |
| ✖ **Reject** | Request → **Rejected**. The attendance record is not changed. |

A request that was already reviewed cannot be reviewed again. If you are not the person's manager you will see *"You are not authorized to review this request."*

## 5.16 Request Regularization

**Purpose:** ask for a correction when you forgot to punch or punched wrongly.
**Who:** everyone with My Attendance.

| Field | Meaning | Required | Example |
|---|---|:-:|---|
| Attendance date | The day to fix (today or earlier) | Yes | `2026-10-01` |
| Requested punch in | The correct in-time | No | `09:05` |
| Requested punch out | The correct out-time | No | `18:10` |
| Reason | Why | Yes | `Forgot to punch out – left at 6:10 pm` |
| Attachment | Proof (.pdf, .jpg, .jpeg, .png) | No | – |

Click **Submit request** – status **Pending**. Below the form, **My Regularization Requests** shows your past requests with their status. **Cancel** returns to My Attendance.

## 5.17 Attendance Reports
See Section 12.1.

## 5.18 Attendance Settings (every setting explained)

**Purpose:** company-wide attendance rules.
**Who:** Company Admin by default.
Click **Save settings** at the bottom to apply. Changes affect days calculated **from then on**.

### Default shift
**What it means:** the shift used for employees who have no personal shift assignment.
**Required:** No (choose *None* if you have no default).
**Example:** `General Shift`.
**Effect:** with a default shift everyone gets late/early/overtime calculations even without an individual assignment. With *None*, employees without their own shift get no late/early/overtime calculation.

### Grace Minutes
**What it means:** minutes after the shift start that are forgiven.
**Required:** yes, whole number ≥ 0 (default 10).
**Example:** shift starts 09:00, **Grace = 5**:

| Arrives | Late minutes recorded | Why |
|---|---|---|
| 09:03 | 0 | inside grace |
| 09:05 | 0 | exactly at the end of grace – still forgiven |
| 09:06 | 1 | one minute after the grace ended |
| 09:15 | 10 | ten minutes after the grace ended |

**Effect:** only minutes **after** the grace period count as late minutes (shown in the Late column and the Late report).

### Late Mark Minutes
**What it means:** how many late minutes (counted *after* grace) are needed before the day's **status becomes "Late"**.
**Required:** yes (default 15).
**Example:** shift 09:00, Grace 5, **Late Mark 15**:

| Arrives | Late minutes | Status |
|---|---|---|
| 09:05 | 0 | Present |
| 09:12 | 7 | Present (7 late minutes are recorded but below 15) |
| 09:20 | 15 | **Late** |
| 09:40 | 35 | **Late** |

With **Late Mark = 1**, 09:06 already becomes *Late*. With **Late Mark = 0**, any minute past grace is Late.
**Effect:** controls the Late status, the Late tile on the dashboard and "late days" in the monthly report. (The Late *report* lists every day with late minutes above zero.)

### Half Day Minutes
**What it means:** the minimum worked minutes for a day to count as a **Half Day**.
**Required:** yes (default 240 = 4 hours).
**Example:** Half Day = 240, Full Day = 480. Worked 3 h 50 m (230) → not half day; worked 4 h 10 m (250) → **Half Day**; worked 7 h (420) → **Half Day**; worked 8 h (480) → full day (Present/Late).
**Effect:** a Half Day record is paid as half present + half LOP in payroll (7.12).

### Full Day Minutes
**What it means:** the worked minutes that make a full working day. **480 means 480 minutes = 8 hours.**
**Required:** yes (default 480).
**Example:** worked 8 h 05 m → full day. Worked 7 h 59 m → Half Day.
**Effect:** besides the Half Day test, this number is (a) the hours given when HR bulk-marks someone *Present*, and (b) the length of a standard working day used to turn overtime hours into an hourly pay rate in payroll.

### GPS required (rejects punches outside radius)
Full table in 5.3. **Checked:** punches from outside the office radius, or without a location, are rejected with a message. **Unchecked:** every punch is accepted and the Inside/Outside/Low accuracy result is only recorded for HR to see. See also 15.5.

### Device approval required
**Checked:** a never-seen device is listed as **Pending** until HR approves it. **Unchecked:** new devices are listed as **Approved** automatically. In both cases the punch is accepted. See 15.6.

### Self attendance enabled
Intended to switch employee self-punching on or off. **In this version the switch is saved but the My Attendance punch screen does not change when it is turned off**, so do not rely on it to stop self-punching. See 15.7.

### Overtime tracking enabled
**Checked:** when someone works longer than their shift, the extra minutes are recorded as an **overtime entry** (Pending) that HR/Manager approve (5.19). **Unchecked:** no overtime entries are created and overtime minutes stay zero. See 15.8.

### Weekend policy / Holiday policy
**What they are:** two free-text boxes (defaults *Sunday Off* and *Paid*). There is **no list of options** – you type a short note describing company policy.
**Effect:** none on calculations in this version. Weekly offs are really driven by the **Weekly Off rules** (5.9) and holidays by the **Holidays** list (5.10). Treat these two boxes as policy notes.

### Timezone
**What it means:** the company's clock, written as a standard timezone name, e.g. `Asia/Kolkata`.
**Required:** yes.
**Why it matters:** it decides what "today", "now" and the cut-off between two days mean for punches, late minutes, payroll due dates and logs. With the wrong zone, "today" can roll over at the wrong hour, so late minutes can be measured against the wrong clock and a late-night punch can land on the wrong date. Keep it the same as **Company Settings → Timezone** (11.1).

## 5.19 Overtime approval (no sidebar link)

**Purpose:** approve extra hours before they can be paid.
**Who:** needs *View attendance* to see and *Approve attendance* to decide (Admin, HR, Manager).
**How to open:** type `/attendance/overtime` after your HRMS web address.

Filter: Status (Pending default, Approved, Rejected, All). Columns: Employee, Date, Shift Min, Worked Min, OT Min, Status. Buttons ✔ **Approve**, ✖ **Reject**.

**What happens next:** payroll pays **only approved** overtime for the pay period (7.9). Pending or rejected overtime is not paid.

---

# 6. Leave

**Menu group:** Leave

## 6.1 The leave workflow in one picture

```
Employee: My Leave → Apply Leave → Submit
                           │
                           ▼
                      PENDING ── Level 1: Reporting Manager reviews
                           │         (skipped if the employee has no manager with a login)
                           ▼
                 Level 2: HR Manager / Company Admin reviews
                    │                      │
                    ▼                      ▼
                APPROVED               REJECTED
                    │
        ┌───────────┴─────────────┐
        ▼                         ▼
 Balance is reduced      Attendance for those days is set to
 (only now)              Leave / LOP / WFH / On Duty (per leave type)
```

Key points:
- The balance is reduced **only when the final approval happens**, not when you apply. When you apply, the system only checks that you have enough balance.
- A manager's **Level 1 approval does not approve the leave** – it just passes it to HR. A **Level 1 rejection ends it** (status Rejected).
- Nobody can approve or reject **their own** application (not even Company Admin – see "Company Admin override").
- Approved leave later **cancelled** gives the days back to the balance and removes the leave from attendance.

## 6.2 My Leave (employee self-service)

**Who:** everyone with a linked employee record. Tabs across the top: **Dashboard, Apply Leave, My Applications, Balance, Calendar, History**.

### Dashboard tab
Tiles: **Available Leave** (total days left), **Pending Approval**, **Approved This Year**, **Rejected**, **Upcoming Leave**, plus a table of your recent applications (Type, From, To, Days, Status).

### Apply Leave tab

| Field | What it means | Required | What to enter | After saving |
|---|---|:-:|---|---|
| Leave type | Kind of leave | Yes | e.g. Casual Leave (CL) | Rules of that type apply (attachment, half-day, etc.). |
| **Half day (single date only)** (tick) | See 15.15 | – | – | – |
| First Half / Second Half | Appears when Half day is ticked: which half you will be away | Yes if half day | Second Half | – |
| From date / To date | First and last day of leave | Yes | `2026-10-12` → `2026-10-14` | The system counts only real working days in this range. |
| Reason | Why | Yes | `Family function` | Seen by approvers. |
| **Emergency leave (bypasses minimum notice)** (tick) | See 15.16 | – | – | – |
| Emergency contact name / phone | Who to reach during leave | No | `Ravi – 98xxxxxx10` | Shown to approvers. |
| Delegate to | A colleague covering your work | No | choose | Recorded on the application. |
| Delegation notes | Hand-over instructions | No | `Invoices pending – ask Priya` | Recorded. |
| Attachment | Supporting file | Only if the leave type requires it | medical certificate | Stored with the application. |

Click **Submit application**. If any rule fails you stay on the form with a red message. Rules checked (in simple words):

| Message you may see | Why |
|---|---|
| Half-day leave is not allowed for this leave type | The leave type or the company setting disallows half days |
| Leave cannot be applied for a past date | From date is before today |
| This date is too far in the future to apply for leave | Beyond "Max future apply days" |
| Selected dates contain no working leave days | All the chosen days are holidays/weekly offs |
| This overlaps an existing pending or approved leave application | You already have leave on those dates |
| Minimum of N day(s) per application / Maximum consecutive leave is N day(s) / Maximum number of applications reached | Policy limits |
| This leave type requires at least N day(s) notice | Apply earlier, or tick *Emergency leave* |
| An attachment / medical certificate is required for this leave type | Upload the file |
| Insufficient leave balance: X available, Y requested | Not enough balance (unless the company allows negative balance; unpaid types skip this check) |
| Half-day leave can only be applied for a single date; cannot apply half-day on a holiday or weekly off | Choose correct dates |

After a successful submit the application is **Pending**, and your Level-1 approver (reporting manager) gets a notification *"<Name> applied for <Leave type>"*.

### My Applications tab
Filter by Status (Draft, Pending, Approved, Rejected, Cancelled). Columns: Type, From, To, Days, Status, Level (Level1/Level2 while pending). The red ✖ **Cancel** icon appears on **Pending** applications.

> An employee can cancel only a **Pending** application. Once it is Approved, cancellation must be done by HR or a manager with cancel-any rights (6.4).

### Balance tab
One row per leave type: **Opening, Earned, Availed, Adjusted, Carry Fwd In, Encashed, Closing Balance**.

| Column | Meaning |
|---|---|
| Opening | Balance at the start of the leave year |
| Earned | Days credited to you this year |
| Availed | Days already used by approved leave |
| Adjusted | Manual corrections by HR (plus or minus) |
| Carry Fwd In | Days brought forward from the previous year |
| Encashed | Days converted to money |
| Closing Balance | What you can still use |

### Calendar and History tabs
Calendar shows leave on a calendar; History lists your closed (Approved, Rejected, Cancelled) applications.

## 6.3 Leave Applications (approval desk)

**Purpose:** review and act on everyone's leave.
**Who:** Admin, HR, Manager (see all; act as allowed).

**Filters:** Status, Level (Level 1 Manager / Level 2 HR), Leave type, Branch, Department, From/To date, **Clear**. A **Reports** button leads to 6.12.

**Open an application** to see leave type, dates, total days, half day, reason, emergency contact, attachment, delegation, the **Day breakdown** table (Date, Type, Category, Counts, Value) and the **Approval history**.

**Take action panel** (title shows the current level):

| Button | Who can use it | What happens |
|---|---|---|
| **Approve at this level** (+ optional Remarks) | Level 1: the employee's reporting manager. Level 2: HR Manager or Company Admin | Level 1 → passes to HR (still Pending). Level 2 → **Approved**: balance reduced, attendance updated, employee notified *"Your leave request was approved"*. |
| **Reject** (+ Remarks) | Same as above | Status **Rejected**; employee notified *"Your leave request was rejected"* with your remarks. |
| **Override approve / Override reject** (+ **Override reason**, required) | **Company Admin only** | Skips the normal chain. The reason is stored permanently. If the Admin is the applicant, this works only when the setting *"Company Admin may override-approve own leave"* is ticked (15.25). |
| **Cancel this leave** (+ Cancellation reason) | HR / Manager with *cancel any* right | Pending or Approved → **Cancelled**. If it had been Approved, the balance is restored and the attendance entries are reversed. |

You will see *"You cannot approve or reject your own leave application"* if you try to decide your own request, and *"You are not the assigned approver for this application"* if you are not the employee's reporting manager (HR/Admin act at Level 2).

**Bulk actions:** tick several rows, then **Bulk approve** (approves each at its *current* level), **Bulk reject** or **Bulk cancel**. Each asks for confirmation; a summary tells how many succeeded and which were skipped.

## 6.4 Leave Calendar
Month view with ◀ ▶ and a **Year view** (counts of employees on leave per month). Choose a **Department** to see a department calendar; clear it for the whole company. **Month view** returns from the year view.

## 6.5 Balances

**Purpose:** see and adjust every employee's leave balances.
**Who:** view – Admin, HR, Manager; adjust – Admin, HR.

Filters: search employee, Branch, Department. Each row shows the employee's balance per leave type. The **Adjust** icon opens:

| Field | Meaning | Required | Example | After saving |
|---|---|:-:|---|---|
| Leave type | Which balance (shows current closing balance) | Yes | Casual Leave (current: 6.0) | – |
| Adjustment (+/-) | Days to add (positive) or remove (negative), steps of 0.5 | Yes | `2` or `-1.5` | Changes the **Adjusted** column and the closing balance immediately. |
| Reason | Why | Yes | `Joining bonus leave` | Stored. |
| Effective date | Date of the adjustment | No (today) | – | Stored. |

Click **Apply adjustment**. **Back** leaves.

## 6.6 Carry Forward

**Purpose:** at year end, move unused leave into next year.
**Who:** Admin, HR.

1. Choose **From financial year** (defaults to last year, shown like *2025–2026*).
2. Click **Run carry forward**.
3. For each active employee and each leave type that allows carry forward, the unused (positive) balance – capped at the limit – is added to **next year's** opening as *Carry Fwd In*. A batch row is written to the history table (Batch, From FY, To FY, Employees, Total Carried, Run At).

Rules: it works only if **Carry forward enabled** is ticked in Leave Settings (otherwise: *"Carry forward is disabled in leave settings."*). It can be run **only once** per financial year (*"Carry forward has already been run for FY …"*), so check before you click. The cap is the policy rule's limit, or the fallback limit in Leave Settings; *Unlimited CF* in a policy rule removes the cap.

## 6.7 Encashment

**Purpose:** convert unused leave into money.
**Who:** list – Admin, HR, Manager; **Request encashment** – anyone with the *apply for leave* right; approve/reject – Admin, HR.

| Field | Meaning | Required | Example |
|---|---|:-:|---|
| Leave type | Which leave to encash | Yes | Earned Leave |
| Days to encash | Number of days (min 0.5, steps 0.5) | Yes | `3` |

Click **Submit request** → status **Pending**. Columns: Employee, Leave Type, Days, Amount (placeholder), Status. ✔ **Approve** / ✖ **Reject**. **The rupee amount is not calculated here** – the screen says the amount will be worked out by Payroll; this page only records the request and its approval.
Errors you may see: *Leave encashment is disabled in leave settings*, *This leave type does not allow encashment*, *Your leave policy does not allow encashment for this leave type*, *Requested encashment days exceed the available balance*.

## 6.8 Leave Types

**Purpose:** define each kind of leave.
**Who:** Admin, HR. Filters: search, Status, Paid/Unpaid. Buttons: **Add leave type**, ✏, 🗑.

| Field | Meaning | Required | Example | After saving |
|---|---|:-:|---|---|
| Leave name | Display name | Yes | `Casual Leave` | Appears in drop-downs and reports. |
| Leave code | Short code (max 20, upper-case) | Yes | `CL` | Shown beside the name; must be unique. |
| Color badge | Colour used in lists/calendars | No | blue | Cosmetic. |
| Description | Note | No | – | – |
| Annual allocation (default) | Days given per year when no policy rule says otherwise | No (0) | `12` | Credited when the employee's balance for the year is first created. |
| Attendance status on approval | What the attendance record shows on approved full-day leave: **Leave, Loss of Pay, Work From Home, On Duty** | Yes | Leave | Decides whether payroll treats the day as paid leave (Leave) or LOP, etc. (half-day leave is always recorded as "Half Day Leave"). |
| Sort order | Position in lists | No | `1` | Cosmetic. |
| Status | Active / Inactive | – | Active | Inactive types cannot be applied for. |
| **Paid** (tick) | See 15.17 | | | |
| **Half day allowed** | 15.18 | | | |
| **Attachment required** | 15.19 | | | |
| **Medical certificate required** | 15.20 | | | |
| **Carry forward allowed** | 15.21 | | | |
| **Encashment allowed** | 15.22 | | | |

## 6.9 Leave Policies

**Purpose:** give different groups different leave entitlements.
**Who:** Admin, HR. Buttons: **Add policy**, 📋 **Rules**, ✏ Edit, 🗑 Delete.

### Policy form
| Field | Meaning | Required | Example |
|---|---|:-:|---|
| Policy name | Label | Yes | `Managers – Pune` |
| Priority (tie-breaker) | Higher number wins when two policies are equally specific | No (0) | `5` |
| Description | Note | No | – |
| Branch / Department / Designation / Employment type | Who it applies to. **Blank = anyone** on that dimension | No | Department: Sales |
| Individual employee | One person; **overrides all other scoping** | No | `EMP000012 — Anita Sharma` |
| Effective from / Effective to | Validity dates | No | `2026-04-01` |
| **Company-wide default policy** (tick) | 15.23 | | |
| Status | Active / Inactive | | Active |

**Which policy applies to a person?** Among active, in-date policies that match the person, the **most specific** wins: Individual employee beats Designation, which beats Department, then Branch, then Employment type; the Priority number breaks ties.

### Rules screen (per leave type, inside a policy)
Tick the first box to enable a leave type in this policy, then set:

| Column | Meaning |
|---|---|
| Annual Allocation | Days per year for this group (overrides the leave type default) |
| Accrual (Annual / Monthly, days/mo) | In this version the **whole annual allocation is credited up front** whichever you choose; the monthly figure is stored for later use |
| Carry Fwd | Tick – unused days can carry forward (15.21) |
| CF Limit | Maximum days carried forward; blank = use the global fallback in Leave Settings |
| Unlimited CF | Tick – no cap on carry-forward (15.24) |
| Encash | Tick – encashment allowed for this group |
| Max Consec. | Longest single leave in days; blank = global |
| Min/App | Smallest allowed application (default 0.5) |
| Max Apps/Yr | Number of applications allowed per financial year; blank = unlimited |
| Sandwich | Tick – the sandwich rule applies to this leave type (15.26) |
| Notice Days | Days' notice required; blank = global |

Click **Save rules**. A leave type with no rule row simply uses the leave type's own defaults.

## 6.10 Leave Settings

**Purpose:** company-wide leave rules. **Who:** Admin, HR. Save with **Save settings**.

| Setting | What it means | Example |
|---|---|---|
| Financial year start month | Month the leave year starts (used for balances, carry forward, "per year" limits) | April → year runs 1 Apr – 31 Mar |
| Leave year start month | Stored with the above | – |
| Half day hours | Hours that make up a half day of leave | `4` |
| **Half day leave enabled company-wide** (tick) | 15.27 | |
| **Sandwich leave rule enabled** (tick) | 15.28 | |
| **Allow applications beyond available balance** (tick) | 15.29 | |
| Holiday between leave | **Do not count** / **Count as leave** | See 15.28 |
| Weekly off between leave | **Do not count** / **Count as leave** | See 15.28 |
| Max consecutive leave days | Longest single leave; blank = no cap | `15` |
| Minimum notice days | Days before the start date you must apply (emergency leave bypasses this) | `2` |
| Max future apply days | How far ahead you may apply; 0 = no limit | `90` |
| Carry forward limit (fallback) | Cap used when a policy rule has no limit | `10` |
| **Carry forward enabled** (tick) | 15.30 | |
| Carry forward expiry month | Month after which carried-forward days lapse; *Never expires* if blank | – |
| **Leave encashment enabled** (tick) | 15.31 | |
| **Company Admin may override-approve own leave (with reason)** (tick) | 15.25 | |

## 6.11 Leave statuses
See Section 17.

## 6.12 Leave Reports
See Section 12.2.

---

# 7. Payroll

**Menu group:** Payroll

## 7.1 Payroll in plain business language

```
1. SET-UP (once, then occasionally)
   Settings (PF/ESI/PT/TDS, basis rules) → Salary Components → Salary Structures
   → Employee Salary (give each person a structure + monthly gross)

2. EVERY MONTH
   Attendance & leave finalised → Add one-off items (bonus, incentive, reimbursement, arrears,
   loans, advances) → Generate payroll → Review → Approve → Lock → Mark Paid
   → Payslips available to employees; bank transfer file for the bank
```

- **Earnings** = the salary-structure lines (e.g. Basic, HRA, allowances) + approved overtime + bonus + incentives + approved reimbursements + arrears.
- **Deductions** = PF (employee share), ESI (employee share), Professional Tax, TDS, **Loss of Pay (LOP)**, loan EMI, advance recovery.
- **Net salary** = Earnings − Deductions (never below zero). Employer shares of PF/ESI are shown on the payslip for information.

## 7.2 My Payroll (employee self-service)

**Who:** Employee, HR, Admin (anyone with the *download own payslip* right). Tabs:

| Tab | Shows |
|---|---|
| **Overview** | Recent payslips (Period, Net Salary, Status, Download) |
| **Salary Summary** | Your assigned structure: Earnings and Deductions lines (a note explains that actual net pay also reflects PF/ESI/PT/TDS, attendance, loans, advances, bonus, reimbursements) |
| **Payslips** | Period, Gross, Deductions, Net, Status, Download |
| **Loans** | Loan #, Type, Principal, EMI, Outstanding, Status |
| **Advances** | Date, Amount, Recovery, Recovered, Remaining, Status |
| **Reimbursements** | Expense type, Amount, Date, Status (read-only) |
| **Tax Summary** | A notice that income-tax declaration is not available yet; your monthly TDS is on each payslip |

Employees **cannot create** loans, advances or reimbursements here; HR enters them.
Your payslip list shows one line for every payroll run you are included in – including runs that are still being prepared – and the **Status** column shows where each stands (Draft, Approved, Locked, Paid). Treat a payslip as **final only when its status is Paid**. When HR marks the run **Paid**, you receive the notification *"Payslip available for <period>"* and, if you have a company or personal e-mail on file, an e-mail too.

## 7.3 Payroll Dashboard
Summary of the latest run (period, status badge) and totals; shows *"No payroll has been generated yet."* if none exists.

## 7.4 Salary Components

**Purpose:** the building blocks of pay (Basic, HRA, PF, etc.). **Who:** Admin, HR. Filters: search, Type, Status. Buttons: **Add component**, ✏, 🗑.

| Field | Meaning | Required | Example |
|---|---|:-:|---|
| Component name | Label on payslips | Yes | `House Rent Allowance` |
| Code | Short capital code used in formulas | Yes | `HRA` |
| Type | **Earning** (adds to pay) or **Deduction** (subtracts) | Yes | Earning |
| Calculation type | **Fixed amount**, **Percentage**, **Formula** | Yes | Percentage |
| Percentage of (component code) | Which component the % is taken from | No | `BASIC` |
| Formula | Used only when Calculation type = Formula | Only for Formula | `BASIC*0.4` |
| Display order | Order on screen/payslip (later rows can refer to earlier ones) | No | `2` |
| Status | Active / Inactive | | Active |
| **Taxable** (tick) | 15.32 | | |
| **PF applicable** (tick) | 15.33 | | |
| **ESI applicable** (tick) | 15.34 | | |

Use the code **BASIC** for the basic-pay component: payroll looks for it to compute overtime and LOP "on basic" and PF "on basic". An inactive component cannot be added to a new structure.

## 7.5 Salary Structures

**Purpose:** a reusable salary template, e.g. *Software Engineer*. **Who:** Admin, HR.
List columns: Name, Description, Effective From, Status. Icons: ⚙ **Components** (builder), ✏ Edit, 🗑 Delete (blocked while any employee uses it: *"This salary structure is assigned to one or more employees and cannot be deleted."*).

**Structure form:** Structure name\*, Effective from, Status, Description.

**Builder (⚙):** click **Add component** to add a row, then set:

| Column | Meaning |
|---|---|
| Component | Choose from active components |
| Calc. Type | Fixed / Percentage / Formula |
| Amount / % | The rupee amount (Fixed) or the percentage |
| Formula | e.g. `BASIC*0.4` (Formula type only) |
| **Editable** (tick) | 15.35 |

A **Live preview** panel takes a **Test gross salary** and shows the resulting lines and net so you can check the maths before saving. Click **Save components** – it saves the complete row set.

## 7.6 Employee Salary

**Purpose:** attach a structure and a monthly gross to each employee. **Who:** Admin, HR.
Filter: search, Status (Active, Scheduled). Columns: Employee, Structure, Gross, CTC, Effective From, Status. Icons: History, Reassign.

**Assign salary form**

| Field | Meaning | Required | Example | After saving |
|---|---|:-:|---|---|
| Employee | Who | Yes | Anita Sharma | – |
| Salary structure | Template | Yes | Software Engineer | Pay lines come from it |
| Effective from | Date it starts | Yes | `2026-10-01` | Earlier pay periods keep the old assignment (shown in History) |
| Gross salary (monthly) | Total monthly pay | Yes | `60000` | The structure splits this into lines |
| CTC (annual) | Auto-calculated, read-only | – | `720000` | – |

Click **Assign**. An employee **without** an active assignment is **skipped** when payroll is generated (the run tells you who).

## 7.7 Payroll Runs

**Purpose:** create and process the month's payroll. **Who:** view – Admin, HR; Generate/Cancel – those with *Generate payroll*; Approve – *Approve payroll*; Lock/Unlock – *Lock/unlock payroll* (Admin by default); Mark Paid – *Mark payroll as paid* (Admin by default).

**List:** Period, Employees, Gross, Net, Status, Generated.

### Generate payroll
Choose **Month** and **Year**, click **Generate**.

What the system does:
1. Takes every employee who is Active, on Probation, on Notice Period or Suspended and joined on or before the period end (plus people **Relieved** during the period).
2. Skips anyone **without a salary assignment** or **without attendance data for the period** (the result lists each skipped person and why).
3. Reads attendance and leave for the period (7.9), works out earnings and deductions (7.10), and creates a **Draft/Generated** run.
4. Notifies users who can approve payroll: *"Payroll generated for 2026-10 – N employee(s) processed."*

Only **one active run per month** is allowed: *"Payroll has already been generated for this month. Cancel the existing run first to regenerate."*

The pay period comes from Payroll Settings: if *Payroll start day* is later than *Payroll end day* (e.g. 26 → 25) the period runs across two calendar months; otherwise it is the plain calendar month.

### Run screen
Tiles: Employees, Gross Payroll, Net Payroll, PF, ESI, TDS. Table: Employee, Working, Present, LOP, Gross, Deductions, Net, Status. 👁 opens the employee's detailed line (Attendance Summary, Earnings, Deductions); 📄 downloads that payslip.

| Button | When shown | What happens |
|---|---|---|
| **Approve** | Run is *Generated* | Confirms the figures. At this moment the loan EMIs and advance recoveries are **committed** and the bonuses, incentives, reimbursements and arrears included are marked **Paid**. |
| **Lock** | Run is *Approved* | Freezes the run – no more edits. |
| **Mark Paid** | Run is *Locked* | Marks the salary as paid; every employee in the run is notified (and e-mailed) that their payslip is available. |
| **Unlock** | Run is *Locked* | Opens a panel asking the **Reason for unlocking** (required), returns the run to *Approved*. |
| **Cancel** | Run is *Draft/Generated* | Discards the run's lines so the month can be generated again. Nothing was committed yet, so it is safe. |
| **Payslips (zip)** | Approved/Locked/Paid | Downloads all payslips in one ZIP (needs "download any payslip" right). |
| **Bank File ▾ Excel / CSV** | Approved/Locked/Paid | Downloads the bank transfer list (Employee code, name, bank, account number, IFSC, net salary) built from each employee's **primary** bank account. |

### Add adjustment (on an employee's line in a run)
On the employee detail page you can add a one-off line:

| Field | Meaning |
|---|---|
| Type | Earning or Deduction |
| Label | Name shown on the payslip, e.g. `Notice period recovery` |
| Amount | Rupees |
| Reason | Optional note |

Click **Add adjustment**.

## 7.8 Payslips
List with Search, Month, Year; columns Employee, Period, Net Salary, Status; ⬇ Download (PDF). Admin/HR download any payslip; employees download only their own from My Payroll.

## 7.9 How attendance and leave affect payroll

Each daily attendance record in the pay period is turned into pay days like this:

| Attendance status | Counted as |
|---|---|
| Present, Late, Missed Punch, Work From Home, On Duty | 1 day **present** |
| Leave (paid leave type) | 1 day **paid leave** |
| Half Day Leave | ½ present + ½ paid leave |
| Half Day (worked part of the day) | ½ present + **½ LOP** |
| Loss of Pay, Absent | 1 day **LOP** |
| Holiday, Weekly Off | not a working day (not counted) |

- **Working days** = number of attendance records in the period minus holiday/weekly-off records – or the **Fixed working days** number if *Working days basis* is *Fixed*. Because of this, **make sure every working day has an attendance record** (an unmarked day is neither present nor LOP).
- **LOP deduction** = (basis ÷ working days) × LOP days, when *LOP enabled* is ticked; the basis is Basic or Gross as set in Payroll Settings.
- **Overtime** (when *Overtime enabled*): hourly rate = basis ÷ (working days × standard hours per day), where standard hours per day = *Full Day Minutes* ÷ 60 from Attendance Settings (480 → 8 h). Pay = hourly rate × **approved** overtime hours × multiplier (e.g. 1.5). Only overtime that HR/Manager **approved** is paid.

## 7.10 Loans, Advances, Bonus, Incentives, Reimbursements, Arrears

All are entered by HR/Admin; employees only view them in My Payroll.

### Loans
Filter: Status (Active, Closed, Foreclosed). Columns: Loan #, Employee, Type, Principal, EMI, Tenure, Outstanding, Status. Icons: 📋 **Installments** (schedule: #, Due, EMI, Principal, Interest, Status), ✖ **Foreclose**.

| Field | Meaning | Required | Example |
|---|---|:-:|---|
| Employee | Borrower | Yes | – |
| Loan number | Your reference | Yes | `LN-2026-004` |
| Loan type | Free text | Yes | `Personal Loan` |
| Interest rate % p.a. | Yearly interest | No (0) | `8` |
| Principal amount | Loan amount | Yes | `60000` |
| EMI amount | Monthly instalment | Yes | `5200` |
| Tenure (months) | Number of instalments | Yes | `12` |
| Start month / Start year | First deduction month | Yes | `11` / `2026` |

After **Create loan**, an installment schedule is created. Each payroll run **previews** the next due EMI; it is actually recorded when you **Approve** the run. **Foreclose** ends the loan (status *Foreclosed*).

### Advances
Filter: Status (Active, Closed). Columns: Employee, Amount, Date, Recovery, Installment, Recovered, Remaining, Status. ✖ **Close** ends an advance.

| Field | Meaning | Required | Example |
|---|---|:-:|---|
| Employee | Who | Yes | – |
| Amount | Advance given | Yes | `10000` |
| Advance date | Date given | Yes | today |
| Recovery type | **Installments** or **Lump sum (next payroll)** | Yes | Installments |
| Number of installments | Shown for Installments | Yes | `4` |
| Reason | Optional | No | `Medical` |

### Bonus
Filter: Status (Pending, Paid). Fields: Employees (one or more), Bonus type (Festival, Annual, Performance, Other), Amount, Payroll month (blank = **next generated run**), Remarks. **Apply bonus** creates a *Pending* entry per employee; 🗑 deletes a pending entry. When a run including it is approved the entry becomes **Paid**.

### Incentives
Same as Bonus, but Incentive type is free text (e.g. `Sales Incentive`) and one employee per entry.

### Reimbursements
Filter: Status (Pending, Approved, Rejected, Paid). Fields: Employee, Expense type (`Travel`), Amount, Expense date, Attachment (optional), Remarks. **Submit reimbursement** creates a *Pending* request; ✔ **Approve** / ✖ **Reject**. Only **Approved** reimbursements are picked up by the next payroll run, and become **Paid** when that run is approved. Reimbursements are **not** taxed.

### Arrears
Filter: Status (Pending, Paid). Fields: Employee, From month/year, To month/year, Amount, Pay in month (blank = **next generated run**), Reason. Use it to pay back pay for earlier months (for example after a late salary revision). 🗑 deletes a pending entry.

## 7.11 Payroll Settings

**Purpose:** the rules payroll follows. **Who:** Company Admin by default. Each block has its own Save button.

### General (Save settings)
| Setting | Meaning | Example |
|---|---|---|
| Payroll start day / end day | The day of the month the pay period starts and ends | `1` / `31`, or `26` / `25` |
| Salary payment day | Day salaries are paid | `5` |
| Financial year start month | `4` = April | |
| Currency | 3-letter code | `INR` |
| Working days basis | **Calendar days in month** or **Fixed days** | See 7.9 |
| Fixed working days | Used when basis = Fixed | `26` |
| Payslip prefix | Prefix for payslip numbers | `PAY` |
| Overtime multiplier | Overtime pay factor | `1.5` |
| Overtime rate basis | **Basic salary** or **Gross salary** | Basic |
| LOP deduction basis | **Basic salary** or **Gross salary** | Gross |
| **Overtime enabled / LOP enabled / PF enabled / ESI enabled / PT enabled / TDS enabled / Lock payroll after approval** | Seven ticks – see 15.36 to 15.42 | |

### Provident Fund (PF)
Employee contribution %, Employer contribution %, Wage ceiling (the maximum wage PF is calculated on), **PF wage basis** (Basic / Basic + DA / Gross). If components are ticked *PF applicable*, those are used as the PF wage instead.

### ESI
Employee %, Employer %, Wage ceiling.

### Professional Tax (PT) slabs
**Add slab:** State, Min gross, Max gross (blank = and above), Tax amount, Effective from. Rows can be edited (Save ✔), set Active/Inactive, or deleted. The state used is the **State** entered on the employee's **Branch** (8.1), so fill it in.

### TDS
Default TDS % and "Applicable above gross". This is a flat estimate only – there is no income-tax declaration module; HR can add a manual adjustment on a run line.

## 7.12 Payroll Reports
See Section 12.3.

---

# 8. Organization

**Menu group:** Organization → **Branches, Departments, Designations**
**Who:** Company Admin can create/edit/delete; HR Manager can view. Set these up **before** adding employees – an employee needs a branch, department and designation.

All three work the same way: a list with a search box and **Clear**, an **Add** button (opens a side panel), ✏ Edit, 🗑 Delete, and an **Archived** button listing deleted items with **Restore**. "Delete" archives the item; it can be restored.

## 8.1 Branches

| Field | Meaning | Required | Example | After saving |
|---|---|:-:|---|---|
| Branch name | Office/location name | Yes | `Pune Office` | Appears in all branch drop-downs and reports. |
| Code | Short unique code | Yes | `PUN` | Must be unique: *"This branch code is already in use."* |
| Address | Postal address | No | – | Information. |
| **State** (for professional tax) | The state whose Professional Tax slabs apply to this branch's employees | No, but needed for PT | `Maharashtra` | Payroll matches this text to the PT slab "State". If blank, no PT slab is found. |
| Phone / Email | Contact | No | – | Information. |
| Manager | Branch manager | No | pick an employee | Information. |
| Status | Active / Inactive | – | Active | – |

Delete is blocked while departments still belong to the branch (*"This branch still has departments assigned to it…"*).
A branch's **attendance locations**, **holidays** and **weekly-off rules** are set under Attendance (Section 5).

## 8.2 Departments

| Field | Meaning | Required | Example |
|---|---|:-:|---|
| Department name | Team/function | Yes | `Finance` |
| Code | Unique code | Yes | `FIN` |
| Branch | The branch it belongs to | Yes | Pune Office |
| Head of department | Leader | No | pick an employee |
| Status | Active / Inactive | – | Active |

Delete is blocked while designations belong to the department.

## 8.3 Designations

| Field | Meaning | Required | Example |
|---|---|:-:|---|
| Designation name | Job title (unique within the department) | Yes | `Senior Accountant` |
| Department | Where it sits | Yes | Finance |
| Level | A number showing seniority (higher/lower is up to you) | No (0) | `3` |
| Status | Active / Inactive | – | Active |

Delete is blocked while employees hold the designation.

---

# 9. Documents

## 9.1 Where documents live

This HRMS has **no separate "Documents" menu**. Documents are kept **per employee**, on the **Documents tab of the employee's profile** (Employees → click the employee → *Documents*).

**Who can use it** (needs the *Manage employee documents* right – Company Admin and HR Manager by default):

| Action | Who |
|---|---|
| See the Documents tab, preview, download | Company Admin, HR Manager |
| Upload | Company Admin, HR Manager |
| Delete | Company Admin, HR Manager |

Managers and Employees do not have this right, so they **cannot see or upload** documents in this version.

## 9.2 Upload a document

1. Open the employee's profile → **Documents** tab → **Upload document**.
2. Fill the side panel:

| Field | Meaning | Required | Example | After saving |
|---|---|:-:|---|---|
| Document type | Aadhaar, PAN, Passport, Driving License, Resume, Appointment Letter, Offer Letter, Education Certificate, Experience Certificate, Other | Yes | PAN | Shown in the Type column. |
| Document number | The ID printed on it | No | `ABCDE1234F` | Shown in the Number column. |
| Expiry date | When it stops being valid | No | `2030-06-30` | Shown in the Expiry column (see 9.4). |
| File | The file itself: PDF, JPG, PNG, WEBP, DOC or DOCX, **up to 10 MB** | Yes | – | Stored safely; the real file type is checked, not just the name. |
| Remarks | Note | No | `Scanned copy` | Stored. |

3. Click **Upload**. The document appears in the list with status **Pending**.

Errors: *"Document must be 10MB or smaller."*, *"Unsupported file type. Allowed: PDF, JPG, PNG, WEBP, DOC, DOCX."*

## 9.3 The documents list

Columns: **Type, Number, File, Expiry, Status, Uploaded** and action icons:

| Icon | What it does |
|---|---|
| 👁 **Preview** | Opens the file in a new browser tab. |
| ⬇ **Download** | Saves the file to your computer. |
| 🗑 **Delete** | After confirmation ("This document will be removed.") removes it from the list. The file itself is kept on the server for audit/recovery. |

## 9.4 Status, expiry, required/optional, verification – what exists

| Topic | In this HRMS |
|---|---|
| **Status** | Every upload starts as **Pending**. The list can show **Verified** (green) and **Rejected** (red) badges, but **there is no screen or button to verify or reject a document** in this version. |
| **Verification** | Not available (no "Verify" action). |
| **Expiry date** | Stored and displayed only. There is **no "Expired" status, no reminder and no alert** – check dates manually. |
| **Required / optional documents** | Not available. There is no checklist of mandatory documents and no setting that forces an upload. |
| **Replace document** | Not available. To change a file, upload the new one and delete the old one. |
| **Document categories / document types set-up** | Not configurable – the ten types above are fixed. |
| **Employee self-upload / self-view** | Not available (the Employee role has no documents right). |

Because none of these settings exist, there are no "Required Document – enabled/disabled" switches to document.

---

# 10. Administration

**Menu group:** Administration → **Users, Roles, Permissions**

## 10.1 Users

**Purpose:** manage login accounts (who can sign in and as what).
**Who:** Company Admin and HR Manager (view/create/edit); delete – Company Admin only.

> Tip: for an existing employee it is easiest to manage the login from the employee's profile (4.5). The Users screen is the full list of every login, including people without an employee record.

**List:** search (name, e-mail, username), filters Status (Active/Inactive), Role, Branch. Columns: Name, Email, Role, Status, Last login.

### Add user / Edit user

| Field | Meaning | Required | Example | After saving |
|---|---|:-:|---|---|
| Full name | Display name | Yes | `Anita Sharma` | Shown in the top bar and sidebar. |
| Username | Optional short name for the login | No | `anita.sharma` | Stored if given. (Sign-in itself uses the e-mail.) |
| Email | The sign-in e-mail | Yes | `anita@company.com` | Used to sign in and for reset/verification e-mails. |
| Mobile | Phone | No | – | – |
| Linked employee | Connects the login to an employee record (an employee can have only one login) | No | `EMP000012 — Anita` | Enables My Attendance, My Leave, My Payroll and the employee dashboard box for this login. |
| Role | Decides menus and permissions | Yes | Manager | Takes effect at the person's next sign-in/refresh. Only a Company Admin can give the Company Admin role. |
| Branch / Department / Designation | Organisation info on the login | No | – | – |
| Status | Active / Inactive | – | Active | Inactive = cannot sign in. |

On **Create user** a **temporary password** is generated and displayed **once** at the top of the next page – copy it and give it to the person. They must change it at first sign-in.

### User page buttons

| Button | What happens |
|---|---|
| **Edit** | Opens the form. |
| **Activate** / **Suspend** | Turns the login on/off. You cannot suspend yourself. |
| **Reset password** | New temporary password shown once. |
| **Unlock** | Clears a lock (appears if the account is locked). |
| **Delete** | After confirmation removes the user from the workspace (Company Admin only). |

The same page also offers the actions listed in 4.5 (Lock, Set Password Manually, Send Reset Link, Expire Password, resend verification e-mail, logout devices, change role, account switches).

A Company Admin cannot remove their own Company Admin role, and nobody can lock their own account.

## 10.2 Roles

**Purpose:** a role is a named bundle of permissions.
**Who:** Company Admin.

**List:** Role, Description, Users (how many), Type (System / Custom). Filter Active / Archived.

| Button | What happens |
|---|---|
| **Add role** | Role name\* and Description → **Create role**. Then set its permissions. |
| 🛡 **Permissions** | Opens the permission matrix (below). |
| ✏ **Edit** | Change name/description (system role names cannot be changed). |
| ⧉ **Duplicate** | Copies a role with all its permissions – the quickest way to make a variant. |
| 🗄 **Archive** | Existing holders keep it, but it can no longer be given to new users. System roles cannot be archived. |
| ↺ **Restore** | Brings an archived role back. |

### Permission matrix
A grid: one row per **module** (Dashboard, Users, Roles, Branches, Departments, Designations, Settings, Audit, Employee, Attendance, Leave, Payroll, Salary structure, Salary component, Loan, Advance, Bonus, Reimbursement, Payslip, Assets, Reports) and columns **View, Create, Edit, Delete, Export, Import, Approve, Reject, Process, Manage**. Only the boxes that exist for a module are shown (others show "–"). Hover over a box to read what it allows.

| Control | What it does |
|---|---|
| Tick-box | Grants that permission to the role. |
| **All** (end of row) | Ticks/unticks the whole row. |
| **Check all / Uncheck all** | Applies to every box. |
| **Search modules** | Filters the rows. |
| **Save permissions** | Saves. |

Special roles: **Company Admin** always has every permission and cannot be edited. **Employee** is shown read-only (its permission set is fixed). **HR Manager** and **Manager** can be edited.

> **⚠ Read this before you press Save permissions.** The grid has boxes only for the ten action columns above. Several permissions have **no box** in the grid – for example the self-service "view own" permissions (My Attendance, My Leave), *Approve attendance regularizations*, *Cancel any leave application*, *View/Edit employee bank details*, *Download own / any payslip*, *Manage employee documents*, *Search the employee directory*, and the "manage shifts / locations / devices / settings" permissions. When you click **Save permissions**, the role ends up with **only the boxes that are ticked in the grid**, so permissions without a box are **removed** from that role. On a role such as Manager or HR Manager this can make menus such as My Attendance, My Leave, Regularizations or My Payroll disappear. Safe practice: do not Save the matrix for the standard HR Manager / Manager roles unless you intend to rebuild their access; ask your system administrator for help when you need a role that includes these special permissions, and always check the new role by signing in as a test user.

## 10.3 Permissions

A **read-only reference** listing every permission grouped by module with its code (e.g. `leave.approve`) and a plain description. Use it to understand what a box in the role matrix means. Nothing can be changed here.

---

# 11. Settings

**Menu group:** Settings → **Company Settings, Audit Logs**

## 11.1 Company Settings

**Who:** Company Admin (view; changes need *Manage company settings*). Click **Save changes** to apply.

| Setting | What it controls | Required | Example / what happens if changed |
|---|---|:-:|---|
| **Company name** | The name shown in the sidebar, on payslips and PDFs | Yes | `Acme Pvt Ltd` → appears immediately everywhere. |
| **Timezone** | The clock used to **display** all stored times (punch times, activity, notifications) | Yes | `Asia/Kolkata`. Change it and every displayed time shifts accordingly. Keep it the same as Attendance Settings → Timezone. |
| **Currency** | The company's currency code | – | `INR`. (Payslip and payroll amounts use the currency in **Payroll Settings**.) |
| **Date format** | Preferred date style: `d-m-Y`, `m-d-Y`, `Y-m-d` | – | Saved as the company preference. |
| **Time format** | 24-hour or 12-hour | – | Saved as the company preference. |
| **Theme** | Light / Dark / Match device | – | Company default look. Each person can also flip light/dark with the moon icon; that personal choice is remembered in their own browser and takes priority. |
| **Brand color** | Read-only | – | "Managed by your platform administrator." You cannot change it here. |
| **Font** | System default / Inter / Roboto | – | Saved as the company preference. |
| **Default language** | English / Hindi | – | Saved as the company preference. |
| **Week starts on** | Monday / Sunday | – | Saved as the company preference. |
| **Require email verification before login** (switch) | See 15.10 | | |

> **Honest note:** Company name and Timezone have a visible effect everywhere. The date format, time format, currency, font, language and week-start options are saved as company preferences, but in this version most screens keep their own fixed layout, so you may not see a visible change when you alter them.

### Branding images (Company Settings page, second card)
Upload a **Logo** (PNG/JPEG/WEBP – shown top-left of the sidebar), a **Favicon** (PNG/ICO/WEBP – the browser-tab icon) and a **Login banner** (PNG/JPEG/WEBP). Choose a file and click that card's **Upload**. Sidebar colours, header colour and button radius are **not** settings you can change in this HRMS.

### Email (SMTP)
Button **Email (SMTP)** opens a page that **shows** the current mail server (protocol, host, port, encryption, user name, message format, From name/address) – it cannot be edited here; your IT team sets it up. If it says *"SMTP isn't configured yet"*, password-reset, verification and payslip e-mails will not be delivered. Use **Send a test email**: type an address and click **Send test** to confirm delivery.

## 11.2 Audit Logs

**Purpose:** a tamper-evident diary of important actions (who did what and when).
**Who:** Company Admin.
Filters: search action or user; **Module** drop-down; **Clear**. Columns: **Action, Module, User, IP, When**. Read-only.

---

# 12. Reports – Complete List

All reports share the same layout: a tile page listing the reports → click one → set the **filters** at the top → click **Apply** → read the table (paged) → use **Export ▾** for **Excel (.xlsx), CSV or PDF** (the export keeps your filters). **All reports** returns to the tile page. Exporting needs a separate "export" right (see each section); viewing and exporting are controlled independently.

> The filters available are exactly the ones listed below – for example, most reports do **not** have Branch or Department filters. To narrow further, export to Excel and filter there.

## 12.1 Attendance Reports
**Menu:** Attendance → Reports. **View:** Admin, HR, Manager. **Export:** Admin, HR.

| Report | What it shows | Filters | Columns |
|---|---|---|---|
| **Daily Attendance** | Everyone's record for one day | **Date** (today by default) | Employee code, Name, Branch, Department, Status, Punch In, Punch Out, Working minutes, Late minutes |
| **Monthly Attendance** | One summary row per employee for a month | **Year, Month** | Code, Name, Present, Absent, Half Day, Late, Holiday, Weekly Off, Total hours, Overtime hours |
| **Employee Attendance** | One employee's day-by-day record | **Employee** (search), **From, To** (current month by default) – nothing is shown until you pick an employee | Date, Status, Punch In, Punch Out, Working min, Late min, OT min |
| **Late Report** | Days with late minutes above zero | **From, To** | Code, Name, Date, Punch In, Late minutes |
| **Missing Punch Report** | Days that ended with a punch-in but no punch-out | **From, To** | Code, Name, Date, Punch In, Punch Out |
| **Overtime Report** | Overtime entries | **From, To** | Code, Name, Date, Shift min, Worked min, OT min, Status |
| **Holiday Report** | The year's active holidays | **Year** | Holiday, Date, Type, Branch, Optional |
| **Weekly Off Report** | Active weekly-off rules | none | Rule, Day, Pattern, Branch, Shift |

## 12.2 Leave Reports
**Menu:** Leave → Reports. **View:** Admin, HR, Manager. **Export:** Admin, HR.

| Report | What it shows | Filters | Columns |
|---|---|---|---|
| **Leave Summary** | Totals by leave type | From, To | Leave type, Code, Applications, Approved, Pending, Rejected, Approved days |
| **Employee Leave Balance** | Every employee's balance per leave type | **Financial year** (number) | Code, Name, Leave type, Opening, Earned, Availed, Adjusted, Carry Fwd In, Encashed, Closing |
| **Leave Register** | Every application | From, To | Code, Name, Leave type, From, To, Days, Status, Applied on |
| **Pending Approvals** | Applications still waiting | none | Code, Name, Leave type, From, To, Days, **Pending at** (level), Applied on |
| **Department Leave Report** | Leave by department | From, To | Department, Applications, Approved days |
| **Leave Type Report** | Usage by type | From, To | Leave type, Paid, Applications, Approved days, Average days per application |
| **Monthly Leave Report** | How each employee's days fell in a month | **Year, Month** | Code, Name, Full leave, Half leave, LOP, WFH, On Duty |
| **Carry Forward Report** | Result of carry-forward runs | none | Code, Name, Leave type, From FY, To FY, Eligible, Carried, Expired, Batch |

## 12.3 Payroll Reports
**Menu:** Payroll → Reports. **View:** Admin, HR. **Export:** Admin, HR. Every payroll report has the same two filters – **Year** and **Month** – and includes only payroll lines that are **Approved, Locked or Paid** (not drafts).

| Report | Columns / purpose |
|---|---|
| **Payroll Register** | Code, Name, Department, Working days, LOP days, Gross earnings, Gross deductions, Net salary |
| **Salary Register** | Code, Name, Structure, Gross, PF, ESI, PT, TDS, Net salary |
| **Bank Transfer Report** | Code, Name, Bank, Account number, IFSC, Net salary – for paying the bank |
| **PF Report** | Employee PF and Employer PF |
| **ESI Report** | Employee ESI and Employer ESI |
| **Professional Tax Report** | Professional tax per employee |
| **TDS Report** | Gross earnings and TDS |
| **LOP Report** | Working days, LOP days, LOP amount |
| **Overtime Report** | OT hours, OT amount |
| **Loan Report** | Loan #, Type, Installment, EMI, Status |
| **Bonus Report** | Type, Amount, Status |
| **Incentive Report** | Type, Amount, Status |
| **Reimbursement Report** | Expense type, Amount, Status |
| **Payslip Report** | Payslip #, Generated at, Download count |

Other downloads (not "reports"): employee list (Employees → Export), payroll **Bank File** and **Payslips ZIP** (on a payroll run), individual payslip PDF.

---

# 13. Notifications

## 13.1 The bell
The **bell** in the top bar shows a red number for **unread** notifications (9+ if more). Click it to see the latest eight:
- **Unread** notifications are normal text; **read** ones are greyed.
- Click a notification: it is marked **read** and takes you to the relevant page.
- **Mark all read** (in the panel) clears the unread count.
- **View all** opens the full **Notifications** page (also reachable at `/notifications`) with date/time for each item and its own **Mark all read** button.

## 13.2 What notifications you can receive

| Notification | Who receives it | Triggered when |
|---|---|---|
| *"<Name> applied for <Leave type>"* (dates shown) | The employee's **reporting manager** (Level 1 approver) | An employee submits a leave application |
| *"Your leave request was approved"* | The employee | Final (Level 2 or Admin override) approval |
| *"Your leave request was rejected"* (with remarks) | The employee | Rejection at any level |
| *"Attendance correction approved"* | The employee | Their regularization request is approved |
| *"Payroll generated for <period>"* | Everyone who can approve payroll | A payroll run is generated |
| *"Payslip available for <period>"* (plus an **e-mail**) | Each employee in the run | The run is marked **Paid** |
| *"<Name>'s birthday is coming up"* and *"<Name>'s N-year work anniversary"* | HR users | A **daily reminder job** runs – this works only if your IT team has scheduled it |

Not notified in this version: a manager is **not** notified when an attendance correction request is submitted, and an employee is not notified when a correction is **rejected** – managers should check the Regularizations list (the dashboard shows the pending count), and employees should check **My Regularization Requests**.

## 13.3 E-mails the HRMS sends
Password-reset link, e-mail verification link, and "payslip available". They are delivered only when the company's e-mail (SMTP) is configured (11.1). SMS, WhatsApp and push messages are **not** available.

---

# 14. Your Profile, Password and Logout

Open from the sidebar footer (your name) or **top-bar user menu → My profile**.

## 14.1 My Profile
Three tabs:

| Tab | Contents |
|---|---|
| **Personal Info** | **Full name** (editable), **Email** (read-only – "Contact an administrator to change your login email"), **Mobile** (editable). Click **Save changes**. |
| **Security** | **Change password** button and **Log out other devices** button. |
| **Activity** | A list of your recent actions (Action, Module, When). |

## 14.2 Change password
**Top-bar menu → Change password.**

| Field | Meaning | Required |
|---|---|:-:|
| Current password | Proves it's you | Yes |
| New password | At least **8 characters** with an upper-case letter, a lower-case letter, a number and a symbol | Yes |
| Confirm new password | Retype it | Yes |

You cannot reuse any of your **last 5** passwords. If your password is older than **90 days**, a reminder asks you to change it. After a manual password set, an administrator's reset or a "temporary password", you are taken to this page automatically.

## 14.3 Log out other devices
Signs you out of every other browser/phone where you are signed in (a confirmation pop-up appears). Use it if you forgot to log out on a shared computer or suspect someone else has your login. You stay signed in on the current browser.

## 14.4 Light / dark appearance
The **moon icon** in the top bar switches between light and dark for you, on this browser.

## 14.5 Logout
**Top-bar user menu → Logout.** Always log out on shared computers.

There are no other profile options (no photo upload for your own login, no personal notification preferences, no language choice in the profile).

---

# 15. Master Reference – Every Checkbox, Toggle and Switch

For each: **What it means / When ENABLED (ticked) / When DISABLED (unticked) / Example / Depends on.** Numbers match the references in earlier sections.

## Attendance

### 15.1 Night shift (crosses midnight) – *Shift form*
- **Means:** the shift ends the next calendar day.
- **Enabled:** the shift carries a **Night** badge (shown on My Attendance) and is treated as an overnight shift.
- **Disabled:** a normal same-day shift.
- **Example:** a security guard works 22:00 → 06:00. Punch-in 21:55 Monday and punch-out 06:05 Tuesday are both counted on **Monday's** record.
- **Depends on:** the End time. The system also treats any shift whose end time is earlier than its start time as overnight, so always set the times correctly.

### 15.2 Replace existing assignments – *Shift Assignments*
- **Means:** what to do with people who already have a shift starting on/after the chosen date.
- **Enabled:** their pending assignment is replaced by this one.
- **Disabled (default):** those employees are skipped and listed in the summary.
- **Example:** you assign Night shift from 1 Nov to a department; 3 people were already scheduled to start General shift on 1 Nov. Unticked → those 3 keep General and are reported. Ticked → they move to Night.

### 15.3 Optional holiday – *Holiday form*
- **Means:** a holiday that is not compulsory (e.g. a regional festival).
- **Enabled:** the holiday is flagged **Optional** in the list and in the Holiday report.
- **Disabled:** shown as a normal holiday.
- **Note:** in this version both kinds are treated as holidays in attendance; there is no "pick N optional holidays" process.

### 15.4 Recurs every year (fixed date) – *Holiday form*
- **Enabled:** when you run **Generate next year**, this holiday is carried over with the same date automatically (e.g. 26 Jan Republic Day).
- **Disabled:** you must type a new date yourself for the next year (e.g. Diwali, which moves each year).

### 15.5 GPS required (rejects punches outside radius) – *Attendance Settings*
- **Enabled:** a punch from outside the office radius is rejected ("Attendance not allowed — you are outside the office location…"); a punch with no location is rejected ("Location access is required…").
- **Disabled:** every punch is accepted; the location result (Inside / Outside / Low accuracy / GPS disabled) is stored for HR to review.
- **Example:** Anita opens the punch page from home, 4 km from the office. Enabled → she cannot punch. Disabled → the punch is recorded as *Outside*.
- **Depends on:** an active **Location** for the employee's branch (5.11). With no location configured, punches are accepted either way.

### 15.6 Device approval required – *Attendance Settings*
- **Enabled:** the first time an employee punches from a new browser/phone, that device is registered as **Pending**; HR approves/rejects/blocks it in **Attendance → Devices**.
- **Disabled:** a new device is registered as **Approved** automatically.
- **In both cases the punch itself still goes through** – the device list is a review trail.
- **Example:** Ravi punches from a friend's phone; the device appears as *Pending* so HR can query it.

### 15.7 Self attendance enabled – *Attendance Settings*
- **Intended meaning:** allow employees to punch themselves.
- **Enabled / Disabled:** in this version the setting is saved but **the My Attendance punch screen behaves the same either way**. Do not rely on it to stop self-punching.

### 15.8 Overtime tracking enabled – *Attendance Settings*
- **Enabled:** if worked time exceeds the shift length, the extra minutes are recorded as an **overtime entry (Pending)** for approval.
- **Disabled:** no overtime entries; overtime minutes stay 0 and nothing flows to payroll.
- **Example:** 09:00–18:00 shift (9 h), employee works 10 h 30 m → 90 overtime minutes, pending approval.
- **Depends on:** a shift being assigned; and, for payment, **Overtime enabled** in Payroll Settings (15.36) and HR approving the entry.

## Employees and Users

### 15.9 Enable login for this employee – *Add Employee → Login Account tab*
- **Enabled:** a login is created with the employee (Role and Login email become required; a temporary password is shown once).
- **Disabled:** employee record only; no sign-in. Create a login later from the profile.

### 15.10 Require email verification before login – *Company Settings*
- **Enabled:** users must click the verification link in their e-mail before they can sign in. New users receive it automatically.
- **Disabled:** users can sign in without verifying.
- **Warning:** turn it on only after the test e-mail in **Settings → Email (SMTP)** really arrives – otherwise unverified users are locked out.
- **Example:** with it on and a working mail server, a new hire clicks the link in the welcome e-mail, then signs in.

### 15.11 Skip duplicate employee codes – *Employee Import preview*
- **Enabled (default):** rows whose Employee Code already exists (in the system or earlier in the file) are skipped.
- **Disabled:** if the file contains any duplicate code the import is refused: *"This file has duplicate employee codes. Enable "Skip duplicates" or fix the file and re-upload."*
- **Note:** a blank Employee Code cell is fine – the system assigns the next number (EMP000123…) automatically.

### 15.12 Primary account – *Bank account*
- **Enabled:** this is the account used in the payroll bank-transfer file.
- **Disabled:** kept on file only. If an employee has several accounts, mark exactly one as primary.

### 15.13 Dependent / Nominee – *Family member*
- **Dependent:** marks the person as financially dependent. **Nominee:** marks them as a nominee. Both are information flags on the profile; no screen changes behaviour based on them.

### 15.14 Account Settings switches – *Employee profile → Login Account (users with user-edit rights)*
| Switch | Enabled | Disabled |
|---|---|---|
| **Allow "Remember Me"** | The "Remember me" tick at sign-in keeps the person signed in for 30 days | "Remember me" is ignored; the person must sign in again after the session ends |
| **Allow Mobile Login** | Saved for mobile access | Saved; the web screens do not change |
| **Allow Web Login** | Normal | The person cannot sign in on the web: *"Web login is disabled for this account. Contact your administrator."* |
| **Require Two-Factor Authentication (coming soon)** | Not active yet | – |

All four start ON. **Save Settings** applies them.

## Leave (employee form)

### 15.15 Half day (single date only) – *Apply Leave*
- **Enabled:** the application is for half a day on one date; you choose **First Half** or **Second Half**; it counts 0.5 day. From and To must be the same date and not a holiday/weekly off.
- **Disabled:** whole days.
- **Depends on:** *Half day leave enabled company-wide* (15.27) and the leave type's *Half day allowed* (15.18) – if either is off the application is refused.

### 15.16 Emergency leave (bypasses minimum notice) – *Apply Leave*
- **Enabled:** the minimum-notice rule is skipped.
- **Disabled:** the notice rule applies (policy rule's Notice Days, else the company *Minimum notice days*).
- **Example:** company minimum notice is 2 days; you need leave tomorrow after a family emergency → tick it. Other rules (balance, overlap, attachment) still apply.

## Leave Types

### 15.17 Paid
- **Enabled:** a normal paid leave; applications are checked against the balance.
- **Disabled:** unpaid leave; the balance check is skipped (there is no allocation to run out of).
- **Important:** what payroll does with the day is decided by **Attendance status on approval**. For genuinely unpaid leave, untick *Paid* **and** set that status to *Loss of Pay*.

### 15.18 Half day allowed
- **Enabled:** employees may take half days of this type. **Disabled:** half-day applications for this type are refused.

### 15.19 Attachment required
- **Enabled:** a file must be uploaded with every application of this type. **Disabled:** optional.

### 15.20 Medical certificate required
- **Enabled:** a document must be uploaded (it uses the same upload box; the message says "A medical certificate is required"). **Disabled:** not required.
- **Example:** Sick Leave with both ticked → the employee must upload a doctor's note.

### 15.21 Carry forward allowed (leave type, and *Carry Fwd* column in a policy rule)
- **Enabled:** unused days of this type can move to next year when the Carry Forward run is done.
- **Disabled:** unused days lapse at year end.
- **Depends on:** *Carry forward enabled* in Leave Settings (15.30). If a policy rule turns it off for a group, that group does not carry forward.

### 15.22 Encashment allowed (leave type, and *Encash* in a policy rule)
- **Enabled:** employees may request to convert days of this type into money. **Disabled:** requests are refused.
- **Depends on:** *Leave encashment enabled* in Leave Settings (15.31).

## Leave Policies and Policy Rules

### 15.23 Company-wide default policy
- **Enabled:** the policy is marked as the default and **cannot be deleted**.
- **Disabled:** it can be deleted.
- **Note:** who a policy applies to is decided by its scope fields (blank = everyone), not by this tick.

### 15.24 Unlimited CF (policy rule)
- **Enabled:** no cap on carried-forward days for this leave type in this policy. **Disabled:** the CF Limit (or the Leave Settings fallback limit) caps it.
- **Example:** limit 10, balance 14 → disabled: 10 carried; enabled: 14 carried.

### 15.25 Company Admin may override-approve own leave (with reason) – *Leave Settings*
- **Enabled:** if a Company Admin applies for leave and no one else can approve, the Admin may use **Override approve** on their own application (a reason is required and recorded).
- **Disabled:** *"Self-approval override is disabled in leave settings."* – another approver is needed.

### 15.26 Sandwich (policy rule) – *Sandwich rule applicable*
- **Enabled (default):** the sandwich rule (15.28) applies to this leave type for this group. **Disabled:** it does not.

## Leave Settings

### 15.27 Half day leave enabled company-wide
- **Enabled:** half-day leave is possible (where the leave type also allows it). **Disabled:** nobody can apply half-day leave.

### 15.28 Sandwich leave rule enabled (with *Holiday between leave* / *Weekly off between leave*)
- **Means:** what to do with holidays/weekly offs that sit **between two leave days**.
- **Disabled:** holidays and weekly offs inside a leave never count as leave.
- **Enabled:** the two drop-downs decide: *Count as leave* makes sandwiched holidays (or weekly offs) count as full leave days; *Do not count* leaves them free.
- **Example:** Leave Friday and Monday; Saturday/Sunday are weekly offs. Rule enabled + Weekly off = *Count as leave* → 4 days are deducted (Fri, Sat, Sun, Mon). Rule disabled (or Weekly off = *Do not count*) → 2 days.
- **Depends on:** the policy rule's *Sandwich* tick (15.26). A leave that starts or ends on a non-working day is not "sandwiched".

### 15.29 Allow applications beyond available balance
- **Enabled:** employees can apply even when the balance is too low (the balance can go negative). **Disabled:** *"Insufficient leave balance…"*.

### 15.30 Carry forward enabled
- **Enabled:** the Carry Forward run can be executed. **Disabled:** running it gives *"Carry forward is disabled in leave settings."*

### 15.31 Leave encashment enabled
- **Enabled:** encashment requests are accepted. **Disabled:** *"Leave encashment is disabled in leave settings."*

## Payroll

### 15.32 Taxable – *Salary component*
- **Enabled:** this earning counts toward the income on which **Professional Tax and TDS** are calculated. **Disabled:** excluded (e.g. an exempt conveyance allowance).

### 15.33 PF applicable – *Salary component*
- **Enabled:** this component's amount is added into the PF wage. **Disabled:** it is left out. If **no** component in the structure is ticked, PF uses the *PF wage basis* in Payroll Settings. PF is capped at the wage ceiling.

### 15.34 ESI applicable – *Salary component*
- **Enabled:** counted into the ESI wage. **Disabled:** left out. ESI is all-or-nothing: if the ESI wage exceeds the wage ceiling the employee pays and the employer pays **no ESI at all**.

### 15.35 Editable – *Structure builder row*
- **Means:** flags a line as adjustable. In this version the Employee Salary screen only asks for a structure and a gross amount, so ticking it does not add extra fields. Treat it as a note for HR.

### 15.36 Overtime enabled – *Payroll Settings*
- **Enabled:** approved overtime hours are paid in the run. **Disabled:** overtime is never paid even if approved.

### 15.37 LOP enabled
- **Enabled:** a Loss-of-Pay deduction is made for LOP days. **Disabled:** no deduction – absences are not salary-cut.
- **Example:** gross 30,000, 30 working days, 2 LOP days → 2,000 deducted (if basis = gross).

### 15.38 PF enabled / 15.39 ESI enabled / 15.40 PT enabled / 15.41 TDS enabled
- **Enabled:** that statutory deduction is calculated for every employee. **Disabled:** it is zero for everyone. (PT also needs a matching state slab; TDS is a flat percentage.)

### 15.42 Lock payroll after approval
- **Means:** meant to lock a run automatically after approval. **In this version the setting is saved, but runs are still locked only by clicking Lock** (and paid with Mark Paid). Follow the manual steps.

## Sign-in, access and display

### 15.43 Remember me – *Sign-in page*
- **Ticked:** stays signed in on this device for 30 days (if the account allows it, 15.14). **Unticked:** signed out when the session ends (about 2 hours of inactivity).
- Do not tick on shared computers.

### 15.44 Permission boxes – *Role permission matrix*
- **Ticked:** the role holds that permission, and the matching menu/button appears for its users (they may need to sign in again). **Unticked:** it does not. Read the warning in 10.2 before saving.

### 15.45 Same as permanent – *Address tab*
- **Ticked:** the Current address is copied from the Permanent address. **Unticked:** type a different current address.

### 15.46 Row checkboxes – *Leave Applications*
- Tick applications to run **Bulk approve / Bulk reject / Bulk cancel** on them together. Unticked rows are not touched.

### 15.47 Include (per row) – *Generate next-year holidays*
- **Ticked:** that holiday is created for next year. **Unticked:** skipped.

### 15.48 Moon icon (light/dark) – *Top bar*
- **Dark:** dark colours for you on this browser. **Light:** normal colours. Not a company setting.

---

# 16. Common Workflows

## 16.1 Setting up the company (first time, by Admin/HR)
1. **Organization → Branches:** add each branch (fill **State** if you use Professional Tax).
2. **Organization → Departments**, then **Designations.**
3. **Attendance → Shifts:** create your shifts. **Attendance → Settings:** choose the *Default shift*, grace/late/half/full-day minutes, tick the switches you want, set the *Timezone*.
4. **Attendance → Locations:** add each office with its real map coordinates and radius.
5. **Attendance → Weekly Off** and **Holidays:** add the rules and the year's holidays.
6. **Leave → Leave Types**, then **Leave Policies** (+ Rules) and **Leave Settings.**
7. **Payroll → Settings** (PF, ESI, PT slabs, TDS, working-days basis), **Salary Components**, **Salary Structures.**
8. **Administration → Roles** if you need roles beyond the four standard ones (read the warning in 10.2 first).

## 16.2 New employee
```
Branch/Department/Designation exist
  → Employees → Add employee (Basic, Contact, Organization: manager, shift, joining date)
  → Login Account tab: Enable login + Role + Login email  → copy the temporary password
  → Give the employee the temporary password (they must change it at first sign-in)
  → Profile: Address, Bank Details (mark one primary), Family, Emergency, Education, Experience
  → Profile → Documents: upload ID proofs, offer letter, etc.
  → Attendance: shift is set (on the form or via Shift Assignments); employee's branch has a Location
  → Leave: balances are created automatically the first time they are needed (from the policy / leave type)
  → Payroll → Employee Salary → Assign: structure + monthly gross
```
Only the first steps (name, mobile, branch, department, designation, joining date) are *required* by the form; the rest is needed for the person to punch, take leave and be paid properly. Without a **reporting manager**, leave skips Level 1. Without an **Employee Salary** assignment, payroll skips the person.

## 16.3 A normal attendance day
```
Login → Attendance → My Attendance → wait for GPS locked → PUNCH IN
      → Location verification (Inside / Outside / Low accuracy)
      → Work
      → PUNCH OUT
      → Daily record: status, hours, late / early / overtime
      → Forgot something? Request Regularization → Manager/HR approves → record corrected
```

## 16.4 Applying for leave
```
Login → Leave → My Leave → Apply Leave → fill form → Submit application
      → Status: Pending (Level 1: reporting manager)
      → Manager approves → Pending (Level 2: HR)
      → HR approves → Approved → balance reduced + attendance shows Leave
      → Rejected at any step → balance unchanged; you are notified with the remarks
```
To withdraw: **My Applications → ✖ Cancel** (only while Pending).

## 16.5 Approving leave (manager / HR)
```
Notification "<Name> applied for …" → Leave → Leave Applications → open the application
→ check dates, day breakdown, balance, reason
→ Approve at this level (or Reject with remarks)
```

## 16.6 Monthly payroll (HR/Admin)
```
1. Make sure attendance is complete: corrections approved, Absent days marked, overtime approved
2. Make sure leave for the period is approved
3. Enter one-offs: Bonus, Incentives, Reimbursements (approve them), Arrears; Loans/Advances exist
4. Payroll → Payroll Runs → Generate payroll (Month, Year) → read the "skipped" list
5. Open the run → check totals and a few employee lines (add adjustments if needed)
6. Approve   (loans/advances/bonuses are committed now)
7. Lock      (freezes it)
8. Mark Paid (employees are told their payslip is available)
9. Download the Bank File and send to the bank; Payslips ZIP if needed
10. Reports: PF, ESI, PT, TDS for filing
```
Made a mistake before approval? **Cancel** the run and generate again. After locking, **Unlock** (with a reason) takes it back to *Approved*.

## 16.7 Documents
```
Employees → open the employee → Documents → Upload document (type, number, expiry, file)
→ status: Pending
→ Preview / Download whenever needed; Delete if uploaded by mistake
→ Expiry: no automatic reminder – check the Expiry column periodically
```

## 16.8 An employee leaves
```
Profile → Activity → Change status: Notice Period → Resigned (with remarks)
→ When the last day passes: More → Relieve   (the login becomes inactive automatically)
→ Run payroll in the month of leaving: employees whose status is Resigned, Terminated, Retired or Absconded are NOT included; Relieved employees are included once as a "final" settlement for the month they were relieved, so relieve them before generating that month's payroll
→ Employee can be brought back with More → Rejoin
```

## 16.9 Fixing a wrong attendance day
- One person, one day: Attendance → Attendance list → ✏ Correct → choose status → Save correction.
- Many people, one day: Attendance → Bulk mark.
- Many days from a file: Attendance → Excel Import.
- Employee-initiated, with exact times: they use Request Regularization; you approve it.

---

# 17. Status Explanations

## 17.1 Employee statuses
| Status | What it means | What you should do | What happens next |
|---|---|---|---|
| **Probation** | New hire in probation (default for new employees) | Confirm later by moving to Active | Appears in payroll like Active |
| **Active** | Normal working employee | – | Counted in dashboards; login on |
| **Notice Period** | Serving notice | Track the exit | Still paid; can go to Resigned, Terminated, Relieved or back to Active |
| **Suspended** | Temporarily barred | – | Login switched off; still in payroll runs |
| **Resigned** | Resignation recorded | Move to Relieved on the last day | Login switched off. **Not picked up by payroll** while Resigned – only Relieved employees are included (once) in the month they are relieved |
| **Terminated** | Employment ended by the company | – | Login off; excluded from new payroll runs |
| **Retired** | Retired | – | Excluded from new payroll runs |
| **Absconded** | Left without notice | Decide: Terminated or back to Active | Login off |
| **Relieved** | Formally released | – | Login off; included once in the payroll of the month of relieving (final settlement) |

## 17.2 Login / user statuses
| Status | Meaning | Action | Next |
|---|---|---|---|
| **Active** | Can sign in | – | – |
| **Inactive / Suspended** | Cannot sign in | Admin: *Activate* | Sign-in works again |
| **Locked** | Blocked after 5 wrong passwords (15 min) or locked by an admin | Wait, or admin *Unlock* | Wrong attempts reset on next good sign-in |
| **Email unverified** | Link not clicked (only matters if verification is required) | Click the e-mail link / admin resends | Can sign in |

## 17.3 Attendance statuses
| Status | Meaning | What to do | Next |
|---|---|---|---|
| **Present** | Worked a full day, on time (or late within tolerance) | – | Paid day |
| **Late** | Arrived later than grace + late-mark tolerance | Be on time; request correction if wrong | Paid as present (late mark noted) |
| **Half Day** | Worked between the half-day and full-day minutes | – | Payroll: ½ present + ½ LOP |
| **Missed Punch** | Punched in but never punched out | **Request Regularization** | After approval the day is recalculated; payroll treats it as present |
| **Absent** | No work, no leave | Apply for leave if appropriate / ask HR | Payroll: 1 LOP day |
| **Holiday / Weekly Off** | Non-working day | – | Not counted as a working day |
| **Leave** | Approved paid leave | – | Payroll: paid leave |
| **Half Day Leave** | Approved half-day leave | – | Payroll: ½ present + ½ paid leave |
| **Loss of Pay (LOP)** | Approved leave of an unpaid type | – | Payroll: 1 LOP day |
| **Work From Home / On Duty** | Working remotely / on official duty | – | Paid as present |

## 17.4 Location (geofence) results
| Result | Meaning | What to do | Next |
|---|---|---|---|
| **Inside** | Within the office radius | – | Accepted |
| **Outside** | Beyond the radius | Go to the office (or ask HR if GPS is not required) | Rejected if *GPS required* is on; otherwise recorded |
| **Low accuracy** | Inside but GPS accuracy worse than 100 m | Move outdoors / near a window, retry | Accepted and flagged |
| **GPS disabled** | No coordinates received | Allow location, Retry | Rejected if *GPS required* is on |
| **Not applicable** | No office location configured, or not a GPS punch | – | Accepted |

## 17.5 Device statuses
| Status | Meaning | HR action |
|---|---|---|
| **Pending** | New device waiting for review | Approve, Reject or Block |
| **Approved** | Accepted | – |
| **Rejected** / **Blocked** | HR does not accept the device | Informational – punches are still accepted in this version |

## 17.6 Regularization, Overtime and Encashment (all use the same three)
| Status | Meaning | What to do | Next |
|---|---|---|---|
| **Pending** | Waiting for a decision | Approver reviews | Becomes Approved or Rejected |
| **Approved** | Accepted | – | Regularization → record corrected; Overtime → can be paid in payroll; Encashment → recorded |
| **Rejected** | Declined | Employee may raise a fresh request | Nothing changes |

## 17.7 Leave statuses
| Status | Meaning | What to do | Next |
|---|---|---|---|
| **Pending** | Awaiting Level 1 (manager) or Level 2 (HR) | Approvers act; employee may cancel | Approved / Rejected / Cancelled |
| **Approved** | Final approval given | – | Balance reduced; attendance updated; can be cancelled by HR/manager |
| **Rejected** | Refused at some level | Read the remarks; apply again if needed | Balance unchanged |
| **Cancelled** | Withdrawn or cancelled | – | If it had been approved, balance and attendance are restored |
| **Draft** | A saved but unsubmitted state (can appear in the status filter) | – | – |
| *Level 1 / Level 2 / Completed* | Which approval step a pending application is at | Approver at that level acts | Moves on after approval |

## 17.8 Payroll run statuses
| Status | Meaning | Next step |
|---|---|---|
| **Draft** | Being created | Becomes Generated |
| **Generated** | Calculated, awaiting review | Approve (or Cancel) |
| **Approved** | Figures confirmed; loans/advances/bonuses committed | Lock |
| **Locked** | Frozen | Mark Paid (or Unlock with a reason) |
| **Paid** | Salary paid; payslips announced | Final |
| **Cancelled** | Discarded before approval | Generate again |

## 17.9 Other money-related statuses
| Item | Statuses |
|---|---|
| Loans | **Active** (being repaid), **Closed** (fully repaid), **Foreclosed** (ended early) |
| Advances | **Active**, **Closed** |
| Bonus / Incentives / Arrears | **Pending** (will go into the next run) → **Paid** (included in an approved run) |
| Reimbursements | **Pending** → **Approved** (will go into the next run) or **Rejected** → **Paid** |
| Employee salary assignment | **Active**, **Scheduled** (starts in the future) |

## 17.10 Documents
| Status | Meaning |
|---|---|
| **Pending** | Uploaded (all new uploads) |
| **Verified** / **Rejected** | Badges exist but nothing in the HRMS can set them; you will normally see only *Pending* |
(There is no *Expired* status.)

## 17.11 Master data
**Active / Inactive** (branches, departments, designations, shifts, locations, holidays, leave types, policies, components, structures, weekly-off rules): Inactive items remain in history but cannot be newly chosen. **Archived** (employees, branches, departments, designations, roles): hidden from normal lists; **Restore** brings them back. **Biometric rows:** pending → synced / failed.

---

# 18. Common Problems and Solutions

| Problem | Normal reason | What to do |
|---|---|---|
| **I can't punch attendance** | (a) You punched less than a minute ago. (b) You are already punched in/out. (c) Location is blocked or you are outside while *GPS required* is on. (d) Your login is not linked to an employee. | Wait a minute; check the button says the right action; allow location and click *Retry location*; ask HR to link your login. |
| **GPS says "Outside"** | You are farther than the office radius, or the office location was entered with wrong coordinates/radius | Move closer. If you are definitely at the office, tell HR to check the **Location** coordinates and radius. |
| **GPS says "Low accuracy"** | The phone/computer cannot fix its position within 100 m (indoors, VPN, weak signal) | Step near a window or outside, turn on high-accuracy location, press *Retry location*. |
| **"Location needs HTTPS"** | Page opened with `http://` | Use the https:// address. |
| **Device requires approval / device shows Pending** | *Device approval required* is on and this is a new browser or phone | Nothing blocks your punch; HR will approve it under Attendance → Devices. |
| **Punch-in worked but my day shows "Missed Punch"** | There is no matching punch-out | Submit **Request Regularization** with the correct out time. |
| **Cannot submit regularization** | Reason is empty; date is in the future; attachment is not PDF/JPG/PNG | Fix the form and submit again. |
| **My regularization is still Pending** | Your manager/HR has not acted | Remind your manager; they see it under Attendance → Regularizations. |
| **Leave button / Apply Leave missing** | Your role lacks "Apply for leave", or your login is not linked to an employee | Ask HR/Admin. |
| **Cannot submit leave – "Insufficient leave balance"** | Not enough days | Choose another leave type or fewer days; HR can adjust a balance. |
| **"This date is too far in the future" / "at least N days notice" / "overlaps"** | Company leave rules | Adjust dates, apply earlier, or tick *Emergency leave* where genuinely urgent. |
| **Cannot cancel my leave** | You can cancel only **Pending** leave yourself | For approved leave ask HR or your manager to cancel it. |
| **I can't approve a leave** | You are not the assigned manager (Level 1), or the application is at Level 2 (HR only), or it is your own | Check the "Level" and who the employee's reporting manager is. |
| **A menu is missing** | Your role does not include that permission | Ask the administrator; it cannot be unlocked by you. |
| **"Permission/access denied" (403) page** | You opened a page your role is not allowed to use | Go back to the Dashboard; request access if needed. |
| **My menus changed / disappeared after someone edited a role** | The role's permissions were saved from the matrix (see warning in 10.2) | Tell the administrator which menus vanished. |
| **Cannot upload a document** | You don't have the document right (only Admin/HR do), file over 10 MB, or file type not PDF/JPG/PNG/WEBP/DOC/DOCX | Ask HR to upload it, or compress/convert the file. |
| **Document expired** | The system does not track this | HR must watch the Expiry column; upload the renewed document. |
| **Payslip unavailable / "No payslips yet"** | Payroll for that month has not been generated for you (no salary assigned, no attendance data, or not yet run) | Ask HR; they can see who was skipped when they generate the run. |
| **Payslip shows but status is not Paid** | The run is still being processed | Wait until it is Paid and you receive the notification. |
| **My payroll doesn't show** | Your login isn't linked to an employee, or you lack the payslip right (Managers don't have it by default) | Ask HR. |
| **My salary is lower than expected** | LOP days, half days, unmarked absences, loan EMI or advance recovery | Open your payslip: Attendance Summary, Earnings, Deductions. Ask HR if a day is wrong; they can correct attendance before the run is approved. |
| **Employee skipped in payroll** | No salary assignment / no attendance data for the period | HR: Payroll → Employee Salary → Assign; fix attendance; cancel and regenerate the draft run. |
| **Payroll "already generated for this month"** | One active run per month | Cancel the existing Draft/Generated run, or work with it. |
| **Forgot password** | – | Sign-in page → Forgot password → follow the e-mailed link (valid 30 minutes). No e-mail? Ask HR to **Send Reset Link** or generate a **temporary password**. |
| **"Account temporarily locked"** | 5 wrong passwords | Wait 15 minutes or ask HR/Admin to **Unlock**. |
| **Cannot sign in – "verify your email"** | Email verification is required | Use the link in your e-mail; ask Admin to resend it. |
| **Emails are not arriving** | E-mail server not configured | Admin: Settings → Company Settings → Email (SMTP) → send a test. Tell your IT team if it fails. |
| **Times look wrong** | Timezone settings | Admin: set the same timezone in Company Settings and Attendance Settings. |
| **Import file is rejected** | Wrong columns/date format, unknown employee codes, duplicates | Use *Download sample template*; read the Result column in the preview; fix and re-upload. |

---

# 19. Role-Based Guide

## 19.1 Administrator (Company Admin)
Has every permission. Typically:
- Sets up the company: Organization, Attendance Settings, Leave set-up, Payroll Settings, Company Settings, branding, e-mail check.
- Manages **users, roles and permissions**; unlocks/locks accounts; deletes users.
- Has the only ability by default to **Lock/Unlock** payroll and **Mark Paid**.
- Can **override** leave approvals (with a recorded reason).
- Reviews **Audit Logs** and Devices.
- Everything an HR Manager can do.

## 19.2 HR Manager
- **Employees:** add, edit, import, export, bank details, documents, status changes, login accounts.
- **Attendance:** company list, bulk mark, shifts and assignments, holidays, weekly off, locations, devices, imports (Excel and biometric), regularization approvals, reports and exports. *(No Attendance Settings.)*
- **Leave:** all applications (Level 2 approver), cancel any, balances and adjustments, carry forward, encashment approval, leave types, policies, settings, reports and exports.
- **Payroll:** salary components/structures, employee salary, loans, advances, bonus/incentives, reimbursements, arrears, generate and **approve** runs, payslips and ZIP, bank file, reports. *(No Lock, Mark Paid or Payroll Settings by default.)*
- **Administration:** create/edit users. *(No roles/permissions, company settings, audit logs.)*
- **Organization:** view branches, departments, designations.
- Has own self-service too: My Attendance, My Leave, My Payroll.

## 19.3 Manager
- **Own self-service:** My Attendance, Request Regularization, My Leave, apply/cancel own pending leave. *(No My Payroll by default.)*
- **Team:** approve/reject **Level 1 leave** and **attendance corrections** for people who report to them; see the Attendance list, Dashboard, Leave Applications, Calendar, Balances, Encashment list, Locations, and reports (view only – exports are not included by default); see the Employee list (view only).
- Gets a notification when a team member applies for leave.
- Can cancel any leave application, and approve/reject overtime entries.
- Cannot change settings, shifts, holidays, payroll or users.

## 19.4 Employee
- **Dashboard** with "My status today".
- **My Attendance:** punch in/out, see shift, recent punches; **Request Regularization**; view **Weekly Off** and **Holidays**.
- **My Leave:** apply, track, cancel while pending, view balance, calendar and history.
- **My Payroll:** payslips (download PDF), salary summary, loans, advances, reimbursements (read-only), tax summary note.
- **Profile:** edit name and mobile, change password, log out other devices.
- Quick search can look up colleagues' names/codes (name lookup only).
- Cannot see other people's data, the Employees list, documents, or any set-up screens.

> Your company may have changed these roles or added others. The sidebar you actually see is always the truth for *your* access.

---

# 20. A–Z Quick Reference

| Letter | Feature (where to find it) |
|---|---|
| **A** | **Attendance** (menu) · Advances (Payroll) · Arrears (Payroll) · Audit Logs (Settings) · Attendance Settings |
| **B** | **Branches** (Organization) · Balances (Leave) · Bank details (Employee profile) · Biometric Import · Bonus (Payroll) · Bulk mark |
| **C** | **Company Settings** · Carry Forward (Leave) · Change password · Calendar (Leave, Holidays) |
| **D** | **Dashboard** · Departments · Designations · Devices (Attendance) · Documents (Employee profile tab) |
| **E** | **Employees** · Employee Salary · Encashment (Leave) · Excel Import · Export · Emergency contacts |
| **F** | **Favorites** (sidebar ★) · Family details |
| **G** | **Generate payroll** · Geofence / GPS · Generate next-year holidays |
| **H** | **Holidays** · History (shift, salary, leave) |
| **I** | **Incentives** (Payroll) · Import (employees, attendance, holidays) |
| **J** | Joining date (employee field) |
| **K** | **Ctrl + K** quick search |
| **L** | **Leave** (menu) · Leave Types · Leave Policies · Loans · Locations · Late report · LOP · Logout · Lock/Unlock (payroll, account) |
| **M** | **My Attendance** · My Leave · My Payroll · My profile |
| **N** | **Notifications** (bell) · Night shift |
| **O** | **Organization** (menu) · Overtime |
| **P** | **Payroll** (menu) · Payslips · Permissions · Profile · PF / ESI / PT settings |
| **Q** | **Quick add** (+) |
| **R** | **Regularizations** · Reports · Roles · Reimbursements · Remember me |
| **S** | **Settings** (menu) · Salary Components · Salary Structures · Shifts · Shift Assignments · SMTP test · Status history |
| **T** | **Timezone** · TDS |
| **U** | **Users** |
| **V** | Verify email |
| **W** | **Weekly Off** · Working-days basis (Payroll Settings) |
| **X, Y, Z** | – (no features start with these letters) |

---

# Appendix A – Things this HRMS does not have

So you do not look for them:

- **Documents menu / categories / required-document rules / verification workflow / expiry alerts / "replace" action / employee self-upload** (documents are only an Admin/HR tab on the employee profile – Section 9).
- **Automatic Absent marking** – no job creates Absent records for days nobody punched.
- **Two-factor authentication** (the switch says "coming soon"); **SMS / WhatsApp / push** messages.
- **Income-tax declaration / full TDS computation** – only a flat TDS percentage and manual adjustments.
- **Employee-submitted** loans, advances or reimbursements (HR enters them).
- **Company colour customisation** – brand colour is managed by the platform administrator; there are no sidebar-colour, header-colour or button-radius settings.
- **Blocking punches by device status**; the *Self attendance enabled* and *Lock payroll after approval* switches currently have no visible effect (15.7, 15.42).
- **Automatic monthly leave accrual** – the annual allocation is credited up front (6.9).
- **A separate language, week-start or font behaviour** – the options are stored but most screens do not visibly change.
- A **Department-level or branch-level** filter on most Payroll and Attendance reports (use Excel after export).
