import { PermissionType } from '@/types/permission';

export type RoleType = {
    id: number;
    name: string;
    permissions: PermissionType[];
    created_at: string;
    updated_at: string;
};
