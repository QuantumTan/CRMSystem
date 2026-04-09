# CRM Notes (Current System Behavior)

## Lead Lifecycle

- New lead starts with status: `new`
- First contact made: `contacted`
- Prospect is ready/qualified: `qualified`
- Proposal sent: `proposal_sent`
- Ongoing deal discussion: `negotiation`
- Deal closed: `won`
- Lead can then be converted into a customer
- If the deal does not proceed: `lost`
- Lost leads can be reopened and moved back to `contacted`

## Conversion Rule

- Only leads with status `won` can be converted to a customer
- Conversion moves lead activities and follow-ups to the new customer

## Module Flow

Leads Module
-> Activity/Interaction Log Module
-> Follow-ups Module
-> Dashboard Module
-> Reports (CSV/PDF export)

## Roles Snapshot

- `admin`: full access, user management, reopen completed follow-ups
- `manager`: monitor dashboards, assignment review, reports
- `sales`: manage assigned leads/customers, activities, and follow-ups