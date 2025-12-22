// utils/helpdesk-mappers.ts
export function mapTicket(apiTicket: any) {
    return {
        id: apiTicket.id,
        url: apiTicket.uuid, // used in /dashboard/helpdesk/[url]
        title: apiTicket.title,
        status: apiTicket.status?.label ?? apiTicket.status, // e.g. "Open"
        statusValue: apiTicket.status?.value ?? null,        // e.g. "open"
        priority: apiTicket.priority?.label ?? apiTicket.priority, // "High"
        priorityValue: apiTicket.priority?.value ?? null,          // "high"
        createdAt: new Date(apiTicket.created_at).toLocaleString(),
        description: apiTicket.description,
        topic: apiTicket.topic ?? null
    }
}
