export type AdminNavigationItem = {
    label: string;
    description: string;
    href: string;
    icon: string;
    badge: string | null;
    external: boolean;
};

export type AdminNavigationGroup = {
    label: string;
    items: AdminNavigationItem[];
};

export type AdminConsoleContext = {
    user: {
        name: string;
        email: string;
        role: string | null;
        roleLabel: string;
        initials: string;
    };
    navigation: AdminNavigationGroup[];
    searchUrl: string;
    dashboardUrl: string;
    logoutUrl: string;
    filamentFallbackEnabled: boolean;
    environment: string;
};

export type AdminPaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

export type AdminPaginated<T> = {
    data: T[];
    links: {
        first?: string | null;
        last?: string | null;
        prev?: string | null;
        next?: string | null;
    };
    meta: {
        current_page: number;
        from: number | null;
        last_page: number;
        links: AdminPaginationLink[];
        path: string;
        per_page: number;
        to: number | null;
        total: number;
    };
};
