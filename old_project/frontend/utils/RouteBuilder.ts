import { computed } from 'vue'
import { useRoute } from 'vue-router'

type UserType = 'regular' | 'member' | 'promoter' | 'advisor' | 'mentor' | 'applicant'
type UserStatus =
    | 'draft' | 'incomplete' | 'in_review' | 'pending' | 'active'
    | 'suspended' | 'inactive' | 'banned' | 'subscribed' | 'unsubscribed'

export interface NavLinkOptions {
    to?: string
    label?: string
    icon?: string
    badge?: string | (() => string | number)
    group?: string
    order?: number
    auth?: boolean
    types?: UserType[] | '*'
    status?: UserStatus[] | '*'
    currentUrl?: string | string[]
    groupBadge?: string | number
    extra?: any
}


export class RouteBuilder {
    private links: NavLinkOptions[] = []

    // ----- LINK BUILDER -----
    link(to?: string, label?: string) {
        this.links.push({
            to: to || '#?url_not_provided',
            label: label || 'New Link',
            order: this.links.length + 1, // default order
        })
        return this
    }

    order(value: number) {
        this.lastLink().order = value
        return this
    }

    auth(auth = true) {
        this.lastLink().auth = auth
        return this
    }

    status(status: UserStatus[] | '*') {
        this.lastLink().status = status
        return this
    }

    type(types: UserType[] | '*') {
        this.lastLink().types = types
        return this
    }

    icon(icon: string) {
        this.lastLink().icon = icon
        return this
    }

    badge(badge: string | (() => string | number)) {
        this.lastLink().badge = badge
        return this
    }

    currentUrl(url: string | string[]) {
        this.lastLink().currentUrl = url
        return this
    }

    prefix(prefix: string) {
        const last = this.lastLink()
        if (last.to) last.to = `${prefix}${last.to}`
        return this
    }

    // ----- GROUP BUILDER -----
    group(name: string, icon?: string, cb?: (rb: RouteBuilder) => void, groupBadge?: string | number) {
        const groupBuilder = new RouteBuilder()
        cb?.(groupBuilder)
        const groupLinks = groupBuilder.getLinks()
        groupLinks.forEach((l) => (l.group = name))
        groupLinks.forEach((l) => (l.icon = l.icon || icon || 'material-symbols:folder'))
        // assign group badge to first link for rendering
        if (groupBadge !== undefined && groupLinks.length) {
            groupLinks[0].groupBadge = groupBadge
        }
        this.links.push(...groupLinks)
        return this
    }

    // ----- GETTERS -----
    getLinks() {
        return this.links
    }

    getGroups() {
        const map: Record<string, NavLinkOptions[]> = {}
        this.links.forEach((l) => {
            if (l.group) {
                if (!map[l.group]) map[l.group] = []
                map[l.group].push(l)
            }
        })
        return map
    }

    getCommon() {
        return this.links.filter((l) => !l.group)
    }

    // ----- FILTER BY CURRENT USER & URL -----
    filterLinks(user: { type: string; status: string } | null) {
        const route = useRoute()
        const path = route.path

        return this.links.filter((l) => {
            if (l.currentUrl) {
                const urls = Array.isArray(l.currentUrl) ? l.currentUrl : [l.currentUrl]
                if (!urls.some((u) => path.startsWith(u))) return false
            }
            if (l.auth && !user) return false
            if (l.types && l.types !== '*') {
                const typeMatch = user && l.types.includes(user.type.toLowerCase() as UserType)
                if (!typeMatch) return false
            }
            if (l.status && l.status !== '*') {
                const statusMatch = user && l.status.includes(user.status.toLowerCase() as UserStatus)
                if (!statusMatch) return false
            }
            return true
        })
    }

    private lastLink() {
        if (!this.links.length) throw new Error('No link defined yet.')
        return this.links[this.links.length - 1]
    }

    // ----- IMPORT / EXPORT -----
    importLinks(links: NavLinkOptions[]) {
        this.links.push(...links)
        return this
    }
}
