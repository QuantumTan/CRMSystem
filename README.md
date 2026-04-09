# CRM System

A modern, full-stack customer relationship management platform built to streamline lead handling, customer management, assignment workflows, follow-ups, and reporting. Helps teams track the complete sales journey from first contact to customer conversion — with activity history and role-based access controls in one clean interface.

---

## Table of Contents

- [Features by Role](#features-by-role)
  - [Sales](#sales)
  - [Manager](#manager)
  - [Admin](#admin)
- [Core Workflows](#core-workflows)
- [Tech Stack](#tech-stack)

---

## Features by Role

### Sales

#### Dashboard
- View personal pipeline metrics and workload snapshot
- See personal customer, lead, and follow-up counts
- Review recent activities and upcoming follow-ups

#### Leads
- Create new leads with source, priority, and expected value
- View assigned leads in list and Kanban board views
- Move lead status across pipeline stages
- Mark leads as lost with required category and reason
- Reopen lost leads back into active pipeline
- Convert won leads into customers
- Update lead details and priority for assigned records

#### Customers
- Create and view assigned customer records
- Update assigned customer details
- Work within assignment approval rules

#### Activities
- Log calls, emails, meetings, and notes
- Attach activities to either a lead or a customer
- View and update activities linked to assigned entities

#### Follow-ups
- Create follow-ups for assigned leads and customers
- Set due dates and statuses
- Mark follow-ups as completed
- Edit pending follow-ups assigned to you

---

### Manager

#### Dashboard
- View team-level CRM metrics and status overview
- Monitor lead flow and follow-up health
- Track recent activities and upcoming follow-ups

#### Customer Oversight
- View customer records across the system
- Approve, reject, or reassign customer assignments

#### Monitoring
- View leads across all pipeline stages
- Monitor activities and follow-up lists for the team
- Access consolidated operational visibility

#### Reports
- View reports with date filters
- Export reports to CSV or PDF

---

### Admin

#### Dashboard
- Full system overview with key CRM metrics
- Access to all modules and admin controls

#### User Management
- Create, update, and soft-delete user accounts
- Assign and manage roles: `admin`, `manager`, `sales`

#### Customers
- Full CRUD access
- Manage assignment workflows, approvals, and ownership reassignment

#### Leads
- Full CRUD access
- Assign leads to sales staff
- Manage statuses, priorities, loss handling, and conversions

#### Activities & Follow-ups
- Full activity and follow-up management
- Reopen completed follow-ups when needed

#### Reports
- Full report access with date filtering
- CSV and PDF export support

---

## Core Workflows

### Lead Pipeline

```
new → contacted → qualified → proposal_sent → negotiation → won / lost
```

### Lead-to-Customer Conversion

- Only `won` leads can be converted
- Conversion creates a customer record from lead data
- Related activities and follow-ups are transferred to the new customer

### Customer Assignment Workflow

```
pending → approved / rejected
```

### Access Model

- Authentication via Laravel Fortify
- Route-level role middleware
- Policy and scope-based data visibility
- Sales access restricted to assigned records in key modules

---

## Tech Stack

### Frontend

![Blade](https://img.shields.io/badge/Blade-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![Vite](https://img.shields.io/badge/Vite-646CFF?style=for-the-badge&logo=vite&logoColor=white)
![Sass](https://img.shields.io/badge/Sass-CC6699?style=for-the-badge&logo=sass&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap_5-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)

### Backend

![Laravel](https://img.shields.io/badge/Laravel_12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP_8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)

### Exports & Reporting

![Excel](https://img.shields.io/badge/Maatwebsite_Excel-217346?style=for-the-badge&logo=microsoftexcel&logoColor=white)
![PDF](https://img.shields.io/badge/Dompdf-CC0000?style=for-the-badge&logo=adobeacrobatreader&logoColor=white)

### Testing & Quality

![Pest](https://img.shields.io/badge/Pest-EF3B2D?style=for-the-badge&logo=testinglibrary&logoColor=white)
![Pint](https://img.shields.io/badge/Laravel_Pint-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
