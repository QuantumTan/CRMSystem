# CRM System

CRM System is a modern, full-stack customer relationship management platform built to streamline lead handling, customer management, assignment workflows, follow-ups, and reporting. It helps teams track the complete sales journey from first contact to customer conversion while keeping activity history and role-based access controls in one clean interface.

## Table of contents

- Sales features
- Manager features
- Admin features
- Core workflows
- Tech stack

## Sales Features

### Dashboard

- View personal pipeline metrics and workload snapshot
- See personal customer, lead, and follow-up counts
- Review recent activities and upcoming follow-ups

### Leads

- Create new leads with source, priority, and expected value
- View assigned leads in list and Kanban board views
- Move lead status across pipeline stages
- Mark leads as lost with required category and reason
- Reopen lost leads back into active pipeline
- Convert won leads into customers
- Update lead details and priority for assigned records

### Customers

- Create customer records
- View assigned customers
- Update assigned customer details
- Work within assignment approval rules

### Activities

- Log calls, emails, meetings, and notes
- Attach activities to either a lead or a customer
- View and update activities linked to assigned entities

### Follow-ups

- Create follow-ups for assigned leads and customers
- Set due dates and statuses
- Mark follow-ups as completed
- Edit pending follow-ups assigned to you

## Manager Features

### Dashboard

- View team-level CRM metrics and status overview
- Monitor lead flow and follow-up health
- Track recent activities and upcoming follow-ups

### Customer Oversight

- View customer records across the system
- Review assignment decisions
- Approve customer assignments
- Reject customer assignments
- Reassign customers to sales staff

### Monitoring Views

- View leads across pipeline stages
- View activities and follow-up lists for monitoring
- Access consolidated operational visibility

### Reports

- View reports with date filters
- Export reports to CSV
- Export reports to PDF

## Admin Features

### Dashboard

- Full system overview with key CRM metrics
- Access all modules and admin controls

### User Management

- Create user accounts
- Update user profile and role assignments
- Delete users (soft delete)
- Manage roles: admin, manager, sales

### Customers

- Full create, read, update, and delete access
- Manage assignment workflow and approvals
- Reassign ownership across sales users

### Leads

- Full create, read, update, and delete access
- Assign leads to sales staff
- Manage statuses, priorities, loss handling, and conversions

### Activities and Follow-ups

- Full activity management
- Full follow-up management
- Reopen completed follow-ups when needed

### Reports

- Full report access with date filtering
- CSV and PDF export support

## Core Workflows

### Lead pipeline

- new
- contacted
- qualified
- proposal_sent
- negotiation
- won
- lost

### Lead to customer conversion

- Only won leads can be converted
- Conversion creates a customer from lead data
- Related activities and follow-ups are moved to the customer

### Customer assignment workflow

- pending
- approved
- rejected

### Access model

- Authentication handled by Laravel Fortify
- Route-level role middleware
- Policy and scope-based data visibility
- Sales access restricted to assigned records in key modules

## Tech stack

### Frontend

- Blade templates
- Vite
- Sass
- Bootstrap 5
- Bootstrap Icons

### Backend

- Laravel 12
- PHP 8.2+
- Eloquent ORM
- MySQL-compatible relational database
- Laravel Fortify

### Exports and Reporting

- Maatwebsite Excel for CSV exports
- Dompdf for PDF exports

### Testing and Quality

- Pest
- Laravel Pint

